@php($nomineeInfo = $employee->nomineeRecord ? $employee->nomineeRecord->toArray() : [])
@php($nomineeImage = $employee->nomineeRecord?->photoUrl())
@php($districtNames = collect($options['districts'] ?? [])->pluck('name')->map(fn ($name) => (string) $name)->all())
@php($thanaNames = collect($options['thanas'] ?? [])->pluck('name')->map(fn ($name) => (string) $name)->all())
@php($countryNames = collect($options['countries'] ?? [])->pluck('name')->map(fn ($name) => (string) $name)->all())
@php($nomineeDistrict = old('nominee_district', $employee->nomineeRecord?->district?->name))
@php($nomineePoStation = old('nominee_po_station', $employee->nomineeRecord?->policeStation?->name))
@php($nomineePostOffice = old('nominee_post_office', data_get($nomineeInfo, 'post_office')))
@php($nomineeNationality = old('nominee_nationality', null))
@php($nomineeVillage = old('nominee_village', data_get($nomineeInfo, 'village')))
@php($nomineeNid = old('nominee_nid', data_get($nomineeInfo, 'nid_no')))
@php($nomineeMobile = old('nominee_mobile', data_get($nomineeInfo, 'mobile_no')))
@php($nomineeRelation = old('nominee_relation', $employee->nominee_relation))
@php($nomineeAge = old('nominee_age', $employee->nominee_age))
@php($nomineeNameBn = old('nominee_bn_name', data_get($nomineeInfo, 'bn_name')))
@php($nomineePostOfficeBn = old('nominee_post_office_bn', data_get($nomineeInfo, 'bn_post_office')))
@php($nomineeVillageBn = old('nominee_village_bn', data_get($nomineeInfo, 'bn_village')))
@php($nomineeRelationBn = old('nominee_relation_bn', data_get($nomineeInfo, 'bn_relation')))
@php($nomineeDistrictObj = collect($options['districts'] ?? [])->firstWhere('name', $nomineeDistrict))
@php($nomineeDistrictId = $nomineeDistrictObj->id ?? null)
@php($nomineeThanaResult = collect($options['thanas'])->where('parent_id', $nomineeDistrictId)->all())

<div class="row">
    <div class="col-md-6 mb-2">
        <label class="mb-1">Nominee Photo</label>
        <input type="file" name="nominee_image" accept="image/*" class="form-control form-control-sm" onchange="previewNomineeImage_{{ $employee->id ?? 'new' }}(this)">
        <small class="text-muted d-block mt-1">jpg, jpeg, png, gif, webp (max 2MB)</small>
        <img id="nominee_preview_{{ $employee->id ?? 'new' }}" src="{{ $nomineeImage ?: '' }}" alt="Nominee Photo" style="width:80px;height:80px;object-fit:cover;border:1px solid #ddd;border-radius:4px;margin-top:6px;{{ $nomineeImage ? '' : 'display:none;' }}">
    </div>
    <div class="col-md-6 mb-2"><label class="mb-1">Nominee Name</label><input type="text" name="nominee" value="{{ old('nominee', $employee->nominee) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Nominee Name (Bangla)</label><input type="text" name="nominee_bn_name" value="{{ old('nominee_bn_name', $nomineeNameBn) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">District</label><select name="nominee_district" id="nominee_district" class="form-control form-control-sm"><option value="">Select</option>@foreach(($options['districts'] ?? []) as $row)<option value="{{ $row->name }}" data-id="{{ $row->id }}" @selected((string) $nomineeDistrict === (string) $row->name)>{{ $row->name }}</option>@endforeach @if(!empty($nomineeDistrict) && !in_array((string) $nomineeDistrict, $districtNames, true))<option value="{{ $nomineeDistrict }}" selected>{{ $nomineeDistrict }}</option>@endif</select></div>
    <div class="col-md-6 mb-2">
        <label class="mb-1">Po. Station</label>
        <select name="nominee_po_station" id="nominee_po_station" class="form-control form-control-sm">
            <option value="">Select</option>
            @foreach($nomineeThanaResult as $row)
                <option value="{{ $row->name }}" data-id="{{ $row->id }}" @selected((string) $nomineePoStation === (string) $row->name)> {{ $row->name }} </option>
            @endforeach
            @if(!empty($nomineePoStation) && !in_array((string) $nomineePoStation, $thanaNames, true))
                <option value="{{ $nomineePoStation }}" selected> {{ $nomineePoStation }} </option>
            @endif
        </select>
    </div>
    <div class="col-md-6 mb-2"><label class="mb-1">Post Office</label><input type="text" name="nominee_post_office" value="{{ $nomineePostOffice }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Post Office (Bangla)</label><input type="text" name="nominee_post_office_bn" value="{{ old('nominee_post_office_bn', $nomineePostOfficeBn) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Country/Nationality</label>
        <select name="nominee_nationality" class="form-control form-control-sm">
            <option value="bangladeshi">Bangladeshi</option>
            {{-- <option value="">Select</option>@foreach(($options['countries'] ?? []) as $row)
            <option value="{{ $row->name }}" @selected((string) $nomineeNationality === (string) $row->name)>
                {{ $row->name }}</option>@endforeach @if(!empty($nomineeNationality) && !in_array((string) $nomineeNationality, $countryNames, true))
            <option value="{{ $nomineeNationality }}" selected>{{ $nomineeNationality }}</option>@endif --}}
        </select></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Village</label><input type="text" name="nominee_village" value="{{ $nomineeVillage }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Village (Bangla)</label><input type="text" name="nominee_village_bn" value="{{ old('nominee_village_bn', $nomineeVillageBn) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">NID No.</label><input type="text" name="nominee_nid" value="{{ $nomineeNid }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Mobile No.</label><input type="text" name="nominee_mobile" value="{{ $nomineeMobile }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Relation</label><input type="text" name="nominee_relation" list="relationList" value="{{ old('nominee_relation', $employee->nominee_relation) }}" class="form-control form-control-sm" placeholder="Select or enter"><datalist id="relationList"><option value="Father"><option value="Mother"><option value="Brother"><option value="Sister"><option value="Husband"><option value="Wife"><option value="Son"><option value="Daughter"></datalist></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Relation (Bangla)</label><input type="text" name="nominee_relation_bn" value="{{ old('nominee_relation_bn', $nomineeRelationBn) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Age</label><input type="number" name="nominee_age" value="{{ old('nominee_age', $employee->nominee_age) }}" class="form-control form-control-sm"></div>

    <div class="col-12 mt-2"><h6 class="mb-2">Payment Distribution (%)</h6></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Net Payment</label><input type="number" step="0.01" name="distribution_net_payment" value="{{ old('distribution_net_payment', data_get($nomineeInfo, 'net_payment')) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Provident Fund</label><input type="number" step="0.01" name="distribution_provident_fund" value="{{ old('distribution_provident_fund', data_get($nomineeInfo, 'provident_fund')) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Insurance</label><input type="number" step="0.01" name="distribution_insurance" value="{{ old('distribution_insurance', data_get($nomineeInfo, 'insurance')) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Accident Fine</label><input type="number" step="0.01" name="distribution_accident_fine" value="{{ old('distribution_accident_fine', data_get($nomineeInfo, 'accident_fine')) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Profit</label><input type="number" step="0.01" name="distribution_profit" value="{{ old('distribution_profit', data_get($nomineeInfo, 'profit')) }}" class="form-control form-control-sm"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Others</label><input type="number" step="0.01" name="distribution_others" value="{{ old('distribution_others', data_get($nomineeInfo, 'others')) }}" class="form-control form-control-sm"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function previewNomineeImage_{{ $employee->id ?? 'new' }}(input) {
    var previewId = 'nominee_preview_{{ $employee->id ?? 'new' }}';
    var preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Delegated + name-scoped on purpose: this partial is included once per employee
// row (one modal per employee), so `id="nominee_district"` is duplicated across the
// page. jQuery's `$('#id')` always resolves to the first matching element in the
// whole document, so ID-based binding only ever worked for the first employee's
// modal. Delegate on document and scope to the closest form instead.
if (!window.__hrNomineeThanaBound) {
    window.__hrNomineeThanaBound = true;

    $(document).on('change', 'select[name="nominee_district"]', function () {
        let $district = $(this);
        let districtId = $district.find(':selected').data('id');
        let $thanaSelect = $district.closest('form').find('select[name="nominee_po_station"]');

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
</script>
