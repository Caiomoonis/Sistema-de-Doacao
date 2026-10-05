<?= $this->extend('doador/layout/dashboard') ?>

<?= $this->section('title') ?>Minhas Doações<?= $this->endSection() ?>

<?= $this->section('content') ?>

<head>
    <link rel="stylesheet" href="<?= base_url('assets/css/doador/roupa/index.css') ?>">
</head>

<h2>Minhas Doações</h2>

<table class="table table-bordered mt-4">
    <thead class="table-dark">
        <tr>
            <th>Tipo</th>
            <th>Cor</th>
            <th>Quantidade</th>
            <th>Bairro</th>
            <th>Ponto de Doação</th>
            <th>Imagem</th>            
            <th>Ações</th>
            
        </tr>
    </thead>

    <tbody>

    <?php if(!empty($roupas)): ?>

        <?php foreach($roupas as $r): ?>
            <tr>
                <td><?= $r['tipo'] ?></td>
                <td><?= $r['cor'] ?></td>
                <td><?= $r['quantidade'] ?></td>
                <td><?= $r['bairro'] ?></td>
                <td><?= $r['ponto_doacao'] ?></td>
                <td>
                    <?php if($r['imagem']): ?>
                        <img src="<?= base_url('uploads/'.$r['imagem']) ?>" width="80">
                    <?php endif; ?>
                </td>

                <td>
                    <a href="<?= base_url('doador/roupa/excluir/'.$r['id']) ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Tem certeza que deseja excluir?')">
                        Excluir
                    </a>
                    <form action="<?= base_url('doador/doar/'.$r['id']) ?>" method="post">
                        <button class="btn btn-warning btn-sm">
                            Marcar como Doado
                        </button>
                    </form>

                    <small class="text-danger">
                        Após marcar como doado, será removido em 7 dias.
                    </small>
                </td>
            </tr>
        <?php endforeach; ?>

    <?php else: ?>

        <tr>
            <td colspan="6" class="text-center">
                Nenhuma doação cadastrada.
            </td>
        </tr>

    <?php endif; ?>

    </tbody>
</table>

<?= $this->endSection() ?>