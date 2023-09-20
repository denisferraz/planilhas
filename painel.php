<?php
session_start();

require('conexao.php');
require('verifica_login.php');


//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

if (isset($_POST['hotel'])) {
    $_SESSION['hotel'] = $_POST['hotel'];

    $_SESSION['entrada'] = '1';


    $query = $conexao->prepare("SELECT * FROM excel_hotels WHERE hotel_rid = :hotel_rid AND hotel_status = 'Ativo'");
    $query->execute(array('hotel_rid' => $_SESSION['hotel']));
    while($select = $query->fetch(PDO::FETCH_ASSOC)){
        $_SESSION['hotel_name'] = $select['hotel_name'];
    }

    header("Location: {$_SERVER['REQUEST_URI']}");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Planilhas Hotelaria</title>
    <link rel="icon" type="image/x-icon" href="images/favicon.ico">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<span class="card-group-right"><a href="logout.php"><button>Clique aqui para Sair</button></a></span>
<?php
if($_SESSION['hierarquia'] == 'Administrador'){
$id = base64_encode('Ver,123');
?>
<span class="card-group-right"><a href="configuracoes.php?id=<?php echo $id; ?>"><button>Configurações</button></a></span>
<?php }
if($_SESSION['hierarquia'] != 'Colaborador'){
$id = base64_encode('Novo,123');
?>
<span class="card-group-left"><a href="profiles.php"><button>Cadastros</button></a></span>
<span class="card-group-left"><a href="profile.php?id=<?php echo $id; ?>"><button>Novo Cadastro</button></a></span>
<?php
}

$id = base64_encode('Senha,123');
?>
<span class="card-group-left"><a href="profile.php?id=<?php echo $id; ?>"><button>Alterar Senha</button></a></span>
        <div class="card">
       <h1><?php echo $_SESSION['hotel_name']; ?></h1>

<?php if (count($_SESSION['hotels']) >= 1) : ?>
<form method="post" action="">
       <div class="sem-botao">
       <select name="hotel">
    <?php

    foreach ($_SESSION['hotels'] as $valor) {
        $query = $conexao->prepare("SELECT * FROM excel_hotels WHERE hotel_rid = :hotel_rid AND hotel_status = 'Ativo'");
        $query->execute(array('hotel_rid' => $valor));
        while($select = $query->fetch(PDO::FETCH_ASSOC)){
            $hotel_name = $select['hotel_name'];
            $hotel_rid = $select['hotel_rid'];
        }
        if ($valor === $_SESSION['hotel'] && $valor == $hotel_rid) {
            echo '<option value="' . $valor . '" selected>' . $hotel_name . '</option>';
        } else if ($valor == $hotel_rid) {
            echo '<option value="' . $valor . '">' . $hotel_name . '</option>';
        }
    }
    ?>
</select>
        <button class="botao-alterar" type="submit">Alterar Hotel</button>
    </div>
</form>
<?php endif; ?>

       <br>
            <div class="card-top">
                <h2 class="title">Bem Vindo(a) <?php echo $_SESSION['name']; ?></h2>
            <?php if($_SESSION['entrada'] == '1'){ ?>
                <p>Escolha a planilha que deseja acessar</p>
            </div>
            <center>
            <div class="card-group">
              <a href="gestao/<?php echo $_SESSION['hotel']; ?>/"><button class="botao-planilha-1">Gestão Recepção (Downtime)</button></a>
              <a href="comissao/<?php echo $_SESSION['hotel']; ?>/"><button class="botao-planilha-2">Comissões (Centralizadas)</button></a>
              <a href="auditoria/<?php echo $_SESSION['hotel']; ?>/"><button class="botao-planilha-1">Auditoria Digital (Recepção)</button></a>
              <a href="all/<?php echo $_SESSION['hotel']; ?>/"><button class="botao-planilha-2">Conciliação ALL (Auditoria)</button></a>
            </div>
            </center>
            <?php }else{ ?>
                <h2 class="title">Antes de Começar, selecione seu Hotel!!</h2>
            <?php } ?>
        </div>

</body>
</html>