@php($other = is_array($employee->other_information) ? $employee->other_information : json_decode($employee->other_information, true))
@php($ageInfo = data_get($other, 'age_verification', []))
<div class="row">
    <div class="col-md-12 mb-2"><label class="mb-1">Physical Ability</label><input type="text" name="physical_ability" value="{{ old('physical_ability', data_get($ageInfo, 'physical_ability')) }}" class="form-control form-control-sm"></div>
    <div class="col-md-12 mb-2"><label class="mb-1">Identification Mark</label><input type="text" name="distinguished_mark" value="{{ old('distinguished_mark', $employee->distinguished_mark) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Age(Years)</label><input type="number" name="verified_age" value="{{ old('verified_age', data_get($ageInfo, 'verified_age')) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Date</label><input type="date" name="age_verification_date" value="{{ old('age_verification_date', data_get($ageInfo, 'age_verification_date')) }}" class="form-control form-control-sm"></div>
</div>