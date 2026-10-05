<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Veste+ — Doe roupas, transforme vidas</title>
  <meta name="description" content="Plataforma de doação de roupas. Cadastre-se, encontre pontos de coleta e ajude quem precisa de agasalho." />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,700;9..144,900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="assets/css/index.css" />
 <link rel="icon" href="icon/favicon.ico" type="image/x-icon">
</head>


<style>
body {
    margin: 0;
    font-family: 'Inter', Arial, sans-serif;
    background: linear-gradient(135deg, #f8f3ee, #fff);
}

/* CARD PRINCIPAL */
.container-verificacao {
    max-width: 480px;
    margin: 80px auto;
    background: #fff;
    padding: 35px;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border: 1px solid #eee;
}

/* TÍTULO */
.container-verificacao h2 {
    text-align: center;
    font-size: 28px;
    margin-bottom: 10px;
    color: #1a1a1a;
}

/* TEXTO */
.container-verificacao p {
    font-size: 14px;
    color: #666;
    text-align: center;
    line-height: 1.5;
}

/* INPUT */
input.form-control {
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    border-radius: 12px;
    border: 1px solid #ddd;
    outline: none;
    transition: 0.3s;
    font-size: 15px;
}

input.form-control:focus {
    border-color: #ff6b2c;
    box-shadow: 0 0 0 3px rgba(255,107,44,0.15);
}

/* BOTÃO CONFIRMAR */
.btn-success {
    margin-top: 15px;
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 12px;
    background: #ff6b2c;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
}

.btn-success:hover {
    background: #e85a1f;
    transform: translateY(-2px);
}

/* ALERTAS */
.alert {
    padding: 12px;
    border-radius: 10px;
    margin-bottom: 10px;
    font-size: 13px;
    text-align: center;
}

.alert-danger {
    background: #ffe5e5;
    color: #c0392b;
}

.alert-success {
    background: #e8fff1;
    color: #1e7e34;
}

/* BOTÃO WHATS */
.btn-whatsapp {
    display: block;
    margin-top: 20px;
    text-align: center;
    padding: 12px;
    background: #25D366;
    color: white;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    transition: 0.3s;
}

.btn-whatsapp:hover {
    background: #1ebe5d;
    transform: translateY(-2px);
}
</style>

<div class="container-verificacao">

    <h2>Verificação de Conta</h2>

    <p>
        Enviamos um código para seu contato.  
        Digite abaixo para ativar sua conta.
    </p>

    <?php if(session()->getFlashdata('erro')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('erro') ?>
        </div>
    <?php endif; ?>

    <?php if(session()->getFlashdata('sucesso')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('sucesso') ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('cliente/confirmarCodigo/'.$id) ?>">
        
        <input type="text" 
               name="codigo" 
               class="form-control" 
               placeholder="Digite o código de 6 dígitos">

        <button class="btn-success">
            Confirmar código
        </button>

    </form>

    <?php
        // evita erro se não vier do controller
        $codigoSafe = $codigo ?? '';
        $celularSafe = $celular ?? '';

        $mensagem = "Olá, estou confirmando meu cadastro no Veste+. Meu código é: " . $codigoSafe;
        $link = "https://wa.me/55" . $celularSafe . "?text=" . urlencode($mensagem);
    ?>

    <a href="<?= $link ?>" target="_blank" class="btn-whatsapp">
        💬 Enviar código pelo WhatsApp
    </a>

</div>