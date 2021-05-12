<?php

declare(strict_types=1);

namespace Kaxiluo\ApiSignature;

class SignatureUtil
{
    public static function sign(string $beSignedString, string $key, string $algo = 'SHA256')
    {
        return hash_hmac($algo, $beSignedString, $key);
    }

    public static function verify(string $beSignedString, string $signature, string $key, string $algo = 'SHA256'): bool
    {
        $hash = static::sign($beSignedString, $key, $algo);
        return hash_equals($signature, $hash);
    }
}
