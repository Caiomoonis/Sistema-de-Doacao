<!DOCTYPE html>
<html>
<head>
    <title>Login Doador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/doador/login.css') ?>">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        
        <a class="navbar-brand" href="<?= base_url('/') ?>">
            Sistema Doação
        </a>

        <div class="ms-auto">
            <a href="<?= base_url('/') ?>" class="btn btn-outline-light btn-sm">
                🏠 Início
            </a>
        </div>

    </div>
</nav>
<div class="d-flex justify-content-center align-items-center" style="height:90vh;">

<form action="<?= base_url('doador/autenticar') ?>" method="post" class="p-4 border rounded">

    <h3>Login Doador</h3>

    <?php if(session()->getFlashdata('erro')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('erro') ?>
        </div>
    <?php endif; ?>

    <input type="email" name="email" placeholder="Email" class="form-control mb-2">
    <input type="password" name="senha" placeholder="Senha" class="form-control mb-2">

    <button class="btn btn-success w-100">Entrar</button>

</form>

</div>

</body>
</html>