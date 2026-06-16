@foreach($entity['fields'] as $name => $field)
    <div class="col-md-6 mb-3">
        <label class="form-label mb-1">{{ $field['label'] }}</label>
        @php($type = $field['type'] ?? 'text')

        @if($type === 'textarea')
            <textarea
                name="{{ $name }}"
                class="form-control form-control-sm @if(isset($field['tinymce']) && $field['tinymce']) js-master-textarea @endif "
                rows="3"
                @if(isset($field['tinymce']) && $field['tinymce']) data-tinymce="1" @endif
            >{{ old($name, $item->{$name}) }}</textarea>
        @elseif($type === 'select')
            @php($selectOptions = $options[$name] ?? [])
            @php($isBinaryStatus = $name === 'status' && isset($selectOptions['active']) && isset($selectOptions['inactive']) && count($selectOptions) === 2)
            @php($queryDefault = !$item->exists && request()->filled($name) ? request()->input($name) : null)
            @php($defaultValue = old($name, $queryDefault ?? $item->{$name}))

            @if($isBinaryStatus)
                @php($isActive = old($name, $item->{$name} ?? 'active') === 'active')
                <input type="hidden" name="{{ $name }}" value="inactive">
                <div class="custom-control custom-switch mt-1">
                    <input
                        class="custom-control-input"
                        id="status_switch_{{ $formContext }}_{{ $entityKey }}_{{ $item->id ?: 'new' }}"
                        type="checkbox"
                        name="{{ $name }}"
                        value="active"
                        @checked($isActive)
                    >
                    <label class="custom-control-label" for="status_switch_{{ $formContext }}_{{ $entityKey }}_{{ $item->id ?: 'new' }}">Active</label>
                </div>
            @else
                <select name="{{ $name }}" class="form-control form-control-sm">
                    <option value="">Select</option>
                    @foreach($selectOptions as $value => $label)
                        <option value="{{ $value }}" @selected((string) $defaultValue === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            @endif
        @elseif($type === 'checkbox')
            @php($switchId = 'switch_' . $formContext . '_' . $entityKey . '_' . $name . '_' . ($item->id ?: 'new'))
            <input type="hidden" name="{{ $name }}" value="0">
            <div class="custom-control custom-switch mt-1">
                <input
                    type="checkbox"
                    class="custom-control-input"
                    id="{{ $switchId }}"
                    name="{{ $name }}"
                    value="1"
                    @checked((int) old($name, $item->{$name}) === 1)
                >
                <label class="custom-control-label" for="{{ $switchId }}">Enabled</label>
            </div>
        @else
            <input
                type="{{ $type === 'number' ? 'number' : $type }}"
                name="{{ $name }}"
                value="{{ old($name, $item->{$name}) }}"
                class="form-control form-control-sm"
                @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
            >
        @endif

        @error($name)
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>
@endforeach

{{-- DEBUG: Show which fields are marked for TinyMCE --}}
@if(isset($tinymceFields))
    <div style="background: #ffeeba; color: #856404; padding: 5px; font-size: 12px;">
        TinyMCE fields: {{ implode(', ', $tinymceFields) }}
    </div>
@endif



