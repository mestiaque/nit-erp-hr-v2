@extends('admin.layouts.app')

@section('title')
<title>HR Center</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">HR Center</h4>
                </div>
                <div class="card-body">
                    <p class="mb-0">Basic setup, requisitions, and report entry points are managed from this module.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Basic Setup</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($entities as $key => $entity)
                            <div class="col-md-6 mb-3">
                                <a href="{{ route('hr-center.masters.index', $key) }}" class="btn btn-light border w-100 text-left">
                                    <strong>{{ $entity['title'] }}</strong><br>
                                    <small>Manage {{ strtolower($entity['title']) }}</small>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Existing Admin HR Setup</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($legacyLinks as $link)
                            <div class="col-md-6 mb-3">
                                <a href="{{ url($link['url']) }}" class="btn btn-outline-secondary w-100 text-left">
                                    <strong>{{ $link['title'] }}</strong><br>
                                    <small>{{ $link['description'] }}</small>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Requisition</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('hr-center.masters.index', 'requisitions') }}" class="btn btn-primary w-100">Open Requisitions</a>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Reports</h5>
                </div>
                <div class="card-body">
                    @foreach($reports as $key => $label)
                        <a href="{{ route('hr-center.reports.show', $key) }}" class="d-block mb-2">{{ $label }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
