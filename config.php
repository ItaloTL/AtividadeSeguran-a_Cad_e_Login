<?php
/**
 * ============================================================
 *  Sistema de Cadastro e Login Seguro — Vesper Aurora
 *  Arquivo : config.php
 *  Descrição: Centraliza a configuração do banco de dados,
 *             o início seguro da sessão PHP e funções
 *             utilitárias usadas em todo o sistema.
 *
 *  Segurança implementada:
 *    - Conexão via PDO com prepared statements
 *    - Sessão segura (httponly cookie, uso exclusivo de cookies)
 *    - Função escape() para prevenção de XSS
 *    - Geração e validação de tokens CSRF
 * ============================================================
 */

// ──────────────────────────────────────────────────────────
//  1. Credenciais do Banco de Dados
//     Altere os valores abaixo conforme o ambiente (XAMPP).
// ──────────────────────────────────────────────────────────
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_cadastro');
define('DB_USER', 'root');
define('DB_PASS', '');           // Senha padrão do XAMPP (vazia)
define('DB_CHARSET', 'utf8mb4');

// ──────────────────────────────────────────────────────────
//  2. Conexão PDO — Singleton simples
//     Retorna sempre a mesma instância de conexão.
// ──────────────────────────────────────────────────────────
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // Lança exceções em erros
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,          // Retorna arrays associativos
            PDO::ATTR_EMULATE_PREPARES   => false,                      // Prepara realmente no servidor
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Em produção, registre o erro em log e exiba mensagem genérica.
            // Nunca exponha $e->getMessage() para o usuário final.
            error_log('[Vesper Aurora] Erro de conexão com o banco: ' . $e->getMessage());
            die('Serviço temporariamente indisponível. Tente novamente mais tarde.');
        }
    }

    return $pdo;
}

// ──────────────────────────────────────────────────────────
//  3. Início Seguro da Sessão
//     - cookie_httponly : impede acesso via JavaScript
//     - use_only_cookies: força uso exclusivo de cookies
//     - cookie_samesite : proteção adicional contra CSRF
// ──────────────────────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Strict');
    // Descomente a linha abaixo ao usar HTTPS em produção:
    // ini_set('session.cookie_secure', '1');
    session_start();
}

// ──────────────────────────────────────────────────────────
//  4. Helper: escape()
//     Sanitiza qualquer dado antes de renderizá-lo no HTML,
//     prevenindo ataques de Cross-Site Scripting (XSS).
// ──────────────────────────────────────────────────────────
function escape(string $dados): string
{
    return htmlspecialchars($dados, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ──────────────────────────────────────────────────────────
//  5. Helpers CSRF
//     Tokens gerados com random_bytes() garantem
//     aleatoriedade criptograficamente segura.
// ──────────────────────────────────────────────────────────

/**
 * Gera (ou reutiliza) um token CSRF e o armazena na sessão.
 * Deve ser chamado dentro de cada formulário como campo oculto.
 */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida o token CSRF enviado via POST.
 * Usa hash_equals() para evitar ataques de timing.
 */
function csrfValidar(): bool
{
    $tokenEnviado  = $_POST['csrf_token'] ?? '';
    $tokenSessao   = $_SESSION['csrf_token'] ?? '';

    if (empty($tokenSessao) || !hash_equals($tokenSessao, $tokenEnviado)) {
        return false;
    }

    // Rotaciona o token após uso (double-submit protection)
    unset($_SESSION['csrf_token']);
    return true;
}

/**
 * Redireciona para uma URL e encerra a execução.
 */
function redirecionar(string $url): never
{
    header('Location: ' . $url);
    exit;
}
