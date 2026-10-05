<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $this->renderSection('title') ?> — Veste+</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700;9..144,900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= base_url('icon/favicon.ico') ?>" type="image/x-icon">
    <link rel="stylesheet" href="<?= base_url('assets/css/doador/layout/dashboard.css') ?>">
    <?= $this->renderSection('styles') ?>
</head>
<body>

<div class="app-shell">

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">

        <div class="sidebar-brand">
            <a href="<?= base_url('/') ?>" class="brand-logo">
                <span class="logo-mark"></span>
                <span>Veste<em>+</em></span>
            </a>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar">
                <?= strtoupper(substr(session()->get('nome') ?? 'D', 0, 1)) ?>
            </div>
            <div class="user-info">
                <span class="user-name"><?= esc(session()->get('nome') ?? 'Doador') ?></span>
                <span class="user-role">Doador</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <span class="nav-section-label">Menu</span>
            <a href="<?= base_url('doador') ?>" class="nav-item <?= uri_string() === 'doador' ? 'active' : '' ?>">
                <span class="nav-icon">🏠</span>
                Dashboard
            </a>
            <a href="<?= base_url('doador/roupa/cadastrar') ?>" class="nav-item <?= str_contains(uri_string(), 'cadastrar') ? 'active' : '' ?>">
                <span class="nav-icon">➕</span>
                Cadastrar Roupa
            </a>
            <a href="<?= base_url('doador/roupa') ?>" class="nav-item <?= uri_string() === 'doador/roupa' ? 'active' : '' ?>">
                <span class="nav-icon">👕</span>
                Minhas Doações
            </a>

            <span class="nav-section-label" style="margin-top:16px">Conta</span>
            <a href="<?= base_url('doador/perfil') ?>" class="nav-item <?= str_contains(uri_string(), 'perfil') ? 'active' : '' ?>">
                <span class="nav-icon">👤</span>
                Meu Perfil
            </a>
            <a href="<?= base_url('doador/logout') ?>" class="nav-item nav-item-danger">
                <span class="nav-icon">🚪</span>
                Sair
            </a>
        </nav>

    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-wrapper">

        <!-- TOPBAR -->
        <header class="topbar">
            <button class="topbar-toggle" id="sidebarToggle" aria-label="Menu">
                <span></span><span></span><span></span>
            </button>
            <div class="topbar-left">
                <span class="topbar-breadcrumb">Painel do Doador</span>
                <span class="topbar-sep">›</span>
                <span class="topbar-current"><?= $this->renderSection('title') ?></span>
            </div>
            <div class="topbar-right">
                <div class="topbar-user">
                    <div class="topbar-avatar">
                        <?= strtoupper(substr(session()->get('nome') ?? 'D', 0, 1)) ?>
                    </div>
                    <span><?= esc(session()->get('nome')) ?></span>
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="page-content">
            <?= $this->renderSection('content') ?>
        </main>

    </div><!-- /.main-wrapper -->

</div><!-- /.app-shell -->

<script>
// Sidebar toggle (mobile)
const toggle = document.getElementById('sidebarToggle');
const sidebar = document.getElementById('sidebar');
if (toggle && sidebar) {
    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });
}
</script>

<?= $this->renderSection('scripts') ?>
</body>
</html>