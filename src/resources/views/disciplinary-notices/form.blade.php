@extends('admin.layouts.app')

@section('title')
<title>{{ $notice->id ? 'Edit' : 'Create' }} Disciplinary Notice</title>
@endsection

@push('css')
<style>
    .sec-card { border-radius: 12px; border: 1px solid #eef0f4; margin-bottom: 16px; }
    .sec-card .card-header { background: #f8f9fb; font-weight: 700; font-size: 14px; border-radius: 12px 12px 0 0; }
</style>
@endpush

@section('contents')
<div class="flex-grow-1">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">{{ $notice->id ? 'Edit' : 'Create' }} Disciplinary Notice</h4>
        <a href="{{ route('hr-center.disciplinary-notices.index') }}" class="btn btn-light btn-sm btn-icon" title="Back"><i class="fa-solid fa-arrow-left"></i></a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ $notice->id ? route('hr-center.disciplinary-notices.update', $notice->id) : route('hr-center.disciplinary-notices.store') }}">
        @csrf
        @if($notice->id) @method('PUT') @endif

        <div class="card sec-card">
            <div class="card-header">Notice Information</div>
            <div class="card-body">
                @php $noticeType = old('notice_type', $notice->notice_type ?: \ME\Hr\Models\HrDisciplinaryNotice::TYPE_FINE); @endphp
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label mb-1 d-block">Notice Type <span class="text-danger">*</span></label>
                        @foreach(\ME\Hr\Models\HrDisciplinaryNotice::TYPES as $typeValue => $typeLabel)
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" id="notice_type_{{ $typeValue }}" name="notice_type" value="{{ $typeValue }}" class="custom-control-input js-notice-type" @checked($noticeType === $typeValue)>
                                <label class="custom-control-label" for="notice_type_{{ $typeValue }}">{{ $typeLabel }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label mb-1">Employee <span class="text-danger">*</span></label>
                        <select name="employee_id" class="form-control form-control-sm select2" required>
                            <option value="">— Select Employee —</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" @selected($notice->employee_id == $employee->id)>{{ $employee->employee_id }} — {{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Notice No (1st/2nd/3rd) — parked for now, not decided yet how it should
                         affect the letter. Always saved as 1 until this is re-enabled. --}}
                    <input type="hidden" name="notice_no" value="1">
                    {{--
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Notice No <span class="text-danger">*</span></label>
                        <select name="notice_no" class="form-control form-control-sm" required>
                            @foreach($noticeLabels as $value => $label)
                                <option value="{{ $value }}" @selected((int) old('notice_no', $notice->notice_no ?? 1) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    --}}
                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Notice Date <span class="text-danger">*</span></label>
                        <input type="date" name="notice_date" class="form-control form-control-sm"
                               value="{{ old('notice_date', optional($notice->notice_date)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label class="form-label mb-1">Incident Date</label>
                        <input type="date" name="incident_date" class="form-control form-control-sm"
                               value="{{ old('incident_date', optional($notice->incident_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3 mb-2" data-notice-type="fine">
                        <label class="form-label mb-1">Deduction (Days) <span class="text-danger">*</span></label>
                        <input type="number" min="1" name="deduction_days" class="form-control form-control-sm" value="{{ old('deduction_days', $notice->deduction_days) }}" required>
                    </div>
                    @if($notice->id)
                    <div class="col-md-6 mb-2">
                        <label class="form-label mb-1">Memo No <small class="text-muted">(auto-generated)</small></label>
                        <input type="text" class="form-control form-control-sm" value="{{ $notice->memo_no }}" disabled>
                    </div>
                    @endif

                    <div class="col-md-12 mb-2" data-notice-type="show_cause">
                        <label class="form-label mb-1">Subject (বিষয়) <span class="text-danger">*</span></label>
                        <input type="text" name="subject" maxlength="255" class="form-control form-control-sm" value="{{ old('subject', $notice->subject) }}" placeholder="অতি গুরুত্বপূর্ণ প্রোডাক্ট হারানো প্রসঙ্গে।" required>
                    </div>

                    <div class="col-md-12 mb-2">
                        <label class="form-label mb-1">Incident Description <small class="text-muted">(what happened — printed in the notice body)</small></label>
                        <textarea name="incident_description" class="form-control form-control-sm" rows="3" placeholder="আপনার দায়িত্বে অবহেলায় উৎপাদন কাজে একক ভাবে ভুল সিদ্ধান্ত গ্রহণ করার কারণে উৎপাদন কাজে ব্যাঘাত ঘটে।">{{ old('incident_description', $notice->incident_description) }}</textarea>
                    </div>

                    <div class="col-md-12 mb-2" data-notice-type="show_cause">
                        <label class="form-label mb-1">Consequence <small class="text-muted">(printed as its own line after the description)</small></label>
                        <textarea name="consequence" class="form-control form-control-sm" rows="2" placeholder="উক্ত প্রোডাক্ট দুইটি আগামী ৪ ঘন্টার মধ্যে খুঁজে পাওয়া না গেলে আপনার বেতন হইতে কর্তন করা হইবে।">{{ old('consequence', $notice->consequence) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary btn-sm px-4">Save</button>
        </div>
    </form>
</div>
@endsection

@push('js')
<script>
$('.select2').select2({ width: '100%' });

// Show only the selected notice type's fields; hidden ones are disabled so they are neither required nor posted.
function toggleNoticeType() {
    var type = $('.js-notice-type:checked').val();
    $('[data-notice-type]').each(function () {
        var on = $(this).data('notice-type') === type;
        $(this).toggle(on).find('input, textarea, select').prop('disabled', !on);
    });
}
$('.js-notice-type').on('change', toggleNoticeType);
toggleNoticeType();
</script>
@endpush
