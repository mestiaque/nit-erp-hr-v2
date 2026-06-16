@extends('admin.layouts.app')

@section('title')
<title>Edit Production Rate</title>
@endsection

@section('contents')
<div class="flex-grow-1">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Edit Production Rate</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('hr-center.production-rate.update', $rate->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Local Agent</label>
                        <input type="text" name="local_agent" class="form-control" value="{{ $rate->local_agent }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Buyer</label>
                        <input type="text" name="buyer" class="form-control" value="{{ $rate->buyer }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Style Name</label>
                        <input type="text" name="style_name" class="form-control" value="{{ $rate->style_name }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Style Number</label>
                        <input type="text" name="style_number" class="form-control" value="{{ $rate->style_number }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Gauge</label>
                        <input type="text" name="gauge" class="form-control" value="{{ $rate->gauge }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Order Qty</label>
                        <input type="number" name="order_qty" class="form-control" value="{{ $rate->order_qty }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Merchandiser</label>
                        <input type="text" name="merchandiser" class="form-control" value="{{ $rate->merchandiser }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Process</label>
                        <input type="text" name="process" class="form-control" value="{{ $rate->process }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Rate</label>
                        <input type="number" step="0.01" name="rate" class="form-control" value="{{ $rate->rate }}">
                    </div>
                    <div class="form-group col-md-4">
                        <label>Pro. Process</label>
                        <input type="text" name="pro_process" class="form-control" value="{{ $rate->pro_process }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('hr-center.production-rate.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
