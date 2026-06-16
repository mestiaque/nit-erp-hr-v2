@php
    $other = is_array($employee->other_information) ? $employee->other_information : json_decode($employee->other_information, true);
    $addressInfo = data_get($other, 'address_info', []);
    $districtNames = collect($options['districts'] ?? [])->pluck('name')->map(fn ($name) => (string) $name)->all();
    $thanaNames = collect($options['thanas'] ?? [])->pluck('name')->map(fn ($name) => (string) $name)->all();
    $permanentDistrict = old('permanent_district', $employee->permanent_district);
    $permanentUpazila = old('permanent_upazila', $employee->permanent_upazila);
    $permanentPostOffice = old('permanent_post_office', $employee->permanent_post_office);
    $presentDistrict = old('present_district', $employee->present_district);
    $presentUpazila = old('present_upazila', $employee->present_upazila);
    $presentPostOffice = old('present_post_office', $employee->present_post_office);
    $permanentPostOfficeBn = old('permanent_post_office_bn', data_get($addressInfo, 'permanent_post_office_bn'));
    $permanentVillageBn = old('permanent_village_bn', data_get($addressInfo, 'permanent_village_bn'));
    $presentPostOfficeBn = old('present_post_office_bn', data_get($addressInfo, 'present_post_office_bn'));
    $presentVillageBn = old('present_village_bn', data_get($addressInfo, 'present_village_bn'));

    $permanentDistrictObj = collect($options['districts'] ?? [])->firstWhere('name', $permanentDistrict);
    $permanentDistrictId = $permanentDistrictObj->id ?? null;

    $presentDistrictObj = collect($options['districts'] ?? [])->firstWhere('name', $presentDistrict);
    $presentDistrictId = $presentDistrictObj->id ?? null;

    $permanentDistrictResult = collect($options['thanas'])->where('parent_id', $permanentDistrictId)->all();
    $presentDistrictResult = collect($options['thanas'])->where('parent_id', $presentDistrictId)->all();
@endphp

<div class="row">

    {{-- ================= PERMANENT ADDRESS ================= --}}
    <div class="col-12">
        <h6 class="mb-2">Permanent Address</h6>
    </div>
    {{-- District --}}
    <div class="col-md-6 mb-2">
        <label class="mb-1">District</label>
        <select name="permanent_district" id="permanent_district" class="form-control form-control-sm">
            <option value="">Select</option>

            @foreach(($options['districts'] ?? []) as $row)
                <option value="{{ $row->name }}" data-id="{{ $row->id }}" @selected((string) $permanentDistrict === (string) $row->name)> {{ $row->name }} </option>
            @endforeach

            @if(!empty($permanentDistrict) && !in_array((string) $permanentDistrict, $districtNames, true))
                <option value="{{ $permanentDistrict }}" selected> {{ $permanentDistrict }} </option>
            @endif
        </select>
    </div>

    {{-- Upazila / Thana --}}
    <div class="col-md-6 mb-2">
        <label class="mb-1">Po. Station</label>
        <select name="permanent_upazila" class="form-control form-control-sm">
            <option value="">Select</option>

            @foreach($permanentDistrictResult as $row)
                <option value="{{ $row->name }}" data-id="{{ $row->id }}" @selected((string) $permanentUpazila === (string) $row->name)> {{ $row->name }} </option>
            @endforeach

            @if(!empty($permanentUpazila) && !in_array((string) $permanentUpazila, $thanaNames, true))
                <option value="{{ $permanentUpazila }}" selected> {{ $permanentUpazila }} </option>
            @endif
        </select>
    </div>

    {{-- Post Office (INPUT) --}}
    <div class="col-md-6 mb-2">
        <label class="mb-1">Post Office</label>
        <input type="text" name="permanent_post_office" value="{{ old('permanent_post_office', $permanentPostOffice) }}" class="form-control form-control-sm">
    </div>

    {{-- Village --}}
    <div class="col-md-6 mb-2">
        <label class="mb-1">Village</label>
        <input type="text" name="permanent_village" value="{{ old('permanent_village', $employee->permanent_village) }}" class="form-control form-control-sm">
    </div>
    <div class="col-md-6 mb-2">
        <label class="mb-1">Post Office (Bangla)</label>
        <input type="text" name="permanent_post_office_bn" value="{{ old('permanent_post_office_bn', $permanentPostOfficeBn) }}" class="form-control form-control-sm">
    </div>
    <div class="col-md-6 mb-2">
        <label class="mb-1">Village (Bangla)</label>
        <input type="text" name="permanent_village_bn" value="{{ old('permanent_village_bn', $permanentVillageBn) }}" class="form-control form-control-sm">
    </div>


    {{-- ================= PRESENT ADDRESS ================= --}}
    <div class="col-12 mt-2">
        <h6 class="mb-2">Present Address</h6>
    </div>
    {{-- District --}}
    <div class="col-md-6 mb-2">
        <label class="mb-1">District</label>
        <select name="present_district" id="present_district" class="form-control form-control-sm">
            <option value="">Select</option>

            @foreach(($options['districts'] ?? []) as $row)
                <option value="{{ $row->name }}" data-id="{{ $row->id }}" @selected((string) $presentDistrict === (string) $row->name)> {{ $row->name }} </option>
            @endforeach

            @if(!empty($presentDistrict) && !in_array((string) $presentDistrict, $districtNames, true))
                <option value="{{ $presentDistrict }}" selected> {{ $presentDistrict }} </option>
            @endif
        </select>
    </div>
    {{-- Upazila / Thana --}}
    <div class="col-md-6 mb-2">
        <label class="mb-1">Po. Station</label>
        <select name="present_upazila" class="form-control form-control-sm">
            <option value="">Select</option>

            @foreach($presentDistrictResult as $row)
                <option value="{{ $row->name }}" data-id="{{ $row->id }}" @selected((string) $presentUpazila === (string) $row->name)> {{ $row->name }} </option>
            @endforeach

            @if(!empty($presentUpazila) && !in_array((string) $presentUpazila, $thanaNames, true))
                <option value="{{ $presentUpazila }}" selected> {{ $presentUpazila }} </option>
            @endif
        </select>
    </div>

    {{-- Post Office (INPUT) --}}
    <div class="col-md-6 mb-2">
        <label class="mb-1">Post Office</label>
        <input type="text" name="present_post_office" value="{{ old('present_post_office', $presentPostOffice) }}" class="form-control form-control-sm">
    </div>

    {{-- Village --}}
    <div class="col-md-6 mb-2">
        <label class="mb-1">Village</label>
        <input type="text" name="present_village" value="{{ old('present_village', $employee->present_village) }}" class="form-control form-control-sm">
    </div>
    <div class="col-md-6 mb-2">
        <label class="mb-1">Post Office (Bangla)</label>
        <input type="text" name="present_post_office_bn" value="{{ old('present_post_office_bn', $presentPostOfficeBn) }}" class="form-control form-control-sm">
    </div>
    <div class="col-md-6 mb-2">
        <label class="mb-1">Village (Bangla)</label>
        <input type="text" name="present_village_bn" value="{{ old('present_village_bn', $presentVillageBn) }}" class="form-control form-control-sm">
    </div>

</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function loadThanas(districtIdSelector, thanaSelector) {

    $(districtIdSelector).on('change', function () {

        let districtId = $(this).find(':selected').data('id');

        let $thanaSelect = $(thanaSelector);

        if (!districtId) {
            $thanaSelect.html('<option value="">Select</option>');
            return;
        }

        $thanaSelect.html('<option value="">Loading...</option>');

        $.ajax({
            url: '/thanas/by-district/' + districtId,
            type: 'GET',
            success: function (data) {

                $thanaSelect.html('<option value="">Select</option>');

                $.each(data, function (index, thana) {
                    $thanaSelect.append(
                        $('<option>', {
                            value: thana.name,
                            text: thana.name
                        })
                    );
                });

            },
            error: function () {
                $thanaSelect.html('<option value="">Error loading data</option>');
            }
        });

    });
}

// init
loadThanas('#permanent_district', 'select[name="permanent_upazila"]');
loadThanas('#present_district', 'select[name="present_upazila"]');
</script>
