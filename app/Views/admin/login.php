<!doctype html>
<html lang="pt-br"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Administração | Veste+</title>
<style>body{font:16px system-ui;background:#f2f5f2;display:grid;place-items:center;min-height:100vh;margin:0;color:#17332b}.box{background:white;padding:32px;border-radius:16px;width:min(360px,calc(100% - 40px));box-shadow:0 12px 40px #18352b18}h1{margin:0 0 8px}p{color:#60746d}label{display:block;margin:16px 0 6px}input{width:100%;box-sizing:border-box;padding:12px;border:1px solid #cbd8d2;border-radius:8px}button{width:100%;margin-top:20px;padding:12px;background:#19734f;color:white;border:0;border-radius:8px;font-weight:700;cursor:pointer}.error{padding:10px;background:#fff0ee;color:#a32d20;border-radius:8px}</style></head>
<body><main class="box"><h1>Área administrativa</h1><p>Acesso local para consulta dos dados do sistema.</p>
<?php if ($erro = session()->getFlashdata('erro')): ?><div class="error"><?= esc($erro) ?></div><?php endif ?>
<form method="post" action="<?= site_url('admin/autenticar') ?>">
<?= csrf_field() ?><label for="usuario">Usuário</label><input id="usuario" name="usuario" autocomplete="username" required>
<label for="senha">Senha</label><input id="senha" name="senha" type="password" autocomplete="current-password" required>
<button type="submit">Entrar</button></form></main></body></html>