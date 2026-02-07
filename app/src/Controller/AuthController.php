<?php

namespace App\Controller;

use App\Database\Database;
use App\Repository\SessionRepository;
use App\SessionCrypto;
use App\SessionFingerprint;
use App\SessionManager;

final class AuthController
{
    public function login()
    {

        $repo = new SessionRepository(new Database(config()));
        $fingerprint = new SessionFingerprint();
        $crypto = new SessionCrypto(cypherKey());
        $session = new SessionManager($repo, $fingerprint, $crypto);

        $checkLogin = true;
        $userId = 124545;

        if ($checkLogin) {
            $session->login($userId);
        }

        header('Location: /');
        exit;
    }
}
