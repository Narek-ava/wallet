<?php


namespace App\Models;

use App\Models\Backoffice\BUser;
use App\Models\Backoffice\ProjectEmailProvider;
use App\Models\Backoffice\ProjectSmsProvider;
use App\Models\Cabinet\CUser;
use App\Services\BUserService;
use App\Services\SettingService;

/**
 * Class Project
 * @package App\Models
 * @property string $id
 * @property string $name
 * @property string $domain
 * @property int $status
 * @property string $color_settings
 * @property string $individual_rate_templates_id
 * @property string $corporate_rate_templates_id
 * @property string $bank_card_rate_templates_id
 * @property $created_at
 * @property $updated_at
 * @property CUser $users
 * @property ProjectSmsProvider[] $smsProviders
 * @property PaymentForm[] $paymentForms
 * @property PaymentProvider[] $providers
 * @property ClientSystemWallet[] $clientSystemWallets
 * @property Setting[] $settings
 * @property Notification[] $notifications
 * @property Operation[] $operations
 * @property ApiClient[] $apiClients
 * @property ComplianceProvider[] $complianceProvider
 * @property RateTemplate $individualRate
 * @property RateTemplate $corporateRate
 * @property BankCardRateTemplate $bankCardRate
 *
 */
class Project extends BaseModel
{

    protected $fillable = [
        'name', 'status', 'domain', 'color_settings', 'individual_rate_templates_id', 'corporate_rate_templates_id', 'bank_card_rate_templates_id'
    ];

    protected $appends = ['logoPng', 'editUrl', 'colors'];

    public function getLogoPngAttribute()
    {
        return asset('/cratos.theme/' . $this->id . '/images/logo.png?v=' . time());
    }

    public function operations()
    {
        return $this->hasMany(Operation::class, 'project_id', 'id');
    }

    public function users()
    {
        return $this->hasMany(CUser::class, 'project_id', 'id');
    }

    public function providers()
    {
        return $this->belongsToMany(PaymentProvider::class, 'project_providers', 'project_id', 'provider_id')->withTimestamps()->withPivot(['is_default']);
    }

    public function apiClients()
    {
        return $this->hasMany(ApiClient::class, 'project_id', 'id');
    }

    public function complianceProviderModel()
    {
        return $this->belongsToMany(ComplianceProvider::class, 'project_compliance_providers', 'project_id', 'compliance_provider_id')->withPivot(['renewal_interval']);
    }

    public function complianceProvider()
    {
        return $this->complianceProviderModel()->first();
    }

    public static function getCurrentProject()
    {
        return config('projects.currentProject');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'project_id', 'id');
    }

    public function paymentForms()
    {
        return $this->hasMany(PaymentForm::class, 'project_id', 'id');
    }

    public function smsProviders()
    {
        return $this->hasMany(ProjectSmsProvider::class, 'project_id', 'id');
    }

    public function emailProvider()
    {
        return $this->hasOne(ProjectEmailProvider::class, 'project_id', 'id');
    }

    public function getEditUrlAttribute()
    {
        return route('projects.edit', $this->id);
    }

    public function clientSystemWallets()
    {
        return $this->hasMany(ClientSystemWallet::class, 'project_id', 'id');
    }

    public function settings()
    {
        return $this->hasMany(Setting::class, 'project_id', 'id');
    }

    public function getAddressAttribute(): ?string
    {
        $setting = $this->settings()->where('key', $this->id . '_address')->first();
        return $setting->content ?? null;

    }
    public function getColorsAttribute()
    {
        return json_decode($this->color_settings);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function individualRate()
    {
        return $this->belongsTo(RateTemplate::class, 'individual_rate_templates_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function corporateRate()
    {
        return $this->belongsTo(RateTemplate::class, 'corporate_rate_templates_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function bankCardRate()
    {
        return $this->belongsTo(BankCardRateTemplate::class, 'bank_card_rate_templates_id', 'id');
    }

    public function getCompanyDetailsAttribute()
    {
        $details = app(SettingService::class)->getSettingByKey($this->id . '_address', $this->id)->project_company_details ?? '';
        return json_decode($details);
    }

    /**
     * @return string
     */
    public function domainFullPath()
    {
        return request()->getScheme().'://' . $this->domain;
    }

    /**
     * @return mixed
     */
    public function getManagers()
    {
        return app(BUserService::class)->getManagersByProject($this);
    }

    public function kytProviderModel()
    {
        return $this->belongsToMany(KytProviders::class, 'project_kyt_providers', 'project_id', 'kyt_provider_id');
    }

    public function kytProvider(): ?KytProviders
    {
        return $this->kytProviderModel()->first();
    }

}
