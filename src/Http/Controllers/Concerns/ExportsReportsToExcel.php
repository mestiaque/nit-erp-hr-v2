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
    private static string $xlsxImageMarker = '__XLSX_IMG__';

    /**
     * Replaces every image marker cell with a thumbnail anchored in that cell, and
     * sizes the row/column so the picture sits inside it rather than over its neighbours.
     */
    private function placeXlsxImages(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        $thumbPx = 56;
        $imageColumns = [];
        $thumbs = [];
        foreach ($sheet->getCellCollection()->getCoordinates() as $coordinate) {
            $cell = $sheet->getCell($coordinate);
            $value = $cell->getValue();
            if (!is_string($value) || !preg_match('/' . self::$xlsxImageMarker . '([LP])([A-Za-z0-9+\/=]+)__/', $value, $m)) {
                continue;
            }
            $cell->setValue(trim(str_replace($m[0], '', $value)) ?: null);

            // A photo whose file is missing falls back to the default avatar, so every
            // row still gets its thumbnail (same as the print view's placeholder).
            $path = $this->resolveXlsxImagePath((string) base64_decode($m[2]))
                ?? ($m[1] === 'P' ? $this->resolveXlsxImagePath('medies/profile.png') : null);
            if ($path === null) {
                continue;
            }
            $path = $thumbs[$path] ??= $this->makeXlsxThumbnail($path, $thumbPx * 2);

            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setPath($path);
            $drawing->setResizeProportional(true);
            $drawing->setHeight($thumbPx);
            if ($drawing->getWidth() > $thumbPx) {
                $drawing->setWidth($thumbPx);
            }
            $drawing->setCoordinates($coordinate);
            $drawing->setOffsetX(3);
            $drawing->setOffsetY(3);
            $drawing->setWorksheet($sheet);

            [$col, $row] = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::coordinateFromString($coordinate);
            $sheet->getRowDimension((int) $row)->setRowHeight(($thumbPx + 6) * 0.75);
            $sheet->getColumnDimension($col)->setAutoSize(false)->setWidth(9.5);
            $imageColumns[$col] = true;
            $sheet->getStyle($coordinate)->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        }

        return $imageColumns;
    }

    /** Temp thumbnail files to delete once the workbook has been written. */
    private array $xlsxTempFiles = [];

    /**
     * Uploaded photos are often multi-MB originals; embedding them as-is makes a
     * 200-row sheet ~20MB. Downscale to a small JPEG (falls back to the original
     * when GD can't read it).
     */
    private function makeXlsxThumbnail(string $path, int $maxPx): string
    {
        if (!function_exists('imagecreatefromstring') || !($img = @imagecreatefromstring((string) file_get_contents($path)))) {
            return $path;
        }
        [$w, $h] = [imagesx($img), imagesy($img)];
        $scale = min(1, $maxPx / max($w, $h));
        $thumb = imagecreatetruecolor(max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale)));
        imagefill($thumb, 0, 0, imagecolorallocate($thumb, 255, 255, 255));
        imagecopyresampled($thumb, $img, 0, 0, 0, 0, imagesx($thumb), imagesy($thumb), $w, $h);

        $file = tempnam(sys_get_temp_dir(), 'xlsx-img-') . '.jpg';
        imagejpeg($thumb, $file, 85);
        $this->xlsxTempFiles[] = $file;

        return $file;
    }

    /**
     * Maps an <img src> (absolute URL or public-relative path) to a readable local file.
     */
    private function resolveXlsxImagePath(string $src): ?string
    {
        if ($src === '' || str_starts_with($src, 'data:')) {
            return null;
        }
        $path = ltrim(rawurldecode((string) (parse_url($src, PHP_URL_PATH) ?? $src)), '/');
        $candidates = [public_path($path), base_path($path)];
        // /storage/... URLs are served through the public/storage symlink; read the
        // real file directly in case that link is missing on this install.
        if (($pos = strpos($path, 'storage/')) !== false) {
            $candidates[] = storage_path('app/public/' . substr($path, $pos + 8));
        }
        foreach ($candidates as $candidate) {
            if (is_file($candidate) && @getimagesize($candidate)) {
                return $candidate;
            }
        }
        // Sub-folder installs (e.g. /erp-suhana/public/medies/...): retry after the public/ segment.
        if (($pos = strpos($path, 'public/')) !== false) {
            $candidate = public_path(substr($path, $pos + 7));
            if (is_file($candidate) && @getimagesize($candidate)) {
                return $candidate;
            }
        }
        return null;
    }

    /**
     * Renders $view as usual, unless the request asks for an Excel export (?xlsx=1),
     * in which case the same view/data is converted to a downloadable .xlsx instead.
     */
    protected function viewOrXlsx(Request $request, string $view, array $payload, string $filenamePrefix, ?callable $customizeSheet = null)
    {
        if (!$request->boolean('xlsx')) {
            return view($view, $payload);
        }

        if (!class_exists(HtmlReader::class)) {
            abort(501, 'Excel export requires the phpoffice/phpspreadsheet package. Run "composer require phpoffice/phpspreadsheet" in this application, then try again.');
        }

        // Lets a view swap print-only markup (flex header blocks, signature boxes) for
        // plain table rows that read cleanly as spreadsheet cells.
        $payload['isExcelExport'] = true;

        // renderSections() runs the view but hands back each @section's captured
        // content as a string WITHOUT wrapping it in the parent (printMaster2) layout
        // — this is what keeps the exported sheet to just the report's own tables,
        // instead of also carrying the page header, logo, and no-print button bar.
        $sections = view($view, $payload)->renderSections();
        $contentsHtml = $sections['contents'] ?? implode('', $sections);

        // The HTML reader drops <img> tags in at their natural size, floating over the
        // grid (one huge avatar covering a dozen rows instead of a thumbnail per row).
        // Swap each one for a text marker carrying its src; placeImages() below turns
        // every marker back into a small picture anchored inside its own cell.
        $contentsHtml = preg_replace_callback('/<img\b[^>]*>/i', function ($m) {
            if (!preg_match('/\bsrc\s*=\s*["\']([^"\']+)["\']/i', $m[0], $src)) {
                return '';
            }
            // Logos get no avatar fallback when their file is missing ("L" flag).
            $flag = preg_match('/\balt\s*=\s*["\'][^"\']*logo/i', $m[0]) ? 'L' : 'P';
            return self::$xlsxImageMarker . $flag . base64_encode(html_entity_decode($src[1])) . '__';
        }, $contentsHtml);

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

        // Views print amounts through number_format() ("13,500"), which the reader keeps
        // as text — Excel then can't sum them and flags every cell. Turn any
        // thousands-grouped numeric string back into a real number, keeping the same
        // grouped display via a number format.
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            foreach ($sheet->getCellCollection()->getCoordinates() as $coordinate) {
                $cell = $sheet->getCell($coordinate);
                $value = $cell->getValue();
                if (!is_string($value) || !preg_match('/^-?\d{1,3}(,\d{3})+(\.\d+)?$/', trim($value), $m)) {
                    continue;
                }
                $cell->setValue((float) str_replace(',', '', trim($value)));
                $decimals = isset($m[2]) ? strlen($m[2]) - 1 : 0;
                $sheet->getStyle($coordinate)->getNumberFormat()
                    ->setFormatCode($decimals > 0 ? '#,##0.' . str_repeat('0', $decimals) : '#,##0');
            }
        }

        // The HTML reader never sets column widths, so every column opens at Excel's
        // narrow default — long text truncates and any date/number column shows as a
        // solid "####" block until manually widened. Auto-sizing every column to its
        // content on every sheet fixes that without needing per-report column maps.
        // (Image columns keep the fixed thumbnail width placeXlsxImages() gave them.)
        foreach ($spreadsheet->getAllSheets() as $sheet) {
            $imageColumns = $this->placeXlsxImages($sheet);
            $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());
            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                if (!isset($imageColumns[Coordinate::stringFromColumnIndex($col)])) {
                    $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
                }
            }
            $sheet->calculateColumnWidths();
        }

        if ($customizeSheet) {
            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $customizeSheet($sheet);
            }
        }

        $filename = $filenamePrefix . '-' . now()->format('Y-m-d_His') . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        $tempFiles = $this->xlsxTempFiles;
        $this->xlsxTempFiles = [];

        return response()->streamDownload(function () use ($writer, $tempFiles) {
            try {
                $writer->save('php://output');
            } finally {
                foreach ($tempFiles as $file) {
                    @unlink($file);
                    @unlink(substr($file, 0, -4));
                }
            }
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
