<?php

namespace App;

final class SessionFingerprint
{
    public function generate(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        return hash('sha256', $ip . '|' . $userAgent);
    }

    public function equals(string $storedFingerprint): bool
    {
        return hash_equals($storedFingerprint, $this->generate());
    }
}
