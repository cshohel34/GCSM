@extends('layouts.app')
@section('title', 'Sign in')
@section('content')
<div class="relative min-h-screen bg-navy-900 overflow-hidden"
     style="background-image:url('{{ asset('img/login-bg.jpg') }}'); background-size:cover; background-position:center;">

    {{-- Navy overlay for legibility + water waves rippling at the bottom --}}
    <div class="absolute inset-0 login-overlay"></div>
    <div class="login-waves"><span class="login-wave1"></span><span class="login-wave2"></span><span class="login-wave3"></span></div>

    <div class="relative z-10 min-h-screen flex items-center">
        <div class="w-full max-w-6xl mx-auto flex flex-col lg:flex-row items-center justify-between gap-8 lg:gap-14 px-8">

            {{-- LEFT: brand + headline --}}
            <div class="flex-1 min-w-0 max-w-xl">
                <div class="flex items-center gap-3 mb-8">
                    <img src="{{ asset('img/GCSM.png') }}" alt="GCSM" class="w-16 h-16 object-contain rounded-lg bg-white/95 p-1 shadow shrink-0"
                         onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                    <span style="display:none" class="w-16 h-16 rounded-lg bg-gradient-to-br from-[#D4AF37] to-[#C9A227] text-[#0B1F3A] font-extrabold text-2xl items-center justify-center shrink-0">G</span>
                    <span class="text-white text-xl md:text-2xl font-bold tracking-tight">Golden Career Ship Management</span>
                </div>

                <h1 class="text-white text-4xl md:text-5xl lg:text-6xl font-extrabold leading-[1.1] tracking-tight text-balance"
                    style="text-shadow:0 6px 26px rgba(0,0,0,.6);">
                    Find Verified Crew &amp; <span class="text-gold-300">Manage Seafarers</span> Jobs Effortlessly
                </h1>
                <p class="text-slate-200/90 text-sm md:text-base mt-6" style="text-shadow:0 2px 12px rgba(0,0,0,.5);">
                    ISO 9001:2015 Certified · Govt.-Approved Ship Manning Agency · MLA&nbsp;085
                </p>
            </div>

            {{-- RIGHT: login card --}}
            <div class="w-full max-w-md shrink-0">
                <form method="POST" action="{{ route('login') }}"
                      class="bg-white/95 backdrop-blur rounded-2xl shadow-2xl p-9 border border-white/40">
                    @csrf
                    <div class="text-xl font-bold text-navy-800 mb-1">Welcome back</div>
                    <div class="text-sm text-slate-500 mb-6">Sign in to your account</div>
                    @if ($errors->any())
                        <div class="mb-4 text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-3 py-2">{{ $errors->first() }}</div>
                    @endif
                    <label class="block text-xs font-medium text-slate-500 mb-1">Email</label>
                    <input name="email" type="email" value="{{ old('email') }}" required autofocus
                           class="w-full border rounded-lg px-3 py-2.5 mb-4">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Password</label>
                    <input name="password" type="password" required class="w-full border rounded-lg px-3 py-2.5 mb-4">
                    <label class="flex items-center text-sm text-slate-600 mb-6"><input type="checkbox" name="remember" class="mr-2">Remember me</label>
                    <button class="w-full rounded-lg py-3 text-base font-semibold text-white"
                            style="background:linear-gradient(180deg,#274a86,#16294b); box-shadow:inset 0 -2px 0 #D4AF37, 0 12px 26px -12px rgba(18,35,63,.6);">
                        Sign in
                    </button>
                </form>
                <div class="text-[11px] text-slate-300/80 mt-4 text-center">© {{ date('Y') }} Golden Career Ship Management</div>
            </div>
        </div>
    </div>
</div>
@endsection
