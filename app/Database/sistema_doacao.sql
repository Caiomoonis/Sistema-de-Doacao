CREATE DATABASE IF NOT EXISTS sistema_doacao CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE sistema_doacao;

CREATE TABLE IF NOT EXISTS doador (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  celular VARCHAR(20) NOT NULL,
  data_nas DATE NULL,
  senha VARCHAR(255) NOT NULL,
  codigo_verificacao VARCHAR(20) NULL,
  verificado TINYINT(1) NOT NULL DEFAULT 0,
  ultimo_acesso DATETIME NULL,
  PRIMARY KEY (id), UNIQUE KEY uq_doador_email (email), UNIQUE KEY uq_doador_celular (celular)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cliente (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nome VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL,
  celular VARCHAR(20) NOT NULL,
  data_nas DATE NULL,
  senha VARCHAR(255) NOT NULL,
  codigo_verificacao VARCHAR(20) NULL,
  verificado TINYINT(1) NOT NULL DEFAULT 0,
  ultimo_acesso DATETIME NULL,
  PRIMARY KEY (id), UNIQUE KEY uq_cliente_email (email), UNIQUE KEY uq_cliente_celular (celular)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS endereco (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  idReferencia INT UNSIGNED NOT NULL,
  tipo VARCHAR(20) NOT NULL,
  rua VARCHAR(190) NULL,
  complemento VARCHAR(190) NULL,
  bairro VARCHAR(120) NULL,
  municipio VARCHAR(120) NULL,
  estado VARCHAR(60) NULL,
  local_doacao VARCHAR(255) NULL,
  cep VARCHAR(20) NULL,
  PRIMARY KEY (id), KEY idx_endereco_referencia (tipo, idReferencia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS roupas (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  tipo VARCHAR(120) NOT NULL,
  cor VARCHAR(80) NULL,
  quantidade INT UNSIGNED NOT NULL DEFAULT 1,
  bairro VARCHAR(120) NULL,
  ponto_doacao VARCHAR(255) NULL,
  latitude DECIMAL(10,7) NULL,
  longitude DECIMAL(10,7) NULL,
  idDoador INT UNSIGNED NOT NULL,
  imagem VARCHAR(255) NULL,
  criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status VARCHAR(30) NOT NULL DEFAULT 'disponível',
  data_doado DATETIME NULL,
  PRIMARY KEY (id), KEY idx_roupas_doador (idDoador), KEY idx_roupas_status (status),
  CONSTRAINT fk_roupas_doador FOREIGN KEY (idDoador) REFERENCES doador(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS interesse (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  idRoupa INT UNSIGNED NOT NULL,
  idCliente INT UNSIGNED NOT NULL,
  data_interesse DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id), UNIQUE KEY uq_interesse_item_cliente (idRoupa, idCliente), KEY idx_interesse_cliente (idCliente),
  CONSTRAINT fk_interesse_roupa FOREIGN KEY (idRoupa) REFERENCES roupas(id) ON DELETE CASCADE,
  CONSTRAINT fk_interesse_cliente FOREIGN KEY (idCliente) REFERENCES cliente(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;