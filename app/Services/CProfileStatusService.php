<?php


namespace App\Services;


use App\Enums\CProfileStatuses;
use App\Enums\Providers;
use App\Models\Cabinet\CProfile;
use App\Models\Project;

class CProfileStatusService
{

    /**
     * Return cabinet menu items
     * @return array
     */
    public function cabinetMenu() :array
    {
        $cProfile = auth()->guard('cUser')->user()->cProfile;
        $project = Project::getCurrentProject();

        /* @var ProviderService $providerService */
        $providerService = resolve(ProviderService::class);

        $menu =  [
            'ui_cabinet_menu_wallets' => [
                'url' => 'cabinet.wallets.index',
                'active' =>   true,
            ],
            'ui_cabinet_menu_cards' =>
                $cProfile->account_type === CProfile::TYPE_INDIVIDUAL && config('cratos.wallester.enabled') ?
                    [
                        'url' => 'wallester-cards.index',
                        'active' => true,
                    ]:null,

            'ui_cabinet_menu_history' => [
                'url' => 'cabinet.history',
                'active' => in_array($cProfile->status, CProfileStatuses::ALLOWED_TO_ACTIVE_STATUSES),
            ],
            'ui_cabinet_menu_bank_details' => [
                'url' => 'cabinet.bank.details',
                'active' => in_array($cProfile->status, CProfileStatuses::ALLOWED_TO_ACTIVE_STATUSES),
            ],
            'ui_cabinet_menu_settings' => [
                'url' => 'cabinet.settings.get',
                'active' => in_array($cProfile->status, CProfileStatuses::ALLOWED_TO_ACCESS_SETTINGS_STATUSES),
            ],
            'ui_cabinet_menu_notifications' => [
                'url' => 'cabinet.notifications.index',
                'active' => true,
            ],
        ];

        if (!empty(app(ComplianceService::class)->getComplianceProviderAccount())) {
            $menu['ui_cabinet_menu_compliance'] = [
                'url' => 'cabinet.compliance',
                'active' => in_array($cProfile->status, CProfileStatuses::ALLOWED_TO_SEND_COMPLIANCE_REQUEST_STATUSES),
            ];
        } else {
            $menu['ui_limits'] = [
                'url' => 'cabinet.limits',
                'active' => in_array($cProfile->status, CProfileStatuses::ALLOWED_TO_SEND_COMPLIANCE_REQUEST_STATUSES),
            ];
        }

        if ($cProfile->account_type === CProfile::TYPE_CORPORATE) {
            $menu['ui_cabinet_menu_reports'] = [
                'url' => 'cabinet.reports.index',
                'active' => true,
            ];
        }

        if ($cProfile->account_type === CProfile::TYPE_INDIVIDUAL && config('cratos.wallester.enabled') && $providerService->checkProjectProviderExistsByType($project->id, Providers::PROVIDER_CARD_ISSUING)) {
            $menu['ui_cabinet_menu_cards'] = [
                'url' => 'wallester-cards.index',
                'active' =>   true,
            ];
        }


        return array_filter($menu);
    }
}
