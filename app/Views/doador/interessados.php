<?= $this->extend('doador/layout/dashboard') ?>
<?= $this->section('content') ?>

<head>
    <link rel="stylesheet" href="<?= base_url('assets/css/doador/interessados.css') ?>">
</head>

<h2>Lista de Interessados</h2>

<?php if (empty($interessados)): ?>
    <p>Ninguém demonstrou interesse ainda.</p>
<?php else: ?>

    <table border="1" cellpadding="10">
        <tr>
            <th>Nome</th>
            <th>Número de Celular</th>
            <th>Email</th>
            <th>Item</th>
            <th>Cor</th>
            <th>Data/Hora</th>
            <th>Imagem</th>
            

        </tr>

        <?php foreach ($interessados as $i): ?>
            <tr>

            
                <td><?= $i['nome'] ?></td>
                <td><?=  $i['celular'] ?></td>
                <td><?= $i['email'] ?></td>
                <td><?= $i['tipo'] ?></td>
                <td><?= $i['cor'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($i['data_interesse'])) ?></td>
                <td>
                    <?php if (!empty($i['imagem'])): ?>
                        <img src="<?= base_url('uploads/'.$i['imagem']) ?>" width="50">
                    <?php else: ?>
                        Sem imagem
                    <?php endif; ?>
                </td>

            </tr>
        <?php endforeach; ?>

    </table>

<?php endif; ?>

<?= $this->endSection() ?>