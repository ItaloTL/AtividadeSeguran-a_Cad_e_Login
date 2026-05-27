<?php
/**
 * ============================================================
 *  Sistema de Cadastro e Login Seguro — Vesper Aurora
 *  Arquivo : login.php
 *  Descrição: Tela de Login com autenticação segura via PDO,
 *             verificação de senha com password_verify(),
 *             proteção CSRF e prevenção de enumeração de contas.
 * ============================================================
 */

require_once 'config.php';

// Usuário já logado → redireciona para a home
if (isset($_SESSION['user_id'])) {
    redirecionar('home.php');
}

// ──────────────────────────────────────────────────────────
//  Variáveis de estado
// ──────────────────────────────────────────────────────────
$erros      = [];
$valEmail   = '';

// Mensagem flash vinda do cadastro ou logout
$flashSucesso = '';
if (!empty($_SESSION['flash_sucesso'])) {
    $flashSucesso = $_SESSION['flash_sucesso'];
    unset($_SESSION['flash_sucesso']);
}

// ──────────────────────────────────────────────────────────
//  Processamento do POST
// ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Validação do token CSRF
    if (!csrfValidar()) {
        $erros[] = 'Requisição inválida. Recarregue a página e tente novamente.';
    } else {

        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha']      ?? '';

        $valEmail = $email;

        // 2. Validação básica dos campos
        if ($email === '' || $senha === '') {
            // Mensagem genérica — não revela qual campo está vazio
            $erros[] = 'Informe seu <strong>e-mail</strong> e <strong>senha</strong> para continuar.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'Informe um <strong>e-mail</strong> válido.';
        } else {

            // 3. Consulta no banco pelo e-mail
            try {
                $pdo  = getDB();
                $stmt = $pdo->prepare(
                    'SELECT id, nome, email, senha FROM usuarios WHERE email = :email LIMIT 1'
                );
                $stmt->execute([':email' => $email]);
                $usuario = $stmt->fetch();

                // 4. Verificação da senha — usa password_verify() para bcrypt
                //    Mesmo que o usuário não exista, executamos um hash fictício
                //    para equalizar o tempo de resposta e evitar timing attacks.
                $hashFicticio = '$2y$10$invalido.hash.para.timing.protection.xxxxxx';
                $hashVerificar = $usuario ? $usuario['senha'] : $hashFicticio;

                if ($usuario && password_verify($senha, $hashVerificar)) {

                    // 5. Login bem-sucedido
                    //    Regenera o ID de sessão para prevenir Session Fixation Attack
                    session_regenerate_id(true);

                    $_SESSION['user_id']    = $usuario['id'];
                    $_SESSION['user_nome']  = $usuario['nome'];
                    $_SESSION['user_email'] = $usuario['email'];

                    redirecionar('home.php');

                } else {
                    // Mensagem genérica — não indica se o e-mail existe ou não
                    $erros[] = 'E-mail ou senha incorretos. Verifique e tente novamente.';
                }

            } catch (PDOException $e) {
                error_log('[Vesper Aurora] Erro no login: ' . $e->getMessage());
                $erros[] = 'Erro ao processar o login. Tente novamente.';
            }
        }
    }
}

// ──────────────────────────────────────────────────────────
//  Gera token CSRF para o formulário
// ──────────────────────────────────────────────────────────
$tokenCSRF = csrfToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Acesse sua conta no sistema Vesper Aurora de forma segura.">
    <title>Entrar — Vesper Aurora</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="va-page">
    <div class="va-card">

        <!-- Marca -->
        <div class="va-brand">
            <div class="va-brand__icon" aria-hidden="true">✦</div>
            <span class="va-brand__name">Vesper Aurora</span>
            <span class="va-brand__tagline">Sistema Seguro · Acesso Unificado</span>
        </div>

        <h1 class="va-card__title">Bem-vindo de volta</h1>
        <p class="va-card__subtitle">Insira suas credenciais para acessar o sistema.</p>

        <!-- Mensagem de sucesso (flash vinda do cadastro/logout) -->
        <?php if ($flashSucesso): ?>
            <div class="va-alert va-alert--success mb-2" role="status">
                <span class="va-alert__icon" aria-hidden="true">✔</span>
                <span><?= escape($flashSucesso) ?></span>
            </div>
        <?php endif; ?>

        <!-- Mensagens de erro -->
        <?php if (!empty($erros)): ?>
            <?php foreach ($erros as $erro): ?>
                <div class="va-alert va-alert--error mb-2" role="alert">
                    <span class="va-alert__icon" aria-hidden="true">⚠</span>
                    <span><?= $erro ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Formulário de Login -->
        <form
            id="form-login"
            class="va-form"
            method="POST"
            action="login.php"
            novalidate
        >
            <!-- Token CSRF oculto -->
            <input type="hidden" name="csrf_token" value="<?= escape($tokenCSRF) ?>">

            <!-- Campo: E-mail -->
            <div class="va-field">
                <label class="va-field__label" for="email">Endereço de e-mail</label>
                <input
                    class="va-field__input"
                    type="email"
                    id="email"
                    name="email"
                    placeholder="seu@email.com"
                    value="<?= escape($valEmail) ?>"
                    maxlength="150"
                    autocomplete="email"
                    required
                    aria-required="true"
                >
            </div>

            <!-- Campo: Senha -->
            <div class="va-field">
                <label class="va-field__label" for="senha">Senha</label>
                <input
                    class="va-field__input"
                    type="password"
                    id="senha"
                    name="senha"
                    placeholder="Sua senha"
                    maxlength="128"
                    autocomplete="current-password"
                    required
                    aria-required="true"
                >
            </div>

            <!-- Botão de envio -->
            <button
                class="va-btn mt-2"
                type="submit"
                id="btn-entrar"
            >
                ✦ &nbsp;Acessar o sistema
            </button>

        </form>

        <div class="va-divider mt-4">
            <span class="va-divider__text">ou</span>
        </div>

        <p class="va-nav-link mt-2">
            Ainda não tem conta? <a href="register.php">Criar agora</a>
        </p>

    </div>
</div>

</body>
</html>
