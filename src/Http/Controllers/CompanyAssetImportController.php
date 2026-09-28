<?php

namespace ME\Hr\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use ME\Hr\Services\CompanyAssetImportService;

class CompanyAssetImportController extends Controller
{
    public function __construct(private readonly CompanyAssetImportService $importer)
    {
    }

    public function create(): View
    {
        return view('hr::company-assets.import');
    }

    public function preview(Request $request): JsonResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls']]);

        return response()->json($this->importer->preview($request->file('file')));
    }

    public function save(Request $request): JsonResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls']]);

        return response()->json($this->importer->import($request->file('file')));
    }

    public function recheck(Request $request): JsonResponse
    {
        $data = $request->validate(['row' => ['required']]);

        return response()->json(['row' => $data['row'], ...$this->importer->validateRow($request->input('fields', []))]);
    }

    /**
     * Route: GET /company-assets/import/template
     */
    public function template()
    {
        if (! class_exists(\PhpOffice\PhpSpreadsheet\Spreadsheet::class)) {
            abort(501, 'Excel import requires the phpoffice/phpspreadsheet package. Run "composer require phpoffice/phpspreadsheet" in this application, then try again.');
        }

        // Full register column set (matches the company's own asset-register spreadsheet
        // layout exactly, A-S) — not just the importable subset. The 5 calculated columns
        // (Total Acquisition Cost, Age, Annual/Accumulated Depreciation, Net Book Value)
        // are included for reference/consistency with that layout but are never read on
        // import (CompanyAssetImportService's column matching doesn't recognize their
        // headings), so filling them in has no effect — they're always system-computed.
        $headers = [
            'Asset ID', 'Category', 'Item', 'Brand', 'Model', 'Quantity', 'Location / Dept',
            'Purchase Date', 'Supplier / Vendor', 'Unit Cost (BDT)', 'Total Acquisition Cost (BDT)',
            'Useful Life (Years)', 'Depreciation Method', 'Salvage Value (BDT)', 'Age (Years)',
            'Annual Depreciation ($)', 'Accumulated Depreciation (BDT)', 'Net Book Value (BDT)', 'Status',
        ];
        $calculatedColumns = [
            'Total Acquisition Cost (BDT)', 'Age (Years)', 'Annual Depreciation ($)',
            'Accumulated Depreciation (BDT)', 'Net Book Value (BDT)',
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        $instructions = $spreadsheet->getActiveSheet();
        $instructions->setTitle('Instructions');
        $instructions->fromArray([
            ['Company Asset Import — Instructions'],
            [''],
            ['The "Assets" sheet already has 5 example rows filled in — replace or delete them, then add your own, one row per asset.'],
            ['Only "Item" is required — everything else is optional.'],
            ['Leave "Asset ID" blank for a new asset; one will be assigned automatically.'],
            ['To UPDATE an existing asset, put its existing Asset ID in that column — the row will update that asset instead of creating a new one.'],
            ['Category and Location / Dept are matched by name and created automatically if they don\'t already exist.'],
            ['Status must be one of: '.implode(', ', \ME\Hr\Models\HrCompanyAsset::STATUSES).' (defaults to "In Use" if blank or unrecognized).'],
            ['The grey columns (Total Acquisition Cost, Age, Annual Depreciation, Accumulated Depreciation, Net Book Value) are calculated automatically by the system — leave them blank, anything typed there is ignored on import.'],
        ], null, 'A1');
        $instructions->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $instructions->getColumnDimension('A')->setWidth(100);

        // Sample rows so the sheet is never handed over blank — left in place, the row
        // count (or blank Asset ID) makes it obvious these were just examples to replace.
        // Left/right blank cells for the calculated columns on purpose (see note above).
        $sampleRows = [
            ['SFL-01-1001', 'Furniture', 'Q.I Table', '', '', 7, 'Sewing Section', '2024-01-15', '', 7500, '', 5, 'Straight-Line', 500, '', '', '', '', 'In Use'],
            ['SFL-01-1002', 'Furniture', 'Marking Table', '', '', 11, 'Sewing Section', '2024-01-15', '', 4500, '', 5, 'Straight-Line', '', '', '', '', '', 'In Use'],
            ['SFL-01-1003', 'Furniture', 'Trolley', '', '', 17, 'Sewing Section', '2024-01-15', '', 2700, '', 5, 'Straight-Line', '', '', '', '', '', 'In Use'],
            ['SFL-01-1004', 'Furniture', 'Operator Chair', '', '', 82, 'Sewing Section', '2024-01-15', '', 750, '', 5, 'Straight-Line', '', '', '', '', '', 'In Use'],
            ['SFL-01-1005', 'Furniture', 'Bench', '', '', 23, 'Sewing Section', '2024-01-15', '', 700, '', 5, 'Straight-Line', '', '', '', '', '', 'In Use'],
        ];

        $assetsSheet = $spreadsheet->createSheet();
        $assetsSheet->setTitle('Assets');
        $assetsSheet->fromArray($headers, null, 'A1');
        $assetsSheet->fromArray($sampleRows, null, 'A2');
        $assetsSheet->getStyle('A1:'.\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)).'1')
            ->getFont()->setBold(true);
        foreach (range(1, count($headers)) as $colIndex) {
            $assetsSheet->getColumnDimensionByColumn($colIndex)->setAutoSize(true);

            if (in_array($headers[$colIndex - 1], $calculatedColumns, true)) {
                $letter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $assetsSheet->getStyle($letter.'1:'.$letter.(count($sampleRows) + 1))->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('D9D9D9');
            }
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'company-asset-import-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            // The URL never changes between edits to this template, so without this the
            // browser can silently keep serving a stale cached download (exactly what
            // happened here) instead of hitting the server for the current version.
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }
}
