@extends('admin.layouts.app')

@section('title')
<title>{{ $bangla ? 'কর্মচারী তথ্য রিপোর্ট' : 'Employee Basic Info Report' }}</title>
@endsection

@section('contents')
<div class="flex-grow-1 p-4">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $bangla ? 'কর্মচারী তথ্য রিপোর্ট' : 'Employee Basic Info Report' }}</h5>
            <div class="btn-group">
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'en']) }}" class="btn btn-sm {{ $bangla ? 'btn-outline-primary' : 'btn-primary' }}">English</a>
                <a href="{{ request()->fullUrlWithQuery(['lang' => 'bn']) }}" class="btn btn-sm {{ $bangla ? 'btn-primary' : 'btn-outline-primary' }}">বাংলা</a>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead>
                    <tr>
                        <th>{{ $bangla ? 'আইডি' : 'ID' }}</th>
                        <th>{{ $bangla ? 'নাম' : 'Name' }}</th>
                        <th>{{ $bangla ? 'পদবী' : 'Designation' }}</th>
                        <th>{{ $bangla ? 'বিভাগ' : 'Department' }}</th>
                        <th>{{ $bangla ? 'যোগদানের তারিখ' : 'Joining Date' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row['employee_id'] }}</td>
                            <td>{{ $row['name'] }}</td>
                            <td>{{ $row['designation'] }}</td>
                            <td>{{ $row['department'] }}</td>
                            <td>{{ $row['joining_date'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">{{ $bangla ? 'কোনো তথ্য পাওয়া যায়নি' : 'No records found' }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
