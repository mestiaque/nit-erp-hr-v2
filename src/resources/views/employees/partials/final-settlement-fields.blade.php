@php($other = is_array($employee->other_information) ? $employee->other_information : json_decode($employee->other_information, true))
@php($settlement = data_get($other, 'final_settlement', []))
<div class="row">
    <div class="col-md-12 mb-2"><label class="mb-1">Absent Date</label><input type="date" name="absent_date" value="{{ old('absent_date', data_get($settlement, 'absent_date')) }}" class="form-control form-control-sm"></div>
    <div class="col-md-12 mb-2"><label class="mb-1">1st Letter Date</label><input type="date" name="letter_1_date" value="{{ old('letter_1_date', data_get($settlement, 'letter_1_date')) }}" class="form-control form-control-sm"></div>
    <div class="col-md-12 mb-2"><label class="mb-1">2nd Letter Date</label><input type="date" name="letter_2_date" value="{{ old('letter_2_date', data_get($settlement, 'letter_2_date')) }}" class="form-control form-control-sm"></div>
    <div class="col-md-12 mb-2"><label class="mb-1">3rd Letter Date</label><input type="date" name="letter_3_date" value="{{ old('letter_3_date', data_get($settlement, 'letter_3_date')) }}" class="form-control form-control-sm"></div>
    <div class="col-12 mb-2">
        <label class="mb-1">Select Letter to Print</label>
        <select name="final_settlement_option" id="final_settlement_option" class="form-control form-control-sm">
            <option value="">Select Option</option>
            <option value="1st Letter" {{ old('final_settlement_option', data_get($settlement, 'final_settlement_option')) == '1st Letter' ? 'selected' : '' }}>1st Letter (প্রথম চিঠি)</option>
            <option value="2nd Letter" {{ old('final_settlement_option', data_get($settlement, 'final_settlement_option')) == '2nd Letter' ? 'selected' : '' }}>2nd Letter (দ্বিতীয় চিঠি)</option>
            <option value="3rd Letter" {{ old('final_settlement_option', data_get($settlement, 'final_settlement_option')) == '3rd Letter' ? 'selected' : '' }}>3rd Letter (তৃতীয় চিঠি)</option>
        </select>
    </div>
    <div class="col-12">
        <button type="button" class="btn btn-info btn-sm" id="printBtn_{{ $employee->id }}" onclick="handlePrintClick(event, {{ $employee->id }})">
            <i class="fas fa-print"></i> Print Letter
        </button>
    </div>
</div>

@push('js')
<script>
    function handlePrintClick(event, employeeId) {
        event.preventDefault();

        const modal = document.getElementById(`FinalSettlementModal_${employeeId}`);
        if (!modal) return;

        const form = modal.querySelector('form');
        const selectedOption = form.querySelector('#final_settlement_option').value;

        // Validate letter selection
        if (!selectedOption) {
            alert('Please select a letter option first');
            return;
        }

        const printBtn = document.getElementById(`printBtn_${employeeId}`);
        const originalText = printBtn.textContent;
        printBtn.disabled = true;
        printBtn.textContent = 'Processing...';

        // Submit to single print route (PUT) in a new tab; controller saves then returns print page.
        const printForm = document.createElement('form');
        printForm.method = 'POST';
        printForm.action = '{{ route("hr-center.employees.final-settlement.print", $employee->id) }}';
        printForm.target = '_blank';
        printForm.style.display = 'none';

        const addInput = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value || '';
            printForm.appendChild(input);
        };

        addInput('_token', form.querySelector('input[name="_token"]').value);
        addInput('_method', 'PUT');
        addInput('absent_date', form.querySelector('input[name="absent_date"]').value);
        addInput('letter_1_date', form.querySelector('input[name="letter_1_date"]').value);
        addInput('letter_2_date', form.querySelector('input[name="letter_2_date"]').value);
        addInput('letter_3_date', form.querySelector('input[name="letter_3_date"]').value);
        addInput('final_settlement_option', selectedOption);

        document.body.appendChild(printForm);
        printForm.submit();
        document.body.removeChild(printForm);

        printBtn.disabled = false;
        printBtn.textContent = originalText;
    }
</script>
@endpush

