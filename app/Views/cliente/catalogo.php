<?= $this->extend('cliente/layout/dashboard') ?>

<?= $this->section('title') ?>Catálogo<?= $this->endSection() ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/cliente/catalogo.css') ?>">
<h2>Catálogo de Doações</h2>

<div class="card shadow-sm mb-4"><div class="card-body">
    <h5>Encontrar doações perto de mim</h5>
    <p class="text-muted">O site usa a localização do seu dispositivo para calcular a distância até os pontos públicos de retirada. Suas coordenadas não aparecem no endereço da página.</p>
    <?php if ($erroFiltro): ?><div class="alert alert-danger"><?= esc($erroFiltro) ?></div><?php endif ?>
    <form id="filtro-regiao" method="post" action="<?= base_url('cliente/catalogo/filtrar') ?>" class="d-flex flex-wrap gap-2 align-items-end">
        <?= csrf_field() ?>
        <input type="hidden" id="latitude" name="latitude" value="<?= esc($latitude) ?>">
        <input type="hidden" id="longitude" name="longitude" value="<?= esc($longitude) ?>">
        <div><label for="raio" class="form-label">Distância máxima</label><div class="input-group"><input id="raio" name="raio" type="number" step="1" min="1" max="500" class="form-control" value="<?= esc($raio) ?>"><span class="input-group-text">km</span></div></div>
        <button type="button" id="buscar-perto" class="btn btn-primary">Buscar perto de mim</button>
        <?php if ($filtroAtivo): ?><a href="<?= base_url('cliente/catalogo') ?>" class="btn btn-outline-secondary">Limpar filtro</a><?php endif ?>
    </form>
    <small id="localizacao-status" class="text-muted" role="status" aria-live="polite"></small>
</div></div>

<?php if ($filtroAtivo && !$erroFiltro): ?><p>Resultados em até <?= esc($raio) ?> km da sua localização.</p><?php endif ?>
<div class="row">
<?php foreach ($roupas as $r): ?>
    <div class="col-md-4 mb-4"><div class="card shadow h-100">
        <?php if (!empty($r['imagem'])): ?><img src="<?= base_url('uploads/'.esc($r['imagem'])) ?>" class="card-img-top" style="height:200px;object-fit:cover;" alt="<?= esc($r['tipo']) ?>"><?php endif ?>
        <div class="card-body"><h5 class="card-title"><?= esc($r['tipo']) ?></h5>
            <p class="card-text">Cor: <?= esc($r['cor'] ?? '') ?><br>Quantidade: <?= (int) $r['quantidade'] ?><?php if ($filtroAtivo && isset($r['distancia_km'])): ?><br>Distância aproximada: <?= number_format($r['distancia_km'], 1, ',', '.') ?> km<?php endif ?></p>
            <small class="text-muted">Doador: <?= esc($r['nome_doador']) ?></small><br>
            <a href="<?= base_url('cliente/detalhes/'.$r['id']) ?>" class="btn btn-primary mt-2">Ver detalhes</a>
        </div>
    </div></div>
<?php endforeach ?>
<?php if (!$roupas): ?><div class="col-12"><div class="alert alert-info"><?= $filtroAtivo && !$erroFiltro ? 'Nenhum item com coordenadas foi encontrado dentro desse raio.' : 'Ainda não há itens cadastrados.' ?></div></div><?php endif ?>
</div>
<script>
document.getElementById('buscar-perto').addEventListener('click', function () {
    const button = this;
    const status = document.getElementById('localizacao-status');
    const raio = Number(document.getElementById('raio').value);
    if (!Number.isFinite(raio) || raio < 1 || raio > 500) { status.textContent = 'Escolha uma distância entre 1 e 500 km.'; return; }
    if (!navigator.geolocation) { status.textContent = 'Este navegador não oferece localização. Tente outro navegador.'; return; }
    button.disabled = true;
    status.textContent = 'Aguardando permissão para acessar sua localização…';
    navigator.geolocation.getCurrentPosition(function (pos) {
        document.getElementById('latitude').value = pos.coords.latitude.toFixed(6);
        document.getElementById('longitude').value = pos.coords.longitude.toFixed(6);
        status.textContent = 'Localização encontrada. Buscando doações próximas…';
        document.getElementById('filtro-regiao').submit();
    }, function (error) {
        button.disabled = false;
        status.textContent = error.code === 1 ? 'Permita o acesso à localização nas configurações do navegador e tente novamente.' : 'Não foi possível obter sua localização. Confira se a localização do dispositivo está ligada e tente novamente.';
    }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 300000 });
});
</script>
<?= $this->endSection() ?>