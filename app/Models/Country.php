<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Country
 * @package App\Models
 * @property int $id
 * @property string $code
 * @property string $code_ISO3
 * @property array $phone_code
 * @property string $name
 * @property bool $is_banned
 * @property bool $is_alphanumeric_sender
 */
class Country extends Model
{
    const IS_NOT_BANNED = 0;
    const IS_BANNED = 1;

    const  IS_NOT_ALPHANUMERIC_SENDER = 0;
    const  IS_ALPHANUMERIC_SENDER = 1;

    const ALPHANUMERIC_SENDER_NAMES = [
        self::IS_NOT_ALPHANUMERIC_SENDER => 'Not alphanumeric sender',
        self::IS_ALPHANUMERIC_SENDER => 'Alphanumeric sender'
    ];

    const BANNED_NAMES = [
        self::IS_NOT_BANNED => 'Not banned',
        self::IS_BANNED => 'Banned'
    ];

    const FLAG_PATH = '/cratos.theme/images/flag';

    public $timestamps = false;

    protected $casts = [
        'phone_code' => 'array'
    ];

    protected $fillable = [
        'code', 'phone_code', 'name', 'is_banned', 'code_ISO3', 'is_alphanumeric_sender'
    ];

    /**
     * @param bool|null $isBanned
     * @return array
     */
    public static function getCountries(?bool $isBanned = null): array
    {
        $notBannedCountriesQuery = Country::query();

        if (isset($isBanned)) {
            $notBannedCountriesQuery = $notBannedCountriesQuery->where('is_banned', $isBanned);
        }

        return $notBannedCountriesQuery->orderBy('name')->pluck('name', 'code')->toArray();
    }

    public static function getCountryNameByCode(?string $code): string
    {
        if (!$code) {
            return '';
        }

        $country = Country::query()->where('code', $code)->first();;

        return $country->name ?? '';
    }

    public function getFlagAttribute()
    {
        return $this->code . '.png';
    }

    public function scopeFilterCountries(Builder $query, $countryCode = null, $isBanned = null, $name = null, $phoneCode = null, $countryCodeISO3 = null)
    {
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        if ($countryCode) {
            $query->where('code', 'like', '%' . $countryCode . '%');
        }

        if (isset($isBanned)) {
            $query->where('is_banned', $isBanned);
        }

        if ($phoneCode) {
            $query->where('phone_code', 'like', '%' . $phoneCode . '%');
        }

        if ($countryCodeISO3) {
            $query->where('code_ISO3', $countryCodeISO3);
        }
    }

    public static function isAlphanumericSenderEnable($code)
    {
        $country = Country::query()->where('code', strtolower($code))->first();
        if(!$country) {
            return false;
        }

        return (bool) $country->is_alphanumeric_sender;
    }
}
