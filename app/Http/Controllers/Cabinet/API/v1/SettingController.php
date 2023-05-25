<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Project;

class SettingController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/project/settings",
     *     summary="Get project settings",
     *     description="This API call is used to get project all settings.",
     *     tags={"018. Settings"},
     *     @OA\Parameter(
     *         name="api-client",
     *         in="header",
     *         description="API client token",
     *         example="emB4HOVT6s70HCmp3NskxgmxvDG8UAoZcZ4x259jLC2aUwII49FVdP2wbObG",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Examples( example="result",  value={
     *                      "logoPng": "https://example.com/logo-png.png",
     *                      "colors": {
     *                          {
     *                          "mainColor": "#000000",
     *                          "buttonColor": "#000000",
     *                          "borderColor": "#000000",
     *                          "notifyFromColor": "#000000",
     *                          "notifyToColor": "#000000",
     *                          }
     *                      },
     *              }, summary="An result object."),
     *                  @OA\Property(
     *                      property="logoPng",
     *                      description="Project logo(png format)",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="colors",
     *                      type="object",
     *                      @OA\Property(
     *                          property="mainColor",
     *                          description="Project main color",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="buttonColor",
     *                          description="Project buttons color",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="borderColor",
     *                          description="Project borders color",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="notifyFromColor",
     *                          description="Project notify color",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="notifyToColor",
     *                          description="Project notify color",
     *                          type="string"
     *                      ),
     *                  ),
     *              ),
     *         )
     *     ),
     * )
     */
    public function settings()
    {
        /** @var Project $project */
        $project = Project::getCurrentProject();

        return response()->json([
            'logoPng' => $project->logoPng ?? '',
            'colors' => [
                'mainColor' => $project->colors->mainColor ?? '',
                'buttonColor' => $project->colors->buttonColor ?? '',
                'borderColor' => $project->colors->borderColor ?? '',
                'notifyFromColor' => $project->colors->notifyFromColor ?? '',
                'notifyToColor' => $project->colors->notifyToColor ?? '',
            ]
        ]);
    }

}
