<?php

namespace App\Controllers;

use App\Models\DoadorModel;
use App\Models\EnderecoModel;
use App\Models\RoupaModel;
use App\Models\InteresseItemModel;



class Doador extends BaseController
{
    // -- MOSTRA A PAGINA INICIAL
    public function index()
    {
        $this->verificarLogin();
        $this->limparDoacoes();

        $model = new \App\Models\RoupaModel();

        $itens = $model
            ->where('idDoador', session()->get('id'))
            ->findAll();

        return view('doador/index', [
            'itens' => $itens
        ]);
    }

    private function verificarLogin()
{
    if (!session()->get('logado')) {
        return redirect()->to('/doador/login')->send();
        exit;
    }
}


    // -- SALVA A ROUPA CADASTRADA
    public function salvarRoupa()
{
    $model = new \App\Models\RoupaModel();

    $file = $this->request->getFile('imagem');

    $nomeImagem = null;

    if ($file && $file->isValid() && !$file->hasMoved()) {

        $nomeImagem = $file->getRandomName();

        $file->move(ROOTPATH . 'public/uploads', $nomeImagem);
    }

    $model->insert([
        'tipo'          => $this->request->getPost('tipo'),
        'cor'           => $this->request->getPost('cor'),
        'quantidade'    => $this->request->getPost('quantidade'),
        'bairro'        => $this->request->getPost('bairro'),
        'ponto_doacao'  => $this->request->getPost('ponto_doacao'),
        'idDoador'      => session()->get('id'),
        'imagem'        => $nomeImagem,
        'criado_em'     => date('Y-m-d H:i:s')
    ]);

    return redirect()->to('/doador/roupa');
}
public function cadastrarRoupa()
{
    return view('doador/roupa/cadastrar');
}

    // -- LOGIN DO DOADOR   
    public function login()
    {
        return view('doador/login');
    }

    // -- MOSTRA AS ROUPAS DO DOADOR
    public function roupas()
    {

        $this->limparDoacoes(); // 🔥 aqui também

        $model = new \App\Models\RoupaModel();

        $dados['roupas'] = $model
            ->where('idDoador', session()->get('id'))
            ->findAll();

        return view('doador/roupa/index', $dados);
    }

    // -- EXCLUI UMA ROUPA, VERIFICA SE O ITEM PERTENCE AO DOADOR ANTES DE EXCLUIR
    public function excluirRoupa($id)
    {
        $model = new RoupaModel();

        // segurança: só pode excluir se for dele
        $model->where('id', $id)
            ->where('idDoador', session()->get('id'))
            ->delete();

        return redirect()->to('/doador/roupa');
    }

    // -- VERIFICA OS INTERESSADOS DE UMA ROUPA
    public function autenticar()
    {
        $model = new \App\Models\DoadorModel();

        $email = $this->request->getPost('email');
        $senha = $this->request->getPost('senha');

        $doador = $model->where('email', $email)->first();

        if ($doador && $doador['senha'] === $senha) {

            // 🔥 BLOQUEIO AQUI
            if ($doador['verificado'] != 1) {
                return redirect()->back()->with('erro', 'Verifique seu número primeiro');
            }
            $model->update($doador['id'], [
                'ultimo_acesso' => date('Y-m-d H:i:s')
            ]);

            session()->set([
                'id'     => $doador['id'],
                'nome'   => $doador['nome'],
                'tipo'   => 'doador',
                'logado' => true
            ]);

            return redirect()->to('/doador');
        }

        return redirect()->back()->with('erro', 'Email ou senha inválidos');
    }

    public function logout()
{
    session()->destroy();

    return redirect()->to(base_url('/'));
}

    // -- CADASTRAR MOSTRA O FORMULÁRIO DE CADASTRO
    public function cadastrar()
    {
        return view('doador/cadastrar');
    }

    // -- SALVAR RECEBE OS DADOS DO FORMULÁRIO DE CADASTRO, VALIDA E SALVA O DOADOR
    public function salvar()
{
    try {

        $doadorModel   = new \App\Models\DoadorModel();
        $enderecoModel = new \App\Models\EnderecoModel();

        // =====================
        // DADOS DO FORM
        // =====================
        $nome   = trim($this->request->getPost('nome'));
        $email  = trim($this->request->getPost('email'));
        $senha  = $this->request->getPost('senha');

        // 🔥 AQUI ESTAVA O ERRO (faltava essa linha)
        $celular = preg_replace('/\D/', '', $this->request->getPost('celular'));

        $dataNas = $this->request->getPost('data_nas');

        // =====================
        // VALIDAÇÕES BÁSICAS
        // =====================
        if (!$nome || !$email || !$senha) {
            return redirect()->back()->with('erro', 'Preencha todos os campos obrigatórios');
        }

        if (strlen($celular) != 11) {
            return redirect()->back()->with('erro', 'Número de celular inválido');
        }

        // =====================
        // EMAIL DUPLICADO
        // =====================
        if ($doadorModel->where('email', $email)->first()) {
            return redirect()->back()->with('erro', 'Email já cadastrado');
        }

        // =====================
        // CELULAR DUPLICADO
        // =====================
        if ($doadorModel->where('celular', $celular)->first()) {
            return redirect()->back()->with('erro', 'Celular já cadastrado');
        }

        // =====================
        // CÓDIGO VERIFICAÇÃO
        // =====================
        $codigo = rand(100000, 999999);

        // =====================
        // INSERT DOADOR
        // =====================
        $doadorModel->insert([
            'nome'               => $nome,
            'email'              => $email,
            'celular'            => $celular,
            'data_nas'           => $dataNas,
            'senha'              => $senha,
            'codigo_verificacao' => $codigo,
            'verificado'         => 0
        ]);

        $doadorId = $doadorModel->getInsertID();

        // =====================
        // ENDEREÇO (igual cliente)
        // =====================
        $enderecoModel->insert([
            'rua'          => $this->request->getPost('rua'),
            'complemento'  => $this->request->getPost('complemento'),
            'bairro'       => $this->request->getPost('bairro'),
            'municipio'    => $this->request->getPost('municipio'),
            'estado'       => $this->request->getPost('estado'),
            'cep'          => $this->request->getPost('cep'),
            'tipo'         => 'doador',
            'idReferencia' => $doadorId
        ]);

        // =====================
        // EMAIL DE VERIFICAÇÃO
        // =====================
        $emailService = \Config\Services::email();

        $emailService->setFrom('sistema@vestemais.com', 'Veste+');
        $emailService->setTo($email);
        $emailService->setSubject('Código de verificação');
        $emailService->setMessage('Seu código é: ' . $codigo);

        $emailService->send();

        // =====================
        // REDIRECIONAMENTO
        // =====================
        return redirect()->to('/doador/verificar/' . $doadorId);

    } catch (\Exception $e) {
        return redirect()->back()->with('erro', 'Erro: ' . $e->getMessage());
    }
}

    // -- VERIFICA SE O CELULAR É VERDADEIRO
    public function verificar($id)
    {
        $model = new DoadorModel();
        $doador = $model->find($id);

        return view('doador/verificar', [
        'id' => $id,
        'codigo' => $doador['codigo_verificacao'],
        'celular' => $doador['celular']
        ]);
    }

    // -- CONFIRMAR RECEBE O CÓDIGO DO FORMULÁRIO DE VERIFICAÇÃO E ATUALIZA O DOADOR
    public function confirmar()
    {
        $model = new \App\Models\DoadorModel();

        $id = $this->request->getPost('id');
        $codigo = $this->request->getPost('codigo');

        $doador = $model->find($id);

        if ($doador && $doador['codigo_verificacao'] == $codigo) {

            // 🔥 atualiza aqui
            $model->update($id, [
                'verificado' => 1,
                'codigo_verificacao' => null
            ]);

            // 🔥 login automático
            session()->set([
                'id'     => $doador['id'],
                'nome'   => $doador['nome'],
                'tipo'   => 'doador',
                'logado' => true
            ]);

            return redirect()->to('/doador');
        }

        return redirect()->back()->with('erro', 'Código incorreto');
    }

    // -- REENVIAR GERA UM NOVO CÓDIGO DE VERIFICAÇÃO E ATUALIZA O DOADOR
    public function reenviarCodigo()
    {
        $model = new \App\Models\DoadorModel();

        // antes de gerar novo código
        $tempo = session()->get('tempo_codigo');

        if ($tempo && (time() - $tempo < 30)) {
            return redirect()->back()->with('erro', 'Aguarde 30 segundos');
        }

        session()->set('tempo_codigo', time());

        $id = $this->request->getPost('id');

        $novoCodigo = rand(100000, 999999);

        $model->update($id, [
            'codigo_verificacao' => $novoCodigo
        ]);

        return redirect()->to('/doador/verificar/'.$id)
                        ->with('sucesso', 'Novo código gerado!');
    }

    // -- MARCAR COMO DOADO ATUALIZA O STATUS DA ROUPA PARA DOADO E REGISTRA A DATA DE DOAÇÃO
    public function marcarDoado($id)
    {
        $model = new RoupaModel();

        $model->update($id, [
            'status' => 'doado',
            'data_doado' => date('Y-m-d H:i:s')
        ]);

        return redirect()->back()->with('sucesso', 'Marcado como doado!');
    }

    // -- LIMPAR DOAÇÕES APAGA AS ROUPAS MARCADAS COMO DOADAS HÁ MAIS DE 7 DIAS
    public function limparDoacoes()
    {
        $model = new RoupaModel();

        $roupas = $model
            ->where('status', 'doado')
            ->where('data_doado <=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->findAll();

        foreach ($roupas as $r) {

            // apagar imagem
            if ($r['imagem'] && file_exists(FCPATH.'uploads/'.$r['imagem'])) {
                unlink(FCPATH.'uploads/'.$r['imagem']);
            }

            // apagar do banco
            $model->delete($r['id']);
        }
    }

    // -- INTERESSADOS MOSTRA OS INTERESSADOS DE UMA ROUPA, SE O ID FOR PASSADO, OU TODOS OS INTERESSADOS DAS ROUPAS DO DOADOR SE O ID FOR NULO
    public function interessados($item_id = null)
    {
        $interesseModel = new \App\Models\InteresseItemModel();

        if ($item_id) {
            $data['interessados'] = $interesseModel->get_interessados_por_item($item_id);
        } else {
            // 🔥 TODOS os interessados das roupas do doador
            $data['interessados'] = $interesseModel->getTodosInteressadosDoador(session()->get('id'));
        }

        return view('doador/interessados', $data);
    }

    // -- PERFIL MOSTRA O PERFIL DO DOADOR
    public function perfil()
    {
        $this->verificarLogin();

        $doadorModel = new \App\Models\DoadorModel();
        $enderecoModel = new \App\Models\EnderecoModel();

        $id = session()->get('id');

        $doador = $doadorModel->find($id);

        $endereco = $enderecoModel
            ->where('idReferencia', $id)
            ->where('tipo', 'doador')
            ->first();

        return view('doador/perfil', [
            'doador' => $doador,
            'endereco' => $endereco
        ]);
    }

    // -- ATUALIZAR PERFIL RECEBE OS DADOS DO FORMULÁRIO DE PERFIL E ATUALIZA O DOADOR
    public function atualizarPerfil()
    {
        $doadorModel = new \App\Models\DoadorModel();
        $enderecoModel = new \App\Models\EnderecoModel();

        $id = session()->get('id');

        // 🔹 dados do doador
        $dataDoador = [
            'nome'   => $this->request->getPost('nome'),
            'email'  => $this->request->getPost('email'),
            'celular'=> $this->request->getPost('celular'),
            'data_nas'=> $this->request->getPost('data_nas'),
        ];

        if ($this->request->getPost('senha')) {
            $dataDoador['senha'] = $this->request->getPost('senha');
        }

        $doadorModel->update($id, $dataDoador);

        // 🔹 endereço
        $dataEndereco = [
            'cep'         => $this->request->getPost('cep'),
            'rua'         => $this->request->getPost('rua'),
            'complemento' => $this->request->getPost('complemento'),
            'bairro'      => $this->request->getPost('bairro'),
            'municipio'   => $this->request->getPost('municipio'),
            'estado'      => $this->request->getPost('estado'),
            'tipo'        => 'doador',
            'idReferencia'=> $id
        ];

        $endereco = $enderecoModel
            ->where('idReferencia', $id)
            ->where('tipo', 'doador')
            ->first();

        if ($endereco) {
            $enderecoModel->update($endereco['id'], $dataEndereco);
        } else {
            $enderecoModel->insert($dataEndereco);
        }

        return redirect()->back()->with('sucesso', 'Perfil atualizado!');
    }


}