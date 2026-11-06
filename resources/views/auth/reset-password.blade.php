<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Bitmap Inventory OS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 min-h-screen">

    <!-- Ambient Glow Background -->
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
                Credential Reset & Security
            </p>
        </div>

        <!-- Form Card -->
        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-slate-900/90 border border-slate-800/80 backdrop-blur-xl py-8 px-6 shadow-2xl rounded-3xl sm:px-10">
                
                <div class="mb-6 pb-4 border-b border-slate-800">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                        Admin-Authorized Password Reset
                    </h3>
                    <p class="mt-1.5 text-xs text-slate-400 leading-relaxed">
                        Enter your username and the single-use reset code issued by your Super Administrator. Codes expire 24 hours after generation.
                    </p>
                </div>

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

                <form class="space-y-4" action="{{ route('password.update_with_code') }}" method="POST">
                    @csrf

                    <div>
                        <label for="username" class="block text-xs font-semibold text-slate-300">
                            Username
                        </label>
                        <div class="mt-1.5 relative">
                            <input id="username" name="username" type="text" autocomplete="username" required autofocus
                                value="{{ old('username') }}"
                                placeholder="Your username (e.g. warehouse)"
                                class="block w-full rounded-xl border border-slate-700/80 bg-slate-950/60 px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="code" class="block text-xs font-semibold text-slate-300 flex items-center justify-between">
                            <span>1-Time Reset Code</span>
                            <span class="text-[10px] text-indigo-400 uppercase font-mono">From Superadmin</span>
                        </label>
                        <div class="mt-1.5 relative">
                            <input id="code" name="code" type="text" required
                                value="{{ old('code') }}"
                                placeholder="e.g. A7X9K2P4"
                                style="text-transform: uppercase; letter-spacing: 0.1em;"
                                class="block w-full rounded-xl border border-slate-700/80 bg-slate-950/60 px-3.5 py-2.5 text-xs sm:text-sm font-mono text-indigo-300 placeholder-slate-600 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-slate-300">
                            New Password
                        </label>
                        <div class="mt-1.5 relative">
                            <input id="password" name="password" type="password" required autocomplete="new-password"
                                placeholder="At least 6 characters"
                                class="block w-full rounded-xl border border-slate-700/80 bg-slate-950/60 px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-all">
                        </div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-300">
                            Confirm New Password
                        </label>
                        <div class="mt-1.5 relative">
                            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                                placeholder="Re-type new password"
                                class="block w-full rounded-xl border border-slate-700/80 bg-slate-950/60 px-3.5 py-2.5 text-xs sm:text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 focus:outline-none transition-all">
                        </div>
                    </div>

                    <div class="pt-3">
                        <button type="submit"
                            class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-xl text-xs sm:text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-md shadow-indigo-600/30 transition-all cursor-pointer">
                            Update Password
                        </button>
                    </div>

                    <div class="pt-2 text-center">
                        <a href="{{ route('login') }}" class="text-xs font-semibold text-slate-400 hover:text-white transition-colors inline-flex items-center gap-1">
                            &larr; Back to Sign In
                        </a>
                    </div>
                </form>

            </div>

            <p class="mt-6 text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} Bitmap Inventory Management. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
