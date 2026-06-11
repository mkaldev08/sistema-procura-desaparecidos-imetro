<?php
include("conexao.php");
if (isset($_GET['id_pessoa'])) {
    $id_pessoa = $_GET['id_pessoa'];
    $query = "SELECT * FROM tb_pessoa WHERE id_pessoa = $id_pessoa";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_array($result);
        $nome = $row['nome'];
        $genero = $row['genero'];
        $morada = $row['morada'];
        $data_nascimento = $row['data_nascimento'];
        $contacto = $row['contacto'];
    }
}
if (isset($_POST['actualizar_dados'])) {
    $id_pessoa = $_GET['id_pessoa'];
    $nome = $_POST['nome'];
    $genero = $_POST['genero'];
    $morada = $_POST['morada'];
    $data_nascimento = $_POST['data_nascimento'];
    $contacto = $_POST['contacto'];
    $query = "UPDATE tb_pessoa set nome ='$nome', genero ='$genero', morada ='$morada',
data_nascimento ='$data_nascimento', contacto='$contacto' where id_pessoa =
$id_pessoa";
    mysqli_query($conn, $query);
    $_SESSION['sms'] = 'Registo Actualizado com Sucesso';
    header("Location: visualizar_Pessoa.php");
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Actualizar Pessoa</title>
</head>

<body>
    <form action="Actualizar_Pessoa.php?id_pessoa=<?php echo $_GET['id_pessoa']; ?>" method="POST"> <br /><br />
        <h5>FORMULARIO DE ACTUALIZAÇÃO DAS PESSOAS</h5> Nome: <input type="text" name="nome" maxlength="10"
            required="required" placeholder=" Digite Nome da Pessoa" size="50" value="<?php echo $nome ?>"> </br>
        Genero: <select name="genero">
            <option selected value=value="<?php echo $genero ?>">
            <option selected value="Masculino">Masculino
            <option selected value="Femenino">Femenino
        </select> </br> Data de Nascimento: <input type="date" name="data_nascimento" required="required"
            value="<?php echo $data_nascimento ?>"> </br> Morada: <input type="text" name="morada" required="required"
            value="<?php echo $morada ?>"> </br>
        Contacto:
        <input type="number" name="contacto" maxlength="9" required="required" placeholder="
Digite Contacto da Pessoa" value="<?php echo $contacto ?>">
        <input type="submit" name="actualizar_dados" autofocus value="ACTUALIZAR DADOS">
    </form>
</body>

</html>