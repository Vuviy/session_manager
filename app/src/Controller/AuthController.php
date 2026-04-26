<?php

declare(strict_types=1);

namespace App\Controller;

use App\SessionManager;

final class AuthController
{
    public function __construct(private SessionManager $session)
    {
    }

    public function login()
    {
        $body = json_decode(file_get_contents('php://input'), true);

        $username = $body['username'] ?? '';
        $password = $body['password'] ?? '';

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username and password required']);
            return;
        }

        // TODO: check credentials in UserRepository or UserService
        // $user = $this->userRepository->findByUsername($username);
        // if (!$user || !password_verify($password, $user->passwordHash)) { ... }

        $userId = 1; // for example
        $this->session->login($userId);

        header('Location: /');
        exit;
    }
}
