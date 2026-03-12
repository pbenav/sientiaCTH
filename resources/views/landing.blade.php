<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'sientiaCTH') }} - {{ __('sientiaCTH - Time and Schedule Control') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@500;600;700;800&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
    @endif

    <style>
        :root {
            --bg: #030712;
            --bg2: #0f172a;
            --border: #1e293b;
            --text: #f8fafc;
            --muted: #64748b;
            --accent: #3b82f6;
            --accent-glow: rgba(59, 130, 246, 0.5);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            overflow-x: hidden;
        }

        h1, h2, h3, h4 {
            font-family: 'Space Grotesk', sans-serif;
        }

        .gradient-text {
            background: linear-gradient(135deg, #60A5FA 0%, #A78BFA 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .hero-glow {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .glow-1 {
            absolute: -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-blue-600/10 blur-[128px];
        }

        .glow-2 {
            absolute: top-[40%] -right-[10%] w-[40%] h-[40%] rounded-full bg-indigo-600/10 blur-[128px];
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <div class="hero-glow">
        <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] rounded-full bg-blue-600/10 blur-[120px]"></div>
        <div class="absolute top-[30%] -right-[10%] w-[30%] h-[50%] rounded-full bg-indigo-600/10 blur-[120px]"></div>
    </div>

    <!-- Nav -->
    <nav class="sticky top-0 z-50 w-full glass-effect border-b border-white/5">
        <div class="max-w-7xl mx-auto px-6 h-20 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-blue-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <span class="text-2xl font-bold tracking-tight">sientia<span class="text-blue-400">CTH</span></span>
            </div>
            
            <div class="flex items-center gap-4">
                <a href="{{ url('/') }}" class="text-sm font-medium text-slate-300 hover:text-white transition-colors">{{ __('Clock-in Pad') }}</a>
                <a href="{{ route('login') }}" class="px-6 py-2 rounded-full bg-white/5 hover:bg-white/10 border border-white/10 hover:border-white/20 transition-all text-sm font-medium">
                    {{ __('Log in') }}
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero -->
    <main class="relative z-10 flex-grow flex items-center justify-center px-6 py-20">
        <div class="max-w-4xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-300 text-xs font-medium uppercase tracking-wider mb-8">
                <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                {{ __('Intelligent Attendance Control') }}
            </div>
            
            <h1 class="text-5xl md:text-7xl font-extrabold leading-tight tracking-tight mb-6">
                {{ __('Time management') }} <br/>
                <span class="gradient-text">{{ __('without frictions.') }}</span>
            </h1>
            
            <p class="text-lg md:text-xl text-slate-400 max-w-2xl mx-auto leading-relaxed mb-10">
                {{ __('The ultimate solution for time tracking, vacations and team presence. Simple, powerful and designed for modern teams.') }}
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ url('/') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-semibold text-lg shadow-lg shadow-blue-500/30 transition-all duration-300 transform hover:-translate-y-1">
                    {{ __('Go to Clock-in Pad') }}
                </a>
                <a href="{{ route('login') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl glass-effect hover:bg-white/10 text-white font-medium transition-all duration-300">
                    {{ __('Admin Panel') }}
                </a>
            </div>
        </div>
    </main>

    <!-- Features -->
    <section class="relative z-10 py-24 px-6 bg-slate-900/50">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-3 gap-8">
                <div class="glass-effect p-8 rounded-2xl">
                    <div class="w-12 h-12 rounded-lg bg-blue-500/20 flex items-center justify-center mb-6 text-blue-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">{{ __('Quick Clock-in') }}</h3>
                    <p class="text-slate-400">{{ __('Numpad interface optimized for street-level terminals or tablets.') }}</p>
                </div>
                <div class="glass-effect p-8 rounded-2xl">
                    <div class="w-12 h-12 rounded-lg bg-indigo-500/20 flex items-center justify-center mb-6 text-indigo-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">{{ __('Workday Reports') }}</h3>
                    <p class="text-slate-400">{{ __('Automatic generation of records and exports for legal compliance.') }}</p>
                </div>
                <div class="glass-effect p-8 rounded-2xl">
                    <div class="w-12 h-12 rounded-lg bg-purple-500/20 flex items-center justify-center mb-6 text-purple-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold mb-3">{{ __('Absence Management') }}</h3>
                    <p class="text-slate-400">{{ __('Full control over vacations, sick leaves and custom permits.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <footer class="relative z-10 py-12 px-6 border-t border-white/5 mt-auto">
        <div class="max-w-7xl mx-auto flex flex-col md:row justify-between items-center gap-6">
            <div class="text-slate-500 text-sm">
                © {{ date('Y') }} sientiaCTH. Todos los derechos reservados.
            </div>
            <div class="flex gap-8 text-slate-400 text-sm">
                <a href="#" class="hover:text-white transition-colors">{{ __('Privacy') }}</a>
                <a href="#" class="hover:text-white transition-colors">{{ __('Terms') }}</a>
                <a href="https://cv.sientia.com" class="hover:text-white transition-colors">{{ __('Contact') }}</a>
            </div>
        </div>
    </footer>
</body>
</html>
