<?php
session_start();

require('conexao.php');
require('verifica_login.php');

$query = $conexao->prepare("SELECT * FROM excel_users WHERE id > 0 AND hotel LIKE :hotels AND username != :username");
$query->execute(array('hotels' => '%' . $_SESSION['hotel'] . '%', 'username' => $_SESSION['username']));
$query_qtd = $query->rowCount();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Planilhas Hotelaria</title>

    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<span class="card-group-right"><a href="logout.php"><button>Clique aqui para Sair</button></a></span>
<span class="card-group-left"><a href="painel.php"><button>Voltar</button></a></span>
        <div class="card">
            <div class="card-top">
                <h2 class="title">Bem Vindo(a) <?php echo $_SESSION['name']; ?></h2>
                <p>Cadastros de <b><?php echo $_SESSION['hotel_name']; ?> [<?php echo $_SESSION['hotel']; ?>]</b></p>
            </div>
            <center>
            <div class="card-group">
              <br>
              <table>
                <tr>
                    <td><b>Login</b></td>
                    <td><b>Nome</b></td>
                    <td><b>Hierarquia</b></td>
                    <td><b>Status</b></td>
                    <td><b>Deletar</b></td>
                </tr>
              <?php
                while($select = $query->fetch(PDO::FETCH_ASSOC)){
                $id = $select['id'];
                $username = $select['username'];
                $nome = $select['nome'];
                $hierarquia = $select['hierarquia'];
                $userstatus = $select['userstatus'];
                $hotel = $select['hotel'];

                $id_del = base64_encode('Deletar,'.$id.','.$nome.','.$username);
                $id_edit = base64_encode('Editar,'.$id.','.$nome.','.$username.','.$hierarquia.','.$hotel);
              ?>

                <tr>
                    <td><a href="profile.php?id=<?php echo $id_edit; ?>"><button><?php echo $username; ?></button></a></td>
                    <td><?php echo $nome; ?></td>
                    <td><?php echo $hierarquia; ?></td>
                    <td><?php echo $userstatus; ?></td>
                    <td><a href="profile.php?id=<?php echo $id_del; ?>"><button>Deletar</button></a></td>
                </tr>
                <tr>
                    <td colspan="5"><hr></td>
                </tr>

              <?php } ?>

              </table>
            </div>
            </center>
        </div>

</body>
</html>