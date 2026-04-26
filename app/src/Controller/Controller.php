<?php

declare(strict_types=1);

namespace App\Controller;

use App\SessionManager;

final class Controller
{

    public function __construct(private SessionManager $session) {}

    public function home()
    {
        $visits = $this->session->get('visits', 0);
        $this->session->set('visits', $visits + 1);

        echo json_encode([
            'visits' => $visits + 1,
            'session_active' => $this->session->getStatusOfSession(),
            'last_active' => $this->session->getLastActivityOfSession(),
        ]);

    }
}
