<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2.5 bg-bumdes-red-700 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-wider hover:bg-bumdes-red-800 active:bg-bumdes-red-900 focus:outline-none focus:ring-2 focus:ring-bumdes-red-700 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
