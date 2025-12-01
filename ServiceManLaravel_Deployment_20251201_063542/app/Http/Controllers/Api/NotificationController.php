<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = $user->notifications()->with('serviceRequest');
        
        // Filter by notification type if provided
        if ($request->has('type')) {
            $query->where('notification_type', $request->type);
        }
        
        // Filter by read status if provided
        if ($request->has('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }
        
        $notifications = $query->orderBy('created_at', 'desc')->paginate(15);
        
        return response()->json($notifications);
    }

    public function show($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->load('serviceRequest');
        
        return response()->json($notification);
    }

    public function markAsRead($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->markAsRead();
        
        return response()->json(['message' => 'Notification marked as read']);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->notifications()->unread()->update(['is_read' => true]);
        
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function getUnreadCount()
    {
        $user = Auth::user();
        $unreadCount = $user->notifications()->unread()->count();
        
        return response()->json(['unread_count' => $unreadCount]);
    }

    public function destroy($id)
    {
        $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
        $notification->delete();
        
        return response()->json(['message' => 'Notification deleted']);
    }

    public function markAllAsReadByType(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'type' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = Auth::user();
        $user->notifications()
            ->where('notification_type', $request->type)
            ->unread()
            ->update(['is_read' => true]);
        
        return response()->json(['message' => 'Notifications marked as read']);
    }
}