<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>UCMS - @yield('title', 'Auth')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
</head>

<body class="min-h-screen bg-slate-950 flex items-center justify-center my-5" data-page="@yield('page-id')">

    <div class="w-full max-w-md mx-4">
        <div class="bg-slate-900/80 border border-slate-700 rounded-2xl shadow-xl p-6 md:p-8 text-slate-100">
            <div class="flex justify-center items-center gap-2 mb-4">
                <div class="font-extrabold w-11 h-10 rounded-xl bg-indigo-500 flex items-center justify-center text-xs">
                    PUC
                </div>
                <div>
                    <div class="text-md font-bold">PREMIER UNIVERSITY, CHITTAGONG</div>
                    <div class="text-[12px] text-slate-400">A Center of Exellence for Quality Learning</div>
                </div>
            </div>

            @yield('content')
        </div>
    </div>

    @vite('resources/js/app.js')
</body>

</html>