<?php


namespace App\Facades;

use App\Services\InterfaceActivityLogService;
use \Illuminate\Support\Facades\Facade;

/**
 * @method static InterfaceActivityLogService generateContextId()
 * @method static string|null getContextId()
 * @method static InterfaceActivityLogService setContextId(string $contextId)
 * @method static InterfaceActivityLogService setAction(string $action)
 * @method static InterfaceActivityLogService setType(int $type)
 * @method static InterfaceActivityLogService setReplacements(array $replacements = [])
 * @method static InterfaceActivityLogService setResultType(int $resultType)
 * @method static InterfaceActivityLogService setCUserId(string $cUserId)
 * @method static string|null getCUserId()
 * @method static string|null getBUserId()
 * @method static InterfaceActivityLogService setLevel(int $level)
 * @method static InterfaceActivityLogService setAdditionalData(array $additionalData)
 * @method static bool log()
 * @method static void saveLog(string $message, array $replacements, int $resultType, int $type, string $contextId = null, $cUserId = null, ?string $createdAt = null)
 *
 * @see ActivityLogService
 */
class ActivityLogFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ActivityLogFacade';
    }
}
