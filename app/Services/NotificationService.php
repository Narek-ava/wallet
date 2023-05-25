<?php
namespace App\Services;

use App\Enums\NotificationRecipients;
use App\Enums\OperationOperationType;
use App\Models\Backoffice\BUser;
use App\Models\Cabinet\CUser;
use App\Models\Notification;
use App\Enums\Notification as NotificationTitles;
use App\Models\NotificationUser;
use App\Models\Operation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class NotificationService extends CoreNotification
{

    protected function getModelClass(): string
    {
        return Notification::class;
    }

    private function getNotificationData(string $bodyMessage, $recepients, $titleMessage, array $titleParams, array $bodyParams, bool $isSystem, ?string $project_id = null): array
    {
        return [
            'recepient'  => $recepients,
            'title_message'  => $titleMessage,
            'body_message'  => $bodyMessage,
            'title_params'  => empty($titleParams) ? json_encode([]) : collect($titleParams)->toJson(),
            'body_params'  => empty($bodyParams) ? json_encode([]) : collect($bodyParams)->toJson(),
            'b_user_id' => Auth::user() instanceof BUser ? Auth::id() : null,
            'is_system' => $isSystem,
            'project_id' => $project_id,
        ];
    }

    public function createNotification(string $bodyMessage, $recepients, $titleMessage = null, array $titleParams = [], array $bodyParams = [], bool $isSystem = false, ?string $project_id = null): int
    {
        return $this->getModel()->create($this->getNotificationData($bodyMessage, $recepients, $titleMessage, $titleParams, $bodyParams, $isSystem, $project_id))->id;
    }

    public function addNewNotificationForUser(array $userId, int $recipient, string $tag, string $projectId, $message)
    {
        /* @var NotificationUserService $notificationUserService */
        $notificationUserService = resolve(NotificationUserService::class);
        $notificationId = $this->createNotification($message, $recipient, $tag, [], [],true, $projectId);
        $userType = $recipient == NotificationRecipients::ALL_USERS ? BUser::class : CUser::class;
        $notificationUserService->createNotificationUserRecord($userId, $notificationId, $userType);
    }

    public function setOperationUrlForNotification(string $operationId, string $notificationId)
    {

        $operation = $operationId ? Operation::find($operationId) : null;
        $notification = $notificationId ? Notification::find($notificationId) : null;
        if ($operation && $notification) {
            if (in_array($operation->operation_type, [\App\Enums\OperationOperationType::TYPE_WITHDRAW_CRYPTO, \App\Enums\OperationOperationType::TYPE_TOP_UP_CRYPTO, OperationOperationType::TYPE_WITHDRAW_CRYPTO_PF, OperationOperationType::TYPE_TOP_UP_CRYPTO_PF, OperationOperationType::TYPE_TOP_UP_CRYPTO_EXTERNAL_PF])) {
                $notification->operation_url = route('backoffice.withdraw.crypto.transaction', $operation->id, true);
            } else if (in_array($operation->operation_type, [\App\Enums\OperationOperationType::TYPE_WITHDRAW_WIRE_SEPA, \App\Enums\OperationOperationType::TYPE_WITHDRAW_WIRE_SWIFT])) {
                $notification->operation_url = route('backoffice.withdraw.wire.transaction', $operation->id, true);
            }else if(in_array($operation->operation_type, [\App\Enums\OperationOperationType::TYPE_CARD, \App\Enums\OperationOperationType::MERCHANT_PAYMENT, OperationOperationType::TYPE_CARD_PF]))
                $notification->operation_url = route('backoffice.card.transaction', $operation->id, true);
            else {
                $notification->operation_url = route('backoffice.show.transaction', $operation->id, true);
            }
            $notification->save();
        }
    }

    public function getNotificationsDataWithPaginate(?string $projectId = null)
    {
        $bUser = auth()->guard('bUser')->user();
        $query = $this->getModel()->whereIn('recepient', NotificationRecipients::MANAGERS)->with('bUser');
        if ($projectId) {
            $query->where('project_id', $projectId);
        } else if (!$bUser->is_super_admin) {
            $projectIds = $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions'));
            $query->whereIn('project_id', $projectIds);
        }
        return $query->orderBy('updated_at', 'desc')->paginate(config('cratos.pagination.notifications'));
    }

    public function getNotificationsDataHistoryWithPaginate(?string $project_id = null)
    {
        $query = $this->getModel()->where('is_system', true)->with('bUser');
        $bUser = auth()->guard('bUser')->user();
        if ($project_id) {
            $query->where('project_id', $project_id);
        } elseif (!$bUser->is_super_admin) {
            $projectIds = $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions'));
            $query->whereIn('project_id', $projectIds);
        }
        return $query->orderBy('updated_at', 'desc')->paginate(config('cratos.pagination.notifications'));
    }

    public function getNotificationsDataSearchWithPaginate(array $filters)
    {
        $bUser = auth()->guard('bUser')->user();
        $query = $this->getModel()->with('bUser');
        $recepients = [];
        if (!empty($filters['incoming_from'])) {
            foreach (NotificationRecipients::RECIPIENTS as $key => $recepient) {
                if (strpos(strtolower(t($recepient)), strtolower($filters['incoming_from'])) !== false)
                    $recepients[] = $key;
            }
        }
        if (!empty($recepients)) {
            $query->whereIn('recepient', $recepients);
        }
        if (!empty($filters['from'])) {
            $query->where('updated_at', '>=', Carbon::create($filters['from'])->toDateString() . ' 00:00:00');
        }
        if (!empty($filters['to'])) {
            $query->where('updated_at', '<=', Carbon::create($filters['to'])->toDateString() . ' 23:59:59');
        }
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        } elseif (!$bUser->is_super_admin) {
            $projectIds = $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions'));
            $query->where('project_id', $projectIds);
        }

        $messages = [];
        if (!empty($filters['search'])) {
            foreach (\App\Enums\Notification::MESSAGES as $value) {
                if (strpos(strtolower(t($value)), $filters['search']) !== false) {
                    array_push($messages, $value);
                }
            }

            $search = $filters['search'];
            $query->where(function ($q) use ($search, $messages) {
                $q->where('body_message', 'like', '%' . $search . '%')->orWhereIn('body_message', $messages);
            });
        }
        $pagination = $query->paginate(15, ['*'], 'per_page')->appends(request()->query());
        $pagination->setPageName('per_page');
        return $pagination;
    }

    public function getTags(?string $projectId = null)
    {
        $creates = $this->getModel()->whereNotIn('title_message', NotificationTitles::TITLES)->whereNotIn('title_message', NotificationTitles::DISPOSABLE_TITLES);
        $bUser = auth()->guard('bUser')->user();

        if ($projectId) {
            $creates->where('project_id', $projectId);
        }else if (!$bUser->is_super_admin) {
            $projectIds = $bUser->getAvailableProjectsByPermissions(config()->get('projects.currentPermissions'));
            $creates->whereIn('project_id', $projectIds);
        }
        $creates = $creates->pluck('title_message')->unique()->toArray();
        return NotificationTitles::TITLES + $creates;
    }

    public function getNotificationByTitleAndBody($title, $body)
    {
        return $this->getModel()->where(['title_message' => $title, 'body_message' => $body])->first();
    }

    public function getNotificationById($id)
    {
        return $this->getModel()->find($id);
    }

    public function notificationForManagerFromClientCompliance($cUser)
    {
        $notification = Notification::create([
            'recepient' => NotificationRecipients::MANAGER,
            'title_message' => 'update_compliance_document_header',
            'body_message' => 'update_compliance_document_body',
            'title_params' => json_encode([]),
            'body_params' => json_encode(['profileId' => $cUser->cProfile->profile_id])
        ]);
        // todo change to manager relation
        $manager = $cUser->cProfile->getManager();
        (new NotificationUserService)->createNotificationComplianceUpdateDocument($manager, $notification->id);
    }
}
