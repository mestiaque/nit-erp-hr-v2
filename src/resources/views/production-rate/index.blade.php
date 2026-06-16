@extends('admin.layouts.app')

@section('title')
<title>Production Rate</title>
@endsection

@section('contents')
<div class="flex-grow-1">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Production Rate</h4>
            <a href="javascript:void(0)" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#CreateProductionRateModal">
                + Add
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="thead-light">
                        <tr>
                            <th>SL</th>
                            <th>Local Agent</th>
                            <th>Buyer</th>
                            <th>Style Name</th>
                            <th>Style Number</th>
                            <th>Gauge</th>
                            <th>Order Qty</th>
                            <th>Merchandiser</th>
                            <th>Process</th>
                            <th>Rate</th>
                            <th>Pro. Process</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $index => $rate)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $rate->local_agent }}</td>
                            <td>{{ $rate->buyer }}</td>
                            <td>{{ $rate->style_name }}</td>
                            <td>{{ $rate->style_number }}</td>
                            <td>{{ $rate->gauge }}</td>
                            <td>{{ $rate->order_qty }}</td>
                            <td>{{ $rate->merchandiser }}</td>
                            <td>{{ $rate->process }}</td>
                            <td>{{ $rate->rate }}</td>
                            <td>{{ $rate->pro_process }}</td>
                            <td>
                                <button type="button" class="btn btn-custom info btn-sm btn-assign-progress" data-id="{{ $rate->id }}" data-toggle="modal" data-target="#AssignProgressModal">Assign Progress</button>
                                <a href="{{ route('hr-center.production-rate.edit', $rate->id) }}" class="btn btn-custom warning btn-sm">Edit</a>
                                <form action="{{ route('hr-center.production-rate.destroy', $rate->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-custom danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="text-center">No data found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="CreateProductionRateModal" tabindex="-1" role="dialog" aria-labelledby="CreateProductionRateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('hr-center.production-rate.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="CreateProductionRateModalLabel">Add Production Rate</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Local Agent</label>
                        <input type="text" name="local_agent" class="form-control form-control-sm   ">
                    </div>
                    <div class="form-group">
                        <label>Buyer</label>
                        <input type="text" name="buyer" class="form-control form-control-sm ">
                    </div>
                    <div class="form-group">
                        <label>Style Name</label>
                        <input type="text" name="style_name" class="form-control form-control-sm    ">
                    </div>
                    <div class="form-group">
                        <label>Style Number</label>
                        <input type="text" name="style_number" class="form-control form-control-sm  ">
                    </div>
                    <div class="form-group">
                        <label>Gauge</label>
                        <input type="text" name="gauge" class="form-control form-control-sm ">
                    </div>
                    <div class="form-group">
                        <label>Order Qty</label>
                        <input type="number" name="order_qty" class="form-control form-control-sm   ">
                    </div>
                    <div class="form-group">
                        <label>Merchandiser</label>
                        <input type="text" name="merchandiser" class="form-control form-control-sm  ">
                    </div>
                    <div class="form-group">
                        <label>Process</label>
                        <input type="text" name="process" class="form-control form-control-sm   ">
                    </div>
                    <div class="form-group">
                        <label>Rate</label>
                        <input type="number" step="0.01" name="rate" class="form-control form-control-sm    ">
                    </div>
                    <div class="form-group">
                        <label>Pro. Process</label>
                        <input type="text" name="pro_process" class="form-control form-control-sm   ">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-custom primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Progress Modal -->
<div class="modal fade" id="AssignProgressModal" tabindex="-1" role="dialog" aria-labelledby="AssignProgressModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="assignProgressForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="AssignProgressModalLabel">Assign Progress</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-sm table-bordered" id="processTable">
                        <thead>
                            <tr>
                                <th>Process</th>
                                <th>Rate</th>
                                <th>Pro. Process</th>
                                <th><button type="button" class="btn btn-custom success btn-sm" id="addRowBtn">+</button></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-custom secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-custom primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let currentProductionRateId = null;
    function renderProcessRows(processes = []) {
        const tbody = $('#processTable tbody');
        tbody.empty();
        if (processes.length === 0) processes.push({process: '', rate: '', pro_process: ''});
        processes.forEach((proc, idx) => {
            tbody.append(`
                <tr>
                    <td><input type="text" name="processes[${idx}][process]" class="form-control form-control-sm    " value="${proc.process || ''}"></td>
                    <td><input type="number" step="0.01" name="processes[${idx}][rate]" class="form-control form-control-sm " value="${proc.rate || ''}"></td>
                    <td><input type="text" name="processes[${idx}][pro_process]" class="form-control form-control-sm    " value="${proc.pro_process || ''}"></td>
                    <td><button type="button" class="btn btn-custom danger btn-sm removeRowBtn">-</button></td>
                </tr>
            `);
        });
    }

    $(document).on('click', '.btn-assign-progress', function() {
        const id = $(this).data('id');
        currentProductionRateId = id;
        $('#assignProgressForm').attr('action', '/admin/hr-center/production-rate/' + id + '/assign-progress');
        // Fetch existing processes
        $.get('/admin/hr-center/production-rate/' + id + '/assign-progress', function(res) {
            renderProcessRows(res.processes || []);
            $('#AssignProgressModal').modal('show');
        });
    });

    $(document).on('click', '#addRowBtn', function() {
        const rows = $('#processTable tbody tr').length;
        renderProcessRows([...$('#processTable tbody tr').map(function() {
            return {
                process: $(this).find('input[name*="[process]"]').val(),
                rate: $(this).find('input[name*="[rate]"]').val(),
                pro_process: $(this).find('input[name*="[pro_process]"]').val()
            };
        }).get(), {process: '', rate: '', pro_process: ''}]);
    });

    $(document).on('click', '.removeRowBtn', function() {
        const rows = $('#processTable tbody tr').length;
        if (rows > 1) {
            $(this).closest('tr').remove();
        }
    });

    $('#assignProgressForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const url = form.attr('action');
        const data = form.serialize();
        $.post(url, data, function(res) {
            if (res.status === 'success') {
                $('#AssignProgressModal').modal('hide');
                location.reload();
            }
        });
    });
</script>
@endsection
