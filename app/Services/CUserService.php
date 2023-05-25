<?php

namespace App\Services;

use App\Enums\AccountType;
use App\Enums\CProfileStatuses;
use App\Enums\TwoFAType;
use App\Models\ApiClient;
use App\Models\Cabinet\CProfile;
use App\Models\Cabinet\CUser;
use App\Models\EmailVerification;
use App\Models\ReferralPartner;
use Carbon\Carbon;
use GuzzleHttp\Psr7\ServerRequest as GuzzleRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Passport\Client as OClient;
use Laravel\Passport\Http\Controllers\ConvertsPsrResponses;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\AuthorizationServer;
use Lcobucci\JWT\Parser as JwtParser;

class CUserService
{
    use ConvertsPsrResponses;

    CONST USER_PASSWORD_RESET_CACHE = 'user_password_reset_cache_';
    CONST GRANT_TYPE_PASSWORD = 'password';
    CONST GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';

    /**
     * Create CUser
     *
      */
    public function create($request, $verifyEmail = false): CUser
    {
        $user = CUser::create([
            'id' => Str::uuid(),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => bcrypt($request->input('password')),
            'project_id' => $request->project_id,
            'email_verified_at' => $verifyEmail ? now() : null,
        ]);
        return $user;
    }

    /**
     * Create CUser
     *
     * @param array $registerData
     * @return CUser
     */
    public function createCUser(array $registerData, $turnOnTwoFa = false): CUser
    {
        return CUser::create([
            'id' => Str::uuid(),
            'email' => $registerData['email'],
            'phone' => $registerData['phone'],
            'password' => $registerData['password_encrypted'],
            'project_id' => $registerData['project_id'] ?? null,
            'two_fa_type' => $turnOnTwoFa ? TwoFAType::EMAIL : TwoFAType::NONE
        ]);
    }

    /**
     * Find User By Email
     *
      */
    public function findByEmail($email, $id = null)
    {
        $query = $this->getUserByEmailQuery($email);
        if ($id) {
            $query->where('id', '<>', $id);
        }
        return $query->get();
    }

    /**
     * @param $email
     * @return Builder|Model|object|null
     */
    public function firstByEmail($email)
    {
        return $this->getUserByEmailQuery($email)->first();
    }

    public function getUserByEmailQuery($email)
    {
        return CUser::query()->where('email', $email);
    }

    public function getUserByPhone($phone)
    {
        return CUser::query()->where('phone', $phone)->first();
    }

    public function findById($id)
    {
        return CUser::find($id);
    }

    public function getUserIdsArray($projectId = null)
    {
        $query = CUser::query();
        if ($projectId) {
            if (is_array($projectId)) {
                $query->whereIn('project_id',$projectId);
            } else {
                $query->where('project_id',$projectId);
            }
        }
        return $query->pluck('id')->toArray();
    }

    public function getUsersByType(int $type, $projectId = null)
    {
        $query = CUser::query()->whereHas('cProfile', function ($query) use ($type) {
            $query->where('account_type', $type);
        });
        if ($projectId) {
            if (is_array($projectId)) {
                $query->whereIn('project_id',$projectId);
            } else {
                $query->where('project_id',$projectId);
            }
        }
        return $query->pluck('c_users.id')->toArray();
    }

    public function getUserEmailsArray($projectId = null)
    {
        $query = CUser::query()->whereHas('cProfile', function ($q) {
                $q->where('status', CProfileStatuses::STATUS_ACTIVE);
        });
        if ($projectId) {
            if (is_array($projectId)) {
                $query->whereIn('project_id', $projectId);
            } else {
                $query->where('project_id', $projectId);
            }
        }
        return $query->pluck('email')->toArray();
    }

    public function hasNotCurrentIp($ip, $cUserId)
    {
        $cUserIp = CUser::where('id', $cUserId)->whereHas('ips', function ($q) use ($ip) {
            $q->where('ip', $ip);
        })->first();
        if (!$cUserIp) {
            (new IpService)->addIpForCUser($cUserId, $ip);
            return true;
        }
        return false;
    }

    public function getUserByEmailAndAccountType(string $email, int $accountType)
    {
        return $this->getUserByEmailQuery($email)->whereHas('cProfile', function ($q) use($accountType){
                return  $q->where('account_type', $accountType);
        })->first();
    }

    public function putRegisterDataIntoCache(string $key, array $data): bool
    {
        return Cache::put(CUser::REGISTER_DATA_CACHE_KEY . $key, $data, 1800);
    }

    public function getRegisterDataFromCache(string $key): array
    {
        return Cache::get(CUser::REGISTER_DATA_CACHE_KEY . $key) ?? [];
    }

    public function hasRegisterDataFromCache(string $key): bool
    {
        return Cache::has(CUser::REGISTER_DATA_CACHE_KEY . $key);
    }

    public function deleteRegisterDataFromCache(string $key): bool
    {
        return Cache::forget(CUser::REGISTER_DATA_CACHE_KEY . $key);
    }

    public function getAccessAndRefreshTokens($tokenData, $type) {

        $oClient = OClient::where('password_client', 1)->first();

        if(!$oClient) {
            return false;
        }

        switch ($type) {
            case self::GRANT_TYPE_PASSWORD:
                $refresh_token_data = [
                    'grant_type' => 'password',
                    'client_id' => $oClient->id,
                    'client_secret' => $oClient->secret,
                    'username' => $tokenData['email'],
                    'password' => $tokenData['password'],
                    'scope' => '',
                ];
                break;
            case self::GRANT_TYPE_REFRESH_TOKEN:
                $refresh_token_data = [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $tokenData['refresh_token'],
                    'client_id' =>  $oClient->id,
                    'client_secret' => $oClient->secret,
                    'scope' => '',
                ];
                break;
            default:
                return false;

        }

        try {
            $psrReponse = app(AuthorizationServer::class)->respondToAccessTokenRequest((new GuzzleRequest('GET', '/oauth/token'))->withParsedBody(
                $refresh_token_data
            ), new \GuzzleHttp\Psr7\Response());

            $responseBody = $this->convertResponse(
                $psrReponse
            );

            $response = json_decode($responseBody->getContent());
            $this->setUserAgentName($response->access_token, $tokenData['userAgent']);

            return $response;
        }catch (\Exception $exception) {
            return false;
        }

    }

    public function revokeAccessAndRefreshTokens($token)
    {
        $tokenRepository = app(TokenRepository::class);
        $tokenRepository->revokeAccessToken($token);

        Passport::refreshToken()->where('access_token_id', $token)->update(['revoked' => true]);
    }

    public function setTokensExpireTime(ApiClient $apiClient)
    {
        Passport::tokensExpireIn(Carbon::now()->addHours($apiClient->access_token_expires_time));
        Passport::refreshTokensExpireIn(Carbon::now()->addMonths($apiClient->refresh_token_expires_time));
    }

    protected function setUserAgentName($token, $userAgent)
    {
        $tokenRepository = app(TokenRepository::class);
        $tokenId = $this->getAccessTokenId($token);

        $accessToken = $tokenRepository->find($tokenId);

        if($accessToken) {
            $accessToken->update(['name' => $userAgent]);
        }
    }

    protected function getAccessTokenId($token)
    {
       return  app(JwtParser::class)->parse($token)->claims()->get('jti') ?? '';
    }
}
