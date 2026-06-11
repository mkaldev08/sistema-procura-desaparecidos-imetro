-- =====================================================================
-- Sistema Encontra-me — esquema da base de dados
-- MySQL 8.x / MariaDB 10.4+
-- Executar com: mysql -u root -P 3307 -h 127.0.0.1 < schema.sql
-- =====================================================================

CREATE DATABASE IF NOT EXISTS desaparecidos_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE desaparecidos_db;

-- Tabela de utilizadores registados
CREATE TABLE IF NOT EXISTS users (
    id           INT           NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(100)  NOT NULL,
    email        VARCHAR(100)  NULL,
    phone_number VARCHAR(9)    NOT NULL,
    password     VARCHAR(255)  NOT NULL,
    UNIQUE KEY username     (username),
    UNIQUE KEY email        (email),
    UNIQUE KEY phone_number (phone_number)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Tabela de pessoas desaparecidas
CREATE TABLE IF NOT EXISTS missing_persons (
    id                   INT UNSIGNED  NOT NULL AUTO_INCREMENT PRIMARY KEY,
    full_name            VARCHAR(150)  NOT NULL,
    age                  TINYINT UNSIGNED NULL,
    gender               ENUM('male','female','other') NOT NULL DEFAULT 'other',
    height_cm            SMALLINT UNSIGNED NULL,
    eye_color            VARCHAR(40)   NULL,
    hair_color           VARCHAR(40)   NULL,
    distinguishing_marks TEXT          NULL,
    last_seen_location   VARCHAR(255)  NOT NULL,
    last_seen_date       DATE          NOT NULL,
    circumstances        TEXT          NULL,
    photo_path           VARCHAR(255)  NULL,
    reporter_name        VARCHAR(150)  NOT NULL,
    reporter_phone       VARCHAR(40)   NOT NULL,
    reporter_email       VARCHAR(150)  NULL,
    status               ENUM('missing','found') NOT NULL DEFAULT 'missing',
    created_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    user_id              INT           NOT NULL,

    INDEX idx_status          (status),
    INDEX idx_last_seen_date  (last_seen_date),
    INDEX idx_user_id         (user_id),
    FULLTEXT idx_search       (full_name, last_seen_location, distinguishing_marks),
    CONSTRAINT fk_missing_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE NO ACTION
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
