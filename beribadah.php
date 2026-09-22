<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beribadah</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #edf1ee;
        }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased">
    <div class="relative overflow-hidden bg-[#0b6a4d] pb-24 pt-10 rounded-b-[120px] shadow-[0_20px_40px_rgba(11,93,63,0.18)]">
        <div class="absolute -right-12 -top-8 h-36 w-36 rounded-full bg-[#0a5d42]/40 blur-2xl"></div>
        <div class="absolute -left-10 bottom-8 h-32 w-32 rounded-full bg-[#0d7f5a]/30 blur-2xl"></div>

        <div class="relative z-10 mx-auto max-w-4xl px-4 text-center">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-[22px] bg-white shadow-lg shadow-emerald-900/10">
                <span class="text-[2.1rem]" aria-label="logo ibadah">🕌</span>
            </div>
            <h1 class="mt-7 text-[2.5rem] font-extrabold tracking-[-0.05em] text-white">Beribadah</h1>
            <p class="mt-1 text-lg font-medium text-white/85">Aplikasi Tujuh Kebiasaan Anak Indonesia Hebat</p>
        </div>
    </div>

    <div class="relative z-20 mx-auto -mt-16 w-full max-w-[460px] px-4">
        <div class="rounded-[30px] bg-white p-6 shadow-[0_20px_40px_rgba(15,23,42,0.10)] border border-slate-100">
            <form class="space-y-5">
                <div>
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Waktu Ibadah</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-500">
                            <i class="fa-solid fa-clock text-sm"></i>
                        </span>
                        <input type="time" value="05:15" class="w-full rounded-[14px] border border-slate-200 bg-[#edf3f2] py-3.5 pl-11 pr-4 text-base font-semibold text-slate-700 focus:border-[#0d6b4e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100" />
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Jenis Ibadah</label>
                    <div class="relative">
                        <select class="w-full rounded-[14px] border border-slate-200 bg-[#edf3f2] px-4 py-3.5 text-base font-semibold text-slate-700 focus:border-[#0d6b4e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100">
                            <option selected>Sholat</option>
                            <option>Doa Pagi</option>
                            <option>Tilawah</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="mb-2 block text-[0.72rem] font-extrabold uppercase tracking-[0.14em] text-slate-700">Catatan</label>
                    <textarea rows="4" class="w-full rounded-[14px] border border-slate-200 bg-[#edf3f2] px-4 py-3 text-base font-medium text-slate-700 placeholder:text-slate-500 focus:border-[#0d6b4e] focus:bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100" placeholder="Tuliskan pengalaman ibadah Anda...">Sholat subuh tepat waktu dan membaca Al-Qur'an 10 lembar.</textarea>
                </div>

                <button type="submit" class="w-full rounded-[14px] bg-[#0d6b4e] py-4 text-center text-lg font-extrabold text-white shadow-lg shadow-emerald-800/20 transition hover:bg-[#0a5a40]">Simpan</button>
            </form>
        </div>
    </div>

    <footer class="py-10 text-center text-sm text-slate-500">
        © 2026 Tujuh Kebiasaan Anak Indonesia Hebat
    </footer>
</body>
</html>
