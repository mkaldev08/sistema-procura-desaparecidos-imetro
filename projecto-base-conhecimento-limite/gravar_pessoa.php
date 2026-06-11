<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>

<?php
include("conexao.php");

if (isset($_POST['gravar_dados'])) {
    $nome = $_POST['Nome'];
    $genero = $_POST['Genero'];
    $data_nascimento = $_POST['Data_Nascimento'];
    $morada = $_POST['Morada'];
    $contacto = $_POST['Contacto'];

    $query = "INSERT INTO tb_pessoa (nome, genero, data_nascimento, morada, contacto)
              VALUES ('$nome', '$genero', '$data_nascimento', '$morada', '$contacto')";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        die("FALHA NA CONSULTA");
    }

    $mensagem = 'DADOS GRAVADOS COM SUCESSO';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gravar Pessoa</title>
</head>
<body>
    <form action="gravar_pessoa.php" method="POST">
        <h3>FORMULARIO DE CADASTRO DAS PESSOAS</h3>

        <label>Nome:</label>
        <input type="text" name="Nome" maxlength="20" required placeholder="Digite Nome da Pessoa" size="50" />
        <br>

        <label>Genero:</label>
        <select name="Genero">
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
        </select>
        <br>

        <label>Data de Nascimento:</label>
        <input type="date" name="Data_Nascimento" required />
        <br>

        <label>Morada:</label>
        <textarea name="Morada" rows="4" cols="50"></textarea>
        <br>

        <label>Contacto:</label>
        <input type="number" name="Contacto" maxlength="9" required placeholder="Digite Contacto da Pessoa" />
        <br>

        <input type="submit" name="gravar_dados" value="GRAVAR DADOS">

        <?php
        if (isset($mensagem)) {
            echo "<p>$mensagem</p>";
        }
        ?>
    </form>
</body>
</html>
