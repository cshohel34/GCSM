@extends('layouts.app')
@section('title', 'Recycle Bin — Deleted Crew')
@section('actions')
    <a href="{{ route('crew.index') }}" class="inline-flex items-center gap-1 rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100 transition">← Back to Crew</a>
@endsection
@section('content')

<div class="mb-4 rounded-lg bg-slate-50 border border-slate-200 px-4 py-3 text-sm text-slate-600">
    <span class="font-semibold text-navy-800">Recycle Bin.</span>
    Deleted crew profiles are kept here and are <span class="font-semibold">not permanently erased</span>. A Super Admin can restore any profile back to Crew Management.
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 text-left">
            <tr>
                <th class="px-4 py-2">Crew ID</th>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Rank</th>
                <th class="px-4 py-2">Mobile</th>
                <th class="px-4 py-2">Deleted</th>
                <th class="px-4 py-2 text-right">Action</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($crew as $c)
            <tr class="border-t hover:bg-slate-50">
                <td class="px-4 py-2 font-mono text-xs">{{ $c->display_id }}</td>
                <td class="px-4 py-2 font-medium text-navy-800">{{ $c->name }}</td>
                <td class="px-4 py-2">{{ optional($c->currentRank)->rank_name }}</td>
                <td class="px-4 py-2">{{ $c->mobile }}</td>
                <td class="px-4 py-2 text-slate-500">{{ optional($c->deleted_at)->format('Y-m-d H:i') }}</td>
                <td class="px-4 py-2 text-right">
                    <button type="button"
                            onclick="gcsmOpenRestore('{{ route('crew.restore', $c->id) }}', @js($c->name), @js($c->display_id))"
                            class="inline-flex items-center gap-1 rounded-md bg-[#1F3864] text-white font-semibold text-xs px-3 py-1.5 hover:bg-[#2E74B5] transition">↩ Restore</button>
                </td>
            </tr>
        @empty
            <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">The Recycle Bin is empty — no deleted crew profiles.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $crew->links() }}</div>

{{-- Restore confirmation modal (Super Admin only) — themed to the GCSM navy/gold template --}}
<div id="restoreCrewModal" class="hidden fixed inset-0 z-[100] items-center justify-center bg-[#12233F]/60 backdrop-blur-sm p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden ring-1 ring-slate-200">
        <div class="px-5 py-4 bg-gradient-to-r from-[#1F3864] to-[#12233F] flex items-center gap-3">
            <span class="w-9 h-9 rounded-full bg-emerald-500/20 text-emerald-200 flex items-center justify-center text-lg">↩</span>
            <div>
                <div class="text-white font-bold leading-tight">Restore crew profile</div>
                <div class="text-[11px] text-gold-300">Super Admin authorisation required</div>
            </div>
        </div>
        <form id="restoreCrewForm" method="POST" action="" class="p-5 space-y-4">
            @csrf
            <p class="text-sm text-slate-600">
                You are about to restore <span id="restoreCrewName" class="font-semibold text-navy-800"></span>
                (<span id="restoreCrewId" class="font-mono text-xs"></span>) back to Crew Management.
            </p>
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-400 mb-1">Enter your account password to confirm</label>
                <input type="password" name="password" required autocomplete="off"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1F3864] @error('password') border-red-400 ring-1 ring-red-300 @enderror"
                       placeholder="••••••••">
                @error('password')<div class="text-xs text-red-600 mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="flex items-center justify-end gap-2 pt-1">
                <button type="button" onclick="gcsmCloseRestore()"
                        class="rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-4 py-1.5 hover:bg-slate-100">Cancel</button>
                <button type="submit"
                        class="rounded-md bg-[#1F3864] text-white font-semibold text-sm px-4 py-1.5 hover:bg-[#2E74B5]">Restore profile</button>
            </div>
        </form>
    </div>
</div>

<script>
    function gcsmOpenRestore(action, name, id) {
        var m = document.getElementById('restoreCrewModal');
        document.getElementById('restoreCrewForm').setAttribute('action', action);
        document.getElementById('restoreCrewName').textContent = name;
        document.getElementById('restoreCrewId').textContent = id;
        m.classList.remove('hidden'); m.classList.add('flex');
        var pw = m.querySelector('input[name=password]'); if (pw) { pw.value=''; setTimeout(function(){pw.focus();}, 50); }
    }
    function gcsmCloseRestore() {
        var m = document.getElementById('restoreCrewModal');
        m.classList.add('hidden'); m.classList.remove('flex');
    }
    @if (session('restore_error'))
        gcsmOpenRestore(@js(session('restore_error')['action']), @js(session('restore_error')['name']), @js(session('restore_error')['id']));
    @endif
</script>
@endsection
