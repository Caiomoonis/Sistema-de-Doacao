<?= $this->extend('cliente/layout/dashboard') ?>

<?= $this->section('content') ?>

<head>
    <link rel="stylesheet" href="<?= base_url('assets/css/cliente/historico.css') ?>">
</head>

<h2>Meu Histórico de Interesses</h2>

<?php if (empty($historico)): ?>
    <p>Você ainda não demonstrou interesse em nenhuma doação.</p>
<?php endif; ?>

<div class="row">

<?php foreach ($historico as $item): ?>

    <div class="col-md-4">
        <div class="card p-3 mb-3">

            <?php if ($item['imagem']): ?>
                <img src="<?= base_url('uploads/'.$item['imagem']) ?>" style="width:100%; height:200px; object-fit:cover;">
            <?php endif; ?>

            <h5><?= $item['tipo'] ?></h5>
            <p>Cor: <?= $item['cor'] ?></p>

            <small>
                Interessado em: <?= date('d/m/Y H:i', strtotime($item['data_interesse'])) ?>
            </small>

            <a href="<?= base_url('cliente/detalhes/'.$item['idRoupa']) ?>" class="btn btn-primary mt-2">
                Ver doação
            </a>

        </div>
    </div>

<?php endforeach; ?>

</div>

<?= $this->endSection() ?>  