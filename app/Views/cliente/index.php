<?= $this->extend('cliente/layout/dashboard') ?>

<?= $this->section('title') ?>Dashboard Cliente<?= $this->endSection() ?>

<?= $this->section('content') ?>
<head>
    <link rel="stylesheet" href="<?= base_url('assets/css/cliente/layout/dashboard.css') ?>">
</head>
<h2>Bem-vindo!</h2>

<div class="row mt-4">
    <div class="col-md-4">
        <div class="card p-3">
            <a href="<?= base_url('cliente/perfil') ?>">
                Atualizar perfil
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <a href="<?= base_url('cliente/historico') ?>">
                Ver histórico de interesses
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <h5>Catálogo de Doações</h5>
            <p>Veja as roupas disponíveis</p>
            <a href="<?= base_url('cliente/catalogo') ?>" class="btn btn-primary">Acessar</a>
        </div>
</div>

<?= $this->endSection() ?>