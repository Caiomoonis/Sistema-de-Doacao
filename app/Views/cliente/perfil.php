<?= $this->extend('cliente/layout/dashboard') ?>

<?= $this->section('content') ?>


<head>
    <link rel="stylesheet" href="<?= base_url('assets/css/cliente/perfil.css') ?>">
</head>

<h2>Meu Perfil</h2>

<?php if(session()->getFlashdata('sucesso')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('sucesso') ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= base_url('cliente/perfil/salvar') ?>">
    <?= csrf_field() ?>

    <label>Nome</label>
    <input type="text" name="nome" value="<?= $cliente['nome'] ?>" class="form-control">

    <label>Email</label>
    <input type="email" name="email" value="<?= $cliente['email'] ?>" class="form-control">

    <label>Celular</label>
    <input type="text" name="celular" value="<?= $cliente['celular'] ?>" class="form-control">

    <label>Data Nascimento</label>
    <input type="date" name="data_nas" value="<?= $cliente['data_nas'] ?>" class="form-control">

    <label>Senha (deixe vazio para não alterar)</label>
    <input type="password" name="senha" class="form-control">

    <h4 class="mt-4">Endereço</h4>

<input type="text" name="cep" id="cep" class="form-control mb-2"
       value="<?= $endereco['cep'] ?? '' ?>" placeholder="CEP">

<input type="text" name="rua" id="rua" class="form-control mb-2"
       value="<?= $endereco['rua'] ?? '' ?>" placeholder="Rua">

<input type="text" name="complemento" class="form-control mb-2"
       value="<?= $endereco['complemento'] ?? '' ?>" placeholder="Complemento">

<input type="text" name="bairro" id="bairro" class="form-control mb-2"
       value="<?= $endereco['bairro'] ?? '' ?>" placeholder="Bairro">

<input type="text" name="municipio" id="municipio" class="form-control mb-2"
       value="<?= $endereco['municipio'] ?? '' ?>" placeholder="Cidade">

<input type="text" name="estado" id="estado" class="form-control mb-2"
       value="<?= $endereco['estado'] ?? '' ?>" placeholder="Estado">

    <button class="btn btn-primary mt-3">Salvar</button>
</form>

<script>
document.getElementById('cep').addEventListener('blur', function () {

    let cep = this.value.replace(/\D/g, '');

    if (cep.length !== 8) return;

    fetch('https://viacep.com.br/ws/' + cep + '/json/')
        .then(res => res.json())
        .then(data => {

            if (data.erro) return;

            document.getElementById('rua').value = data.logradouro;
            document.getElementById('bairro').value = data.bairro;
            document.getElementById('municipio').value = data.localidade;
            document.getElementById('estado').value = data.uf;
        });
});
</script>

<?= $this->endSection() ?>