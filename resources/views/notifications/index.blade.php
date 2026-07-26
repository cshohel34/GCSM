@extends('layouts.app')
@section('title', isset($crew) && $crew ? 'Reminder log — '.$crew->name : 'Notifications')
@section('actions')
    <form method="POST" action="{{ route('notifications.readAll') }}">@csrf
        <button class="rounded-md border border-slate-300 text-slate-700 font-semibold text-sm px-3 py-1.5 hover:bg-slate-100">Mark all read</button>
    </form>
@endsection
@section('content')

@if (isset($crew) && $crew)
    <div class="bg-white rounded-lg shadow p-4 mb-4 flex flex-wrap items-center gap-3">
        <div class="flex items-center gap-3 min-w-0">
            @if ($crew->photo_path)
                <img src="{{ asset('storage/'.$crew->photo_path) }}" class="w-11 h-11 rounded-lg object-cover ring-1 ring-gold-300 shrink-0" alt="">
            @endif
            <div class="min-w-0">
                <div class="text-xs text-slate-400">Reminder / notification log</div>
                <div class="font-bold text-navy-800 truncate">{{ $crew->name }} <span class="text-slate-400 font-normal text-sm">· {{ $crew->display_id }}</span></div>
            </div>
        </div>
        <div class="ml-auto flex items-center gap-2">
            <a href="{{ route('crew.show', $crew) }}" class="text-xs font-semibold rounded-md border border-slate-300 text-slate-700 px-3 py-1.5 hover:bg-slate-100">← Back to profile</a>
            <a href="{{ route('notifications.index') }}" class="text-xs font-semibold rounded-md border border-slate-300 text-slate-700 px-3 py-1.5 hover:bg-slate-100">All notifications</a>
        </div>
    </div>
@endif

<div class="bg-white rounded-lg shadow p-4">
    <div class="gcsm-head justify-between">
        <h3 class="font-semibold text-sm md:text-base">{{ isset($crew) && $crew ? 'Sent reminder log' : 'Notifications' }}</h3>
        <span class="text-xs text-slate-400">{{ $notifications->total() }} total</span>
    </div>
    <p class="text-xs text-slate-400 -mt-1 mb-3">Notifications are kept permanently and can never be deleted — you can only mark them read or unread. High-priority document &amp; certificate expiry alerts repeat every day until the item is renewed.</p>

    <div class="divide-y divide-slate-100">
        @forelse ($notifications as $n)
            @php $isHigh = in_array($n->priority ?? 'normal', ['high']) || in_array($n->type, ['document_expiry','certificate_expiry','license_expiry']); @endphp
            <div class="py-3 px-2 -mx-2 rounded-lg flex justify-between items-start gap-3 {{ $n->read_at ? '' : 'bg-blue-50/60' }} {{ $isHigh ? 'border-l-4 border-red-400 pl-3' : '' }}">
                <div class="min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        @if ($isHigh)<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">HIGH</span>@endif
                        @unless ($n->read_at)<span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-blue-100 text-blue-700">NEW</span>@endunless
                        <div class="font-semibold text-navy-800">{{ $n->title }}</div>
                    </div>
                    <div class="text-sm text-slate-500 mt-0.5">{{ $n->body }}</div>
                    <div class="text-xs text-slate-400 mt-1">🕒 Sent {{ $n->created_at->format('d M Y, h:i A') }} · {{ $n->created_at->diffForHumans() }}</div>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    @if ($n->link)
                        <form method="POST" action="{{ route('notifications.open', $n) }}">@csrf<button class="text-xs font-semibold text-[#1F3864] hover:underline">Open</button></form>
                    @endif
                    @if ($n->read_at)
                        <form method="POST" action="{{ route('notifications.unread', $n) }}">@csrf<button class="text-xs text-slate-500 hover:underline">Mark unread</button></form>
                    @else
                        <form method="POST" action="{{ route('notifications.read', $n) }}">@csrf<button class="text-xs text-emerald-600 hover:underline">Mark read</button></form>
                    @endif
                </div>
            </div>
        @empty
            <div class="py-10 text-center text-slate-400 text-sm">No notifications yet.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
