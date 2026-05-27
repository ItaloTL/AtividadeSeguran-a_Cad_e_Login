<?php
/**
 * ============================================================
 *  Sistema de Cadastro e Login Seguro — Vesper Aurora
 *  Arquivo : logout.php
 *  Descrição: Encerramento seguro e completo da sessão do
 *             usuário. Não renderiza HTML — apenas executa a
 *             limpeza e redireciona para login.php.
 *
 *  Procedimento de segurança:
 *    1. Limpa todos os dados do array $_SESSION
 *    2. Invalida o cookie de sessão no navegador do usuário
 *    3. Destrói os dados da sessão no servidor
 *    4. Redireciona com mensagem de feedback
 * ============================================================
 */

require_once 'config.php';

// Somente usuários autenticados podem chamar este script.
// Acesso direto por visitantes simplesmente redireciona.
if (!isset($_SESSION['user_id'])) {
    redirecionar('login.php');
}

// ── Passo 1: Apaga todos os dados da superglobal $_SESSION ──
$_SESSION = [];

// ── Passo 2: Invalida o cookie de sessão no navegador ───────
//    Sobrescreve o cookie com data de expiração no passado,
//    forçando o browser a descartá-lo imediatamente.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// ── Passo 3: Destrói os dados da sessão no servidor ─────────
session_destroy();

// ── Passo 4: Inicia nova sessão limpa para a flash message ──
session_start();
$_SESSION['flash_sucesso'] = 'Você saiu com segurança. Até logo!';

// ── Passo 5: Redireciona para a tela de login ───────────────
redirecionar('login.php');
