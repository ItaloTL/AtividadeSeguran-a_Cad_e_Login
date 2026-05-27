<?php
/**
 * ============================================================
 *  Sistema de Cadastro e Login Seguro — Vesper Aurora
 *  Arquivo : register.php
 *  Descrição: Tela de Cadastro com validação completa no
 *             servidor, proteção CSRF, criptografia de senha
 *             e prevenção de SQL Injection via PDO.
 * ============================================================
 */

require_once 'config.php';

// Usuário já logado → redireciona para a home
if (isset($_SESSION['user_id'])) {
    redirecionar('home.php');
}

// ──────────────────────────────────────────────────────────
//  Variáveis de estado do formulário
// ──────────────────────────────────────────────────────────
$erros   = [];
$sucesso = '';

// Valores para repopular os campos em caso de erro
// (nunca repopulamos campos de senha por segurança)
$valNome  = '';
$valEmail = '';

// ──────────────────────────────────────────────────────────
//  Processamento do POST
// ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Validação do token CSRF
    if (!csrfValidar()) {
        $erros[] = 'Requisição inválida. Recarregue a página e tente novamente.';
    } else {

        // 2. Coleta e sanitização básica das entradas
        $nome             = trim($_POST['nome']             ?? '');
        $email            = trim($_POST['email']            ?? '');
        $senha            = $_POST['senha']                 ?? '';
        $confirmarSenha   = $_POST['confirmar_senha']       ?? '';

        // Preserva para repopular em caso de erro
        $valNome  = $nome;
        $valEmail = $email;

        // 3. Validação dos campos
        if ($nome === '') {
            $erros[] = 'O campo <strong>Nome</strong> é obrigatório.';
        } elseif (mb_strlen($nome) > 100) {
            $erros[] = 'O <strong>Nome</strong> deve ter no máximo 100 caracteres.';
        }

        if ($email === '') {
            $erros[] = 'O campo <strong>E-mail</strong> é obrigatório.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erros[] = 'Informe um <strong>E-mail</strong> válido.';
        } elseif (mb_strlen($email) > 150) {
            $erros[] = 'O <strong>E-mail</strong> deve ter no máximo 150 caracteres.';
        }

        if ($senha === '') {
            $erros[] = 'O campo <strong>Senha</strong> é obrigatório.';
        } elseif (mb_strlen($senha) < 8) {
            $erros[] = 'A <strong>Senha</strong> deve ter no mínimo 8 caracteres.';
        }

        if ($confirmarSenha === '') {
            $erros[] = 'O campo <strong>Confirmar Senha</strong> é obrigatório.';
        } elseif ($senha !== $confirmarSenha) {
            $erros[] = 'As senhas informadas <strong>não coincidem</strong>.';
        }

        // 4. Processamento (somente se sem erros de validação)
        if (empty($erros)) {
            try {
                $pdo = getDB();

                // Verifica se o e-mail já está cadastrado
                $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
                $stmt->execute([':email' => $email]);

                if ($stmt->fetch()) {
                    $erros[] = 'Este <strong>E-mail</strong> já está cadastrado. Faça login ou use outro.';
                } else {
                    // Criptografa a senha com bcrypt (custo padrão ≥ 10)
                    $hashSenha = password_hash($senha, PASSWORD_DEFAULT);

                    // Insere o novo usuário
                    $stmt = $pdo->prepare(
                        'INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)'
                    );
                    $stmt->execute([
                        ':nome'  => $nome,
                        ':email' => $email,
                        ':senha' => $hashSenha,
                    ]);

                    // Registra mensagem de sucesso na sessão e redireciona
                    $_SESSION['flash_sucesso'] = 'Cadastro realizado com sucesso! Faça login para continuar.';
                    redirecionar('login.php');
                }

            } catch (PDOException $e) {
                error_log('[Vesper Aurora] Erro no cadastro: ' . $e->getMessage());
                $erros[] = 'Erro ao processar o cadastro. Tente novamente.';
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
    <meta name="description" content="Crie sua conta no sistema Vesper Aurora. Cadastro seguro e protegido.">
    <title>Criar Conta — Vesper Aurora</title>
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

        <h1 class="va-card__title">Criar sua conta</h1>
        <p class="va-card__subtitle">Preencha os dados abaixo para se cadastrar.<br>Todos os campos são obrigatórios.</p>

        <!-- Mensagens de erro -->
        <?php if (!empty($erros)): ?>
            <?php foreach ($erros as $erro): ?>
                <div class="va-alert va-alert--error mb-2" role="alert">
                    <span class="va-alert__icon" aria-hidden="true">⚠</span>
                    <span><?= $erro ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Formulário de Cadastro -->
        <form
            id="form-cadastro"
            class="va-form"
            method="POST"
            action="register.php"
            novalidate
        >
            <!-- Token CSRF oculto -->
            <input type="hidden" name="csrf_token" value="<?= escape($tokenCSRF) ?>">

            <!-- Campo: Nome -->
            <div class="va-field">
                <label class="va-field__label" for="nome">Nome completo</label>
                <input
                    class="va-field__input"
                    type="text"
                    id="nome"
                    name="nome"
                    placeholder="Ex.: Maria Silva"
                    value="<?= escape($valNome) ?>"
                    maxlength="100"
                    autocomplete="name"
                    required
                    aria-required="true"
                >
            </div>

            <!-- Campo: E-mail -->
            <div class="va-field">
                <label class="va-field__label" for="email">Endereço de e-mail</label>
                <input
                    class="va-field__input"
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Ex.: maria@email.com"
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
                    placeholder="Mínimo 8 caracteres"
                    maxlength="128"
                    autocomplete="new-password"
                    required
                    aria-required="true"
                >
            </div>

            <!-- Campo: Confirmar Senha -->
            <div class="va-field">
                <label class="va-field__label" for="confirmar_senha">Confirmar senha</label>
                <input
                    class="va-field__input"
                    type="password"
                    id="confirmar_senha"
                    name="confirmar_senha"
                    placeholder="Repita a senha acima"
                    maxlength="128"
                    autocomplete="new-password"
                    required
                    aria-required="true"
                >
            </div>

            <!-- Botão de envio -->
            <button
                class="va-btn mt-2"
                type="submit"
                id="btn-cadastrar"
            >
                ✦ &nbsp;Criar minha conta
            </button>

        </form>

        <div class="va-divider mt-4">
            <span class="va-divider__text">ou</span>
        </div>

        <p class="va-nav-link mt-2">
            Já possui uma conta? <a href="login.php">Fazer login</a>
        </p>

    </div>
</div>

</body>
</html>
