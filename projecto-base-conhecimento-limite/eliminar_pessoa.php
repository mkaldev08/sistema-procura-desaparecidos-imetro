<?php
include("conexao.php");
if (isset($_GET['id_pessoa'])) {
    $id_pessoa = $_GET['id_pessoa'];
    $query = "DELETE FROM tb_pessoa WHERE id_pessoa = $id_pessoa";
    $result = mysqli_query($conn, $query);
    if (!$result) {
        die("Falha na Remoção do Registo");
    }
    $_SESSION['sms'] = 'Registo Eliminadi com Sucesso';
    header("Location: visualizar_pessoa.php");
}
?>