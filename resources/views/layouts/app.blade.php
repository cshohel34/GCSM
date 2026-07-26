<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GCSM Crew Management')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: {
                colors: {
                    navy: { 900:'#0B1F3A', 800:'#12233F', 700:'#1F3864', 600:'#2E4A7A', 500:'#3B5C93' },
                    gold: { 600:'#B8901F', 500:'#C9A227', 400:'#D4AF37', 300:'#E4C868', 200:'#F0DCA0' },
                },
                fontFamily: { sans: ['Inter','ui-sans-serif','system-ui','sans-serif'] },
            }},
        };
    </script>
    <style>
        :root { --navy:#12233F; --gold:#C9A227; }
        html { scroll-behavior: smooth; }
        body { font-family:'Inter',ui-sans-serif,system-ui,sans-serif; background:#F3F5F9; color:#243447; }
        /* Anchor targets clear the sticky header/tab bar */
        [id^="sec-"], #voyages { scroll-margin-top: 7rem; }
        /* Profile content tabs (navy + gold) */
        .ptab { padding:.4rem .62rem; border-radius:9px; font-size:.8rem; font-weight:500; color:#51607a; white-space:nowrap; cursor:pointer; transition:.15s; border:1px solid transparent; }
        .ptab:hover { background:#fff; color:#12233F; border-color:#e6eaf1; }
        .ptab.active { background:linear-gradient(180deg,#1F3864,#12233F); color:#fff; box-shadow:inset 0 -2px 0 #D4AF37, 0 6px 16px -8px rgba(18,35,63,.5); }
        :root { --ease: cubic-bezier(.22,.61,.36,1); }
        /* Fixed condensed crew bar — animates purely with transform+opacity (no reflow, no jitter) */
        #miniBar { transform: translateY(-115%); opacity:0; pointer-events:none; transition: transform .4s var(--ease), opacity .3s var(--ease); will-change: transform, opacity; }
        #miniBar.show { transform: translateY(0); opacity:1; pointer-events:auto; }
        .tab-panel { animation: gcsmPanel .28s ease; min-height: calc(100vh - 9rem); }
        @keyframes gcsmPanel { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
        /* Elegant cards everywhere */
        main .bg-white.rounded-lg.shadow, main .bg-white.rounded-xl.shadow {
            border:1px solid #e9edf3;
            border-radius:16px;
            box-shadow:0 1px 2px rgba(16,33,60,.04), 0 14px 34px -20px rgba(16,33,60,.20);
        }
        /* Modern, light section header with a gold tick */
        .gcsm-head { display:flex; align-items:center; gap:.6rem; margin-bottom:1rem; padding-bottom:.6rem; border-bottom:1px solid #eef1f6; }
        .gcsm-head::before { content:""; flex:0 0 auto; width:4px; height:18px; border-radius:99px; background:linear-gradient(180deg,#E4C868,#C9A227); }
        .gcsm-head h3, .gcsm-head .t { color:var(--navy); font-weight:600; letter-spacing:-.01em; }
        /* Buttons */
        .btn-gold { background:linear-gradient(180deg,#D4AF37,#C9A227); color:#3a2d00; }
        .btn-gold:hover { filter:brightness(1.05); }
        /* Inputs a touch softer, with a golden focus glow */
        main input:not([type=checkbox]):not([type=radio]), main select, main textarea { border-color:#dfe4ec; }
        main input:focus, main select:focus, main textarea:focus { outline:none; border-color:#C9A227; box-shadow:0 0 0 3px rgba(201,162,39,.28); }
        main select, main input[type=checkbox], main input[type=radio] { accent-color:#C9A227; }
        /* Date / time pickers — themed to the navy+gold template */
        main input[type=date], main input[type=datetime-local], main input[type=time], main input[type=month], main input[type=week] { accent-color:#C9A227; color:#243447; }
        main input[type=date]::-webkit-calendar-picker-indicator,
        main input[type=datetime-local]::-webkit-calendar-picker-indicator,
        main input[type=time]::-webkit-calendar-picker-indicator,
        main input[type=month]::-webkit-calendar-picker-indicator {
            cursor:pointer; opacity:.65; border-radius:4px; padding:2px;
            filter: invert(16%) sepia(52%) saturate(1200%) hue-rotate(186deg) brightness(95%);
        }
        main input[type=date]::-webkit-calendar-picker-indicator:hover,
        main input[type=datetime-local]::-webkit-calendar-picker-indicator:hover,
        main input[type=time]::-webkit-calendar-picker-indicator:hover,
        main input[type=month]::-webkit-calendar-picker-indicator:hover {
            opacity:1; background:#FBF5E0;
            filter: sepia(90%) saturate(1600%) hue-rotate(5deg) brightness(90%);
        }
        /* Unified button style (matches the tab buttons): navy gradient + gold underline */
        main button.bg-\[\#1F3864\], main a.bg-\[\#1F3864\], .btn-navy {
            background: linear-gradient(180deg,#274a86,#16294b) !important;
            color:#fff !important; border-radius:10px !important; font-weight:600 !important;
            box-shadow: inset 0 -2px 0 #D4AF37, 0 6px 16px -10px rgba(18,35,63,.55) !important;
            transition: filter .15s ease, transform .05s ease;
        }
        main button.bg-\[\#1F3864\]:hover, main a.bg-\[\#1F3864\]:hover, .btn-navy:hover { filter:brightness(1.08); }
        main button.bg-\[\#1F3864\]:active, main a.bg-\[\#1F3864\]:active { transform: translateY(1px); }
        /* Searchable dropdown list — gold accent on hover */
        .combo-item { padding:.375rem .75rem; cursor:pointer; border-left:2px solid transparent; }
        .combo-item:hover { background:#FBF5E0; border-left-color:#C9A227; color:#12233F; }
        /* Themed, searchable dropdown that replaces native <select> */
        .gsel { position:relative; }
        .gsel-native { position:absolute !important; opacity:0 !important; width:1px !important; height:1px !important; pointer-events:none !important; }
        .gsel-btn { width:100%; text-align:left; border:1px solid #dfe4ec; border-radius:.375rem; padding:.375rem .5rem; background:#fff; font-size:.875rem; line-height:1.4; cursor:pointer; display:flex; justify-content:space-between; align-items:center; gap:.5rem; color:#243447; }
        .gsel-btn::after { content:"▾"; color:#94a3b8; font-size:.7rem; }
        .gsel-btn:hover { border-color:#C9A227; }
        .gsel-btn:focus { outline:none; border-color:#C9A227; box-shadow:0 0 0 3px rgba(201,162,39,.28); }
        .gsel-btn.placeholder { color:#9aa4b2; }
        .gsel-pop { position:absolute; z-index:40; left:0; right:0; top:100%; margin-top:.25rem; background:#fff; border:1px solid #e6eaf1; border-radius:.6rem; box-shadow:0 14px 34px -16px rgba(16,33,60,.4); overflow:hidden; }
        .gsel-search { width:100%; border:0; border-bottom:1px solid #eef1f6; padding:.5rem .75rem; font-size:.85rem; outline:none; }
        .gsel-list { max-height:15rem; overflow:auto; padding:.25rem 0; }
        .gsel-group { padding:.4rem .8rem .15rem; font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.04em; color:#94a3b8; }
        .gsel-item { padding:.4rem .85rem; font-size:.85rem; cursor:pointer; border-left:2px solid transparent; color:#243447; }
        .gsel-item:hover { background:#FBF5E0; border-left-color:#C9A227; color:#12233F; }
        .gsel-item.selected { background:#eef2fb; color:#12233F; font-weight:600; }
        .gsel-empty { padding:.5rem .85rem; color:#94a3b8; font-size:.8rem; }
        /* Custom themed date picker (replaces the native calendar popup) */
        .gdate { position:relative; }
        .gdate-native { position:absolute !important; opacity:0 !important; width:1px !important; height:1px !important; pointer-events:none !important; }
        .gdate-btn { width:100%; text-align:left; border:1px solid #dfe4ec; border-radius:.375rem; padding:.375rem .5rem; background:#fff; font-size:.875rem; cursor:pointer; display:flex; justify-content:space-between; align-items:center; color:#243447; }
        .gdate-btn::after { content:"📅"; font-size:.8rem; opacity:.7; }
        .gdate-btn:hover { border-color:#C9A227; }
        .gdate-btn:focus { outline:none; border-color:#C9A227; box-shadow:0 0 0 3px rgba(201,162,39,.28); }
        .gdate-btn.placeholder { color:#9aa4b2; }
        .gdate-pop { position:absolute; z-index:45; top:100%; margin-top:.25rem; width:17rem; background:#fff; border:1px solid #e6eaf1; border-radius:.7rem; box-shadow:0 16px 40px -18px rgba(16,33,60,.45); overflow:hidden; }
        .gdate-pop.right { right:0; }
        .gdate-head { display:flex; align-items:center; justify-content:space-between; padding:.55rem .75rem; background:linear-gradient(180deg,#274a86,#16294b); color:#fff; }
        .gdate-head button { color:#fff; padding:.1rem .45rem; border-radius:.3rem; font-size:1rem; line-height:1; }
        .gdate-head button:hover { background:rgba(255,255,255,.16); }
        .gdate-title { font-weight:600; font-size:.85rem; flex:1; text-align:center; }
        .gdate-title:hover { background:rgba(255,255,255,.16); }
        .gdate-months { display:grid; grid-template-columns:repeat(3,1fr); gap:4px; padding:.6rem; }
        .gdate-mon { text-align:center; padding:.55rem 0; border-radius:.45rem; cursor:pointer; font-size:.82rem; color:#243447; }
        .gdate-mon:hover { background:#FBF5E0; color:#12233F; }
        .gdate-mon.today { box-shadow:inset 0 0 0 1px #C9A227; }
        .gdate-mon.selected { background:linear-gradient(180deg,#1F3864,#12233F); color:#fff; box-shadow:inset 0 -2px 0 #D4AF37; }
        .gdate-years { position:relative; display:grid; grid-template-columns:repeat(4,1fr); gap:4px; padding:.6rem; max-height:14rem; overflow:auto; }
        .gdate-year { text-align:center; padding:.5rem 0; border-radius:.45rem; cursor:pointer; font-size:.8rem; color:#243447; }
        .gdate-year:hover { background:#FBF5E0; color:#12233F; }
        .gdate-year.today { box-shadow:inset 0 0 0 1px #C9A227; }
        .gdate-year.selected { background:linear-gradient(180deg,#1F3864,#12233F); color:#fff; box-shadow:inset 0 -2px 0 #D4AF37; }
        .gdate-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; padding:.5rem; }
        .gdate-wd { text-align:center; font-size:.62rem; font-weight:700; color:#94a3b8; padding:.2rem 0; }
        .gdate-day { text-align:center; font-size:.8rem; padding:.35rem 0; border-radius:.4rem; cursor:pointer; color:#243447; }
        .gdate-day:hover { background:#FBF5E0; color:#12233F; }
        .gdate-day.muted { color:#cbd5e1; }
        .gdate-day.today { box-shadow:inset 0 0 0 1px #C9A227; }
        .gdate-day.selected { background:linear-gradient(180deg,#1F3864,#12233F); color:#fff; box-shadow:inset 0 -2px 0 #D4AF37; }
        .gdate-foot { display:flex; justify-content:space-between; padding:.4rem .75rem .55rem; border-top:1px solid #eef1f6; font-size:.8rem; }
        .gdate-foot button { color:#2E4A7A; font-weight:600; }
        .gdate-foot button:hover { text-decoration:underline; }
        @keyframes gcsmFade { 0%,100% { opacity:1; } 50% { opacity:.5; } }
        .urgency-normal { animation: gcsmFade 2.8s ease-in-out infinite; }
        .urgency-high   { animation: gcsmFade 1.7s ease-in-out infinite; }
        .urgency-urgent { animation: gcsmFade 1s   ease-in-out infinite; }
        @keyframes gcsmBlink { 0%,100% { background-color:#fef9c3; } 50% { background-color:#fde047; } }
        .gcsm-incomplete { animation: gcsmBlink 1.1s ease-in-out infinite; }
        /* Login page — static navy overlay + animated water waves at the bottom */
        .login-overlay { background:linear-gradient(110deg, rgba(9,20,38,.92) 0%, rgba(11,31,58,.82) 42%, rgba(11,31,58,.55) 100%); }
        @keyframes gcsmWave { from { background-position-x:0; } to { background-position-x:1200px; } }
        .login-waves { position:absolute; left:0; right:0; bottom:0; height:190px; z-index:1; pointer-events:none; overflow:hidden; }
        .login-waves span { position:absolute; left:0; right:0; bottom:0; height:100%; background-repeat:repeat-x; background-size:1200px 190px; will-change:background-position; }
        .login-wave1 { background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 190'%3E%3Cpath fill='%23ffffff' fill-opacity='0.06' d='M0,100 C200,160 400,50 600,100 C800,150 1000,50 1200,100 L1200,190 L0,190 Z'/%3E%3C/svg%3E"); animation:gcsmWave 20s linear infinite; }
        .login-wave2 { background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 190'%3E%3Cpath fill='%23bcd3ef' fill-opacity='0.09' d='M0,115 C250,75 450,165 700,115 C950,65 1050,150 1200,115 L1200,190 L0,190 Z'/%3E%3C/svg%3E"); animation:gcsmWave 12s linear infinite reverse; }
        .login-wave3 { background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 190'%3E%3Cpath fill='%23D4AF37' fill-opacity='0.05' d='M0,130 C200,165 400,90 600,130 C800,165 1000,90 1200,130 L1200,190 L0,190 Z'/%3E%3C/svg%3E"); animation:gcsmWave 28s linear infinite; }
    </style>
</head>
<body class="text-slate-800">
@auth
@php $nav = fn ($active) => 'flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition '.($active
        ? 'bg-white/10 text-white font-semibold shadow-[inset_3px_0_0_#D4AF37]'
        : 'text-slate-300/90 hover:bg-white/5 hover:text-white'); @endphp
<div class="min-h-screen flex">
    <aside class="w-64 shrink-0 sticky top-0 h-screen bg-gradient-to-b from-[#0B1F3A] to-[#14294A] text-slate-100 flex flex-col">
        <div class="px-5 py-5 border-b border-white/10">
            <div class="flex items-center gap-2.5">
                <img src="{{ asset('img/GCSM.png') }}" alt="GCSM" class="w-11 h-11 rounded-lg object-contain bg-white p-0.5 shrink-0"
                     onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
                <span style="display:none" class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gradient-to-br from-[#D4AF37] to-[#C9A227] text-[#0B1F3A] font-bold shrink-0">G</span>
                <div class="min-w-0">
                    <div class="font-bold text-lg leading-none tracking-tight">GCSM</div>
                    <div class="text-[11px] text-gold-300/90 tracking-wide">Crew Management</div>
                </div>
            </div>
        </div>
        <nav class="flex-1 px-2.5 py-3 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}" class="{{ $nav(request()->routeIs('dashboard')) }}">Dashboard</a>
            @can('crew.view')<a href="{{ route('crew.index') }}" class="{{ $nav(request()->routeIs('crew.*')) }}">Crew Management</a>@endcan
            @can('crew.view')<a href="{{ route('intake.index') }}" class="{{ $nav(request()->routeIs('intake.*')) }}">CV Intake / Approvals</a>@endcan
            @can('selection.view')<a href="{{ route('selection.index') }}" class="{{ $nav(request()->routeIs('selection.*')) }}">Crew Selection</a>@endcan
            @can('principal.view')<a href="{{ route('principal.index') }}" class="{{ $nav(request()->routeIs('principal.*')) }}">Principals</a>@endcan
            @can('salary.view')<a href="{{ route('salary.index') }}" class="{{ $nav(request()->routeIs('salary.*')) }}">Salary</a>@endcan
            @can('document.view')<a href="{{ route('document.index') }}" class="{{ $nav(request()->routeIs('document.*')) }}">Documents</a>@endcan
            @can('staff.view')<a href="{{ route('staff.index') }}" class="{{ $nav(request()->routeIs('staff.*')) }}">Staff &amp; Partners</a>@endcan
            @can('license.view')<a href="{{ route('license.index') }}" class="{{ $nav(request()->routeIs('license.*')) }}">Licences</a>@endcan
            @can('accounting.view')<a href="{{ route('accounting.dashboard') }}" class="{{ $nav(request()->routeIs('accounting.*')) }}">Accounting</a>@endcan
            @can('settings.view')<a href="{{ route('settings.roles') }}" class="{{ $nav(request()->routeIs('settings.*')) }}">Settings</a>@endcan
        </nav>
        <form method="POST" action="{{ route('logout') }}" data-no-ajax class="p-3 border-t border-white/10">
            @csrf
            <div class="flex items-center gap-2 mb-2">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-white/10 text-gold-300 text-xs font-semibold">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</span>
                <div class="text-xs text-slate-300 truncate">{{ auth()->user()->name }}</div>
            </div>
            <button class="w-full text-left text-sm px-3 py-2 rounded-lg bg-white/5 hover:bg-white/10 text-slate-200">Log out</button>
        </form>
    </aside>
    <main id="gcsmMain" class="flex-1 min-w-0">
        <header class="bg-white border-b border-slate-200 px-6 py-3 flex items-center justify-between sticky top-0 z-40">
            <h1 class="text-lg font-semibold text-navy-800 tracking-tight">@yield('title', 'Dashboard')</h1>
            <div class="flex items-center gap-3">
                @php $unread = \App\Models\AppNotification::where('user_id', auth()->id())->whereNull('read_at')->count(); @endphp
                <a href="{{ route('notifications.index') }}" class="relative text-slate-500 hover:text-navy-700" title="Notifications">
                    <span class="text-xl">🔔</span>
                    @if ($unread) <span class="absolute -top-1 -right-1 bg-red-600 text-white text-[10px] rounded-full px-1">{{ $unread }}</span> @endif
                </a>
                <div class="flex items-center gap-2">@yield('actions')</div>
            </div>
        </header>
        <div class="p-6">
            @if (session('status'))
                <div class="mb-4 rounded bg-green-100 border border-green-300 text-green-800 px-4 py-2 text-sm">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded bg-red-100 border border-red-300 text-red-800 px-4 py-2 text-sm">
                    <ul class="list-disc ml-4">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
</div>

{{-- Global "back to top" button --}}
<button id="gcsmToTop" type="button" title="Back to top" aria-label="Back to top"
    class="hidden fixed bottom-6 right-6 z-40 w-11 h-11 rounded-full bg-navy-800 text-white shadow-lg hover:bg-navy-700 flex items-center justify-center text-lg">↑</button>
<script>
(function () {
    var btn = document.getElementById('gcsmToTop');
    if (!btn) return;
    function toggle() { if (window.scrollY > 300) btn.classList.remove('hidden'); else btn.classList.add('hidden'); }
    window.addEventListener('scroll', toggle, { passive: true });
    btn.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
    toggle();
})();
</script>

{{-- Turn every native <select> into a themed, searchable dropdown (gold hover) --}}
<script>
(function () {
    function enhance(sel) {
        if (sel.multiple || sel.dataset.enh || sel.classList.contains('no-enhance')) return;
        sel.dataset.enh = '1';
        var wrap = document.createElement('div'); wrap.className = 'gsel';
        sel.parentNode.insertBefore(wrap, sel); wrap.appendChild(sel);
        sel.classList.add('gsel-native'); sel.setAttribute('tabindex', '-1');
        var btn = document.createElement('button'); btn.type = 'button'; btn.className = 'gsel-btn';
        var pop = document.createElement('div'); pop.className = 'gsel-pop hidden';
        var search = document.createElement('input'); search.className = 'gsel-search'; search.type = 'text'; search.placeholder = 'Search…';
        var list = document.createElement('div'); list.className = 'gsel-list';
        pop.appendChild(search); pop.appendChild(list); wrap.appendChild(btn); wrap.appendChild(pop);

        function selText() { var o = sel.options[sel.selectedIndex]; return o ? o.text : ''; }
        function syncBtn() { var t = selText(); btn.textContent = (t && t.trim() && t.trim() !== '—') ? t : (sel.getAttribute('data-placeholder') || 'Select…'); btn.classList.toggle('placeholder', !(t && t.trim() && t.trim() !== '—')); }
        function mkItem(o) {
            var d = document.createElement('div'); d.className = 'gsel-item' + (o.value === sel.value ? ' selected' : '');
            d.textContent = (o.text && o.text.trim()) ? o.text : '—';
            d.addEventListener('mousedown', function (ev) { ev.preventDefault(); sel.value = o.value; sel.dispatchEvent(new Event('change', { bubbles: true })); syncBtn(); close(); });
            return d;
        }
        function build(q) {
            q = (q || '').toLowerCase(); list.innerHTML = '';
            Array.prototype.forEach.call(sel.children, function (node) {
                if (node.tagName === 'OPTGROUP') {
                    var opts = Array.prototype.filter.call(node.children, function (o) { return o.text.toLowerCase().indexOf(q) > -1; });
                    if (!opts.length) return;
                    var g = document.createElement('div'); g.className = 'gsel-group'; g.textContent = node.label; list.appendChild(g);
                    opts.forEach(function (o) { list.appendChild(mkItem(o)); });
                } else if (node.tagName === 'OPTION') {
                    if (node.text.toLowerCase().indexOf(q) > -1) list.appendChild(mkItem(node));
                }
            });
            if (!list.children.length) { var e = document.createElement('div'); e.className = 'gsel-empty'; e.textContent = 'No match'; list.appendChild(e); }
        }
        function open() { pop.classList.remove('hidden'); search.value = ''; build(''); setTimeout(function () { search.focus(); }, 0); }
        function close() { pop.classList.add('hidden'); }
        btn.addEventListener('click', function () { pop.classList.contains('hidden') ? open() : close(); });
        search.addEventListener('input', function () { build(search.value); });
        document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) close(); });
        sel.addEventListener('change', syncBtn);
        syncBtn();
    }
    window.gcsmEnhanceSelects = function (root) { (root || document).querySelectorAll('select').forEach(enhance); };
    window.gcsmEnhanceSelects();
})();
</script>

{{-- Replace native date pickers with a themed navy+gold calendar --}}
<script>
(function () {
    var MON = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var WD = ['Su','Mo','Tu','We','Th','Fr','Sa'];
    function pad(n) { return (n < 10 ? '0' : '') + n; }

    function enhanceDate(inp) {
        if (inp.dataset.gd || inp.classList.contains('no-enhance')) return;
        inp.dataset.gd = '1';
        var wrap = document.createElement('div'); wrap.className = 'gdate';
        inp.parentNode.insertBefore(wrap, inp); wrap.appendChild(inp);
        inp.classList.add('gdate-native'); inp.setAttribute('tabindex', '-1');
        var btn = document.createElement('button'); btn.type = 'button'; btn.className = 'gdate-btn';
        var pop = document.createElement('div'); pop.className = 'gdate-pop hidden';
        wrap.appendChild(btn); wrap.appendChild(pop);
        var view = null, mode = 'days';

        function parse() { var v = inp.value; if (!v) return null; var p = v.split('-'); return { y: +p[0], m: +p[1] - 1, d: +p[2] }; }
        function fmt() { var o = parse(); return o ? pad(o.d) + '-' + MON[o.m] + '-' + o.y : ''; }
        function syncBtn() { var t = fmt(); btn.textContent = t || (inp.getAttribute('data-placeholder') || 'Select date'); btn.classList.toggle('placeholder', !t); }
        function ensureView() { if (!view) { var s = parse(), t = new Date(); view = s ? new Date(s.y, s.m, 1) : new Date(t.getFullYear(), t.getMonth(), 1); } }
        function render() {
            ensureView();
            var sel = parse(), today = new Date(), y = view.getFullYear(), m = view.getMonth();
            var title = (mode === 'days') ? (MON[m] + ' ' + y) : (mode === 'months' ? ('' + y) : 'Select year');
            var h = '<div class="gdate-head"><button type="button" data-act="prev">‹</button><button type="button" data-act="title" class="gdate-title">' + title + '</button><button type="button" data-act="next">›</button></div>';
            if (mode === 'years') {
                h += '<div class="gdate-years">';
                var cy = today.getFullYear();
                for (var yy = cy - 70; yy <= cy + 10; yy++) {
                    var yc = 'gdate-year';
                    if (sel && sel.y === yy) yc += ' selected';
                    if (today.getFullYear() === yy) yc += ' today';
                    h += '<div class="' + yc + '" data-year="' + yy + '">' + yy + '</div>';
                }
                h += '</div>';
            } else if (mode === 'days') {
                var first = new Date(y, m, 1).getDay(), days = new Date(y, m + 1, 0).getDate(), prev = new Date(y, m, 0).getDate();
                h += '<div class="gdate-grid">';
                WD.forEach(function (w) { h += '<div class="gdate-wd">' + w + '</div>'; });
                for (var i = 0; i < first; i++) h += '<div class="gdate-day muted">' + (prev - first + 1 + i) + '</div>';
                for (var d = 1; d <= days; d++) {
                    var iso = y + '-' + pad(m + 1) + '-' + pad(d), c = 'gdate-day';
                    if (sel && sel.y === y && sel.m === m && sel.d === d) c += ' selected';
                    if (today.getFullYear() === y && today.getMonth() === m && today.getDate() === d) c += ' today';
                    h += '<div class="' + c + '" data-iso="' + iso + '">' + d + '</div>';
                }
                h += '</div>';
            } else {
                h += '<div class="gdate-months">';
                for (var mi = 0; mi < 12; mi++) {
                    var cc = 'gdate-mon';
                    if (sel && sel.y === y && sel.m === mi) cc += ' selected';
                    if (today.getFullYear() === y && today.getMonth() === mi) cc += ' today';
                    h += '<div class="' + cc + '" data-mon="' + mi + '">' + MON[mi] + '</div>';
                }
                h += '</div>';
            }
            h += '<div class="gdate-foot"><button type="button" data-act="clear">Clear</button><button type="button" data-act="today">Today</button></div>';
            pop.innerHTML = h;
            if (mode === 'years') {
                var box = pop.querySelector('.gdate-years'), cur = pop.querySelector('.gdate-year.selected') || pop.querySelector('[data-year="' + y + '"]');
                if (box && cur) box.scrollTop = cur.offsetTop - box.clientHeight / 2 + cur.offsetHeight / 2;
            }
        }
        function shift(n) { ensureView(); if (mode === 'days') view.setMonth(view.getMonth() + n); else if (mode === 'months') view.setFullYear(view.getFullYear() + n); else view.setFullYear(view.getFullYear() + n * 12); render(); }
        function set(iso) { inp.value = iso; inp.dispatchEvent(new Event('change', { bubbles: true })); syncBtn(); close(); }
        function open() { view = null; mode = 'days'; render(); pop.classList.remove('hidden'); var r = wrap.getBoundingClientRect(); pop.classList.toggle('right', r.left + 275 > window.innerWidth); }
        function close() { pop.classList.add('hidden'); mode = 'days'; }
        btn.addEventListener('click', function () { pop.classList.contains('hidden') ? open() : close(); });
        pop.addEventListener('click', function (e) {
            e.stopPropagation();   // keep the calendar open when navigating months/years
            var el = e.target.closest('[data-act],[data-iso],[data-mon],[data-year]'); if (!el || !pop.contains(el)) return;
            var act = el.getAttribute('data-act');
            if (el.hasAttribute('data-iso')) { set(el.getAttribute('data-iso')); return; }
            if (el.hasAttribute('data-mon')) { ensureView(); view.setMonth(+el.getAttribute('data-mon')); mode = 'days'; render(); return; }
            if (el.hasAttribute('data-year')) { ensureView(); view.setFullYear(+el.getAttribute('data-year')); mode = 'months'; render(); return; }
            if (act === 'prev') shift(-1);
            else if (act === 'next') shift(1);
            else if (act === 'title') { mode = (mode === 'days') ? 'months' : (mode === 'months' ? 'years' : 'months'); render(); }
            else if (act === 'clear') set('');
            else if (act === 'today') { var d = new Date(); set(d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate())); }
        });
        document.addEventListener('click', function (e) { if (!wrap.contains(e.target)) close(); });
        inp.addEventListener('change', syncBtn);
        syncBtn();
    }
    window.gcsmEnhanceDates = function (root) { (root || document).querySelectorAll('input[type=date]').forEach(enhanceDate); };
    window.gcsmEnhanceDates();
})();
</script>

{{-- Copy-to-clipboard for any element with .js-copy[data-copy] --}}
<script>
document.addEventListener('click', function (e) {
    var b = e.target.closest('.js-copy'); if (!b) return;
    e.preventDefault();
    var v = b.getAttribute('data-copy') || '';
    var done = function () { var o = b.innerHTML; b.innerHTML = '✓'; b.classList.add('text-emerald-600'); setTimeout(function () { b.innerHTML = o; b.classList.remove('text-emerald-600'); }, 900); };
    if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(v).then(done, done); }
    else { var t = document.createElement('textarea'); t.value = v; t.style.position = 'fixed'; t.style.opacity = '0'; document.body.appendChild(t); t.focus(); t.select(); try { document.execCommand('copy'); } catch (err) {} t.remove(); done(); }
});
</script>

{{-- In-app themed confirmation dialog. Any form with data-confirm="…" uses it instead of the browser popup. --}}
<div id="gcsmConfirm" class="hidden fixed inset-0 z-[120] items-center justify-center bg-[#12233F]/60 backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden ring-1 ring-slate-200">
        <div class="px-5 py-4 bg-gradient-to-r from-[#1F3864] to-[#12233F] flex items-center gap-3">
            <span id="gcsmConfirmIcon" class="w-9 h-9 rounded-full bg-amber-400/20 text-amber-200 flex items-center justify-center text-lg">⚠</span>
            <div>
                <div id="gcsmConfirmTitle" class="text-white font-bold leading-tight">Please confirm</div>
                <div class="text-[11px] text-gold-300">This action needs your confirmation</div>
            </div>
        </div>
        <div class="p-5">
            <p id="gcsmConfirmMsg" class="text-sm text-slate-600"></p>
            <div class="flex items-center justify-end gap-2 pt-5">
                <button type="button" id="gcsmConfirmCancel" class="rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-4 py-1.5 hover:bg-slate-100">Cancel</button>
                <button type="button" id="gcsmConfirmOk" class="rounded-md text-white font-semibold text-sm px-4 py-1.5">Confirm</button>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var modal  = document.getElementById('gcsmConfirm');
    if (!modal) return;
    var msgEl  = document.getElementById('gcsmConfirmMsg');
    var titleEl= document.getElementById('gcsmConfirmTitle');
    var okBtn  = document.getElementById('gcsmConfirmOk');
    var cancel = document.getElementById('gcsmConfirmCancel');
    var pending = null;

    function close() {
        modal.classList.add('hidden'); modal.classList.remove('flex');
        pending = null;
    }
    function open(opts, onOk) {
        titleEl.textContent = opts.title || 'Please confirm';
        msgEl.textContent   = opts.message || 'Are you sure?';
        okBtn.textContent   = opts.ok || 'Confirm';
        okBtn.className = 'rounded-md text-white font-semibold text-sm px-4 py-1.5 ' +
            (opts.danger ? 'bg-red-600 hover:bg-red-700' : 'bg-[#1F3864] hover:bg-[#2E74B5]');
        pending = onOk;
        modal.classList.remove('hidden'); modal.classList.add('flex');
        setTimeout(function () { try { okBtn.focus(); } catch (e) {} }, 40);
    }

    okBtn.addEventListener('click', function () { var fn = pending; close(); if (fn) fn(); });
    cancel.addEventListener('click', close);
    modal.addEventListener('click', function (e) { if (e.target === modal) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.classList.contains('hidden')) close(); });

    // Intercept submits of forms that opt in with data-confirm
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.nodeName !== 'FORM') return;
        var message = form.getAttribute('data-confirm');
        if (!message || form.dataset.gcsmConfirmed === '1') return;
        e.preventDefault();
        open({
            message: message,
            title:  form.getAttribute('data-confirm-title') || 'Please confirm',
            ok:     form.getAttribute('data-confirm-ok') || 'Confirm',
            danger: form.hasAttribute('data-confirm-danger'),
        }, function () {
            form.dataset.gcsmConfirmed = '1';
            if (typeof form.requestSubmit === 'function') { form.requestSubmit(); } else { form.submit(); }
        });
    }, true);
})();
</script>

{{-- Reusable themed date picker popup: gcsmAskDate({action,title,label,dateName,value,ok,extra}) --}}
<div id="gcsmDateModal" class="hidden fixed inset-0 z-[125] items-start justify-center bg-[#12233F]/60 backdrop-blur-sm p-4 pt-20 overflow-auto">
    <div class="bg-white w-full max-w-sm rounded-2xl shadow-2xl ring-1 ring-slate-200">
        <div class="px-5 py-4 bg-gradient-to-r from-[#1F3864] to-[#12233F] rounded-t-2xl flex items-center gap-3">
            <span class="w-9 h-9 rounded-full bg-white/15 text-white flex items-center justify-center text-lg">📅</span>
            <div>
                <div id="gcsmDateTitle" class="text-white font-bold leading-tight">Choose a date</div>
                <div class="text-[11px] text-gold-300">Pick a date to continue</div>
            </div>
        </div>
        <form id="gcsmDateForm" method="POST" action="" class="p-5">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <div id="gcsmDateExtra"></div>
            <label id="gcsmDateLabel" class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Date</label>
            <input type="date" id="gcsmDateInput" name="date" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1F3864]">
            <div id="gcsmDateError" class="hidden text-xs text-red-600 mt-1">Please choose a date.</div>
            <div id="gcsmDateFields"></div>
            <div class="flex items-center justify-end gap-2 pt-4">
                <button type="button" id="gcsmDateCancel" class="rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-4 py-1.5 hover:bg-slate-100">Cancel</button>
                <button type="button" id="gcsmDateOk" class="rounded-md bg-[#1F3864] text-white font-semibold text-sm px-4 py-1.5 hover:bg-[#2E74B5]">Continue</button>
            </div>
        </form>
    </div>
</div>
<script>
(function () {
    var m = document.getElementById('gcsmDateModal');
    if (!m) return;
    var form = document.getElementById('gcsmDateForm');
    var inp = document.getElementById('gcsmDateInput');
    var err = document.getElementById('gcsmDateError');
    function close() { m.classList.add('hidden'); m.classList.remove('flex'); }
    window.gcsmAskDate = function (opts) {
        opts = opts || {};
        form.setAttribute('action', opts.action || '');
        document.getElementById('gcsmDateTitle').textContent = opts.title || 'Choose a date';
        document.getElementById('gcsmDateLabel').textContent = opts.label || 'Date';
        document.getElementById('gcsmDateOk').textContent = opts.ok || 'Continue';
        inp.setAttribute('name', opts.dateName || 'date');
        inp.value = opts.value || '';
        err.classList.add('hidden');
        var extra = document.getElementById('gcsmDateExtra'); extra.innerHTML = '';
        if (opts.extra) { Object.keys(opts.extra).forEach(function (k) { var i = document.createElement('input'); i.type = 'hidden'; i.name = k; i.value = opts.extra[k]; extra.appendChild(i); }); }
        // Optional extra visible fields, e.g. salary + place of joining.
        var fld = document.getElementById('gcsmDateFields'); fld.innerHTML = '';
        (opts.fields || []).forEach(function (f) {
            var lbl = document.createElement('label');
            lbl.className = 'block text-[11px] uppercase tracking-wide text-slate-400 mb-1 mt-3';
            lbl.textContent = f.label || f.name;
            var i = document.createElement('input');
            i.type = f.type || 'text'; i.name = f.name; i.value = f.value || '';
            if (f.placeholder) i.placeholder = f.placeholder;
            if (f.step) i.step = f.step;
            i.className = 'w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1F3864]';
            fld.appendChild(lbl); fld.appendChild(i);
        });
        // Theme any date inputs among the dynamic fields (e.g. expected joining date).
        try { if (window.gcsmEnhanceDates) window.gcsmEnhanceDates(fld); } catch (e) {}
        m.classList.remove('hidden'); m.classList.add('flex');
        setTimeout(function () { try { inp.focus(); } catch (e) {} }, 40);
    };
    document.getElementById('gcsmDateCancel').addEventListener('click', close);
    m.addEventListener('click', function (e) { if (e.target === m) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !m.classList.contains('hidden')) close(); });
    document.getElementById('gcsmDateOk').addEventListener('click', function () {
        if (!inp.value) { err.classList.remove('hidden'); return; }
        close();
        if (typeof form.requestSubmit === 'function') { form.requestSubmit(); } else { form.submit(); }
    });
    // The enhancers ran before this modal was parsed — enhance its date field now so
    // the calendar matches the software theme.
    try { if (window.gcsmEnhanceDates) window.gcsmEnhanceDates(m); } catch (e) {}
})();
</script>

{{-- Reusable themed Service-Charge popup: gcsmAskCharge({action, amount, reason}) --}}
<div id="gcsmChargeModal" class="hidden fixed inset-0 z-[125] items-center justify-center bg-[#12233F]/60 backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden ring-1 ring-slate-200">
        <div class="px-5 py-4 bg-gradient-to-r from-[#1F3864] to-[#12233F] flex items-center gap-3">
            <span class="w-9 h-9 rounded-full bg-white/15 text-white flex items-center justify-center text-lg">৳</span>
            <div>
                <div class="text-white font-bold leading-tight">Service Charge</div>
                <div class="text-[11px] text-gold-300">Set an amount to draft the journal, or record none</div>
            </div>
        </div>
        <form id="gcsmChargeForm" method="POST" action="" class="p-5">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="decision" id="gcsmChargeDecision">
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Service charge amount (BDT)</label>
                <div class="flex gap-2">
                    <input type="number" step="0.01" min="0.01" name="amount" id="gcsmChargeAmount" placeholder="e.g. 25000" class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1F3864]">
                    <button type="button" id="gcsmChargeYes" class="inline-flex items-center gap-1 rounded-md bg-emerald-600 text-white font-semibold text-xs px-3 py-2 hover:bg-emerald-700 transition whitespace-nowrap">✓ Yes — draft journal</button>
                </div>
            </div>
            <div class="flex items-center gap-3 my-4"><div class="flex-1 h-px bg-slate-200"></div><span class="text-[11px] text-slate-400 uppercase">or</span><div class="flex-1 h-px bg-slate-200"></div></div>
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">No service charge — reason (required)</label>
                <div class="flex gap-2">
                    <input type="text" name="reason" id="gcsmChargeReason" placeholder="e.g. waived for this principal" class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1F3864]">
                    <button type="button" id="gcsmChargeNo" class="inline-flex items-center gap-1 rounded-md border border-red-300 text-red-600 font-semibold text-xs px-3 py-2 hover:bg-red-50 transition whitespace-nowrap">✕ No charge</button>
                </div>
            </div>
            <div id="gcsmChargeError" class="hidden text-xs text-red-600 mt-2"></div>
            <div class="flex items-center justify-end gap-2 pt-4">
                <button type="button" id="gcsmChargeCancel" class="rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-4 py-1.5 hover:bg-slate-100">Cancel</button>
            </div>
        </form>
    </div>
</div>
<script>
(function () {
    var m = document.getElementById('gcsmChargeModal');
    if (!m) return;
    var form = document.getElementById('gcsmChargeForm');
    var amt = document.getElementById('gcsmChargeAmount');
    var rsn = document.getElementById('gcsmChargeReason');
    var dec = document.getElementById('gcsmChargeDecision');
    var err = document.getElementById('gcsmChargeError');
    function close() { m.classList.add('hidden'); m.classList.remove('flex'); }
    window.gcsmAskCharge = function (opts) {
        opts = opts || {};
        form.setAttribute('action', opts.action || '');
        amt.value = opts.amount || '';
        rsn.value = opts.reason || '';
        err.classList.add('hidden');
        m.classList.remove('hidden'); m.classList.add('flex');
        setTimeout(function () { try { amt.focus(); } catch (e) {} }, 40);
    };
    function submit(kind) {
        err.classList.add('hidden');
        if (kind === 'yes' && !(parseFloat(amt.value) > 0)) { err.textContent = 'Enter the service charge amount.'; err.classList.remove('hidden'); return; }
        if (kind === 'no' && !rsn.value.trim()) { err.textContent = 'Please write why there is no service charge.'; err.classList.remove('hidden'); return; }
        dec.value = kind;
        close();
        if (typeof form.requestSubmit === 'function') { form.requestSubmit(); } else { form.submit(); }
    }
    document.getElementById('gcsmChargeYes').addEventListener('click', function () { submit('yes'); });
    document.getElementById('gcsmChargeNo').addEventListener('click', function () { submit('no'); });
    document.getElementById('gcsmChargeCancel').addEventListener('click', close);
    m.addEventListener('click', function (e) { if (e.target === m) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !m.classList.contains('hidden')) close(); });
})();
</script>

{{-- Reusable themed two-step Sign-Off popup: gcsmSignOff({action, signOnDate}) --}}
@php $gcsmSignOffReasons = \App\Models\SignOffReason::where('active', true)->orderBy('sort_order')->orderBy('id')->get(); @endphp
<div id="gcsmSignOffModal" class="hidden fixed inset-0 z-[125] items-start justify-center bg-[#12233F]/60 backdrop-blur-sm p-4 pt-20 overflow-auto">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-visible ring-1 ring-slate-200">
        <div class="px-5 py-4 bg-gradient-to-r from-[#1F3864] to-[#12233F] flex items-center gap-3 rounded-t-2xl">
            <span class="w-9 h-9 rounded-full bg-white/15 text-white flex items-center justify-center text-lg">⚓</span>
            <div>
                <div class="text-white font-bold leading-tight">Sign Off Crew</div>
                <div class="text-[11px] text-gold-300" id="gcsmSoSub">Step 1 of 2 — voyage completion details</div>
            </div>
        </div>
        <form id="gcsmSignOffForm" method="POST" action="" class="p-5">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            {{-- Step 1: sign-off date + reason + (conditional) note --}}
            <div id="gcsmSoStep1">
                <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Sign-off date</label>
                <input type="date" id="gcsmSoDate" name="sign_off_date" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1F3864]">

                <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1 mt-3">Remarks (reason for sign-off)</label>
                <select id="gcsmSoReason" name="reason" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1F3864]">
                    <option value="">— Select a reason —</option>
                    @foreach ($gcsmSignOffReasons as $r)
                        <option value="{{ $r->label }}" data-note="{{ $r->note_required ? '1' : '0' }}">{{ $r->label }}</option>
                    @endforeach
                </select>

                <div id="gcsmSoNoteWrap" class="hidden">
                    <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1 mt-3">Note <span class="text-red-500">*</span></label>
                    <textarea id="gcsmSoNote" name="note" rows="2" maxlength="1000" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1F3864]" placeholder="Describe the reason for this sign-off…"></textarea>
                </div>

                <div id="gcsmSoErr1" class="hidden text-xs text-red-600 mt-2"></div>
                <div class="flex items-center justify-end gap-2 pt-4">
                    <button type="button" id="gcsmSoCancel" class="rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-4 py-1.5 hover:bg-slate-100">Cancel</button>
                    <button type="button" id="gcsmSoNext" class="rounded-md bg-[#1F3864] text-white font-semibold text-sm px-4 py-1.5 hover:bg-[#2E74B5]">Next →</button>
                </div>
            </div>

            {{-- Step 2: update placement status (opens automatically after step 1) --}}
            <div id="gcsmSoStep2" class="hidden">
                <div class="rounded-lg bg-slate-50 ring-1 ring-slate-200 p-3">
                    <div class="text-[11px] uppercase tracking-wide text-[#1F3864] font-semibold mb-2">Update placement status</div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Availability</label>
                            <select name="availability" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1F3864]">
                                <option value="available">Available</option>
                                <option value="resting">Resting</option>
                                <option value="not_available">Not available</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Available from</label>
                            <input type="date" name="available_from" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1F3864]">
                        </div>
                        <div>
                            <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Job urgency</label>
                            <select name="job_urgency" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1F3864]">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Placement deadline</label>
                            <input type="date" name="job_deadline" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1F3864]">
                        </div>
                    </div>
                    <label class="inline-flex items-center gap-2 text-xs text-slate-600 mt-3"><input type="checkbox" name="has_dues" value="1"> Crew has outstanding dues</label>
                </div>
                <div class="flex items-center justify-between gap-2 pt-4">
                    <button type="button" id="gcsmSoBack" class="rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-4 py-1.5 hover:bg-slate-100">← Back</button>
                    <button type="submit" id="gcsmSoConfirm" class="rounded-md bg-emerald-600 text-white font-semibold text-sm px-4 py-1.5 hover:bg-emerald-700">✓ Confirm sign off</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
(function () {
    var m = document.getElementById('gcsmSignOffModal');
    if (!m) return;
    var form = document.getElementById('gcsmSignOffForm');
    var s1 = document.getElementById('gcsmSoStep1'), s2 = document.getElementById('gcsmSoStep2');
    var sub = document.getElementById('gcsmSoSub');
    var dateI = document.getElementById('gcsmSoDate'), reason = document.getElementById('gcsmSoReason');
    var noteWrap = document.getElementById('gcsmSoNoteWrap'), note = document.getElementById('gcsmSoNote');
    var err1 = document.getElementById('gcsmSoErr1');
    function close() { m.classList.add('hidden'); m.classList.remove('flex'); }
    function showStep(n) {
        if (n === 1) { s1.classList.remove('hidden'); s2.classList.add('hidden'); sub.textContent = 'Step 1 of 2 — voyage completion details'; }
        else { s1.classList.add('hidden'); s2.classList.remove('hidden'); sub.textContent = 'Step 2 of 2 — update placement status'; }
    }
    function noteRequired() {
        var o = reason.options[reason.selectedIndex];
        return o && o.getAttribute('data-note') === '1';
    }
    function syncNote() {
        if (reason.value && noteRequired()) { noteWrap.classList.remove('hidden'); }
        else { noteWrap.classList.add('hidden'); note.value = ''; }
    }
    reason.addEventListener('change', syncNote);
    window.gcsmSignOff = function (opts) {
        opts = opts || {};
        form.setAttribute('action', opts.action || '');
        form.reset();
        if (opts.signOnDate) { dateI.setAttribute('min', opts.signOnDate); } else { dateI.removeAttribute('min'); }
        err1.classList.add('hidden');
        syncNote();
        showStep(1);
        m.classList.remove('hidden'); m.classList.add('flex');
        try { if (window.gcsmEnhanceDates) window.gcsmEnhanceDates(m); } catch (e) {}
        // reset() cleared the date values; refresh any themed date buttons to match.
        m.querySelectorAll('input[type=date]').forEach(function (d) { d.dispatchEvent(new Event('change', { bubbles: true })); });
        setTimeout(function () { try { dateI.focus(); } catch (e) {} }, 40);
    };
    document.getElementById('gcsmSoNext').addEventListener('click', function () {
        err1.classList.add('hidden');
        if (!dateI.value) { err1.textContent = 'Please choose the sign-off date.'; err1.classList.remove('hidden'); return; }
        if (!reason.value) { err1.textContent = 'Please select a sign-off reason.'; err1.classList.remove('hidden'); return; }
        if (noteRequired() && !note.value.trim()) { err1.textContent = 'This reason needs a note.'; err1.classList.remove('hidden'); return; }
        showStep(2);
    });
    document.getElementById('gcsmSoBack').addEventListener('click', function () { showStep(1); });
    document.getElementById('gcsmSoCancel').addEventListener('click', close);
    m.addEventListener('click', function (e) { if (e.target === m) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !m.classList.contains('hidden')) close(); });
    form.addEventListener('submit', function () { close(); });
})();
</script>

{{-- In-page form submissions (no full reload). Swaps the <main> region and keeps scroll.
     Any anomaly falls back to a normal submit, so behaviour is never worse than before. --}}
<script>
(function () {
    var MAIN = 'gcsmMain';
    function mainEl() { return document.getElementById(MAIN); }

    function reexec(container) {
        container.querySelectorAll('script').forEach(function (old) {
            try {
                var s = document.createElement('script');
                if (old.src) { s.src = old.src; } else { s.textContent = old.textContent; }
                if (old.type) s.type = old.type;
                old.parentNode.replaceChild(s, old);
            } catch (e) {}
        });
    }

    function swap(html, url) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var fresh = doc.getElementById(MAIN);
        var cur = mainEl();
        if (!fresh || !cur) return false;
        cur.innerHTML = fresh.innerHTML;
        if (doc.title) document.title = doc.title;
        if (url) { try { history.pushState({ gcsm: 1 }, '', url); } catch (e) {} }
        try { if (window.gcsmEnhanceSelects) window.gcsmEnhanceSelects(cur); } catch (e) {}
        try { if (window.gcsmEnhanceDates) window.gcsmEnhanceDates(cur); } catch (e) {}
        reexec(cur);
        return true;
    }

    function skip(form) {
        if (form.hasAttribute('data-no-ajax')) return true;
        if (form.classList.contains('js-add') || form.classList.contains('js-del')) return true;
        if (form.getAttribute('target')) return true;
        if (form.getAttribute('data-confirm') && form.dataset.gcsmConfirmed !== '1') return true;
        try { var a = document.createElement('a'); a.href = form.getAttribute('action') || location.href; if (a.origin && a.origin !== location.origin) return true; } catch (e) {}
        return false;
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.nodeName !== 'FORM' || e.defaultPrevented) return;
        if (skip(form)) return;

        var method = (form.getAttribute('method') || 'get').toLowerCase();
        var action = form.getAttribute('action') || location.href;
        var opts = { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, redirect: 'follow' };
        var url = action;

        if (method === 'get') {
            var qs = new URLSearchParams(new FormData(form)).toString();
            url = action.split('#')[0] + (action.indexOf('?') > -1 ? '&' : '?') + qs;
            opts.method = 'GET';
        } else {
            opts.method = 'POST';
            opts.body = new FormData(form);
        }

        e.preventDefault();
        var btns = form.querySelectorAll('button[type=submit], button:not([type]), input[type=submit]');
        btns.forEach(function (b) { b.disabled = true; });

        fetch(url, opts).then(function (res) {
            return res.text().then(function (t) { return { ok: res.ok, finalUrl: res.url || url, text: t }; });
        }).then(function (o) {
            // The request already ran on the server; never re-submit. If we can't swap
            // cleanly, just navigate to the result page so the outcome is still shown.
            if (o.ok && swap(o.text, o.finalUrl)) return;   // scroll stays where the user was
            window.location.href = o.finalUrl;
        }).catch(function () {
            // Network error before the request completed — safe to submit normally.
            btns.forEach(function (b) { b.disabled = false; });
            if (typeof form.submit === 'function') form.submit();
        });
    });

    window.addEventListener('popstate', function () {
        fetch(location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } })
            .then(function (r) { return r.text(); })
            .then(function (t) { if (!swap(t, null)) location.reload(); })
            .catch(function () { location.reload(); });
    });
})();
</script>
@else
    @yield('content')
@endauth
</body>
</html>
