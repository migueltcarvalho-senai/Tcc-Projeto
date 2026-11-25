<?php
/**
 * config/proteger.php — Proteção de Sessão contra Acesso não Autenticado
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}
