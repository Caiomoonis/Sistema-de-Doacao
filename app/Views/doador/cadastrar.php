<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Doador</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/doador/cadastrar.css') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container">

        <a class="navbar-brand" href="<?= base_url('/') ?>">
            Sistema Doação
        </a>

        <a href="<?= base_url('/') ?>" class="btn btn-outline-light btn-sm">
            🏠 Início
        </a>

    </div>
</nav>

<?php if(session()->getFlashdata('erro')): ?>
    <div class="alert alert-danger">
        <?= session()->getFlashdata('erro') ?>
    </div>
<?php endif; ?>

<!-- FORM -->
<div class="container mt-5" style="max-width: 600px;">

    <h2 class="mb-4">Cadastro de Doador</h2>

    <form action="<?= base_url('doador/salvar') ?>" method="post">

        <input type="text" name="nome" placeholder="Nome" class="form-control mb-2" required>
        <input type="email" name="email" placeholder="Email" class="form-control mb-2" required>
        <input type="text" name="celular" id="celular" class="form-control mb-2" placeholder="(44) 99999-9999">        <input type="date" name="data_nas" class="form-control mb-3">
        <input type="password" name="senha" class="form-control mb-3" placeholder="Senha" required>


        <h5>Endereço</h5>

        <input type="text" name="cep" id="cep" placeholder="CEP" class="form-control mb-2">
        <input type="text" name="rua" id="rua" placeholder="Rua" class="form-control mb-2">
        <input type="text" name="complemento" placeholder="Complemento" class="form-control mb-2">
        <input type="text" name="bairro" id="bairro" placeholder="Bairro" class="form-control mb-2">
        <input type="text" name="municipio" id="municipio" placeholder="Cidade" class="form-control mb-2">
        <input type="text" name="estado" id="estado" placeholder="Estado" class="form-control mb-2">

        <select name="local_doacao" class="form-control mb-3">
            <option value="Nao">Não é ponto de doação</option>
            <option value="Sim">É ponto de doação</option>
        </select>

        <button class="btn btn-success w-100">Cadastrar</button>

    </form>

</div>

<script>
document.getElementById('celular').addEventListener('input', function(e) {
    let v = e.target.value.replace(/\D/g, '');

    if (v.length > 11) v = v.slice(0, 11);

    if (v.length > 6) {
        v = v.replace(/^(\d{2})(\d{5})(\d+)/, '($1) $2-$3');
    } else if (v.length > 2) {
        v = v.replace(/^(\d{2})(\d+)/, '($1) $2');
    }

    e.target.value = v;
});
</script>

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


</body>
</html>