<div>
    <input
        type="password"
        id="{{ $name }}"
        value="{{ isset($value) ? $value : "" }}"
        autocomplete="new-password"
        name="{{ $name }}"
        class="form-control {{ $classes }} {{ isset($class) ? $class : "" }} {{ isset($eye) && $eye == true ? 'has-eye' : '' }}"
        {{ isset($disabled) && $disabled == true ? ' disabled="disabled"' : "" }}
        {{ isset($readonly) && $readonly ? 'readonly=readonly' : '' }}
    >
    @if (isset($eye) && $eye == true)
        <span class="material-symbols-rounded btn-view-password">visibility</span>
    @endif
</div>
