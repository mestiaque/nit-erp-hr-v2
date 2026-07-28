{{--
    One row of the "Alternative Shifts" rotation-order picker, reused inside every
    Auto Roster create/edit modal. $shifts = full shift list, $selected = pre-picked
    shift id (or null for a blank row), $order = 1-based badge number shown to the
    left of the select — purely visual, the real order the server reads back is the
    DOM order of alt_shift_ids[] inputs at submit time.
--}}
<div class="alt-shift-row">
    <span class="alt-shift-badge">{{ $order ?? 1 }}</span>
    <select name="alt_shift_ids[]" class="form-control form-control-sm" required>
        <option value="">-- Select Shift --</option>
        @foreach($shifts as $shift)
            <option value="{{ $shift->id }}" @selected((string) ($selected ?? '') === (string) $shift->id)>{{ $shift->name }}</option>
        @endforeach
    </select>
    <button type="button" class="remove-alt-shift-btn" title="Remove this step"><i class="fas fa-times"></i></button>
</div>
