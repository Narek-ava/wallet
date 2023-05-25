<?php

namespace App\Http\Controllers\Backoffice;

use App\Enums\NotificationRecipients;
use App\Enums\ProjectStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\CustomNotificationRequest;
use App\Models\Cabinet\CProfile;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Services\NotificationUserService;
use App\Services\ProjectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\CProfileService;

class NotificationsController extends Controller
{
    public function index(NotificationService $notificationService, ProjectService $projectService, Request $request)
    {
        $projectId = $request->get('project_id');
        $notificationsWithPaginate = $notificationService->getNotificationsDataWithPaginate($projectId);
        $tags = $notificationService->getTags($projectId);
        $activeProjects =  $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE);
        return view('backoffice.notifications.index', compact('notificationsWithPaginate', 'tags','activeProjects','projectId'));
    }

    public function history(Request $request, NotificationService $notificationService)
    {
        $filters = $request->only(['incoming_from','search','from','to']);
        if (array_key_exists('from', $filters) && array_key_exists('to', $filters) && $filters['to']) {
            $validator = Validator::make($filters, ['to' => 'date|after_or_equal:from',]);
            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }
        $notificationsWithPaginate = $notificationService->getNotificationsDataHistoryWithPaginate($request->project_id);
        $notificationsSearchWithPaginate = $notificationService->getNotificationsDataSearchWithPaginate($request->all());
        return view('backoffice.notifications.history', compact('notificationsWithPaginate', 'notificationsSearchWithPaginate'));
    }

    public function notify(CustomNotificationRequest $request, NotificationService $notificationService, NotificationUserService $notificationUserService,
                           CProfileService $CProfileService, ProjectService $projectService)
    {
        $projectId = $request->get('project_id');
        $bUser = auth()->guard('bUser')->user();
        $isCurrentUser = ((int)$request->recipients === NotificationRecipients::CURRENT_CLIENT);
        if ($isCurrentUser) {
            $cProfile = $CProfileService->getById($request->profile_id, $projectId);
            $notifyUser = [
                $cProfile->cUser->id
            ];
        }else {
            $notifyUser = $notificationUserService->getUserIdsArray((int)$request->recipients, $projectId);
        }
        if (!$projectId) {
            $projectIds = $bUser->is_super_admin ?
                $projectService->getProjectsByStatus(ProjectStatuses::STATUS_ACTIVE)->pluck('id')->toArray()
                : $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions'));
            foreach ($projectIds as $project_id) {
                $notificationService->addNewNotificationForUser($notifyUser, $request->recipients, $request->title ?? $request->tag, $project_id, $request->message);
            }
        } else {
            $notificationService->addNewNotificationForUser($notifyUser, $request->recipients, $request->title ?? $request->tag, $projectId, $request->message);
        }
        return redirect()->back()->with('success', 'A new notification was successfully sent.');
    }

    public function profilesWithNames(Request $request)
    {
        $projectId = $request->project_id;

        return response()->json([
            'profiles' => CProfile::query()->with('cUser')
                ->whereHas('cUser', function ($q) use ($projectId) {
                    $q->where('project_id', $projectId);
                })
                ->pluck( 'profile_id')->toArray()
        ]);

    }

    public function verifyNotification(Request $request)
    {
        verifyNotification($request->id);
        return redirect()->route('backoffice.notifications');
    }

    public function getNotificationBodyByTitle($title)
    {
        $notification = Notification::where('title_message', $title)->first();

        if (!$notification) {
            return '';
        }

        if (\Lang::has('cratos.' . $title . '_body')) {
            return t($title . '_body', json_decode($notification->body_params, true));
        } else {
            return t($notification->body_message, json_decode($notification->body_params, true));
        }
    }

    public function show($notificationId)
    {
        $notification = Notification::find($notificationId);

        if(!$notification){
            return redirect()->route('backoffice.notifications');
        }

        return view('backoffice.notifications.show', compact('notification'));
    }
}
