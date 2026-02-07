<?php

namespace App\Repository;

use App\Database\Database;
use App\DTO\Session;
use DateTimeImmutable;

final class SessionRepository
{
    public function __construct(private Database $db)
    {
    }

    public function findBySessionId(string $sessionId): ?Session
    {
        $row = $this->db
            ->table('sessions')
            ->where('session_id', '=', $sessionId)
            ->where('is_active', '=', 1)
            ->first();

        if (!$row) {
            return null;
        }

        return new Session(
            id: (int)$row['id'],
            sessionId: $row['session_id'],
            userId: $row['user_id'] ? (int)$row['user_id'] : null,
            data: json_decode($row['data']),
            fingerprint: $row['fingerprint'],
            createdAt: new DateTimeImmutable($row['created_at']),
            lastActivity: new DateTimeImmutable($row['last_activity']),
            isActive: (bool)$row['is_active'],
        );
    }

    public function create(Session $session): void
    {
        $this->db->table('sessions')->insert([
            'session_id' => $session->sessionId,
            'user_id' => $session->userId,
            'data' => json_encode($session->data),
            'fingerprint' => $session->fingerprint,
            'created_at' => $session->createdAt->format('Y-m-d H:i:s'),
            'last_activity' => $session->lastActivity->format('Y-m-d H:i:s'),
            'is_active' => (int)$session->isActive,
        ]);
    }

    public function update(Session $session, $encryptedData = []): void
    {
        $this->db->table('sessions')
            ->where('session_id', '=', $session->sessionId)
            ->update([
                'user_id' => $session->userId,
                'data' => empty($encryptedData) ? json_encode($session->data) : $encryptedData,
                'fingerprint' => $session->fingerprint,
                'last_activity' => $session->lastActivity->format('Y-m-d H:i:s'),
                'is_active' => (int)$session->isActive,
            ]);
    }

    public function deactivate(Session $session): void
    {
        $this->db->table('sessions')
            ->where('id', '=', $session->id)
            ->update(['is_active' => 0]);
    }

    public function deactivateById(string $sessionId): void
    {
        $this->db->table('sessions')
            ->where('session_id', '=', $sessionId)
            ->update(['is_active' => 0]);
    }

    public function deactivateOldestByUserId(int $userId, int $limit): void
    {
        $sub = $this->db
            ->table('sessions')
            ->where('user_id', '=', $userId)
            ->where('is_active', '=', 1)
            ->orderBy('last_activity', 'ASC')
            ->limit($limit);

        $this->db
            ->table('sessions')
            ->whereIn('id', $sub)
            ->update(['is_active' => 0]);
    }

    public function findActiveByUserId(int $userId): array
    {
        return $this->db
            ->table('sessions')
            ->where('user_id', '=', $userId)
            ->where('is_active', '=', 1)
            ->orderBy('last_activity', 'ASC')
            ->get();
    }

    public function countActiveByUserId(int $userId): int
    {
        $row = $this->db
            ->table('sessions')
            ->where('user_id', '=', $userId)
            ->where('is_active', '=', 1)
            ->count();

        return  $row ?? 0;
    }
}
