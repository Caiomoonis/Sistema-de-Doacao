<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ------ SÃO ROTAS QUE LIGAM A URL A UM CONTROLADOR E MÉTODO ESPECÍFICO ------
//

// ------------ HOME -----------------
$routes->get('/', 'Home::index');

// ----------- DOADOR -----------------
$routes->get('doador', 'Doador::index');
$routes->get('doador/cadastrar', 'Doador::cadastrar');
$routes->get('doador/roupas/cadastrar', 'Doador::cadastrarRoupa');
$routes->get('doador/login', 'Doador::login');
$routes->get('doador/logout', 'Doador::logout');
$routes->get('doador', 'Doador::index');
$routes->get('doador', 'Doador::index');
$routes->get('doador/interessados', 'Doador::interessados');
$routes->get('doador/roupa', 'Doador::roupas');
$routes->get('doador/roupa/cadastrar', 'Doador::cadastrarRoupa');
$routes->get('doador/roupa/excluir/(:num)', 'Doador::excluirRoupa/$1');
$routes->get('doador/verificar/(:num)', 'Doador::verificar/$1');
$routes->get('doador/interessados/(:num)', 'Doador::interessados/$1');
$routes->get('doador/perfil', 'Doador::perfil');
$routes->post('doador/salvar', 'Doador::salvar');
$routes->post('doador/roupas/salvar', 'Doador::salvarRoupa');
$routes->post('doador/autenticar', 'Doador::autenticar');
$routes->post('doador/roupa/salvar', 'Doador::salvarRoupa');
$routes->post('doador/reenviar', 'Doador::reenviarCodigo');
$routes->post('doador/confirmar', 'Doador::confirmar'); 
$routes->post('doador/doar/(:num)', 'Doador::marcarDoado/$1');
$routes->post('doador/atualizarPerfil', 'Doador::atualizarPerfil');


// ----------- CLIENTE -----------------
$routes->get('/cliente', 'Cliente::index');
$routes->get('cliente/cadastrar', 'Cliente::cadastrar');
$routes->get('cliente/login', 'Cliente::login');
$routes->get('cliente/logout', 'Cliente::logout');
$routes->get('cliente/catalogo', 'Cliente::catalogo');
$routes->get('cliente/detalhes/(:num)', 'Cliente::detalhes/$1');    
$routes->get('cliente', 'Cliente::index');
$routes->get('cliente/verificar/(:num)', 'Cliente::verificar/$1');
$routes->get('cliente/perfil', 'Cliente::perfil');
$routes->get('cliente/historico', 'Cliente::historico');
$routes->post('cliente/salvar', 'Cliente::salvar');
$routes->post('cliente/autenticar', 'Cliente::autenticar');
$routes->post('cliente/interesse/(:num)', 'Cliente::interesse/$1');
$routes->post('cliente/confirmarCodigo/(:num)', 'Cliente::confirmarCodigo/$1');
$routes->post('cliente/perfil/salvar', 'Cliente::atualizarPerfil');

