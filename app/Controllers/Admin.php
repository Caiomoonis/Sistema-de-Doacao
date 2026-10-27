<?php

namespace App\Controllers;

class Admin extends BaseController
{
    public function login()
    {
        if (session()->get('admin_logado')) {
            return redirect()->to('/admin');
        }

        return view('admin/login');
    }

    public function autenticar()
    {
        $usuario = trim((string) $this->request->getPost('usuario'));
        $senha   = (string) $this->request->getPost('senha');
        $usuarioCorreto = (string) env('admin.username', '');
        $hashSenha      = (string) env('admin.passwordHash', '');

        if ($usuarioCorreto !== '' && $hashSenha !== '' && hash_equals($usuarioCorreto, $usuario) && password_verify($senha, $hashSenha)) {
            session()->regenerate(true);
            session()->set('admin_logado', true);
            return redirect()->to('/admin');
        }

        return redirect()->back()->with('erro', 'Usuário ou senha inválidos.');
    }

    public function index()
    {
        if (! session()->get('admin_logado')) {
            return redirect()->to('/admin/login');
        }

        $db = \Config\Database::connect();
        $stats = [
            'doadores' => $db->table('doador')->countAllResults(),
            'beneficiarios' => $db->table('cliente')->countAllResults(),
            'itens' => $db->table('roupas')->countAllResults(),
            'interesses' => $db->table('interesse')->countAllResults(),
        ];
        $itens = $db->table('roupas')
            ->select('roupas.id, roupas.tipo, roupas.cor, roupas.quantidade, roupas.status, roupas.criado_em, doador.nome AS nome_doador')
            ->join('doador', 'doador.id = roupas.idDoador')
            ->orderBy('roupas.id', 'DESC')->limit(50)->get()->getResultArray();
        $doadores = $db->table('doador')->select('id, nome, email, celular, verificado')->orderBy('id', 'DESC')->limit(50)->get()->getResultArray();
        $beneficiarios = $db->table('cliente')->select('id, nome, email, celular, verificado')->orderBy('id', 'DESC')->limit(50)->get()->getResultArray();

        return view('admin/index', compact('stats', 'itens', 'doadores', 'beneficiarios'));
    }

    public function exportar()
    {
        if (! session()->get('admin_logado')) {
            return redirect()->to('/admin/login');
        }

        $db = \Config\Database::connect();
        $stats = [
            'Doadores cadastrados' => $db->table('doador')->countAllResults(),
            'Beneficiários cadastrados' => $db->table('cliente')->countAllResults(),
            'Itens registrados' => $db->table('roupas')->countAllResults(),
            'Interesses registrados' => $db->table('interesse')->countAllResults(),
        ];

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['RELATÓRIO DE IMPACTO — VESTE+'], ';', '"', '');
        fputcsv($stream, ['Gerado em', date('d/m/Y H:i')], ';', '"', '');
        fputcsv($stream, [], ';', '"', '');
        fputcsv($stream, ['Indicador', 'Total'], ';', '"', '');
        foreach ($stats as $label => $total) {
            fputcsv($stream, [$label, $total], ';', '"', '');
        }
        fputcsv($stream, [], ';', '"', '');
        fputcsv($stream, ['ITENS DOADOS'], ';', '"', '');
        fputcsv($stream, ['ID', 'Item', 'Cor', 'Quantidade', 'Status', 'Doador', 'Latitude', 'Longitude'], ';', '"', '');
        $items = $db->query('SELECT roupas.id, roupas.tipo, roupas.cor, roupas.quantidade, roupas.status, doador.nome AS doador, roupas.latitude, roupas.longitude FROM roupas JOIN doador ON doador.id = roupas.idDoador ORDER BY roupas.id DESC')->getResultArray();
        foreach ($items as $item) {
            fputcsv($stream, [$item['id'], $item['tipo'], $item['cor'], $item['quantidade'], $item['status'], $item['doador'], $item['latitude'], $item['longitude']], ';', '"', '');
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);
        $filename = 'relatorio-impacto-' . date('Y-m-d') . '.csv';

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }
    public function sair()
    {
        session()->remove('admin_logado');
        return redirect()->to('/admin/login');
    }
}