@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-xs uppercase tracking-wider text-stone-700']) }}>
    {{ $value ?? $slot }}
</label>
