<?php

namespace App\Controllers;

use App\Models\Cliente_Model;
use App\Models\DoadorModel; 
use App\Models\EnderecoModel;


class Cliente extends BaseController
{
    // -- LOGIN REDIRECIONA PARA O LOGIN
    public function login()
    {
        
        return view('cliente/login');
    }

    // -- AUTENTICAR VERIFICA AS CREDENCIAIS E CRIA SESSÃO
    public function autenticar()
    {
        session()->remove(['id', 'nome', 'logado']);

        $model = new \App\Models\Cliente_Model();

        $email = trim($this->request->getPost('email'));
        $senha = $this->request->getPost('senha');

        
        if (empty($email) || empty($senha)) {
            return redirect()->back()->with('erro', 'Preencha email e senha');
        }

        $cliente = $model->where('email', $email)->first();

        if (!$cliente) {
            return redirect()->back()->with('erro', 'Usuário não encontrado');
        }

        if ($cliente['senha'] !== $senha) {
            return redirect()->back()->with('erro', 'Senha inválida');
        }

        if ($cliente['verificado'] == 0) {
            return redirect()->back()->with('erro', 'Você precisa verificar seu cadastro');
        }

        session()->set([
            'id'     => $cliente['id'],
            'nome'   => $cliente['nome'],
            'logado' => true
        ]);

        return redirect()->to('/cliente');
    }

    // -- LOGOUT DESTRÓI A SESSÃO
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }

    // -- INDEX VERIFICA SE ESTÁ LOGADO E MOSTRA O DASHBOARD
    public function index()
    {
        $this->verificarLogin();

        // 🔥 limpa automaticamente

        return view('cliente/index');
    }

    // -- CADASTRAR MOSTRA O FORMULÁRIO DE CADASTRO
    public function cadastrar()
    {

        return view('cliente/cadastrar');
    }

    // -- VERIFICAR MOSTRA O FORMULÁRIO DE VERIFICAÇÃO DE CÓDIGO
    private function verificarLogin()
    {
        if (!session()->get('logado')) {
            header('Location: ' . base_url('cliente/login'));
            exit;
        }
    }

    // -- CATÁLOGO MOSTRA AS ROUPAS DISPONÍVEIS
    public function catalogo()
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT roupas.*, doador.nome as nome_doador
            FROM roupas
            JOIN doador ON doador.id = roupas.idDoador
        ");

        $dados['roupas'] = $query->getResultArray();

        return view('cliente/catalogo', $dados);
    }

    // -- DETALHES MOSTRA OS DETALHES DE UMA ROUPA
    public function detalhes($id)
    {
        $db = \Config\Database::connect();

        $query = $db->query("
            SELECT roupas.*, 
        doador.nome as nome_doador, 
        doador.celular, 
        doador.email
            FROM roupas
            JOIN doador ON doador.id = roupas.idDoador
            WHERE roupas.id = ?
        ", [$id]);

        $dados['roupa'] = $query->getRowArray();

        return view('cliente/detalhes', $dados);
    }

    // -- INTERESSE REGISTRA O INTERESSE DO CLIENTE EM UMA ROUPA
    public function interesse($idRoupa)
    {
        $db = \Config\Database::connect();

        // evita duplicado
        $existe = $db->table('interesse')
            ->where('idRoupa', $idRoupa)
            ->where('idCliente', session()->get('id'))
            ->get()
            ->getRow();

        if (!$existe) {
            $db->table('interesse')->insert([
                'idRoupa'   => $idRoupa,
                'idCliente' => session()->get('id')
            ]);
        }

        return redirect()->back()->with('sucesso', 'Interesse registrado!');
    }

    // -- SALVAR RECEBE OS DADOS DO FORMULÁRIO DE CADASTRO E SALVA O CLIENTE

    public function salvar()
{
    try {

        $clienteModel  = new \App\Models\Cliente_Model();
        $enderecoModel = new \App\Models\EnderecoModel();

        // =====================
        // DADOS DO FORM
        // =====================
        $nome   = trim($this->request->getPost('nome'));
        $email  = trim($this->request->getPost('email'));
        $senha  = $this->request->getPost('senha');

        $celular = preg_replace('/\D/', '', $this->request->getPost('celular'));

        $dataNas = $this->request->getPost('data_nas');

        // =====================
        // VALIDAÇÕES
        // =====================

        if (!$nome || !$email || !$senha) {
            return redirect()->back()->with('erro', 'Preencha todos os campos obrigatórios');
        }

        if (strlen($celular) != 11) {
            return redirect()->back()->with('erro', 'Número de celular inválido');
        }

        // email duplicado
        if ($clienteModel->where('email', $email)->first()) {
            return redirect()->back()->with('erro', 'Email já cadastrado');
        }

        // celular duplicado
        if ($clienteModel->where('celular', $celular)->first()) {
            return redirect()->back()->with('erro', 'Celular já cadastrado');
        }

        // =====================
        // CÓDIGO VERIFICAÇÃO
        // =====================
        $codigo = rand(100000, 999999);

        // =====================
        // INSERT CLIENTE
        // =====================
        $insert = $clienteModel->insert([
            'nome'                => $nome,
            'email'               => $email,
            'celular'             => $celular,
            'data_nas'            => $dataNas,
            'senha'               => $senha,
            'codigo_verificacao'  => $codigo,
            'verificado'          => 0
        ]);

        if (!$insert) {
            return redirect()->back()->with('erro', 'Erro ao salvar cliente');
        }

        $clienteId = $clienteModel->getInsertID();

        // =====================
        // INSERT ENDEREÇO
        // =====================
        $enderecoModel->insert([
            'rua'          => $this->request->getPost('rua'),
            'complemento'  => $this->request->getPost('complemento'),
            'bairro'       => $this->request->getPost('bairro'),
            'municipio'    => $this->request->getPost('municipio'),
            'estado'       => $this->request->getPost('estado'),
            'cep'          => $this->request->getPost('cep'),
            'tipo'         => 'cliente',
            'idReferencia' => $clienteId
        ]);

        // =====================
        // SUCESSO
        // =====================
        return redirect()
    ->to('/cliente/verificar/' . $clienteId)
    ->with('sucesso', 'Cadastro realizado! Enviamos um código para seu e-mail.');

    } catch (\Exception $e) {

        return redirect()->back()->with('erro', 'Erro: ' . $e->getMessage());
    }
}




    // -- VERIFICAR MOSTRA O FORMULÁRIO DE VERIFICAÇÃO DE CÓDIGO
    public function verificar($id)
{
    $model = new Cliente_Model();
    $cliente = $model->find($id);

    return view('cliente/verificar', [
        'id'      => $id,
        'email'   => $cliente['email'],
        'celular' => $cliente['celular'],
        'codigo'  => $cliente['codigo_verificacao']
    ]);
}

    // -- CONFIRMAR CODIGO RECEBE O CÓDIGO DO FORMULÁRIO DE VERIFICAÇÃO E ATUALIZA O CLIENTE
    public function confirmarCodigo($id)
    {
        $model = new \App\Models\Cliente_Model();

        $codigoPost = $this->request->getPost('codigo');

        $cliente = $model->find($id);

        if (!$cliente) {
            return redirect()->to('/cliente/login')->with('erro', 'Cliente não encontrado');
        }

        if ($cliente['codigo_verificacao'] == $codigoPost) {

            $model->update($id, [
                'verificado' => 1
            ]);

            // login automático (opcional)
            session()->set([
                'id'     => $cliente['id'],
                'nome'   => $cliente['nome'],
                'logado' => true
            ]);

            return redirect()->to('/cliente');
        }

        return redirect()->back()->with('erro', 'Código inválido');
    }
    
    // -- PERFIL MOSTRA O PERFIL DO CLIENTE
    public function perfil()
    {
        $clienteModel = new \App\Models\Cliente_Model();
        $enderecoModel = new \App\Models\EnderecoModel();

        $id = session()->get('id');

        $cliente = $clienteModel->find($id);

        $endereco = $enderecoModel
            ->where('idReferencia', $id)
            ->where('tipo', 'cliente')
            ->first();

        return view('cliente/perfil', [
            'cliente' => $cliente,
            'endereco' => $endereco
        ]);
    }

    // -- ATUALIZAR PERFIL RECEBE OS DADOS DO FORMULÁRIO DE PERFIL E ATUALIZA O CLIENTE
    public function atualizarPerfil()
    {
        $clienteModel = new \App\Models\Cliente_Model();
        $enderecoModel = new \App\Models\EnderecoModel();

        $id = session()->get('id');

        // 🔹 Atualiza cliente
        $dataCliente = [
            'nome'   => $this->request->getPost('nome'),
            'email'  => $this->request->getPost('email'),
            'celular'=> $this->request->getPost('celular'),
            'data_nas'=> $this->request->getPost('data_nas'),
        ];

        if ($this->request->getPost('senha')) {
            $dataCliente['senha'] = $this->request->getPost('senha');
        }

        $clienteModel->update($id, $dataCliente);

        // 🔹 Dados do endereço
        $dataEndereco = [
            'cep'         => $this->request->getPost('cep'),
            'rua'         => $this->request->getPost('rua'),
            'complemento' => $this->request->getPost('complemento'),
            'bairro'      => $this->request->getPost('bairro'),
            'municipio'   => $this->request->getPost('municipio'),
            'estado'      => $this->request->getPost('estado'),
            'tipo'        => 'cliente',
            'idReferencia'=> $id
        ];

        // 🔥 verifica se já existe endereço
        $endereco = $enderecoModel
            ->where('idReferencia', $id)
            ->where('tipo', 'cliente')
            ->first();

        if ($endereco) {
            $enderecoModel->update($endereco['id'], $dataEndereco);
        } else {
            $enderecoModel->insert($dataEndereco);
        }

        return redirect()->back()->with('sucesso', 'Dados atualizados com sucesso!');
    }

    // -- HISTÓRICO MOSTRA O HISTÓRICO DE INTERESSES DO CLIENTE
    public function historico()
    {
        $model = new \App\Models\InteresseItemModel();

        $data['historico'] = $model->historicoCliente(session()->get('id'));

        return view('cliente/historico', $data);
    }
}