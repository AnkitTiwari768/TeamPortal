<?php

declare(strict_types=1);

namespace App\Services;

class CryptoService
{
    public const CRYPTO_KEY = 'crypto_key';
    public const CRYPTO_SALT = 'crypto_salt';
    public const CRYPTO_IV = 'crypto_iv';
    public const CRYPTO_KEY_SIZE = 'crypto_key_size';
    public const CRYPTO_ITERATIONS = 'crypto_iterations';

    private function _generateRandomKey(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function getKey(): string
    {
        return $this->_generateRandomKey();
    }

    public function getSalt(): string
    {
        return $this->_generateRandomKey();
    }

    public function getIv(): string
    {
        return $this->_generateRandomKey();
    }

    public function getKeySize(): int
    {
        return 64 / 8;
    }

    public function getIterations(): int
    {
        return 999;
    }

    public function decrypt(string $encryptedText): string|false
    {
        $encrypt = base64_decode($encryptedText);
        $iterations = session(self::CRYPTO_ITERATIONS);
        $salt = hex2bin(session(self::CRYPTO_SALT));
        $iv = hex2bin(session(self::CRYPTO_IV));
        $key = hash_pbkdf2("sha512", session(self::CRYPTO_KEY), $salt, $iterations, 64);

        return openssl_decrypt(
            $encrypt,
            'AES-256-CBC',
            hex2bin($key),
            OPENSSL_RAW_DATA,
            $iv
        );
    }
}
