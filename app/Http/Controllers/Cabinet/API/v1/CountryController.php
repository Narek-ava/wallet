<?php

namespace App\Http\Controllers\Cabinet\API\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cabinet\API\v1\CountryRequest;
use App\Http\Resources\Cabinet\API\v1\CountryResource;
use App\Services\CountryService;

class CountryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/countries",
     *     summary="Get countries",
     *     description="This API call is used to get countries",
     *     tags={"007. User profile settings"},
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
     *     @OA\Parameter(
     *         description="Countries are banned or not. 0 is for not banned countries, 1 is for banned couontries",
     *         in="query",
     *         name="banned",
     *         required=false,
     *         @OA\Schema(
     *           type="integer",
     *           default="0",
     *           enum={0, 1}
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Name of the country",
     *         in="query",
     *         name="name",
     *         example ="United States",
     *         required=false,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         description="Country code",
     *         in="query",
     *         name="code",
     *         example ="us",
     *         required=false,
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Countries were sent successfully",
     *         @OA\JsonContent(
     *              @OA\Property(
     *                    property="countries",
     *                    description="Countries",
     *                    type="object",
     *                    @OA\Property(
     *                        property="countryCode",
     *                        description="Code of the country",
     *                        type="string",
     *                    ),
     *                    @OA\Property(
     *                        property="countryName",
     *                        description="Name of the country",
     *                        type="string",
     *                    ),
     *                    @OA\Property(
     *                        property="isBanned",
     *                        description="Country is banned or not.",
     *                        type="bool",
     *                     ),
     *                    @OA\Property(
     *                       property="phoneCode",
     *                       description="Phone codes of the country.",
     *                       type="object",
     *                      ),
     *              ),
     *             @OA\Examples(example="result", value={
     *                  "countries": {
     *                      {
     *                         "countryCode": "us",
     *                         "countryName": "United States",
     *                         "isBanned": "false",
     *                         "phoneCode": {"1"},
     *                      },
     *                      {
     *                        "countryCode": "au",
     *                        "countryName": "Australia",
     *                        "isBanned": "false",
     *                        "phoneCode": { "61" },
     *                      },
     *                 },
     *             }, summary="An result object."),
     *          ),
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Countries not found",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         type="object",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="countries_error",
     *                              type="string",
     *                              description="Countries not found",
     *                     ),
     *                     ),
     *                     example={
     *                           "errors":{"countries_error": "Countries not found",}
     *                     }
     *                 )
     *             )
     *         }
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Invalid parameters given.",
     *          content={
     *             @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     @OA\Property(
     *                         property="errors",
     *                         description="Error message",
     *                         @OA\Property(
     *                              property="banned",
     *                              type="string",
     *                              description="Countries not found",
     *                          ),
     *                     ),
     *                     example={
     *                          "errors": {
     *                              "banned": "The selected banned status is invalid.",
     *                          }
     *                     },
     *                 )
     *             )
     *         }
     *     ),
     * )
     */
    public function getCountries(CountryRequest $request, CountryService $countryService)
    {
        $countries = $countryService->getCountries($request->validated());

        if ($countries->isEmpty()) {
            return response()->json([
                "errors" => ['countries_error' => t('error_country_none')]
            ], 404);
        }

        return response()->json([
            'countries' => CountryResource::collection($countries)
        ]);
    }

}
