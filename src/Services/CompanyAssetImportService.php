<?php

namespace ME\Hr\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use ME\Hr\Models\HrAssetCategory;
use ME\Hr\Models\HrAssetLocation;
use ME\Hr\Models\HrCompanyAsset;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Parses the "Assets" sheet of the company-asset-import-template.xlsx (or any file with a
 * matching sheet name/columns). Mirrors mestiaque/acc-sfl's ExpenseImportService's row-by-row,
 * self-contained-error design (preview -> edit-and-recheck -> save), so this import follows
 * the same pattern as the Accounts module's Expense Import.
 *
 * Unlike Expense import (which only ever creates new rows), a row here upserts by Asset ID:
 * an Asset ID that already exists in the register updates that row instead of duplicating it
 * - the company's own register template pre-assigns Asset IDs, so re-importing an edited
 * export is expected to update in place.
 */
class CompanyAssetImportService
{
    private const SHEET_NAME = 'Assets';

    private const REQUIRED_HEADINGS = ['Item'];

    private const HEADINGS = [
        'Asset ID', 'Category', 'Item', 'Brand', 'Model', 'Quantity', 'Location / Dept',
        'Purchase Date', 'Supplier / Vendor', 'Unit Cost', 'Useful Life (Years)',
        'Depreciation Method', 'Salvage Value', 'Status',
    ];

    public function preview(UploadedFile $file): array
    {
        return $this->process($file, save: false);
    }

    public function import(UploadedFile $file): array
    {
        return $this->process($file, save: true);
    }

    private function process(UploadedFile $file, bool $save): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Throwable $e) {
            return ['error' => 'Could not read this file. Please make sure it is a valid .xlsx/.xls file.', 'rows' => []];
        }

        // formatData=false: read each cell's raw underlying value, not its display
        // string. With formatData=true, a currency-formatted cell (e.g. a custom BDT
        // format PhpSpreadsheet can't render cleanly) can leak its raw format mask
        // (e.g. "[$BDT] * #,##0") into the value instead of the number; dates still
        // parse fine as raw values since parseDate() below already handles numeric
        // Excel serials via ExcelDate::excelToDateTimeObject().
        $sheet = $this->findSheet($spreadsheet, self::SHEET_NAME);
        $grid = $sheet->toArray(null, true, false, false);

        if (empty($grid)) {
            return ['error' => 'The sheet appears to be empty.', 'rows' => []];
        }

        // Headings are matched by keyword, ignoring a "(...)" unit suffix — so "Unit Cost"
        // and "Unit Cost (BDT)" both resolve the same column, and re-ordered columns in the
        // uploaded sheet still work (not tied to fixed column letters). Applied to BOTH the
        // uploaded header row and self::HEADINGS itself — 'Useful Life (Years)' has its own
        // "(...)" suffix baked into the expected heading, so without normalizing that side
        // too, "useful life" (stripped) would never contain "useful life (years)" (not
        // stripped) and the column would never match.
        $normalizeHeading = function ($h) {
            $normalized = strtolower(trim((string) preg_replace('/\(.*?\)/', '', (string) $h)));

            return preg_replace('/\s+/', ' ', $normalized);
        };

        // The header row isn't always row 1 — a company letterhead/title (e.g. "Suhana
        // Fashions Limited" / "Asset Register") sitting above the real column headers is
        // common in a hand-maintained register export, so the first several rows are each
        // tried as a candidate header and the one matching the most of self::HEADINGS wins.
        // Everything at or above that row is then dropped as front-matter, not data.
        $headerRowIndex = 0;
        $bestMatchCount = -1;
        $scanLimit = min(count($grid), 15);
        for ($i = 0; $i < $scanLimit; $i++) {
            $candidate = array_map($normalizeHeading, $grid[$i]);
            $matchCount = 0;
            foreach (self::HEADINGS as $heading) {
                $needle = $normalizeHeading($heading);
                foreach ($candidate as $label) {
                    if ($label !== '' && str_contains($label, $needle)) {
                        $matchCount++;
                        break;
                    }
                }
            }
            if ($matchCount > $bestMatchCount) {
                $bestMatchCount = $matchCount;
                $headerRowIndex = $i;
            }
        }

        $header = array_map($normalizeHeading, $grid[$headerRowIndex]);
        $grid = array_slice($grid, $headerRowIndex + 1);

        $columnIndex = [];
        foreach (self::HEADINGS as $heading) {
            $needle = $normalizeHeading($heading);
            $idx = null;
            foreach ($header as $i => $label) {
                if ($label !== '' && str_contains($label, $needle)) {
                    $idx = $i;
                    break;
                }
            }
            $columnIndex[$heading] = $idx;
        }

        $missing = array_filter(self::REQUIRED_HEADINGS, fn ($heading) => $columnIndex[$heading] === null);
        if (! empty($missing)) {
            return ['error' => 'Missing required column(s): '.implode(', ', $missing).'. Please use the provided template.', 'rows' => []];
        }

        $results = [];
        // 1-indexed spreadsheet row of the detected header, so a reported row number
        // still points at the right line even when title rows sat above the header.
        $rowNumber = $headerRowIndex + 1;

        foreach ($grid as $row) {
            $rowNumber++;

            $cell = fn (string $heading) => $columnIndex[$heading] !== null ? trim((string) ($row[$columnIndex[$heading]] ?? '')) : '';

            $fields = [
                'asset_code' => $cell('Asset ID'),
                'category' => $cell('Category'),
                'item' => $cell('Item'),
                'brand' => $cell('Brand'),
                'model' => $cell('Model'),
                'quantity' => $cell('Quantity'),
                'location' => $cell('Location / Dept'),
                'purchase_date' => $cell('Purchase Date'),
                'supplier_vendor' => $cell('Supplier / Vendor'),
                'unit_cost' => $cell('Unit Cost'),
                'useful_life_years' => $cell('Useful Life (Years)'),
                'depreciation_method' => $cell('Depreciation Method'),
                'salvage_value' => $cell('Salvage Value'),
                'status' => $cell('Status'),
            ];

            if (empty(array_filter($fields, fn ($v) => $v !== ''))) {
                continue; // fully blank row
            }

            $results[] = ['row' => $rowNumber, ...$this->validateRow($fields, $save)];
        }

        return ['error' => null, 'rows' => $results];
    }

    /**
     * Validates a single row's fields (same shape used by process() and by the "recheck a
     * row after an inline edit" endpoint) and, when $save is true and the row is valid,
     * persists it. Returns the same row shape as process() minus the 'row' number, which
     * the caller already knows (spreadsheet row, or the value the client echoes back).
     */
    public function validateRow(array $fields, bool $save = false): array
    {
        $assetCode = trim((string) ($fields['asset_code'] ?? ''));
        $categoryName = trim((string) ($fields['category'] ?? ''));
        $item = trim((string) ($fields['item'] ?? ''));
        $brand = trim((string) ($fields['brand'] ?? ''));
        $model = trim((string) ($fields['model'] ?? ''));
        $quantityRaw = trim((string) ($fields['quantity'] ?? ''));
        $locationName = trim((string) ($fields['location'] ?? ''));
        $purchaseDateRaw = trim((string) ($fields['purchase_date'] ?? ''));
        $supplierVendor = trim((string) ($fields['supplier_vendor'] ?? ''));
        $unitCostRaw = trim((string) ($fields['unit_cost'] ?? ''));
        $usefulLifeRaw = trim((string) ($fields['useful_life_years'] ?? ''));
        $depreciationMethod = trim((string) ($fields['depreciation_method'] ?? ''));
        $salvageValueRaw = trim((string) ($fields['salvage_value'] ?? ''));
        $statusRaw = trim((string) ($fields['status'] ?? ''));

        $errors = [];

        if ($item === '') {
            $errors[] = 'Item is required.';
        }

        $quantity = 1;
        if ($quantityRaw !== '') {
            if (! is_numeric($quantityRaw) || (int) $quantityRaw < 1) {
                $errors[] = "Quantity '{$quantityRaw}' must be a whole number of at least 1.";
            } else {
                $quantity = (int) $quantityRaw;
            }
        }

        $purchaseDate = null;
        if ($purchaseDateRaw !== '') {
            $purchaseDate = $this->parseDate($purchaseDateRaw);
            if (! $purchaseDate) {
                $errors[] = "Invalid Purchase Date '{$purchaseDateRaw}'.";
            }
        }

        $unitCost = null;
        if ($unitCostRaw !== '') {
            if (! is_numeric($this->cleanNumber($unitCostRaw))) {
                $errors[] = "Unit Cost '{$unitCostRaw}' is not a valid number.";
            } else {
                $unitCost = (float) $this->cleanNumber($unitCostRaw);
            }
        }

        $usefulLife = null;
        if ($usefulLifeRaw !== '') {
            if (! is_numeric($usefulLifeRaw) || (int) $usefulLifeRaw < 0) {
                $errors[] = "Useful Life '{$usefulLifeRaw}' must be a whole number of years.";
            } else {
                $usefulLife = (int) $usefulLifeRaw;
            }
        }

        $salvageValue = null;
        if ($salvageValueRaw !== '') {
            if (! is_numeric($this->cleanNumber($salvageValueRaw))) {
                $errors[] = "Salvage Value '{$salvageValueRaw}' is not a valid number.";
            } else {
                $salvageValue = (float) $this->cleanNumber($salvageValueRaw);
            }
        }

        $status = $this->resolveStatus($statusRaw);
        $existing = $assetCode !== '' ? HrCompanyAsset::where('asset_code', $assetCode)->first() : null;
        $action = $existing ? 'Update' : 'Create';

        $result = [
            'asset_code' => $assetCode,
            'category' => $categoryName,
            'item' => $item,
            'brand' => $brand,
            'model' => $model,
            'quantity' => $quantityRaw !== '' ? $quantityRaw : (string) $quantity,
            'location' => $locationName,
            'purchase_date' => $purchaseDateRaw,
            'supplier_vendor' => $supplierVendor,
            'unit_cost' => $unitCostRaw,
            'useful_life_years' => $usefulLifeRaw,
            'depreciation_method' => $depreciationMethod,
            'salvage_value' => $salvageValueRaw,
            'status' => $statusRaw !== '' ? $statusRaw : $status,
            'action' => $action,
            'errors' => $errors,
            'saved' => false,
        ];

        if (empty($errors) && $save) {
            try {
                $data = [
                    'asset_category_id' => $categoryName !== '' ? HrAssetCategory::firstOrCreate(['name' => $categoryName], ['status' => 'active'])->id : null,
                    'location_id' => $locationName !== '' ? HrAssetLocation::firstOrCreate(['name' => $locationName], ['status' => 'active'])->id : null,
                    'description' => $item,
                    'brand' => $brand !== '' ? $brand : null,
                    'model' => $model !== '' ? $model : null,
                    'quantity' => $quantity,
                    'purchase_date' => $purchaseDate?->toDateString(),
                    'supplier_vendor' => $supplierVendor !== '' ? $supplierVendor : null,
                    'unit_cost' => $unitCost,
                    'useful_life_years' => $usefulLife,
                    'depreciation_method' => $depreciationMethod !== '' ? $depreciationMethod : null,
                    'salvage_value' => $salvageValue,
                    'status' => $status,
                ];

                if ($existing) {
                    $existing->update($data);
                } else {
                    $data['asset_code'] = $assetCode !== '' ? $assetCode : $this->nextAssetCode();
                    $data['created_by'] = Auth::id();
                    HrCompanyAsset::create($data);
                }

                $result['saved'] = true;
            } catch (\Throwable $e) {
                $result['errors'][] = 'Save failed: '.$e->getMessage();
            }
        }

        return $result;
    }

    private function findSheet(Spreadsheet $spreadsheet, string $preferredName): Worksheet
    {
        foreach ($spreadsheet->getSheetNames() as $name) {
            if (strcasecmp($name, $preferredName) === 0) {
                return $spreadsheet->getSheetByName($name);
            }
        }

        return $spreadsheet->getSheet(0);
    }

    private function resolveStatus(string $status): string
    {
        foreach (HrCompanyAsset::STATUSES as $known) {
            if (strcasecmp($known, $status) === 0) {
                return $known;
            }
        }

        return HrCompanyAsset::STATUSES[0];
    }

    private function cleanNumber(string $value): string
    {
        if (is_numeric($value)) {
            return $value;
        }

        $clean = preg_replace('/[^\d.\-]/', '', $value);

        return $clean === '' || $clean === '-' ? '' : $clean;
    }

    private function parseDate(string $value): ?Carbon
    {
        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value));
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function nextAssetCode(): string
    {
        $year = now()->year;
        $count = HrCompanyAsset::whereYear('created_at', $year)->count() + 1;

        return sprintf('CA-%d-%04d', $year, $count);
    }
}
