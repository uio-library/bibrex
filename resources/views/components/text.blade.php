<input type="{{ $type ?? 'text' }}"
    name="{{ $name }}"
    class="form-control{{ $errors->has($name) ? ' is-invalid' : '' }}"
    value="{{ old($name, isset($value) ? $value : '' ) }}">
<p class="invalid-feedback">
    {{ $errors->first($name) }}
</p>
