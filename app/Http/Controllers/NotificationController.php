<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = AppNotification::where('user_id', $request->user()->id);

        // Optional per-crew sent-log ("See all" from a crew profile).
        $crew = null;
        if ($request->filled('crew')) {
            $crew = \App\Models\CrewProfile::find($request->integer('crew'));
            $query->where('crew_profile_id', $request->integer('crew'));
        }

        $notifications = $query->latest()->paginate(30)->withQueryString();
        return view('notifications.index', compact('notifications', 'crew'));
    }

    public function read(Request $request, AppNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 404);
        $notification->update(['read_at' => now()]);
        return back();
    }

    /** Mark a notification as unread again (notifications are never deleted). */
    public function unread(Request $request, AppNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 404);
        $notification->update(['read_at' => null]);
        return back();
    }

    /** Open the linked record and mark the notification read. */
    public function open(Request $request, AppNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 404);
        $notification->update(['read_at' => now()]);
        return $notification->link ? redirect($notification->link) : back();
    }

    public function readAll(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
        return back()->with('status', 'All marked read.');
    }
}
