<?php

namespace App\Models\Cabinet;

use App\Enums\{AccountStatuses,
    AccountType,
    ComplianceLevel,
    ComplianceRequest,
    Country,
    CProfileStatuses,
    Currency,
    Industry,
    Language,
    LegalForm,
    PaymentFormStatuses};
use App\Models\{Backoffice\BUser,
    BankAccountTemplate,
    CardAccountDetail,
    Commission,
    CompanyOwners,
    CryptoAccountDetail,
    MerchantWebhookAttempt,
    Operation,
    PaymentForm,
    PaymentFormAttempt,
    RatesCategory,

    RateTemplate,
    Account,
    WallesterAccountDetail,
    ReferralLink};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class CProfile
 * @package App\Models
 * @property $id
 * @property $created_at
 * @property $updated_at
 * @property $account_type
 * @property $first_name
 * @property $last_name
 * @property $country
 * @property $company_name
 * @property $company_email
 * @property $company_phone
 * @property $industry_type
 * @property $legal_form
 * @property $beneficial_owner
 * @property $contact_email
 * @property $compliance_level
 * @property $status
 * @property $last_login
 * @property $manager_id
 * @property $refferal_of_user
 * @property $profile_id
 * @property $compliance_officer_id
 * @property $date_of_birth
 * @property $city
 * @property $citizenship
 * @property $zip_code
 * @property $address
 * @property $registration_number
 * @property $legal_address
 * @property $trading_address
 * @property $linkedin_link
 * @property $ceo_full_name
 * @property $secret_key
 * @property $interface_language
 * @property $webhook_url
 * @property $currency_rate
 * @property $phone_verified
 * @property $registration_date
 * @property $status_change_text
 * @property $rates_category_id
 * @property $rate_template_id
 * @property $ref
 * @property $is_merchant
 * @property $ip
 * @property $gender
 * @property $passport
 * @property $timezone
 * @property CUser $cUser
 * @property BUser $manager
 * @property RatesCategory $ratesCategory
 * @property BUser $complianceOfficer
 * @property ComplianceRequest[] $complianceRequest
 * @property RateTemplate $rateTemplate
 * @property BankAccountTemplate[] $bankAccountTemplates
 * @property Account[] $accounts
 * @property Operation[] $operations
 * @property PaymentFormAttempt[] $paymentFormAttempts
 * @property MerchantWebhookAttempt[] $merchantWebhookAttempts
 * @property ReferralLink $referral
 */
class CProfile extends Model
{
    protected $guarded = []; //! temporary
    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string',
    ];

    // @todo maybe Enum
    const STATUS_NEW = 0;
    const STATUS_PENDING_VERIFICATION = 1;
    const STATUS_READY_FOR_COMPLIANCE = 2;
    const STATUS_ACTIVE = 3;
    const STATUS_BANNED = 4;
    const STATUS_SUSPENDED = 5;
    const STATUS_DELETED = 6;

    // account type constants
    const TYPE_INDIVIDUAL = 1;
    const TYPE_CORPORATE = 2;

    const TYPES_LIST = [
        self::TYPE_INDIVIDUAL => 'enum_type_individual',
        self::TYPE_CORPORATE => 'enum_type_corporate',
    ];

     /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function cUser()
    {
        return $this->hasOne(CUser::class, 'c_profile_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function manager()
    {
        return $this->belongsTo(BUser::class, 'manager_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function referral()
    {
        return $this->belongsTo(ReferralLink::class, 'ref', 'id');
    }

    public function getManager()
    {
        return BUser::getBUser();
    }

    public function getManagers()
    {
        return $this->cUser->project->getManagers();
    }

    public function paymentFormAttempts()
    {
        return $this->hasMany(PaymentFormAttempt::class, 'profile_id', 'id');
    }

    public function merchantWebhookAttempts()
    {
        return $this->hasMany(MerchantWebhookAttempt::class, 'merchant_id', 'id');
    }

    public function getSecretKey()
    {
        return $this->secret_key;
    }

    protected function generateSecretKey()
    {
        $this->secret_key = Str::random(27);
        $this->save();
    }

    public function setSecretKey()
    {
        if (!$this->secret_key) {
            $this->generateSecretKey();
        }
    }

    public function paymentForms()
    {
        return $this->hasMany(PaymentForm::class, 'c_profile_id', 'id');
    }

    public function paymentFormsActive()
    {
        return $this->paymentForms()->where('status', PaymentFormStatuses::STATUS_ACTIVE);
    }

    public function companyOwners()
    {
        return $this->hasMany(CompanyOwners::class, 'c_profile_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function ratesCategory()
    {
        return $this->belongsTo(RatesCategory::class, 'rates_category_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function complianceOfficer()
    {
        return $this->belongsTo(BUser::class, 'compliance_officer_id', 'id');
    }

    public function getFiatWallets()
    {
        return $this->accounts()->where([
            'status' => AccountStatuses::STATUS_ACTIVE,
            'is_external' => !AccountType::ACCOUNT_EXTERNAL,
            'account_type' => AccountType::TYPE_FIAT,
            'owner_type' => AccountType::ACCOUNT_OWNER_TYPE_CLIENT
        ])->whereIn('currency', Currency::FIAT_CURRENCY_NAMES)->get();
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Get status name with color
     * @return string
     */
    public function getStatusWithClass()
    {
        // @todo HTML в коде?
        if (isset(CProfileStatuses::NAMES[$this->status])) {
            return '<span class="text-' . CProfileStatuses::STATUS_CLASSES[$this->status] . '">' . CProfileStatuses::getName($this->status) . '</span>';
        }
        return '';
    }

    /**
     * get full name of profile
     * @return mixed|string
     */
    public function getFullName()
    {
        if ($this->account_type == self::TYPE_INDIVIDUAL) {
            return $this->first_name . ' ' . $this->last_name;
        }
        return $this->company_name;
    }

    /**
     * get full name of profile with verify image for cabinet menu
     * @return mixed|string
     */
    public function getFullNameInCabinet()
    {
        $name = $this->getFullName();
        if ($this->compliance_level != ComplianceLevel::VERIFICATION_LEVEL_0) {
            $name .= ' <img src="'.config('cratos.urls.theme').'images/level-1.png"><br>';
        }
        return $name;
    }

    /**
     * get full name of profile with verify image for cabinet menu
     * @return mixed|string
     */
    public function getVerificationName()
    {
        $levelName = '';
        $complianceLevelList = ComplianceLevel::getList();
        if (!empty($complianceLevelList[$this->compliance_level])) {
            $levelName = $complianceLevelList[$this->compliance_level];
            if ($this->compliance_level != ComplianceLevel::VERIFICATION_LEVEL_0) {
                $levelName .= ' <img src="'.config('cratos.urls.theme').'images/level-1.png">';
            }
        }
        return $levelName;
    }

    /**
     * @return array|string|null
     */
    public function getCountryName()
    {
        return $this->country ? \App\Models\Country::getCountryNameByCode($this->country) : '';
    }

    /**
     * @return array|string|null
     */
    public function getIndustryTypeName()
    {
        return $this->industry_type ? Industry::getName($this->industry_type) : '';
    }

    /**
     * @return array|string|null
     */
    public function getLanguageName()
    {
        return $this->interface_language ? Language::getName($this->interface_language) : '';
    }

    /**
     * @return array
     */
    public function getAllowedToChangeStatuses()
    {
        $statusesList = CProfileStatuses::getList();
        unset($statusesList[$this->status]);
        return $statusesList;
    }

    /**
     * returns Profile Compliance requests
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function complianceRequest()
    {
        return $this->hasMany(\App\Models\ComplianceRequest::class, 'c_profile_id', 'id');
    }

    /**
     * returns rate template relation
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function rateTemplate()
    {
        return $this->belongsTo(RateTemplate::class);
    }

    /**
     * returns Bank account temolates
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bankAccountTemplates()
    {
        return $this->hasMany(BankAccountTemplate::class, 'c_profile_id', 'id');
    }

    /**
     * checking if profile has pending compliance request
     * @return bool
     */
    public function hasPendingComplianceRequest()
    {
        return $this->complianceRequest()->where('status', ComplianceRequest::STATUS_PENDING)->exists();
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function getPendingComplianceRequest()
    {
        return $this->complianceRequest()->where('status', ComplianceRequest::STATUS_PENDING)->first();
    }

    /**
     *  Returns last compliance request with approved status
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function lastApprovedComplianceRequest()
    {
        return $this->lastComplianceRequestByStatus(ComplianceRequest::STATUS_APPROVED);
    }

    /**
     *  Returns last compliance request with retry status
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function retryComplianceRequest()
    {
        return $this->lastComplianceRequestByStatus(ComplianceRequest::STATUS_RETRY);
    }

    /**
     * Returns profile last compliance request by status
     * @param int|null $status
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function lastComplianceRequestByStatus(?int $status = null)
    {
        $query = $this->complianceRequest();
        if ($status){
            $query->where('status', $status);
        }
        return $query->orderBy('updated_at', 'desc')->first();
    }

    /** returns cprofile pending compliance request
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function pendingComplianceRequest()
    {
        return $this->lastComplianceRequestByStatus(ComplianceRequest::STATUS_PENDING);
    }

    /** check if last compliance request was declined, if yes returns declined request
     * @return Model|\Illuminate\Database\Eloquent\Relations\HasMany|object|null
     */
    public function lastRequestIfDeclined()
    {
        $lastComplianceRequest = $this->lastComplianceRequestByStatus();
        if ($lastComplianceRequest && $lastComplianceRequest->status == ComplianceRequest::STATUS_DECLINED) {
            return $lastComplianceRequest;
        }
        return null;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function cryptoAccountDetail()
    {
        return $this->hasManyThrough(CryptoAccountDetail::class, Account::class, 'c_profile_id', 'account_id', 'id')
            ->whereHas('account', function($q){
                $q->where('is_external', '!=', AccountType::ACCOUNT_EXTERNAL)->where('account_type', AccountType::TYPE_CRYPTO);
            });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function wallesterAccountDetail()
    {
        return $this->hasManyThrough(WallesterAccountDetail::class, Account::class, 'c_profile_id', 'account_id', 'id')
            ->whereHas('account', function ($q) {
                $q->where('is_external', '!=', AccountType::ACCOUNT_EXTERNAL)->where('account_type', AccountType::TYPE_CARD);
            });
    }

    /**
     * @param string $id
     * @return CryptoAccountDetail
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getCryptoAccountDetailById(string $id): CryptoAccountDetail
    {
        return $this->cryptoAccountDetail()->where('crypto_account_details.id', $id)->firstOrFail();
    }


    /**
     * @param string $id
     * @param int|null $type
     * @return Account
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getAccountById(string $id, ?int $type = null): Account
    {
        $query = $this->accounts()->where(['accounts.id' => $id]);
        if ($type) {
            $query->where(['account_type' => $type]);
        }
        return $query->firstOrFail();
    }

    public function cardAccountDetails()
    {
        return $this->hasManyThrough(CardAccountDetail::class, Account::class, 'c_profile_id', 'account_id', 'id');
    }

    /**
     * returns Commissions relation through rate templates
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function commissions()
    {
        return $this->hasManyThrough(Commission::class, RateTemplate::class, 'id',  'rate_template_id', 'rate_template_id');
    }

    /**
     * returns active Commissions
     */
    public function activeCommissions()
    {
        return $this->commissions()->where('is_active', 1);
    }

    /**
     * returns Commissions relation through rate templates
     * @param $commissionType
     * @param $type
     * @param $currency
     * @return Commission|null
     */
    public function operationCommission($commissionType,  $type, $currency): ?Commission
    {
        return $this->activeCommissions()
            ->where('currency', $currency)
            ->where('commission_type', $commissionType)
            ->where('type', $type)
            ->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accounts()
    {
        return $this->hasMany(Account::class, 'c_profile_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function operations()
    {
        return $this->hasMany(Operation::class, 'c_profile_id', 'id');
    }

    public function getOperationById(string $id, array $types = null): ?Operation
    {
        $query = $this->operations()->where('id' , $id);
        if ($types) {
            $query->whereIn('operation_type', $types);
        }
        return $query->first();
    }


    public function accountByCurrencyType(string $currency,int $type)
    {
        return $this->accounts()
            ->where('status', AccountStatuses::STATUS_ACTIVE)
            ->where('currency', $currency)
            ->where('account_type', $type)
            ->where('is_external', false)
            ->first();
    }

    /**
     * @return array
     */
    public function stepInfo()
    {
        $cUser = $this->cUser;
        // 1 step state
        if ($cUser->email_verified_at) {
            $stepState_1 = 'step-completed';
        }
        elseif (!$cUser->email_verified_at) {
            $stepState_1 = 'step-current';
        }
        else {
            $stepState_1 = 'step-next';
        }

        // 2 step state
        if ($this->status != CProfileStatuses::STATUS_NEW && $this->status != CProfileStatuses::STATUS_PENDING_VERIFICATION && $cUser->email_verified_at) {
            $stepState_2 = 'step-completed';
        }
        elseif ($cUser->email_verified_at && $this->status == CProfileStatuses::STATUS_NEW || $this->status == CProfileStatuses::STATUS_PENDING_VERIFICATION) {
            $stepState_2 = 'step-current';
        }
        else {
            $stepState_2 = 'step-next';
        }

        // 3 step state
        if (!in_array($this->compliance_level, [ComplianceLevel::VERIFICATION_LEVEL_0, ComplianceLevel::VERIFICATION_LEVEL_0]) && $cUser->email_verified_at) {
            $stepState_3 = 'step-completed';
        } elseif (!in_array($this->status, CProfileStatuses::ALLOWED_TO_CHANGE_SETTINGS_STATUSES) && $this->compliance_level == ComplianceLevel::VERIFICATION_LEVEL_0) {
            $stepState_3 = 'step-current';
        } else {
            $stepState_3 = 'step-next';
        }

        return [
            'stepState_1' => $stepState_1,
            'stepState_2' => $stepState_2,
            'stepState_3' => $stepState_3
        ];
    }

    public function bankDetailAccounts()
    {
        return $this->accounts()
            ->whereNotNull('name')
            ->where('status', AccountStatuses::STATUS_ACTIVE)
            ->whereIn('account_type', [AccountType::TYPE_WIRE_SEPA, AccountType::TYPE_WIRE_SWIFT]);
    }

    public function isCorporate(): bool
    {
        return $this->account_type === self::TYPE_CORPORATE;
    }

    public function getOperationByAddress(string $address): ?Operation
    {
        if (!$address) {
            return null;
        }

        /* @var ?Operation $operation*/
        $operation = $this->operations()->where('address', $address)->first();

        return $operation;
    }

    public function getBeneficialOwnersForProfile(): ?array
    {
        return $this->companyOwners()->where('type', \App\Enums\CompanyOwners::TYPE_BENEFICIAL_OWNER)->orderBy('created_at')->pluck('name')->toArray();
    }

    public function getCeosForProfile(): ?array
    {
        return $this->companyOwners()->where('type', \App\Enums\CompanyOwners::TYPE_CEO)->orderBy('created_at')->pluck('name')->toArray();
    }

    public function getShareholdersForProfile(): ?array
    {
        return $this->companyOwners()->where('type', \App\Enums\CompanyOwners::TYPE_SHAREHOLDERS)->orderBy('created_at')->pluck('name')->toArray();
    }

    public function getMainBeneficialOwner(): string
    {
        $beneficialOwners = $this->getBeneficialOwnersForProfile();
        return $beneficialOwners[0] ?? '';
    }

    public function getMainCeo(): string
    {
        $ceos = $this->getCeosForProfile();
        return $ceos[0] ?? '';
    }

    public function getMainShareholder(): string
    {
        $shareholders = $this->getShareholdersForProfile();
        return $shareholders[0] ?? '';
    }

    /**
     * @return bool
     */
    protected function existRateTemplatedByProject()
    {
        return $this->belongsTo(RateTemplate::class)->where('project_id', $this->cUser->project_id)->get()->isNotEmpty();
    }

    /**
     * get full name of profile
     * @return mixed|string
     */
    public function getReferralName()
    {
        if ($this->referral) {
            return $this->referral->name . ' (' . $this->referral->partner->name . ')';
        }
        return '';
    }

    public function isIndividual(): bool
    {
        return $this->account_type == CProfile::TYPE_INDIVIDUAL;
    }
}
