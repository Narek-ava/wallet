<?php

namespace App\Http\Controllers\Cabinet;

use App\Enums\ProjectStatuses;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\Project;
use App\Services\NotificationUserService;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function index()
    {
        $notifications = \C\c_user()->notifications()->orderByDesc('created_at')->paginate(config('cratos.pagination.notifications'));
        $cProfile = getCProfile();
        $timezone = $cProfile->timezone;
        return view('cabinet.notifications.index', compact('notifications', 'timezone'));
    }
}
