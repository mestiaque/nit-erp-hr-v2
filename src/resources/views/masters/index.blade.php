@extends('admin.layouts.app')

@section('title')
<title>{{ $entity['title'] }}</title>
@endsection

@section('contents')
    <div class="flex-grow-1">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">{{ $entity['title'] }}</h4>
                @if($useModalForm)
                    <a href="javascript:void(0)" class="btn btn-primary btn-sm " data-toggle="modal" data-target="#CreateMasterModal">
                        Create
                    </a>
                @else
                    @php
                        $hideCreate = in_array($entity['title'], ['Salary Key', 'Factory']);    
                    @endphp
                    <a href="{{ route('hr-center.masters.create', $entityKey) }}" class="btn btn-primary btn-sm @if($hideCreate) d-none @endif">Create</a>
                @endif
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form method="get" class="row mb-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label mb-1">Search</label>
                        <input type="text" name="search" value="{{ $request->search }}" class="form-control form-control-sm" placeholder="Search">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mb-1">Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="active" @selected($request->status === 'active')>Active</option>
                            <option value="inactive" @selected($request->status === 'inactive')>Inactive</option>
                            <option value="draft" @selected($request->status === 'draft')>Draft</option>
                            <option value="pending" @selected($request->status === 'pending')>Pending</option>
                            <option value="approved" @selected($request->status === 'approved')>Approved</option>
                            <option value="rejected" @selected($request->status === 'rejected')>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button class="btn btn-secondary btn-sm w-100">Filter</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('hr-center.masters.index', $entityKey) }}" class="btn btn-light btn-sm w-100">Reset</a>
                    </div>
                </form>

                <div class="table-responsive">
                    @php($isBonusPolicyIndex = $entityKey === 'bonus-policies')
                    @if($isBonusPolicyIndex)
                        @php($sectionLabels = $options['section_id'] ?? [])
                        @php($designationLabels = $options['designation_id'] ?? [])
                    @endif
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                @if($isBonusPolicyIndex)
                                    <th>Section</th>
                                    <th>Designation</th>
                                    <th>Month From</th>
                                    <th>Month To</th>
                                    <th>Basic/Gross/Production (Basis)</th>
                                    <th>Percentage/Fixed (Type)</th>
                                    <th>Amount</th>
                                    <th>Is Active</th>
                                @else
                                    <th>ID</th>
                                    @foreach($entity['index_fields'] as $field)
                                        <th>{{ ucwords(str_replace(['_', 'id'], [' ', ''], $field)) }}</th>
                                    @endforeach
                                @endif
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($items as $item)
                                <tr>
                                    @if($isBonusPolicyIndex)
                                        <td>{{ $sectionLabels[(int) $item->section_id] ?? '-' }}</td>
                                        <td>{{ $designationLabels[(int) $item->designation_id] ?? '-' }}</td>
                                        <td>{{ $item->month_from ?? '-' }}</td>
                                        <td>{{ $item->month_to ?? '-' }}</td>
                                        <td>{{ ucfirst((string) ($item->salary_basis ?? '-')) }}</td>
                                        <td>{{ ($item->amount_type ?? null) === 'percent' ? 'Percentage' : (($item->amount_type ?? null) === 'fixed' ? 'Fixed' : '-') }}</td>
                                        <td>{{ $item->amount ?? 0 }}</td>
                                        <td>{{ ($item->status ?? null) === 'active' ? 'Yes' : 'No' }}</td>
                                    @else
                                        <td>{{ ($items->currentPage() - 1) * $items->perPage() + $loop->iteration }}</td>
                                        @foreach($entity['index_fields'] as $field)
                                            <td>{{ is_scalar($item->{$field}) || is_null($item->{$field}) ? $item->{$field} : json_encode($item->{$field}) }}</td>
                                        @endforeach
                                    @endif
                                    <td>
                                        @if($useModalForm)
                                            <a href="javascript:void(0)" class="btn-custom yellow" data-toggle="modal" data-target="#EditMasterModal_{{ $item->id }}"><i class="fas fa-edit"></i></a>
                                        @else
                                            <a href="{{ route('hr-center.masters.edit', [$entityKey, $item->id]) }}" class="btn-custom yellow"><i class="fas fa-edit"></i></a>
                                        @endif
                                        <form method="post" action="{{ route('hr-center.masters.destroy', [$entityKey, $item->id]) }}" style="display:inline-block" onsubmit="return confirm('Delete this item?');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="btn-custom danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isBonusPolicyIndex ? 9 : count($entity['index_fields']) + 2 }}" class="text-center">No data found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $items->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

    @if($useModalForm)
        <div class="modal fade text-left" id="CreateMasterModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form method="post" action="{{ route('hr-center.masters.store', $entityKey) }}">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Create {{ $entity['title'] }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                @php($item = $newItem)
                                @php($formContext = 'create')
                                @include('hr::masters.partials.fields')
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @foreach($items as $modalItem)
        <div class="modal fade text-left" id="EditMasterModal_{{ $modalItem->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form method="post" action="{{ route('hr-center.masters.update', [$entityKey, $modalItem->id]) }}">
                        @csrf
                        @method('put')
                        <div class="modal-header">
                            <h5 class="modal-title">Edit {{ $entity['title'] }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                @php($item = $modalItem)
                                @php($formContext = 'edit')
                                @include('hr::masters.partials.fields')
                            </div>
                        </div>
                        <div class="modal-footer text-right">
                            <button type="button" class="btn btn-light btn-sm" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    @endif

    <script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.5/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        (function () {
            function initTiny(scope) {
                if (typeof tinymce === 'undefined') return;

                // Only select textareas with data-tinymce="1"
                var selector = scope
                    ? '#' + scope.id + ' textarea[data-tinymce="1"]'
                    : 'textarea[data-tinymce="1"]';

                tinymce.remove(selector);
                tinymce.init({
                    selector: selector,
                    height: 180,
                    menubar: false,
                    branding: false,
                    plugins: 'lists link code',
                    toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
                    statusbar: true,
                    resize: 'both'
                });
            }

            function ready(fn) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', fn);
                } else {
                    fn();
                }
            }

            ready(function () {
                initTiny();

                var createModal = document.getElementById('CreateMasterModal');
                if (createModal) {
                    createModal.addEventListener('shown.bs.modal', function () {
                        initTiny(createModal);
                    });
                }

                document.querySelectorAll('[id^="EditMasterModal_"]').forEach(function (modal) {
                    modal.addEventListener('shown.bs.modal', function () {
                        initTiny(modal);
                    });
                });
            });
        })();
    </script>
@endsection

@push('css')
    <style>
        .btn-custom{
            padding: 0px 3px !important;
            height: auto !important;
        }
    </style>
@endpush
