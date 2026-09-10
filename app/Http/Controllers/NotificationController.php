<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $account = Auth::guard('web')->user() ?: Auth::guard('students')->user();

        return view('notifications.index', [
            'notifications' => $account->notifications()->latest()->paginate(15),
        ]);
    }

    public function read(string $notification)
    {
        $account = Auth::guard('web')->user() ?: Auth::guard('students')->user();
        $item = $account->notifications()->whereKey($notification)->firstOrFail();
        $item->markAsRead();

        return redirect()->to($item->data['url'] ?? route('notifications.index'));
    }

    public function markAllRead()
    {
        $account = Auth::guard('web')->user() ?: Auth::guard('students')->user();
        $account->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }
}
