<?php

declare(strict_types=1);

/**
 * Shared helper functions.
 */

const UPLOAD_DIR      = __DIR__ . '/../uploads/';
const UPLOAD_URL      = 'uploads/';
const MAX_UPLOAD_BYTES = 3 * 1024 * 1024; // 3 MB
const ALLOWED_MIME    = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
];

/**
 * Escape output for safe HTML rendering (prevents XSS).
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * HTTP redirect and stop execution.
 */
function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

/**
 * Validate a date string in Y-m-d format.
 */
function isValidDate(string $date): bool
{
    $parsed = DateTime::createFromFormat('Y-m-d', $date);
    return $parsed !== false && $parsed->format('Y-m-d') === $date;
}

/**
 * Map gender enum value to a human label (UI in Portuguese).
 */
function genderLabel(string $gender): string
{
    return match ($gender) {
        'male'   => 'Masculino',
        'female' => 'Feminino',
    };
}

/**
 * Validate and store an uploaded photo.
 *
 * @return string|null Stored filename, or null if no file was sent.
 * @throws RuntimeException on any validation/IO failure.
 */
function handlePhotoUpload(array $file): ?string
{
    // No file selected — that's allowed (photo is optional).
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Falha no envio da foto.');
    }

    if ($file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('A foto excede o tamanho máximo de 3 MB.');
    }

    // Trust the real MIME type, never the client-provided extension.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);

    if (!isset(ALLOWED_MIME[$mime])) {
        throw new RuntimeException('Formato inválido. Use JPG, PNG ou WEBP.');
    }

    $extension   = ALLOWED_MIME[$mime];
    $filename    = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('Não foi possível guardar a foto.');
    }

    return $filename;
}

function estaAutenticado(): bool
{
    return isset($_SESSION['user_id']);
}

function exigirAutenticacao(): void
{
    if (!estaAutenticado()) {
        redirect('register-user.php');
    }
}
