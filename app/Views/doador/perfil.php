<?= $this->extend('doador/layout/dashboard') ?>
<?= $this->section('content') ?>




<h2>Meu Perfil</h2>

<?php if(session()->getFlashdata('sucesso')): ?>
    <div class="alert alert-success">
        <?= session()->getFlashdata('sucesso') ?>
    </div>
<?php endif; ?>

<form method="post" action="<?= base_url('doador/atualizarPerfil') ?>">

    <h4>Dados Pessoais</h4>

    <input type="text" name="nome" class="form-control mb-2"
           value="<?= $doador['nome'] ?>">

    <input type="email" name="email" class="form-control mb-2"
           value="<?= $doador['email'] ?>">

    <input type="text" name="celular" class="form-control mb-2"
           value="<?= $doador['celular'] ?>">

    <input type="date" name="data_nas" class="form-control mb-2"
           value="<?= $doador['data_nas'] ?>">

    <input type="password" name="senha" class="form-control mb-2"
           placeholder="Nova senha (opcional)">

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

<?= $this->endSection() ?>

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