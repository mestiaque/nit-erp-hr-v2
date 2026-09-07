@extends('admin.layouts.app')

@section('title')
<title>Current Asset Report</title>
@endsection

@section('contents')
@include('hr::partials.report-loader')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Current Asset Report</h4>
            <a href="{{ route('hr-center.reports.index') }}" class="btn btn-light btn-sm">Back</a>
        </div>
        <div class="card-body">
            <form method="get" action="{{ route('hr-center.reports.company-asset-report-print') }}" target="_blank">
                <div class="row">

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Category</label>
                        <select name="asset_category_id[]" class="form-control form-control-sm select2" multiple>
                            @foreach($categories as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->asset_category_id))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Location / Dept</label>
                        <select name="department_id[]" class="form-control form-control-sm select2" multiple>
                            @foreach($departments as $item)
                                <option value="{{ $item->id }}" @selected(in_array((string)$item->id, (array)$request->department_id))>{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Status</label>
                        <select name="status[]" class="form-control form-control-sm select2" multiple>
                            @foreach($statuses as $item)
                                <option value="{{ $item }}" @selected(in_array($item, (array)$request->status))>{{ $item }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Purchase Date From</label>
                        <input type="date" name="from" class="form-control form-control-sm" value="{{ $request->from }}">
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="mb-1">Purchase Date To</label>
                        <input type="date" name="to" class="form-control form-control-sm" value="{{ $request->to }}">
                    </div>

                    <input type="hidden" name="_render" id="renderFlag" value="0">
                    <input type="hidden" name="xlsx" id="xlsxFlag" value="0">
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <div class="w-100 d-flex gap-2">
                            <button type="submit" id="reportPrintBtn" class="btn btn-primary btn-sm flex-fill"><i class="fa-solid fa-print"></i> Print</button>
                            <button type="submit" id="reportExcelBtn" class="btn btn-success btn-sm flex-fill"><i class="fa-solid fa-file-excel"></i> Excel</button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
(function () {
    var btn = document.getElementById('reportPrintBtn');
    var renderFlag = document.getElementById('renderFlag');
    var xlsxFlag = document.getElementById('xlsxFlag');
    if (btn) {
        btn.addEventListener('click', function () {
            if (renderFlag) renderFlag.value = '0';
            if (xlsxFlag) xlsxFlag.value = '0';
            if (typeof HrLoader === 'undefined') return;
            HrLoader.showWithTimeout('Generating Report', 8000);
            setTimeout(function () { HrLoader.hide(); }, 1500);
        });
    }

    var excelBtn = document.getElementById('reportExcelBtn');
    if (excelBtn && renderFlag && xlsxFlag) {
        excelBtn.addEventListener('click', function () {
            renderFlag.value = '1';
            xlsxFlag.value = '1';
        });
    }
})();

$('.select2').select2({
    placeholder: 'All',
    allowClear: true,
    width: '100%'
});
</script>
@endpush
