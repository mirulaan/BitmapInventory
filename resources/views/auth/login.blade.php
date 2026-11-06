<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Bitmap Inventory OS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 min-h-screen">

    <!-- Ambient Glow / Background decoration -->
    <div class="fixed inset-0 pointer-events-none flex justify-center items-center overflow-hidden">
        <div class="w-[600px] h-[600px] bg-indigo-600/10 rounded-full blur-3xl -translate-y-24"></div>
    </div>

    <div class="relative z-10 sm:mx-auto sm:w-full sm:max-w-md">
        <!-- Brand Header -->
        <div class="flex flex-col items-center">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-indigo-500 flex items-center justify-center text-white text-xl font-black shadow-lg shadow-indigo-600/30 border border-indigo-400/20">
                B
            </div>
            <h2 class="mt-4 text-center text-2xl font-black tracking-tight text-white flex items-center gap-1.5">
                BITMAP<span class="text-indigo-400 font-medium">.OS</span>
            </h2>
            <p class="mt-1 text-center text-xs uppercase tracking-widest font-semibold text-slate-400">
                Industrial Fleet & Asset Management
            </p>
        </div>

        <!-- Card Container -->
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-slate-900/90 border border-slate-800/80 backdrop-blur-xl py-8 px-6 shadow-2xl rounded-3xl sm:px-10">
                
                @if(session('status'))
                    <div class="mb-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 p-3.5 text-xs font-semibold text-emerald-400 flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 rounded-2xl bg-rose-500/10 border border-rose-500/20 p-3.5 text-xs font-semibold text-rose-400">
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                            </svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    </div>
                @endif

                <form class="space-y-4" action="{{ route('login.post') }}" method="POST">
                    @csrf

                    <div>
                        <label for="username" class="block text-xs font-semibold text-slate-300">
                            Username
                        </label>
                        <div class="mt-1.5 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                            <input id="username" name="username" type="text" autocomplete="username" required autofocus
                                value="{{ old('username') }}"
                                placeholder="e.g. admin"
                                class="block w-full rounded-xl border border-slate-700/80 bg-slate-950/60 pl-9 pr-3 py-2.5 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-300">
                            Password
                        </label>
                        <div class="mt-1.5 relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                </svg>
                            </div>
                            <input id="password" name="password" type="password" autocomplete="current-password" required
                                placeholder="••••••••"
                                class="block w-full rounded-xl border border-slate-700/80 bg-slate-950/60 pl-9 pr-3 py-2.5 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-all">
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <div class="flex items-center">
                            <input id="remember" name="remember" type="checkbox"
                                class="h-4 w-4 rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-indigo-500/20 cursor-pointer">
                            <label for="remember" class="ml-2 block text-xs text-slate-400 select-none cursor-pointer">
                                Keep me signed in
                            </label>
                        </div>
                        <div>
                            <a href="{{ route('password.reset_code') }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 transition-colors">
                                Reset with Code
                            </a>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl text-xs sm:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md shadow-indigo-600/30 transition-all cursor-pointer">
                            Sign In to System
                        </button>
                    </div>
                </form>

                <!-- Quick Role Switcher for Demo / Testing -->
                <div class="mt-8 pt-6 border-t border-slate-800">
                    <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 text-center mb-3">
                        Quick Demo Accounts (Password: <code class="text-indigo-400 font-mono">password</code>)
                    </p>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <button type="button" onclick="fillCreds('admin')"
                            class="p-2.5 text-left rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 hover:border-indigo-500/50 transition-all group cursor-pointer">
                            <div class="font-bold text-slate-200 group-hover:text-indigo-400 flex items-center justify-between">
                                <span>Super Admin</span>
                                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500"></span>
                            </div>
                            <div class="text-[10px] text-slate-400 truncate">@admin</div>
                        </button>

                        <button type="button" onclick="fillCreds('warehouse')"
                            class="p-2.5 text-left rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 hover:border-amber-500/50 transition-all group cursor-pointer">
                            <div class="font-bold text-slate-200 group-hover:text-amber-400 flex items-center justify-between">
                                <span>Warehouse</span>
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            </div>
                            <div class="text-[10px] text-slate-400 truncate">@warehouse</div>
                        </button>

                        <button type="button" onclick="fillCreds('engineer')"
                            class="p-2.5 text-left rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 hover:border-sky-500/50 transition-all group cursor-pointer">
                            <div class="font-bold text-slate-200 group-hover:text-sky-400 flex items-center justify-between">
                                <span>Field Engineer</span>
                                <span class="w-1.5 h-1.5 rounded-full bg-sky-500"></span>
                            </div>
                            <div class="text-[10px] text-slate-400 truncate">@engineer</div>
                        </button>

                        <button type="button" onclick="fillCreds('viewer')"
                            class="p-2.5 text-left rounded-xl bg-slate-800/60 hover:bg-slate-800 border border-slate-700/60 hover:border-emerald-500/50 transition-all group cursor-pointer">
                            <div class="font-bold text-slate-200 group-hover:text-emerald-400 flex items-center justify-between">
                                <span>Auditor/Viewer</span>
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            </div>
                            <div class="text-[10px] text-slate-400 truncate">@viewer</div>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Footer -->
            <p class="mt-6 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} Bitmap Inventory Management. All rights reserved.
            </p>
        </div>
    </div>

    <script>
        function fillCreds(username) {
            document.getElementById('username').value = username;
            document.getElementById('password').value = 'password';
            document.getElementById('password').focus();
        }
    </script>
</body>
</html>
