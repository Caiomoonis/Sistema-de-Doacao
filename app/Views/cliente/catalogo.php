<?= $this->extend('cliente/layout/dashboard') ?>

<?= $this->section('title') ?>Catálogo<?= $this->endSection() ?>

<?= $this->section('content') ?>

<head>
    <link rel="stylesheet" href="<?= base_url('assets/css/cliente/catalogo.css') ?>">
</head>

<h2>Catálogo de Doações</h2>

<div class="row">

<?php foreach($roupas as $r): ?>

    <div class="col-md-4 mb-4">
        <div class="card shadow">

            <?php if($r['imagem']): ?>
                <img src="<?= base_url('uploads/'.$r['imagem']) ?>" class="card-img-top" style="height:200px; object-fit:cover;">
            <?php endif; ?>

            <div class="card-body">
                <h5 class="card-title"><?= $r['tipo'] ?></h5>

                <p class="card-text">
                    Cor: <?= $r['cor'] ?><br>
                    Quantidade: <?= $r['quantidade'] ?>
                </p>

                <small class="text-muted">
                    Doador:    <?= $r['nome_doador'] ?>
                </small>

                <a href="<?= base_url('cliente/detalhes/'.$r['id']) ?>" class="btn btn-primary mt-2">
                    Ver detalhes
                </a>
            </div>

        </div>
    </div>

<?php endforeach; ?>

</div>

<?= $this->endSection() ?>