@php
    // The live hr_employee_age_verifications row is the source of truth (it's what
    // updateAgeVerification() actually writes to). other_information is a separate,
    // periodically-regenerated JSON snapshot used elsewhere for reports/print — it is
    // NOT updated when this form saves, so it must only be a fallback for older records
    // that predate the dedicated table, never the primary source.
    $av = $employee->ageVerification;
    $other = is_array($employee->other_information) ? $employee->other_information : json_decode($employee->other_information, true);
    $legacyAgeInfo = data_get($other, 'age_verification', []);

    $physicalAbility = $av->physical_ability ?? data_get($legacyAgeInfo, 'physical_ability');
    $physicalAbilityBn = $av->physical_ability_bn ?? data_get($legacyAgeInfo, 'physical_ability_bn');
    $identificationMark = $av->identification_mark ?? $employee->distinguished_mark;
    $identificationMarkBn = $av->identification_mark_bn ?? data_get($legacyAgeInfo, 'identification_mark_bn');
    $verifiedAge = $av->age_years ?? data_get($legacyAgeInfo, 'verified_age');
    $verifiedDate = $av->verified_date ?? data_get($legacyAgeInfo, 'age_verification_date');
@endphp
<div class="row">
    <div class="col-md-12 mb-2"><label class="mb-1">Physical Ability</label><input type="text" name="physical_ability" value="{{ old('physical_ability', $physicalAbility) }}" class="form-control form-control-sm"></div>
    <div class="col-md-12 mb-2"><label class="mb-1">Physical Ability (Bangla)</label><input type="text" name="physical_ability_bn" value="{{ old('physical_ability_bn', $physicalAbilityBn) }}" class="form-control form-control-sm"></div>
    <div class="col-md-12 mb-2"><label class="mb-1">Identification Mark</label><input type="text" name="distinguished_mark" value="{{ old('distinguished_mark', $identificationMark) }}" class="form-control form-control-sm"></div>
    <div class="col-md-12 mb-2"><label class="mb-1">Identification Mark (Bangla)</label><input type="text" name="distinguished_mark_bn" value="{{ old('distinguished_mark_bn', $identificationMarkBn) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Age(Years)</label><input type="number" name="verified_age" value="{{ old('verified_age', $verifiedAge) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Date</label><input type="date" name="age_verification_date" value="{{ old('age_verification_date', $verifiedDate) }}" class="form-control form-control-sm"></div>
</div>