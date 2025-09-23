<?php
// auth.php - Arquivo único de autenticação
session_start();

// Configurar para expirar quando o navegador fechar
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 86400); // ou outras configurações
}

// Configurar tempo de expiração por inatividade (30 minutos)
$timeout = 1800;

// Verificar se a sessão expirou por inatividade
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Atualizar timestamp da última atividade
$_SESSION['LAST_ACTIVITY'] = time();

// Regenerar ID da sessão a cada 5 minutos para segurança
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 300) {
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}

// Verificar se está logado como admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
?>