<div class="flex flex-wrap gap-1.5 mb-5 bg-white rounded-xl shadow p-2 w-fit">
    <a href="{{ route('settings.roles') }}" class="ptab {{ request()->routeIs('settings.roles') ? 'active' : '' }}">Roles &amp; Permissions</a>
    <a href="{{ route('settings.lists') }}" class="ptab {{ request()->routeIs('settings.lists') ? 'active' : '' }}">Lists &amp; Dropdowns</a>
</div>
