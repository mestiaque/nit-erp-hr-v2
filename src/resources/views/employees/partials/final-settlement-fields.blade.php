@php
    // hr_employee_final_settlements is the live source of truth (what
    // updateFinalSettlement() actually writes to) — other_information is a separate,
    // periodically-regenerated JSON snapshot used elsewhere for reports/print and is
    // never updated by this form, so it must not be the primary source here (it was
    // previously, which made saved values never re-appear on reopen).
    $settlement = $employee->finalSettlement;

    // Only run the (relatively expensive) suggestion calculator for employees who are
    // actually leaving — skip it for regular/active rows rendered on the same index page.
    $suggestion = in_array($employee->employment_status, ['resign', 'lefty', 'transfer'], true)
        ? \ME\Hr\Services\FinalSettlementCalculator::suggest($employee)
        : null;
@endphp
<div class="row">
    <div class="col-12"><h6 class="mb-2">Absence Letters</h6></div>
    <div class="col-md-12 mb-2"><label class="mb-1">Absent Date</label><input type="date" name="absent_date" value="{{ old('absent_date', optional($settlement)->absent_date) }}" class="form-control form-control-sm"></div>
    <div class="col-md-12 mb-2"><label class="mb-1">1st Letter Date</label><input type="date" name="letter_1_date" value="{{ old('letter_1_date', optional($settlement)->first_letter_date) }}" class="form-control form-control-sm"></div>
    <div class="col-md-12 mb-2"><label class="mb-1">2nd Letter Date</label><input type="date" name="letter_2_date" value="{{ old('letter_2_date', optional($settlement)->second_letter_date) }}" class="form-control form-control-sm"></div>
    <div class="col-md-12 mb-2"><label class="mb-1">3rd Letter Date</label><input type="date" name="letter_3_date" value="{{ old('letter_3_date', optional($settlement)->third_letter_date) }}" class="form-control form-control-sm"></div>
    <div class="col-12 mb-2">
        <label class="mb-1">Select Letter to Print</label>
        <select name="final_settlement_option" id="final_settlement_option" class="form-control form-control-sm">
            <option value="">Select Option</option>
            <option value="1st Letter" {{ old('final_settlement_option', optional($settlement)->selected_letter_print) == '1st Letter' ? 'selected' : '' }}>1st Letter (প্রথম চিঠি)</option>
            <option value="2nd Letter" {{ old('final_settlement_option', optional($settlement)->selected_letter_print) == '2nd Letter' ? 'selected' : '' }}>2nd Letter (দ্বিতীয় চিঠি)</option>
            <option value="3rd Letter" {{ old('final_settlement_option', optional($settlement)->selected_letter_print) == '3rd Letter' ? 'selected' : '' }}>3rd Letter (তৃতীয় চিঠি)</option>
        </select>
    </div>
    <div class="col-12 mb-3">
        <button type="button" class="btn btn-info btn-sm" id="printBtn_{{ $employee->id }}" onclick="handlePrintClick(event, {{ $employee->id }})">
            <i class="fas fa-print"></i> Print Letter
        </button>
    </div>

    <div class="col-12"><hr class="my-1"><h6 class="mb-2">Settlement Calculation</h6></div>

    @if($suggestion)
    <div class="col-12 mb-2">
        <div class="alert alert-light border py-2 px-3 mb-2" style="font-size:.8rem;">
            <strong>Suggested</strong> ({{ $suggestion['notes'] }}) —
            Service: {{ $suggestion['service_years'] }} yr,
            Unpaid: {{ number_format($suggestion['unpaid_salary_amount'], 2) }},
            Leave: {{ number_format($suggestion['leave_encashment_amount'], 2) }},
            Gratuity: {{ number_format($suggestion['gratuity_amount'], 2) }},
            Advance due: {{ number_format($suggestion['advance_deduction'], 2) }},
            Net: {{ number_format($suggestion['net_payable'], 2) }}
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm mb-2"
            id="useSuggestedBtn_{{ $employee->id }}"
            data-last-basic-salary="{{ $suggestion['last_basic_salary'] }}"
            data-last-gross-salary="{{ $suggestion['last_gross_salary'] }}"
            data-service-years="{{ $suggestion['service_years'] }}"
            data-unpaid-salary-days="{{ $suggestion['unpaid_salary_days'] }}"
            data-unpaid-salary-amount="{{ $suggestion['unpaid_salary_amount'] }}"
            data-leave-encashment-days="{{ $suggestion['leave_encashment_days'] }}"
            data-leave-encashment-amount="{{ $suggestion['leave_encashment_amount'] }}"
            data-gratuity-amount="{{ $suggestion['gratuity_amount'] }}"
            data-advance-deduction="{{ $suggestion['advance_deduction'] }}"
            onclick="useSuggestedSettlement(event, {{ $employee->id }})">
            <i class="fas fa-magic"></i> Use Suggested
        </button>
    </div>
    @endif

    <div class="col-md-6 mb-2"><label class="mb-1">Last Basic Salary</label><input type="number" step="0.01" name="last_basic_salary" value="{{ old('last_basic_salary', optional($settlement)->last_basic_salary) }}" class="form-control form-control-sm settlement-input" data-field="last_basic_salary"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Last Gross Salary</label><input type="number" step="0.01" name="last_gross_salary" value="{{ old('last_gross_salary', optional($settlement)->last_gross_salary) }}" class="form-control form-control-sm settlement-input" data-field="last_gross_salary"></div>
    <div class="col-md-4 mb-2"><label class="mb-1">Service (Years)</label><input type="number" name="service_years" value="{{ old('service_years', optional($settlement)->service_years) }}" class="form-control form-control-sm settlement-input" data-field="service_years"></div>
    <div class="col-md-4 mb-2"><label class="mb-1">Unpaid Salary Days</label><input type="number" name="unpaid_salary_days" value="{{ old('unpaid_salary_days', optional($settlement)->unpaid_salary_days) }}" class="form-control form-control-sm settlement-input" data-field="unpaid_salary_days"></div>
    <div class="col-md-4 mb-2"><label class="mb-1">Leave Encashment Days</label><input type="number" name="leave_encashment_days" value="{{ old('leave_encashment_days', optional($settlement)->leave_encashment_days) }}" class="form-control form-control-sm settlement-input" data-field="leave_encashment_days"></div>

    <div class="col-md-6 mb-2"><label class="mb-1">Unpaid Salary Amount</label><input type="number" step="0.01" name="unpaid_salary_amount" value="{{ old('unpaid_salary_amount', optional($settlement)->unpaid_salary_amount) }}" class="form-control form-control-sm settlement-input net-input" data-field="unpaid_salary_amount"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Leave Encashment Amount</label><input type="number" step="0.01" name="leave_encashment_amount" value="{{ old('leave_encashment_amount', optional($settlement)->leave_encashment_amount) }}" class="form-control form-control-sm settlement-input net-input" data-field="leave_encashment_amount"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Gratuity Amount</label><input type="number" step="0.01" name="gratuity_amount" value="{{ old('gratuity_amount', optional($settlement)->gratuity_amount) }}" class="form-control form-control-sm settlement-input net-input" data-field="gratuity_amount"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Advance Deduction</label><input type="number" step="0.01" name="advance_deduction" value="{{ old('advance_deduction', optional($settlement)->advance_deduction) }}" class="form-control form-control-sm settlement-input net-input" data-field="advance_deduction" data-negative="1"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Other Earnings (+)</label><input type="number" step="0.01" name="other_earnings" value="{{ old('other_earnings', optional($settlement)->other_earnings) }}" class="form-control form-control-sm settlement-input net-input" data-field="other_earnings"></div>
    <div class="col-md-6 mb-2"><label class="mb-1">Other Deductions (-)</label><input type="number" step="0.01" name="other_deductions" value="{{ old('other_deductions', optional($settlement)->other_deductions) }}" class="form-control form-control-sm settlement-input net-input" data-field="other_deductions" data-negative="1"></div>

    <div class="col-md-6 mb-2"><label class="mb-1"><strong>Net Payable</strong></label><input type="number" step="0.01" name="net_payable" id="netPayable_{{ $employee->id }}" value="{{ old('net_payable', optional($settlement)->net_payable) }}" class="form-control form-control-sm font-weight-bold"></div>
    <div class="col-md-6 mb-2">
        <label class="mb-1">Status</label>
        <select name="settlement_status" class="form-control form-control-sm">
            <option value="draft" @selected(old('settlement_status', optional($settlement)->settlement_status ?? 'draft') === 'draft')>Draft</option>
            <option value="approved" @selected(old('settlement_status', optional($settlement)->settlement_status) === 'approved')>Approved</option>
            <option value="paid" @selected(old('settlement_status', optional($settlement)->settlement_status) === 'paid')>Paid</option>
        </select>
    </div>
    <div class="col-md-12 mb-2"><label class="mb-1">Calculation Notes</label><textarea name="calculation_notes" class="form-control form-control-sm" rows="2">{{ old('calculation_notes', optional($settlement)->calculation_notes) }}</textarea></div>

    <div class="col-12">
        <button type="button" class="btn btn-secondary btn-sm" onclick="printSettlementStatement(event, {{ $employee->id }})">
            <i class="fas fa-file-invoice-dollar"></i> Print Settlement Statement
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
        // NOTE: this script block is re-declared once per employee row (this partial is
        // included per row), so only the last-rendered row's `function` definition survives
        // in the browser — the URL below MUST be built from the passed-in `employeeId`
        // (not a Blade-baked `$employee->id`), otherwise every row's button would submit
        // against whichever employee happened to render last.
        const printForm = document.createElement('form');
        printForm.method = 'POST';
        printForm.action = '{{ route("hr-center.employees.final-settlement.print", "__EMP_ID__") }}'.replace('__EMP_ID__', employeeId);
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

    // Fills the editable settlement fields from the computed suggestion (data-* attributes
    // on the trigger button). Nothing is auto-saved — HR can still edit every field before
    // hitting Save/Print.
    function useSuggestedSettlement(event, employeeId) {
        event.preventDefault();
        const btn = document.getElementById(`useSuggestedBtn_${employeeId}`);
        const modal = document.getElementById(`FinalSettlementModal_${employeeId}`);
        if (!btn || !modal) return;

        const map = {
            last_basic_salary: btn.dataset.lastBasicSalary,
            last_gross_salary: btn.dataset.lastGrossSalary,
            service_years: btn.dataset.serviceYears,
            unpaid_salary_days: btn.dataset.unpaidSalaryDays,
            unpaid_salary_amount: btn.dataset.unpaidSalaryAmount,
            leave_encashment_days: btn.dataset.leaveEncashmentDays,
            leave_encashment_amount: btn.dataset.leaveEncashmentAmount,
            gratuity_amount: btn.dataset.gratuityAmount,
            advance_deduction: btn.dataset.advanceDeduction,
        };

        Object.keys(map).forEach((field) => {
            const input = modal.querySelector(`[data-field="${field}"]`);
            if (input && map[field] !== undefined && map[field] !== '') {
                input.value = map[field];
            }
        });

        recalculateNetPayable(employeeId);
    }

    // Editable running total — every input stays user-editable, this just keeps
    // Net Payable in sync unless/until the user types over it directly.
    function recalculateNetPayable(employeeId) {
        const modal = document.getElementById(`FinalSettlementModal_${employeeId}`);
        if (!modal) return;

        let total = 0;
        modal.querySelectorAll('.net-input').forEach((input) => {
            const val = parseFloat(input.value) || 0;
            total += input.dataset.negative ? -val : val;
        });

        const netField = document.getElementById(`netPayable_${employeeId}`);
        if (netField) {
            netField.value = total.toFixed(2);
        }
    }

    function printSettlementStatement(event, employeeId) {
        event.preventDefault();
        const modal = document.getElementById(`FinalSettlementModal_${employeeId}`);
        if (!modal) return;

        const form = modal.querySelector('form');
        const printForm = document.createElement('form');
        printForm.method = 'POST';
        // Same last-wins caveat as handlePrintClick above: build the URL from the
        // passed-in employeeId, not a Blade-baked value.
        printForm.action = '{{ route("hr-center.employees.final-settlement.statement", "__EMP_ID__") }}'.replace('__EMP_ID__', employeeId);
        printForm.target = '_blank';
        printForm.style.display = 'none';

        form.querySelectorAll('input[name], select[name], textarea[name]').forEach((field) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = field.name;
            input.value = field.value || '';
            printForm.appendChild(input);
        });

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PUT';
        printForm.appendChild(methodInput);

        document.body.appendChild(printForm);
        printForm.submit();
        document.body.removeChild(printForm);
    }

    document.addEventListener('input', function (e) {
        if (e.target.classList && e.target.classList.contains('net-input')) {
            const modal = e.target.closest('.modal');
            if (modal) {
                const employeeId = modal.id.replace('FinalSettlementModal_', '');
                recalculateNetPayable(employeeId);
            }
        }
    });
</script>
@endpush

