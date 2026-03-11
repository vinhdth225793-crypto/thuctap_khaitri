@props([
    'type' => 'text',
    'name',
    'label',
    'icon' => '',
    'placeholder' => '',
    'required' => false,
    'value' => null,
    'error' => null,
    'showTogglePassword' => false,
    'additionalAttributes' => ''
])

<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}{{ $required ? ' *' : '' }}</label>
    <div class="input-group">
        @if($icon)
            <span class="input-group-text">
                <i class="fas {{ $icon }}"></i>
            </span>
        @endif
        
        <input 
            type="{{ $type }}" 
            class="form-control vip-form-control {{ $error ? 'is-invalid' : '' }}" 
            id="{{ $name }}" 
            name="{{ $name }}" 
            value="{{ old($name, $value) }}" 
            placeholder="{{ $placeholder }}"
            {{ $required ? 'required' : '' }}
            {{ $additionalAttributes }}
        >
        
        @if($showTogglePassword)
            <button class="btn btn-outline-secondary" type="button" data-toggle-password="#{{ $name }}">
                <i class="fas fa-eye"></i>
            </button>
        @endif
        
        @if($error)
            <div class="invalid-feedback">
                {{ $error }}
            </div>
        @endif
    </div>
    
    @if($errors->has($name))
        <div class="text-danger small mt-1">
            {{ $errors->first($name) }}
        </div>
    @endif
</div>