<?php

namespace App\DTO;

final class Session
{
    public function __construct(
        public ?int $id,
        public string $sessionId,
        public ?int $userId,
        public array $data,
        public string $fingerprint,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $lastActivity,
        public bool $isActive = true
    ) {
    }
}
