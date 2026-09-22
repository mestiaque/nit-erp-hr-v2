@extends('admin.layouts.app')

@section('title')
<title>Disciplinary Notices</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Disciplinary Notices</h4>
            <a href="{{ route('hr-center.disciplinary-notices.create') }}" class="btn btn-primary btn-sm rounded-pill px-3">
                <i class="fa-solid fa-plus"></i> Create Notice
            </a>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <form method="GET" class="row mb-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label mb-1">Search</label>
                    <input type="text" name="search" value="{{ $request->search }}"
                           class="form-control form-control-sm" placeholder="Employee ID or Name...">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Employee</label>
                    <select name="employee_id" class="form-control form-control-sm select2">
                        <option value="">All</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}" @selected((string)$request->employee_id === (string)$e->id)>{{ $e->employee_id }} — {{ $e->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-secondary btn-sm w-100">Filter</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('hr-center.disciplinary-notices.index') }}" class="btn btn-light btn-sm w-100">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th width="50">SL</th>
                            <th>Employee</th>
                            {{-- Notice No (1st/2nd/3rd) — parked for now, see form.blade.php --}}
                            <th>Notice Date</th>
                            <th>Incident Date</th>
                            <th width="110">Deduction (Days)</th>
                            <th>Memo No</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($notices as $index => $notice)
                        <tr>
                            <td>{{ $notices->firstItem() + $index }}</td>
                            <td>{{ $notice->employee->employee_id ?? '-' }} &mdash; {{ $notice->employee->name ?? 'N/A' }}</td>
                            <td>{{ optional($notice->notice_date)->format('d M Y') }}</td>
                            <td>{{ optional($notice->incident_date)->format('d M Y') ?: '-' }}</td>
                            <td class="text-center">{{ $notice->deduction_days }}</td>
                            <td>{{ $notice->memo_no ?: '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('hr-center.disciplinary-notices.print', $notice->id) }}" class="btn-custom" title="Print" target="_blank">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <a href="{{ route('hr-center.disciplinary-notices.edit', $notice->id) }}" class="btn-custom yellow" title="Edit">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form method="POST" action="{{ route('hr-center.disciplinary-notices.destroy', $notice->id) }}" style="display:inline" onsubmit="return confirm('Delete this notice?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-custom red" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No notices found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $notices->links() }}

        </div>
    </div>
</div>
@endsection

@push('js')
<script>
$('.select2').select2({ placeholder: 'All', allowClear: true, width: '100%' });

@if(session('printed_notice_id'))
    window.open('{{ route("hr-center.disciplinary-notices.print", session("printed_notice_id")) }}', '_blank');
@endif
</script>
@endpush
