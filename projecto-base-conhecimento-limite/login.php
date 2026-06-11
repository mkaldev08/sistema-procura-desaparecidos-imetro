<?php
include("conexao.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>AREA RESERVADA</title>
</head>

<body>
    <form method="POST" action="">
        <h1>AREA RESERVADA</h1>
        <br>
        Nome do Usuario:
        <input type="text" name="nome" maxlength="10" required="required" placeholder="
Digite Nome da Usuario" size="50" />
        Palavra Passe:
        <input type="password" name="senha" maxlength="10" required="required" placeholder="
Digite Palavra Passe" size="50" />
        <input type="submit" name="logar" value="ENTRAR">
    </form>
</body>

</html>
<?php
if (isset($_POST['logar'])) {
    $nome = $_POST['nome'];
    $senha = $_POST['senha'];
    $consulta = ("SELECT * FROM tb_usuario where nome='$nome' and senha='$senha' ");
    $confirma = mysqli_query($conn, $consulta);
    if (mysqli_fetch_assoc($confirma) != 0) {
        header('location:index_funcionario.php');
    } else {
        echo 'DADOS DE USUÁRIO INCORRETOS';
    }
}
?>