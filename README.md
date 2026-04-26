# Session Manager

A secure session management library built with **pure PHP** (no frameworks). Implements session fingerprinting, encryption, database storage, and protection against common session-based attacks.

---

## Features

- **Session fingerprinting** — binds session to IP address and User-Agent hash to detect hijacking attempts
- **Encryption** — session data encrypted via OpenSSL before storing in the database
- **Database session handler** — sessions stored in MySQL instead of the filesystem
- **Session regeneration** — `session_regenerate_id()` called on login to prevent session fixation
- **Concurrent session limits** — configurable maximum number of active sessions per user
- **Secure cookie flags** — `HttpOnly`, `Secure`, `SameSite` enforced on all session cookies

### Protections
- Session hijacking — fingerprint mismatch invalidates the session
- Session fixation — session ID is regenerated on every login
- Data exposure — all session data is encrypted at rest

---

## Tech Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.3 (no framework) |
| Database | MySQL 8.4 |
| Encryption | OpenSSL (via PHP `openssl_encrypt`) |
| Web server | Nginx 1.27 |
| Infrastructure | Docker |

---

## Project Structure

Everything lives in a single repository:

```
session_manager/
  ├── app/               ← PHP application
  ├── docker/            ← Nginx config, PHP Dockerfile
  ├── docker-compose.yml
  └── .env
```

---

## Getting Started

### Prerequisites

- [Docker](https://www.docker.com/) and Docker Compose installed
- Git

### Installation

**1. Clone the repository**

```bash
git clone https://github.com/Vuviy/session_manager.git
cd session_manager
```

**2. Copy the environment file**

```bash
cp .env.example .env
cp app/.env.example app/.env
```

**3. Configure `.env`**

```env
DB_DATABASE=db_ses
MYSQL_ROOT_PASSWORD=root
```

```env
CIPHER_KEY=your-32-character-secret-key-here //base64:T9pE+XJcTbQ4uRk1cLzXhN8HjGv4eL7FqF8r5yYHk1E=
```

**4. Start Docker containers**

```bash
docker compose up -d
```

**5. Install dependencies**

```bash
docker exec ses_php composer install
```

**6. Create the sessions table**

Run the SQL in phpMyAdmin (`http://localhost:8000`):

```sql
CREATE TABLE sessions (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    session_id    CHAR(64)        NOT NULL,
    user_id       BIGINT UNSIGNED NULL,
    data          LONGTEXT        NOT NULL,
    fingerprint   CHAR(64)        NOT NULL,
    created_at    DATETIME        NOT NULL,
    last_activity DATETIME        NOT NULL,
    is_active     TINYINT(1)      NOT NULL DEFAULT 1,

    PRIMARY KEY (id),
    UNIQUE KEY uniq_session_id (session_id),
    KEY idx_user_id (user_id),
    KEY idx_last_activity (last_activity),
    KEY idx_active (is_active),
    KEY idx_fingerprint (fingerprint)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**7. Open in browser**

```
http://localhost
```

---

## Database GUI

phpMyAdmin is available at:

```
http://localhost:8000
```

Login with credentials from your `.env` file (`MYSQL_ROOT_PASSWORD`).

---

## Usage Example

```php
$crypto      = new SessionCrypto(cypherKey());
$repo        = new SessionRepository(new Database(config()));
$fingerprint = new SessionFingerprint();
$session     = new SessionManager($repo, $fingerprint, $crypto);
// session starts automatically in constructor

// Write data
$session->set('visits', 1);

// Read data
$visits = $session->get('visits', 0);

// Login — regenerates session ID automatically
$session->login($userId);

// Destroy session (logout)
$session->destroy();
```

---

## License

This project is open-source and available under the [MIT license](LICENSE).
