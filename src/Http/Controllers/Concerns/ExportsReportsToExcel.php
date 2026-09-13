<?php

namespace ME\Hr\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Html as HtmlReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Lets any *Print report action double as an "Export Excel" action, reusing the
 * exact same print Blade view/data with zero per-report mapping code — the view is
 * rendered to HTML (its @section('contents') only, not the printMaster2 page chrome
 * like the Print/Close buttons or logo), fed through PhpSpreadsheet's HTML reader,
 * and streamed back as a real .xlsx.
 *
 * phpoffice/phpspreadsheet is a *suggested*, not required, dependency of this
 * package (see composer.json) — a host app that hasn't installed it simply gets a
 * clear 501 instead of a fatal class-not-found error, so reports keep working with
 * only the "Export Excel" button unavailable.
 */
trait ExportsReportsToExcel
{
    /**
     * Renders $view as usual, unless the request asks for an Excel export (?xlsx=1),
     * in which case the same view/data is converted to a downloadable .xlsx instead.
     */
    protected function viewOrXlsx(Request $request, string $view, array $payload, string $filenamePrefix)
    {
        if (!$request->boolean('xlsx')) {
            return view($view, $payload);
        }

        if (!class_exists(HtmlReader::class)) {
            abort(501, 'Excel export requires the phpoffice/phpspreadsheet package. Run "composer require phpoffice/phpspreadsheet" in this application, then try again.');
        }

        // renderSections() runs the view but hands back each @section's captured
        // content as a string WITHOUT wrapping it in the parent (printMaster2) layout
        // — this is what keeps the exported sheet to just the report's own tables,
        // instead of also carrying the page header, logo, and no-print button bar.
        $sections = view($view, $payload)->renderSections();
        $contentsHtml = $sections['contents'] ?? implode('', $sections);

        // Belt-and-braces: PhpSpreadsheet's HTML reader escapes non-ASCII characters via a
        // preg_replace_callback() with the /u (UTF-8) modifier, which returns null — not an
        // error, just null — the instant it hits a single invalid UTF-8 byte anywhere in the
        // string. Report data (names, addresses, geo-location names, etc., often
        // Bengali-script and sometimes imported from legacy sources) can contain a stray
        // invalid byte that a browser renders fine but that call can't tolerate. Re-encoding
        // UTF-8 to UTF-8 replaces any ill-formed bytes while leaving valid text untouched.
        $contentsHtml = mb_convert_encoding($contentsHtml, 'UTF-8', 'UTF-8');

        // The actual cause of "Failed to load content as a DOM Document": DOMDocument's
        // HTML parser only *warns* (via PHP's E_WARNING) on recoverable markup issues like
        // an unescaped "&" in ordinary text (e.g. a table header reading "Car & Fuel") — it
        // still parses the document. But Laravel's error handler turns that E_WARNING into a
        // thrown ErrorException, which PhpSpreadsheet's loadFromString() catches as a
        // Throwable and reports as a hard failure, even though the parse actually succeeded.
        // libxml_use_internal_errors(true) redirects libxml's warnings into its own internal
        // buffer instead of raising a PHP warning, so they never reach Laravel's handler.
        $previousLibxmlSetting = libxml_use_internal_errors(true);

        $reader = new HtmlReader();

        try {
            $spreadsheet = $reader->loadFromString('<!doctype html><html><body>' . $contentsHtml . '</body></html>');
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlSetting);
        }

        // The HTML reader never sets column widths, so every column opens at Excel's
        // narrow default — long text truncates and any date/number column shows as a
        // solid "####" block until manually widened. Auto-sizing every column to its
        // content on every sheet fixes that without needing per-report column maps.
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }
            $sheet->calculateColumnWidths();
        }

        $filename = $filenamePrefix . '-' . now()->format('Y-m-d_His') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
