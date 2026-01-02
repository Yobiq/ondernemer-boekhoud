<?php

namespace App\Filament\Client\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class NotificationCenterWidget extends Widget
{
    protected static string $view = 'filament.client.widgets.notification-center-widget';
    protected int | string | array $columnSpan = ['default' => 12, 'md' => 6, 'lg' => 4];
    protected static ?int $sort = 7;
    
    // Poll every 60 seconds
    protected $poll = '60s';
    
    public static function canView(): bool
    {
        // Disabled - widget might cause large icons on dashboard
            return false;
    }
    
    protected function getViewData(): array
    {
        if (!Auth::check()) {
            return [
                'unread_count' => 0,
                'recent_notifications' => [],
                'has_notifications' => false,
            ];
        }
        
        $user = Auth::user();
        
        $unreadCount = $user->unreadNotifications()->count();
        $recentNotifications = $user->notifications()
            ->take(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $this->getNotificationType($notification->type),
                    'title' => $notification->data['title'] ?? 'Melding',
                    'message' => $notification->data['message'] ?? '',
                    'icon' => $this->getNotificationIcon($notification->type),
                    'color' => $this->getNotificationColor($notification->type),
                    'read' => $notification->read_at !== null,
                    'time' => $notification->created_at,
                    'time_human' => $notification->created_at->diffForHumans(),
                ];
            });
        
        return [
            'unread_count' => $unreadCount,
            'recent_notifications' => $recentNotifications,
            'has_notifications' => $recentNotifications->isNotEmpty(),
        ];
    }
    
    protected function getNotificationType(string $class): string
    {
        $parts = explode('\\', $class);
        return end($parts);
    }
    
    protected function getNotificationIcon(string $type): string
    {
        if (str_contains($type, 'Task')) {
            return '📋';
        } elseif (str_contains($type, 'Document')) {
            return '📄';
        } elseif (str_contains($type, 'Approved')) {
            return '✅';
        } elseif (str_contains($type, 'Warning')) {
            return '⚠️';
        } else {
            return '🔔';
        }
    }
    
    protected function getNotificationColor(string $type): string
    {
        if (str_contains($type, 'Task')) {
            return 'warning';
        } elseif (str_contains($type, 'Approved')) {
            return 'success';
        } elseif (str_contains($type, 'Warning') || str_contains($type, 'Error')) {
            return 'danger';
        } else {
            return 'info';
        }
    }
}

