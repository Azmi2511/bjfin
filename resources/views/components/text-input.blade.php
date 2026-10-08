@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-bumdes-border focus:border-bumdes-red-700 focus:ring-bumdes-red-700 rounded-xl shadow-2xs text-sm text-bumdes-dark placeholder:text-bumdes-muted bg-white']) }}>
