<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     *
     * @OA\Get(
     *     path="/api/modules",
     *     summary="Get modules",
     *     description="This API call is used to get cratos modules",
     *     tags={"Modules"},
     *     @OA\Parameter(
     *         name="api-client",
     *         in="header",
     *         description="API client token",
     *         example="emB4HOVT6s70HCmp3NskxgmxvDG8UAoZcZ4x259jLC2aUwII49FVdP2wbObG",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Cratos Modules",
     *         @OA\JsonContent(
     *            @OA\Property(
     *             property="modules",
     *             description="Modules",
     *             type="array",
     *              @OA\Items(
     *             @OA\Property(
     *                     property="name",
     *                     description="Name of module",
     *                     type="string",
     *                 ),
     *             @OA\Property(
     *                     property="enabled",
     *                     description="Module is active or no",
     *                     type="boolean",
     *                 ),
     *                 ),
     *             ),
     *             @OA\Examples(example="result", value={
     *                    "modules": {{
     *                            "name": "wallester",
     *                            "enabled": true
     *                       }}
     *                   }, summary="An result object."),
     *         ),
     *     ),
     *
     *             )
     *         }
     *     ),
     * )
     *
     * @return JsonResponse
     */
    public function getModules(): JsonResponse
    {
        $modules = config('cratos.modules');
        $data = [];
        foreach ($modules as $moduleName => $moduleValue){
            $data []= [
                'name' => $moduleName,
                'enabled' => $moduleValue
            ];
        }

        return response()->json([
            'modules' => $data
        ]);
    }
}
