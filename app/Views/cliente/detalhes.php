<?= $this->extend('cliente/layout/dashboard') ?>

<?= $this->section('title') ?>Detalhes<?= $this->endSection() ?>

<?= $this->section('content') ?>

<head>
    <link rel="stylesheet" href="<?= base_url('assets/css/cliente/detalhes.css') ?>">
</head>


<h2>Detalhes da Roupa</h2>

<?php if(session()->getFlashdata('sucesso')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('sucesso') ?>
    </div>
<?php endif; ?>

<div class="card p-4">

    <?php if($roupa['imagem']): ?>
        <img src="<?= base_url('uploads/'.$roupa['imagem']) ?>" 
             style="max-width:300px; object-fit:cover;" 
             class="mb-3">
    <?php endif; ?>

    <h4><?= $roupa['tipo'] ?></h4>

    <p><strong>Cor:</strong> <?= $roupa['cor'] ?></p>
    <p><strong>Quantidade:</strong> <?= $roupa['quantidade'] ?></p>
    <p><strong>Bairro:</strong> <?= $roupa['bairro'] ?></p>
    <p><strong>Ponto de doação:</strong> <?= $roupa['ponto_doacao'] ?></p>

    <hr>

    <h5>Doador</h5>
    <p><?= $roupa['nome_doador'] ?></p>

    <form action="<?= base_url('cliente/interesse/'.$roupa['id']) ?>" method="post">
    <button class="btn btn-success mt-3">
        Tenho interesse
    </button>
        </form>

        <?php
        $mensagem = "Olá, estou interessado na roupa: ".$roupa['tipo'].", cor: ".$roupa['cor'].", quantidade: ".$roupa['quantidade'].". Poderia me fornecer mais detalhes?";
        $telefone = preg_replace('/\D/', '', $roupa['celular']);
        $link = "https://wa.me/55".$telefone."?text=".urlencode($mensagem);
        ?>


        <a href="<?= $link ?>" target="_blank" class="btn btn-success mt-2">
            💬 Falar no WhatsApp
        </a>

    <a href="<?= base_url('cliente/catalogo') ?>" class="btn btn-secondary mt-3">
        Voltar
    </a>


</div>

<?= $this->endSection() ?>