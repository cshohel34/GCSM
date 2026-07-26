<form method="GET" class="bg-white rounded-lg shadow p-3 mb-4 flex flex-wrap gap-3 text-sm items-end">
    {!! $slot ?? '' !!}
    <div><label class="block text-xs text-slate-400">From</label><input type="date" name="from" value="{{ $from ?? '' }}" class="border rounded px-2 py-1.5"></div>
    <div><label class="block text-xs text-slate-400">To</label><input type="date" name="to" value="{{ $to ?? '' }}" class="border rounded px-2 py-1.5"></div>
    <button class="bg-[#1F3864] text-white px-4 py-1.5 rounded">Apply</button>
</form>
