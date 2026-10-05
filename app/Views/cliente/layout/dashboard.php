<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title><?= $this->renderSection('title') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/cliente/layout/dashboard.css') ?>">
</head>

<body>

<div class="d-flex">

    <!-- MENU -->
    <div class="sidebar p-3 text-white">
        <h4 class="text-center mb-4"><?= session()->get('nome') ?? 'Cliente' ?></h4>

        <a href="<?= base_url('cliente') ?>" class="class="<?= str_contains(uri_string(), 'cliente') ? 'active' : '' ?>"">🏠 Home</a>
        <a href="<?= base_url('cliente/perfil') ?>">📋 Meus dados</a>
        <a href="<?= base_url('cliente/historico') ?>">💳 Historico de interesses</a>
        <a href="<?= base_url('cliente/catalogo') ?>">💳 Catálogo de roupas</a>

    </div>

    <!-- CONTEÚDO -->
    <div class="flex-grow-1">

        <div class="topbar d-flex justify-content-between align-items-center">
    
    <span>Área do Cliente</span>

    <div>
        
        <a href="<?= base_url('cliente/logout') ?>" class="btn btn-sm btn-danger">
            Sair
        </a>
    </div>

</div>

        <div class="p-4">
            <?= $this->renderSection('content') ?>
        </div>

    </div>

</div>

</body>
</html>