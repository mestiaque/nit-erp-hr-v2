@extends('admin.layouts.app')

@section('title')
<title>{{ $reportTitle }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ $reportTitle }}</h4>
            <div>
                <button type="button" onclick="window.print()" class="btn btn-outline-secondary">Print</button>
                <a href="{{ route('hr-center.reports.index') }}" class="btn btn-light">Back</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            @foreach($columns as $column)
                                <th>{{ ucwords(str_replace('_', ' ', $column)) }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                @foreach($columns as $column)
                                    <td>{{ is_array($row) ? ($row[$column] ?? null) : data_get($row, $column) }}</td>
                                @endforeach
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($columns) }}" class="text-center">No data found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
