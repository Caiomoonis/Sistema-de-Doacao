<?= $this->extend('doador/layout/dashboard') ?>

<?= $this->section('title') ?>Doar Roupa<?= $this->endSection() ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="<?= base_url('assets/css/doador/roupa/cadastrar.css') ?>">
<h2>Doar Roupa</h2>
<form action="<?= base_url('doador/roupas/salvar') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <label>Tipo</label><select name="tipo" class="form-control mb-2"><option>Calça</option><option>Blusa</option><option>Short</option><option>Top</option><option>Casaco</option><option>Roupa Intima</option><option>Vestido</option></select>
    <input type="text" name="cor" placeholder="Cor" class="form-control mb-2">
    <input type="number" name="quantidade" placeholder="Quantidade" class="form-control mb-2" min="1">
    <input type="text" name="bairro" placeholder="Bairro" class="form-control mb-2">
    <input type="text" name="ponto_doacao" placeholder="Ponto público de retirada" class="form-control mb-3">
    <fieldset class="border rounded p-3 mb-3"><legend class="float-none w-auto px-2 fs-6">Coordenadas do ponto de retirada</legend>
        <p class="text-muted">Use um local público de retirada, não o endereço da sua casa.</p>
        <div class="row"><div class="col-md-6"><label for="latitude">Latitude</label><input id="latitude" type="number" step="any" min="-90" max="90" name="latitude" class="form-control mb-2" placeholder="Ex.: -23.5505"></div>
        <div class="col-md-6"><label for="longitude">Longitude</label><input id="longitude" type="number" step="any" min="-180" max="180" name="longitude" class="form-control mb-2" placeholder="Ex.: -46.6333"></div></div>
        <button type="button" id="usar-localizacao" class="btn btn-outline-secondary btn-sm">Preencher com minha localização</button> <small id="localizacao-status" class="text-muted"></small>
    </fieldset>
    <label>Imagem da roupa</label><input type="file" name="imagem" class="form-control mb-3">
    <button type="submit" class="btn btn-success">Doar</button>
</form>
<script>
document.getElementById('usar-localizacao').addEventListener('click', function () {
    const status = document.getElementById('localizacao-status');
    if (!navigator.geolocation) { status.textContent = 'Este navegador não oferece localização.'; return; }
    status.textContent = 'Obtendo localização…';
    navigator.geolocation.getCurrentPosition(function (pos) {
        document.getElementById('latitude').value = pos.coords.latitude.toFixed(6);
        document.getElementById('longitude').value = pos.coords.longitude.toFixed(6);
        status.textContent = 'Coordenadas preenchidas.';
    }, function () { status.textContent = 'Não foi possível obter a localização. Preencha as coordenadas manualmente.'; });
});
</script>
<?= $this->endSection() ?>