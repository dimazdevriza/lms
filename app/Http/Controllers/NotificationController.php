<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(Notification $notification): RedirectResponse|JsonResponse
    {
        abort_unless($notification->user_id == Auth::id(), 403);

        $notification->update(['read_at' => now()]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'url' => $notification->url
            ]);
        }

        if ($notification->url) {
            return redirect($notification->url);
        }

        return back()->with('success', 'Notifikasi ditandai sebagai dibaca.');
    }

    public function markAllAsRead(): RedirectResponse|JsonResponse
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi ditandai sebagai dibaca.');
    }

    public function poll(Request $request): JsonResponse
    {
        $since = $request->query('since');
        
        $query = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->orderByDesc('created_at');
        
        if ($since) {
            try {
                $query->where('created_at', '>', Carbon::parse($since));
            } catch (\Exception $e) {
                // Ignore parse failures
            }
        }
        
        $notifications = $query->limit(5)->get(['id', 'title', 'message', 'url', 'created_at']);
        $unreadCount = Notification::where('user_id', Auth::id())->whereNull('read_at')->count();
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }
}
