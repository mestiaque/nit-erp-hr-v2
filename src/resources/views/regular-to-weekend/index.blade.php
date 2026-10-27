@extends('admin.layouts.app')

@section('title')
<title>Weekend to Regular</title>
@endsection

@section('contents')
<div class="flex-grow-1">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Weekend to Regular</h4>
            <button class="btn btn-sm ui-act ui-act-create" data-toggle="modal" data-target="#createModal" title="Add New"><i class="fa-solid fa-plus"></i> Add New</button>
        </div>
        <div class="card-body">
            <form method="get" class="row mb-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Section</label>
                    <select name="section_id" class="form-control form-control-sm">
                        <option value="">All</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}" @selected($request->section_id == $section->id)>{{ $section->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Type</label>
                    <select name="type" class="form-control form-control-sm">
                        <option value="">All</option>
                        <option value="regular" @selected($request->type == 'regular')>Regular</option>
                        <option value="weekend" @selected($request->type == 'weekend')>Weekend</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Is Active</label>
                    <select name="status" class="form-control form-control-sm">
                        <option value="">All</option>
                        <option value="1" @selected($request->status === '1')>Yes</option>
                        <option value="0" @selected($request->status === '0')>No</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label mb-1">Date</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ $request->date }}">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-sm ui-act ui-act-filter" title="Filter"><i class="fa-solid fa-filter"></i> Filter</button>
                    <a href="{{ route('hr-center.regular-to-weekend.index') }}" class="btn btn-sm ui-act ui-act-reset" title="Reset"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Section</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Is Active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                <td>{{ $item->section->name ?? '' }}</td>
                                <td>{{ $item->date }}</td>
                                <td>{{ ucfirst($item->type) }}</td>
                                <td>{{ $item->is_active ? 'Yes' : 'No' }}</td>
                                <td>
                                    <a href="javascript:void(0)" class="btn btn-custom btn-sm btn-icon ui-act ui-act-edit" data-toggle="modal" data-target="#editModal{{ $item->id }}" title="Edit"><i class="fa-solid fa-pen-to-square"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No data found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $items->links('pagination::bootstrap-5') }}
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade text-left" id="createModal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form method="POST" action="{{ route('hr-center.regular-to-weekend.store') }}">
            @csrf
            <div class="modal-header">
              <h5 class="modal-title">Add Weekend to Regular</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label>Section</label>
                <select name="section_id" class="form-control" required>
                  <option value="">Select Section</option>
                  @foreach($sections as $section)
                    <option value="{{ $section->id }}">{{ $section->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="mb-3">
                <label>Date</label>
                <input type="date" name="date" class="form-control" required>
              </div>
              <div class="mb-3">
                <label>Type</label>
                <select name="type" class="form-control" required>
                  <option value="regular">Regular</option>
                  <option value="weekend">Weekend</option>
                </select>
              </div>
              <div class="mb-3">
                <label>Is Active</label>
                <select name="status" class="form-control">
                  <option value="1">Yes</option>
                  <option value="0">No</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-sm ui-act ui-act-cancel" data-dismiss="modal" title="Close"><i class="fa-solid fa-ban"></i> Close</button>
              <button type="submit" class="btn btn-sm ui-act ui-act-save" title="Save"><i class="fa-solid fa-floppy-disk"></i> Save</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Edit Modals -->
    @foreach($items as $item)
    <div class="modal fade text-left" id="editModal{{ $item->id }}" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <form method="POST" action="{{ route('hr-center.regular-to-weekend.update', $item->id) }}">
            @csrf
            @method('PUT')
            <div class="modal-header">
              <h5 class="modal-title">Edit Weekend to Regular</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label>Section</label>
                <select name="section_id" class="form-control" required>
                  @foreach($sections as $section)
                    <option value="{{ $section->id }}" @selected($item->section_id == $section->id)>{{ $section->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="mb-3">
                <label>Date</label>
                <input type="date" name="date" class="form-control" value="{{ $item->date }}" required>
              </div>
              <div class="mb-3">
                <label>Type</label>
                <select name="type" class="form-control" required>
                  <option value="regular" @selected($item->type == 'regular')>Regular</option>
                  <option value="weekend" @selected($item->type == 'weekend')>Weekend</option>
                </select>
              </div>
              <div class="mb-3">
                <label>Is Active</label>
                <select name="status" class="form-control">
                  <option value="1" @selected($item->is_active)>Yes</option>
                  <option value="0" @selected(!$item->is_active)>No</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-sm ui-act ui-act-cancel" data-dismiss="modal" title="Close"><i class="fa-solid fa-ban"></i> Close</button>
              <button type="submit" class="btn btn-sm ui-act ui-act-save" title="Update"><i class="fa-solid fa-floppy-disk"></i> Update</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    @endforeach
</div>
@endsection
