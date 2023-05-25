<?php

namespace App\Facades;

use App\Services\SumSubNotificationsService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void write(array $data, $message = '')
 *
 * @see SumSubNotificationsService
 */
class SumSubNotificationsFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SumSubNotificationsService::class;
    }
}
