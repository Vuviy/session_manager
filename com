./vendor/bin/phpcs src
./vendor/bin/phpcbf src

vendor/bin/phpstan analyse src

./vendor/bin/psalm --no-cache


CREATE TABLE sessions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,

    session_id CHAR(64) NOT NULL,
    user_id BIGINT UNSIGNED NULL,

    data LONGTEXT NOT NULL,

    created_at DATETIME NOT NULL,
    last_activity DATETIME NOT NULL,

    is_active TINYINT(1) NOT NULL DEFAULT 1,

    PRIMARY KEY (id),

    UNIQUE KEY uniq_session_id (session_id),
    KEY idx_user_id (user_id),
    KEY idx_last_activity (last_activity),
    KEY idx_active (is_active)

) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;


  ALTER TABLE sessions
  ADD COLUMN fingerprint CHAR(64) NOT NULL
  AFTER data;

  CREATE INDEX idx_fingerprint ON sessions (fingerprint);