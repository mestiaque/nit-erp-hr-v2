@extends('admin.layouts.app')

@section('title')
<title>HR Reports</title>
@endsection

@section('contents')
<div class="flex-grow-1">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-0">HR Reports</h4>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($reports as $key => $label)
                    <div class="col-md-4 mb-3">
                        <a href="{{ route('hr-center.reports.show', $key) }}" class="btn w-100 ui-act ui-act-view" title="View"><i class="fa-solid fa-eye"></i> {{ $label }}</a>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
