<?php


namespace App\Services\Wallester;

use Firebase\JWT\JWT;
use phpseclib\Crypt\RSA;

class JwtTokenEncoder
{
    private const
        EXPIRATION = 60,
        ALGORITHM = 'RS256',
        SUBJECT = 'api-request';

    private ?string $issuer;
    private ?string $audience;
    private \App\Models\PaymentProvider $cardIssuingProvider;

    /** @var resource  */
    private $privateKey;

    /** @var resource  */
    private $publicKey;

    /**
     * @param string $issuer
     * @param string $audience
     */
    public function __construct(\App\Models\PaymentProvider $cardIssuingProvider)
    {
        $this->cardIssuingProvider = $cardIssuingProvider;

        $this->issuer = $this->getConfigValue('issuer');
        $this->audience = $this->getConfigValue('audience');
        $this->privateKey = openssl_pkey_get_private(
            file_get_contents(storage_path('keys\\' . $this->getConfigValue('path_name') . '\wallester-private.key'))
        );
        $this->publicKey = openssl_pkey_get_public(
            file_get_contents(storage_path('keys\\' . $this->getConfigValue('path_name') . '\wallester-public.key'))
        );
    }

    public function getConfigValue(string $key): ?string
    {
        $configKey = 'cardissuing.' . $this->cardIssuingProvider->api . '.' . $this->cardIssuingProvider->api_account . '.' . $key;
        return config($configKey);
    }


    /**
     * @param string $body
     * @return string
     */
    public function createToken(string $body = '', bool $withRbh = false): string
    {
        $payload = [
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'exp' => time() + self::EXPIRATION,
            'sub' => self::SUBJECT,
        ];
        if ($withRbh) {
            $payload['rbh'] = base64_encode(hash('sha256', $body, true));
        }

        return JWT::encode($payload, $this->privateKey, self::ALGORITHM);
    }

    /**
     * @param string $token
     * @return object
     */
    public function decode(string $token): object
    {
        return JWT::decode($token, $this->publicKey, [self::ALGORITHM]);
    }

    public function decodeRSA(string $encrypted, string $label)
    {
        $rsa = new RSA();

        $beginReplace = '-----BEGIN ' . $label . ' MESSAGE-----';
        $endReplace = '-----END ' . $label . ' MESSAGE-----';
        $encrypted = str_replace([$beginReplace, $endReplace], '', $encrypted);

        $decodedResponse = str_replace(['\/', '\n'], ['/', ''], base64_decode($encrypted));

        $privateKey = file_get_contents(storage_path('keys\\' . $this->getConfigValue('path_name') . '\wallester_encryption_private.key'));
        $rsa->loadKey($privateKey, RSA::PRIVATE_FORMAT_PKCS1);
        $rsa->setPassword(false);
        $rsa->setHash('sha256');
        $rsa->setMGFHash('sha256');
        $rsa->setEncryptionMode(\phpseclib\Crypt\RSA::ENCRYPTION_OAEP);

        return $rsa->_rsaes_oaep_decrypt($decodedResponse, $label);
    }
}
