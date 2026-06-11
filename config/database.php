<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuração e ligação à base de dados
$servidor   = '127.0.0.1';
$utilizador = 'root';
$senha      = '';
$baseDados  = 'desaparecidos_db';
$porta      = 3307;

// Criar a ligação à base de dados
$conn = mysqli_connect($servidor, $utilizador, $senha, $baseDados, $porta);

if (!$conn) {
    die('Falha na ligação à base de dados: ' . mysqli_connect_error());
}

// Forçar UTF-8 para acentos e caracteres especiais
mysqli_set_charset($conn, 'utf8mb4');
