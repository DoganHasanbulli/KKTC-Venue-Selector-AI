<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function adminGirisliMi(): bool
{
    return isset($_SESSION['yonetici_id']) && is_numeric($_SESSION['yonetici_id']);
}

function adminGirisGerekli(): void
{
    if (!adminGirisliMi()) {
        header('Location: login.php');
        exit;
    }
}
