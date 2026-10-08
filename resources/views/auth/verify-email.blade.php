<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-xl font-black text-stone-900 tracking-tight">Verifikasi Email Anda</h2>
        <p class="text-xs text-stone-500 mt-1">Terima kasih telah mendaftar di sistem BJFin. Harap verifikasi alamat email Anda melalui tautan yang baru saja kami kirimkan.</p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-xs font-semibold">
            Tautan verifikasi baru telah dikirimkan ke alamat email yang Anda daftarkan.
        </div>
    @endif

    <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
            @csrf
            <button type="submit" class="w-full sm:w-auto py-2 px-4 bg-bumdes-red-700 hover:bg-bumdes-red-800 text-white font-bold text-xs rounded-xl shadow-sm transition">
                Kirim Ulang Email Verifikasi
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto text-center">
            @csrf
            <button type="submit" class="text-xs font-semibold text-stone-600 hover:text-stone-900 underline">
                Keluar (Logout)
            </button>
        </form>
    </div>
</x-guest-layout>
