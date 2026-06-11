<?php
include("conexao.php");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Visualizar Dados Pessoais</title>
</head>

<body>
    <form action="#" method="POST">
        <div align="center">
            <h2> VISUALIZAÇÃO DE DADOS DAS PESSOAS</h2>
            <?php
            echo $_SESSION['sms']; ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>NOME</th>
                    <th>GENERO</th>
                    <th>MORADA</th>
                    <th>CONTACTOS</th>
                    <th>DATA DE NASCIMENTO</th>
                </tr>
                <?php $consulta = "SELECT * FROM TB_PESSOA";
                $resultado = mysqli_query($conn, $consulta);
                while ($row = mysqli_fetch_array($resultado)) {
                    echo "<tr> <td>" . $row['id_pessoa'] . "</td><td>" . $row['nome'] . "</td><td>" . $row['genero'] . "</td><td>" . $row['morada'] . "</td><td>" . $row['contacto'] . "</td><td>" . $row['data_nascimento'] ?>
                    <td> <a href="actualizar_pessoa.php?id_pessoa=<?php echo $row['id_pessoa'] ?>"> Actualizar <i></i> </a>
                        <a href="eliminar_pessoa.php?id_pessoa=<?php echo $row['id_pessoa'] ?> "> Excluir <i></i> </a> <a
                            href="gravar_pessoa.php"> Gravar <i></i>
                        </a>
                    </td>
                    </tr>
                    </tr>
                    <?php
                }
                ?>
        </div>
    </form>
</body>

</html>