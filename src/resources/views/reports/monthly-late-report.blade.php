@extends('admin.layouts.app')

@section('title')
<title>{{ $reportTitle }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
	<div class="card">
		<div class="card-header d-flex justify-content-between align-items-center">
			<h4 class="mb-0">{{ $reportTitle }}</h4>
			<a href="{{ route('hr-center.reports.index') }}" class="btn btn-light btn-sm">Back</a>
		</div>
		<div class="card-body">
			<form method="get" action="{{ route('hr-center.reports.monthly-late-report') }}">
				<div class="row">
					<div class="col-md-3 mb-3">
						<label class="mb-1">From Date</label>
						<input type="date" name="from" class="form-control form-control-sm" value="{{ $request->from ?? date('Y-m-01') }}">
					</div>

					<div class="col-md-3 mb-3">
						<label class="mb-1">To Date</label>
						<input type="date" name="to" class="form-control form-control-sm" value="{{ $request->to ?? date('Y-m-t') }}">
					</div>

					<div class="col-md-3 mb-3">
						<label class="mb-1">Employee ID(s)</label>
						<input type="text" name="employee_ids" class="form-control form-control-sm" value="{{ $request->employee_ids }}" placeholder="B00144,B00145">
					</div>

					<div class="col-md-3 mb-3">
						<label class="mb-1">Department</label>
						<select name="department" class="form-control form-control-sm">
							<option value="">All</option>
							@foreach($options['departments'] as $item)
								<option value="{{ $item->id }}" @selected((string)$request->department === (string)$item->id)>{{ $item->name }}</option>
							@endforeach
						</select>
					</div>

					<div class="col-md-3 mb-3">
						<label class="mb-1">Section</label>
						<select name="section" class="form-control form-control-sm">
							<option value="">All</option>
							@foreach($options['sections'] as $item)
								<option value="{{ $item->id }}" @selected((string)$request->section === (string)$item->id)>{{ $item->name }}</option>
							@endforeach
						</select>
					</div>

					<div class="col-md-3 mb-3">
						<label class="mb-1">Sub-Section</label>
						<select name="sub_section" class="form-control form-control-sm">
							<option value="">All</option>
							@foreach($options['subSections'] as $item)
								<option value="{{ $item->id }}" @selected((string)$request->sub_section === (string)$item->id)>{{ $item->name }}</option>
							@endforeach
						</select>
					</div>

					<div class="col-md-3 mb-3">
						<label class="mb-1">Designation</label>
						<select name="designation" class="form-control form-control-sm">
							<option value="">All</option>
							@foreach($options['designations'] as $item)
								<option value="{{ $item->id }}" @selected((string)$request->designation === (string)$item->id)>{{ $item->name }}</option>
							@endforeach
						</select>
					</div>

					<div class="col-md-3 mb-3">
						<label class="mb-1">Shift</label>
						<select name="shift" class="form-control form-control-sm">
							<option value="">All</option>
							@foreach($options['shifts'] as $item)
								<option value="{{ $item->id }}" @selected((string)$request->shift === (string)$item->id)>{{ $item->name }}</option>
							@endforeach
						</select>
					</div>

					<div class="col-md-3 mb-3">
						<label class="mb-1">Employee Status</label>
						<select name="employee_status" class="form-control form-control-sm">
							<option value="">All</option>
							<option value="regular" @selected($request->employee_status === 'regular')>Regular</option>
							<option value="lefty" @selected($request->employee_status === 'lefty')>Lefty</option>
							<option value="resign" @selected($request->employee_status === 'resign')>Resign</option>
						</select>
					</div>

					<div class="col-12 mb-3">
						<button type="submit" class="btn btn-secondary btn-sm">Filter</button>
						<a href="{{ route('hr-center.reports.monthly-late-report') }}" class="btn btn-light btn-sm">Reset</a>
						<button type="submit" name="print" value="1" formtarget="_blank" class="btn btn-primary btn-sm">Report</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>
@endsection
