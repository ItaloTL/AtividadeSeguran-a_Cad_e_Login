<?php
/**
 * ============================================================
 *  Sistema de Cadastro e Login Seguro — Vesper Aurora
 *  Arquivo : home.php
 *  Descrição: Dashboard restrito. Somente usuários autenticados
 *             têm acesso. Exibe dados do perfil, informações
 *             da conta e opção segura de logout.
 * ============================================================
 */

require_once 'config.php';

// ──────────────────────────────────────────────────────────
//  Verificação de autenticação — Acesso Restrito
//  Se não houver sessão ativa, redireciona para login.php.
// ──────────────────────────────────────────────────────────
if (!isset($_SESSION['user_id'])) {
    redirecionar('login.php');
}

// ──────────────────────────────────────────────────────────
//  Busca dados atualizados do usuário no banco
//  (evita usar dados de sessão desatualizados)
// ──────────────────────────────────────────────────────────
try {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        'SELECT nome, email, criado_em FROM usuarios WHERE id = :id LIMIT 1'
    );
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $usuario = $stmt->fetch();

    // Sessão inválida (usuário deletado do banco, por exemplo)
    if (!$usuario) {
        session_destroy();
        redirecionar('login.php');
    }

} catch (PDOException $e) {
    error_log('[Vesper Aurora] Erro ao buscar usuário na home: ' . $e->getMessage());
    die('Erro ao carregar os dados. Tente novamente.');
}

// ──────────────────────────────────────────────────────────
//  Formatação dos dados para exibição
// ──────────────────────────────────────────────────────────
$nomeExibicao  = escape($usuario['nome']);
$emailExibicao = escape($usuario['email']);

// Formata a data de cadastro para pt-BR
$dataCriacao = '';
if (!empty($usuario['criado_em'])) {
    $dt = new DateTimeImmutable($usuario['criado_em']);
    $dataCriacao = $dt->format('d/m/Y \à\s H:i');
}

// Extrai o primeiro nome para a saudação
$primeiroNome = escape(explode(' ', trim($usuario['nome']))[0]);

// Saudação dinâmica por hora do dia
$hora = (int) (new DateTimeImmutable())->format('H');
$saudacao = match(true) {
    $hora < 12 => 'Bom dia',
    $hora < 18 => 'Boa tarde',
    default    => 'Boa noite',
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Painel do usuário — Vesper Aurora. Seus dados de perfil e informações da conta.">
    <title>Painel — Vesper Aurora</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="va-page">
    <div class="va-home">

        <!-- ─── Cabeçalho / Navbar ─────────────────────────── -->
        <header class="va-home__header" role="banner">
            <div class="va-home__logo">
                <div class="va-home__logo-icon" aria-hidden="true">✦</div>
                <span class="va-home__logo-text">Vesper Aurora</span>
            </div>

            <a
                href="logout.php"
                id="btn-logout"
                class="va-home__logout"
                aria-label="Sair da conta"
                onclick="return confirm('Deseja realmente sair?')"
            >
                ⏻ &nbsp;Sair
            </a>
        </header>

        <!-- ─── Grid de Cards ─────────────────────────────── -->
        <main class="va-home__grid" role="main">

            <!-- Card de Boas-Vindas -->
            <div class="va-welcome-card" role="region" aria-label="Boas-vindas">
                <div class="va-welcome-card__avatar" aria-hidden="true">
                    <?= mb_substr($usuario['nome'], 0, 1) ?>
                </div>
                <div class="va-welcome-card__content">
                    <p class="va-welcome-card__greeting"><?= escape($saudacao) ?>,</p>
                    <h1 class="va-welcome-card__name"><?= $primeiroNome ?>!</h1>
                    <span class="va-welcome-card__status">
                        <span class="va-status-dot" aria-hidden="true"></span>
                        Sessão ativa e protegida
                    </span>
                </div>
            </div>

            <!-- Card: E-mail -->
            <div class="va-info-card" role="region" aria-label="E-mail cadastrado">
                <div class="va-info-card__icon" aria-hidden="true">✉</div>
                <p class="va-info-card__label">E-mail da conta</p>
                <p class="va-info-card__value"><?= $emailExibicao ?></p>
            </div>

            <!-- Card: Data de Cadastro -->
            <div class="va-info-card" role="region" aria-label="Data de cadastro">
                <div class="va-info-card__icon" aria-hidden="true">📅</div>
                <p class="va-info-card__label">Membro desde</p>
                <p class="va-info-card__value"><?= escape($dataCriacao) ?></p>
            </div>

            <!-- Card: Nome Completo -->
            <div class="va-info-card" role="region" aria-label="Nome completo">
                <div class="va-info-card__icon" aria-hidden="true">◈</div>
                <p class="va-info-card__label">Nome completo</p>
                <p class="va-info-card__value va-info-card__value--gradient"><?= $nomeExibicao ?></p>
            </div>

        </main>

    </div>
</div>

</body>
</html>
