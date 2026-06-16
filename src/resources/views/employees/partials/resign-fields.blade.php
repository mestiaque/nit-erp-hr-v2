@php($other = is_array($employee->other_information) ? $employee->other_information : json_decode($employee->other_information, true))
@php($resign = data_get($other, 'resign_info', []))
<div class="row">
    <div class="col-md-12 mb-2"><label class="mb-1">Status</label><select name="employment_status" class="form-control form-control-sm"><option value="regular" @selected(old('employment_status', $employee->employment_status) === 'regular')>Regular</option><option value="resign" @selected(old('employment_status', $employee->employment_status) === 'resign')>Resign</option><option value="lefty" @selected(old('employment_status', $employee->employment_status) === 'lefty')>Lefty</option><option value="transfer" @selected(old('employment_status', $employee->employment_status) === 'transfer')>Transfer</option></select></div>
    <div class="col-md-12 mb-2"><label class="mb-1">Remarks</label><textarea name="resign_remarks" class="form-control form-control-sm" rows="2">{{ old('resign_remarks', data_get($resign, 'remarks')) }}</textarea></div>
    <div class="col-md-12 mb-2"><label class="mb-1">Date</label><input type="date" name="resign_date" value="{{ old('resign_date', optional($employee->exited_at)->format('Y-m-d')) }}" class="form-control form-control-sm"></div>
    <div class="col-md-12 mb-2"><label class="mb-1">Final Settlement</label><select name="final_settlement_type" class="form-control form-control-sm"><option value="">Select</option><option value="earn_leave_only" @selected(old('final_settlement_type', data_get($resign, 'final_settlement_type')) === 'earn_leave_only')>Earn leave Only</option><option value="earn_leave_with_benefit" @selected(old('final_settlement_type', data_get($resign, 'final_settlement_type')) === 'earn_leave_with_benefit')>Earn leave with service benefit</option><option value="earn_leave_without_benefit" @selected(old('final_settlement_type', data_get($resign, 'final_settlement_type')) === 'earn_leave_without_benefit')>Earn leave without service benefit</option></select></div>
    <div class="col-md-12 mb-2">
        <label class="mb-1">With Paid</label>
        <input type="hidden" name="with_paid" value="0">
        <div class="custom-control custom-switch mt-1">
            <input type="checkbox" class="custom-control-input" id="with_paid_switch_{{ $employee->id }}" name="with_paid" value="1" @checked((int) old('with_paid', data_get($resign, 'with_paid')) === 1)>
            <label class="custom-control-label" for="with_paid_switch_{{ $employee->id }}">Enabled</label>
        </div>
    </div>
</div>




