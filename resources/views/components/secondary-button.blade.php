<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2.5 bg-white border border-bumdes-border rounded-xl font-bold text-xs text-bumdes-dark uppercase tracking-wider shadow-2xs hover:bg-bumdes-cream focus:outline-none focus:ring-2 focus:ring-bumdes-gold-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
