-- ============================================================
--  Sistema de Cadastro e Login Seguro — Vesper Aurora
--  Arquivo : database.sql
--  Descrição: Criação do banco de dados e estrutura da tabela
--             de usuários com suporte a caracteres especiais.
--  Motor   : InnoDB  |  Charset: utf8mb4
-- ============================================================

-- Cria o banco de dados caso não exista, garantindo suporte
-- completo a emojis e caracteres acentuados (utf8mb4).
CREATE DATABASE IF NOT EXISTS `sistema_cadastro`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `sistema_cadastro`;

-- ============================================================
--  Tabela: usuarios
--  Armazena os dados cadastrais de cada usuário do sistema.
--  A coluna `email` possui índice UNIQUE para impedir
--  duplicidade diretamente no banco, complementando a
--  validação realizada na camada PHP.
--  A coluna `senha` comporta hashes bcrypt (≥60 chars);
--  VARCHAR(255) garante espaço para futuras implementações.
-- ============================================================
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id`         INT            NOT NULL AUTO_INCREMENT  COMMENT 'Identificador único do usuário',
    `nome`       VARCHAR(100)   NOT NULL                 COMMENT 'Nome completo fornecido no cadastro',
    `email`      VARCHAR(150)   NOT NULL                 COMMENT 'Endereço de e-mail (chave natural do sistema)',
    `senha`      VARCHAR(255)   NOT NULL                 COMMENT 'Hash bcrypt gerado por password_hash()',
    `criado_em`  TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data e hora do cadastro (UTC)',

    PRIMARY KEY (`id`),
    UNIQUE  KEY `uq_email` (`email`)       -- Garante unicidade de e-mails no banco
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci
  COMMENT='Tabela de usuários cadastrados no sistema';
