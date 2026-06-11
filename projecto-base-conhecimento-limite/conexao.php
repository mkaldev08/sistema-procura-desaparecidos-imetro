<?php
session_start();

$servidor = "127.0.0.1";
$usuario = "root";
$senha = "admin@kalueka";
$dbname = "bd_pessoa";
$port = "3306";

// Criar a conexao com a base de dados.
$conn = mysqli_connect($servidor, $usuario, $senha, $dbname, $port);

if (!$conn) {
	die("Falha na conexao: " . mysqli_connect_error());
}

