<?php

declare(strict_types=1);

namespace App;

use App\DTO\Session;
use App\Repository\SessionRepository;
use DateTimeImmutable;

final class SessionManager
{
    private const COOKIE_NAME = 'SESSION_ID';
    private const TTL_SECONDS = 1800;
    private const MAX_SESSIONS = 3;

    private Session $session;

    public function __construct(private SessionRepository $repository, private SessionFingerprint $fingerprint, private SessionCrypto $crypto)
    {
        $this->start();
    }

    private function start(): void
    {

        $sessionId = $_COOKIE[self::COOKIE_NAME] ?? null;

        if ($sessionId) {
            $session = $this->repository->findBySessionId($sessionId);


            if ($session) {
                if (!$this->fingerprint->equals($session->fingerprint)) {
                    $this->repository->deactivate($session);
                    $this->session = $this->createNewSession();
                    return;
                }

                if (is_string($session->rawData) && $session->rawData !== '') {
                    $session->data = $this->crypto->decrypt($session->rawData);
                } else {
                    $session->data = [];
                }

                if ($this->isExpired($session)) {
                    $this->repository->deactivate($session);
                    $this->session = $this->createNewSession();
                    return;
                }

                $session->lastActivity = new DateTimeImmutable();
                $this->session = $session;
                return;
            }
        }

        $this->session = $this->createNewSession();
    }

    private function isExpired(Session $session): bool
    {
        $now = new DateTimeImmutable();

        return ($now->getTimestamp() - $session->lastActivity->getTimestamp())
            > self::TTL_SECONDS;
    }

    private function createNewSession(): Session
    {
        $sessionId = bin2hex(random_bytes(32));

        setcookie(self::COOKIE_NAME, $sessionId, [
            'expires' => 0,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        $session = new Session(
            id: null,
            sessionId: $sessionId,
            userId: null,
            data: [],
            fingerprint: $this->fingerprint->generate(),
            createdAt: new DateTimeImmutable(),
            lastActivity: new DateTimeImmutable(),
        );

        $this->repository->create($session);

        return $session;
    }

    public function regenerate(): void
    {
        $this->repository->deactivate($this->session);

        $newSessionId = bin2hex(random_bytes(32));

        setcookie(self::COOKIE_NAME, $newSessionId, [
            'path'     => '/',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        $this->session = new Session(
            id: null,
            sessionId: $newSessionId,
            userId: $this->session->userId,
            data: $this->session->data,
            fingerprint: $this->session->fingerprint,
            createdAt: new DateTimeImmutable(),
            lastActivity: new DateTimeImmutable(),
        );

        $this->repository->create($this->session);
    }


    public function get(string $key, mixed $default = null): mixed
    {
        return $this->session->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->session->data[$key] = $value;

        $encrypted = $this->crypto->encrypt($this->session->data);

        $this->repository->update($this->session, $encrypted);
    }


    public function save(): void
    {
        $encrypted = $this->crypto->encrypt($this->session->data);
        $this->repository->update($this->session, $encrypted);
    }

    public function destroy(): void
    {
        $this->session->isActive = false;
        $this->repository->deactivate($this->session);

        setcookie(self::COOKIE_NAME, '', time() - 3600);
    }

    private function enforceSessionLimits(int $userId): void
    {
        $count = $this->repository->countActiveByUserId($userId);

        if ($count <= self::MAX_SESSIONS) {
            return;
        }

        $excess = $count - self::MAX_SESSIONS;

        $this->repository->deactivateOldestByUserId(
            $userId,
            $excess
        );
    }

    public function login(int $userId): void
    {
        $this->regenerate();

        $this->session->userId = $userId;

        $encrypted = $this->crypto->encrypt($this->session->data);

        $this->repository->update($this->session, $encrypted);

        $this->enforceSessionLimits($userId);
    }

    public function getStatusOfSession(): bool
    {
        return $this->session->isActive;
    }

    public function getLastActivityOfSession(): string
    {
        return $this->session->lastActivity->format('Y-m-d H:i:s');
    }
}
