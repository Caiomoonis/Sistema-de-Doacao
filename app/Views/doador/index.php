<?= $this->extend('doador/layout/dashboard') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<head>
    <link rel="stylesheet" href="<?= base_url('assets/css/doador/index.css') ?>">
</head>

<h2>Bem-vindo!</h2>

<div class="row mt-4">
    

    <div class="col-md-4">
        <div class="card p-3">
            <h5>➕ Cadastrar Roupa</h5>
            <p>Adicione novas doações</p>
            <a href="<?= base_url('doador/roupa/cadastrar') ?>" class="btn btn-primary">Acessar</a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card p-3">
            <h5>👕 Minhas Doações</h5>
            <p>Veja roupas cadastradas</p>
            <a href="<?= base_url('doador/roupa') ?>" class="btn btn-primary">Acessar</a>
        </div>
    </div>

    <?php foreach ($itens as $item): ?>

    <div class="col-md-4">
        <div class="card p-3">
            <h5>❤️ Interessados</h5>
            <p>Pessoas interessadas</p>

            <a href="<?= base_url('doador/interessados/'.$item['id']) ?>">
                Ver interessados
            </a>

        </div>
    </div>

<?php endforeach; ?>

</div>

<?= $this->endSection() ?>