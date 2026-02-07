<?php

namespace App;

use RuntimeException;

final class SessionCrypto
{
    private const CIPHER = 'aes-256-cbc';

    public function __construct(
        private string $key
    ) {
        $key = base64_decode(substr($key, 7), true);

        if ($key === false) {
            throw new RuntimeException('Invalid base64 encryption key');
        }

        $this->key = $key;
    }

    public function encrypt(array $data): string
    {
        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = random_bytes($ivLength);

        $payload = serialize($data);

        $encrypted = openssl_encrypt(
            $payload,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($iv . $encrypted);
    }

    public function decrypt(string $cipherText): array
    {
        $raw = base64_decode($cipherText);

        $ivLength = openssl_cipher_iv_length(self::CIPHER);
        $iv = substr($raw, 0, $ivLength);
        $encrypted = substr($raw, $ivLength);

        $decrypted = openssl_decrypt(
            $encrypted,
            self::CIPHER,
            $this->key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return unserialize($decrypted);
    }
}
