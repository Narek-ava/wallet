<?php

use App\Enums\Currency;
use App\Models\Cabinet\CProfile;
use App\Models\Country;
use App\Models\NotificationUser;
use App\Services\CProfileStatusService;
use App\Services\NotificationUserService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

function activeMenu($uri = '') {
  return  \Request::route()->getName() == $uri || request()->is($uri) || request()->is($uri.'/*') ? 'active' : '';
}


/**
 * Translate the given message.
 *
 * @param  string|null  $key
 * @param  array  $replace
 * @param  string|null  $locale
 * @return string|array|null
 */
function t($key = null, $replace = [], $locale = null) {
    if (!\Illuminate\Support\Facades\Lang::has('cratos.'.$key)) {
        logger()->error('translationMissing: '.$key);
    }
    return __('cratos.'.$key, $replace, $locale);
}

function eur_format(?float $number): string
{
    if (is_null($number)) {
        return '';
    }
    return Currency::FIAT_CURRENCY_SYMBOLS[Currency::CURRENCY_EUR].number_format($number, 2);
}

function br2nl(string $string = null): string
{
    return $string ? preg_replace('#<br\s*/?>#i', "\n", $string) : '';
}

/**
 * Returns Cabinet menu items
 * @return array
 */
function cabinet_menu() : array
{
    $cProfileStatusServiceObj = new CProfileStatusService();
    return $cProfileStatusServiceObj->cabinetMenu();
}

/**
 * Check if valid date
 * @return bool
 */
function isValidDate($date): bool
{
    return date('Y-m-d', strtotime($date)) === $date;
}

function getNotification(string $userId): ?NotificationUser
{
    return (new NotificationUserService())->getNotification($userId);
}

function verifyNotification(int $id): void
{
    (new NotificationUserService())->verifyNotification($id);
    return;
}

function getNotificationPartial($admin = false)
{
    return view('cabinet.partials.notification', ['notify' => getNotification(Auth::id()), 'admin' => $admin]);
}

function formatMoney(?float $amount, ?string $currency)
{
    if (is_null($amount) || is_int($amount)) {
        return $amount;
    }
    $amount = in_array($currency, Currency::FIAT_CURRENCY_NAMES) ? floatval($amount) : rtrim(sprintf('%.8f', floatval(number_format($amount, 8, '.', ''))) ,'\.0');
    return $amount;
}

function generalMoneyFormat(?float $amount, ?string $currency, bool $appendCurrency = false)
{
    $suffix = $appendCurrency ? " {$currency}" : '';

    if (is_null($amount) || $amount == 0) {
        return $amount . $suffix;
    }
    $formattedAmount = in_array($currency, Currency::FIAT_CURRENCY_NAMES) ? number_format($amount, 2) : number_format($amount, 8, '.', '');
    return $formattedAmount . $suffix;
}

function getCProfile(): ?CProfile
{
    return auth()->user()->cProfile ?? null;
}

function moneyFormatWithCurrency(string $currency, float $amount): ?string
{
   return ( in_array($currency, \App\Enums\Currency::FIAT_CURRENCY_NAMES) ? \App\Enums\Currency::FIAT_CURRENCY_SYMBOLS[$currency] : $currency ) . ' ' . generalMoneyFormat($amount, $currency);
}

function isRouteFromBackoffice()
{
    return Route::getCurrentRoute()->getPrefix() === '/backoffice';
}


function getNotBannedCountriesForRegister(): string
{
    $countries = Country::query()
        ->where('is_banned', Country::IS_NOT_BANNED)
        ->orderBy('name')
        ->get();

    $data = [];

    foreach ($countries as $country) {
        $tempData = [
            'text' => $country->name,
            'iso_code' => $country->code,

        ];

        $phoneCode = $country->phone_code;

        foreach ($phoneCode as $code) {
            $tempData = array_merge($tempData, [
                'id' => $code,
                'country_code' => '+' . $code,
            ]);
            $data[] = $tempData;
        }
    }
    return json_encode($data);
}

function getCorrectAmount(float $amount, string $currency): float
{
    if (in_array($currency, Currency::CURRENCIES_NEAR_USD)) {
        $amount = round($amount - 0.000005, 5);
    }

    return $amount;
}

function htmlCut(string $text, int $max_length, string $end = '...'): string
{
    if (mb_strwidth($text, 'UTF-8') <= $max_length) {
        return $text;
    }

    $stripped_text = strip_tags($text);

    if ($stripped_text == $text) {
        return \Illuminate\Support\Str::limit($text, $max_length, '...');
    }

    $tags   = array();
    $result = "";
    $is_open   = false;
    $grab_open = false;
    $is_close  = false;
    $in_double_quotes = false;
    $in_single_quotes = false;
    $tag = "";

    $i = 0;
    $stripped = 0;
    $resultLength = 0;

    while ($i < strlen($text) && $resultLength < $max_length  && $stripped < strlen($stripped_text) && $stripped < $max_length)
    {
        $symbol  = $text[$i];
        $result .= $symbol;

        switch ($symbol)
        {
            case '<':
                $is_open   = true;
                $grab_open = true;
                break;

            case '"':
                if ($in_double_quotes)
                    $in_double_quotes = false;
                else
                    $in_double_quotes = true;

                break;

            case "'":
                if ($in_single_quotes)
                    $in_single_quotes = false;
                else
                    $in_single_quotes = true;

                break;

            case '/':
                if ($is_open && !$in_double_quotes && !$in_single_quotes)
                {
                    $is_close  = true;
                    $is_open   = false;
                    $grab_open = false;
                }

                break;

            case ' ':
                if ($is_open)
                    $grab_open = false;
                else
                    $stripped += mb_strwidth($symbol);
                break;

            case '>':
                if ($is_open)
                {
                    $is_open   = false;
                    $grab_open = false;
                    array_push($tags, $tag);
                    $tag = "";
                }
                else if ($is_close)
                {
                    $is_close = false;
                    array_pop($tags);
                    $tag = "";
                }

                break;

            default:
                if ($grab_open || $is_close)
                    $tag .= $symbol;

                if (!$is_open && !$is_close)
                    $stripped += mb_strwidth($symbol);
        }

        $i++;

        $resultLength = mb_strwidth($stripped);
    }

    while ($tags)
        $result .= "</".array_pop($tags).">";

    return $result . $end;
}


function dateFromUserToUTC(string $date, string $format = 'Y-m-d H:i:s', string $timezone = 'UTC'): string
{
    return Carbon::createFromFormat($format, $date, $timezone)->timezone('UTC')->format($format);
}
