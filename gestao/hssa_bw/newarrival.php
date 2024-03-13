<?php
session_start();

require('../../conexao.php');
require('../../verifica_login.php');

$dir = substr(__DIR__, -7);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    window.location.replace('../../index.html')
    </script>";
    exit();
}

error_reporting(0);

$min_date = date('Y-m-d', strtotime("-1 days"));
$max_date = date('Y-m-d', strtotime("+1 days"));

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="../../images/favicon.ico">
    <link rel="shortcut icon" href="../../images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../css/style_tabela.css">
    <title>Gestão Recepção - Downtime</title>
</head>
<body>
<div class="container">

<form action="acao.php?id=<?php echo base64_encode("NewArrival;123") ?>" method="POST">
<div class="appointment">
<label id="room_type">Selecione o Tipo de Quarto</label>
    <select name="room_type" id="room_type">
<?php
$query = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_roomtypes WHERE id > 0");
$query->execute();
while($select = $query->fetch(PDO::FETCH_ASSOC)){  
?>
            <option value="<?php echo $select['room_type'] ?>"><?php echo $select['room_type'] ?></option>
<?php } ?>
    </select><br><br>
<label id="guest_name">Hospede</label>
<input class="input-field" type="text" id="guest_name" name="guest_name" placeholder="Nome Completo" required><br>
<label id="checkin">Checkin</label>
<input class="input-field" type="date" id="checkin" value="<?php echo $hoje; ?>" min="<?php echo $min_date; ?>" max="<?php echo $max_date; ?>" name="checkin"><br>
<label id="checkout">Checkout</label>
<input class="input-field" type="date" id="checkout" value="<?php echo $max_date; ?>" min="<?php echo $hoje; ?>" name="checkout"><br>
<label id="adultos">Adultos</label>
<input class="input-field" min="1" max="4" type="number" id="adultos" value="1" name="adultos"><br>
<label id="criancas">Crianças</label>
<input class="input-field" min="0" max="4" type="number" id="criancas" value="0" name="criancas"><br>
<label id="room_ratecode">Valor Diária</label>
<input class="input-field" type="text" id="room_ratecode" name="room_ratecode" placeholder="Diária" required><br>
<label id="company">Empresa</label>
<input class="input-field" type="text" id="company" name="company" placeholder="Empresa" required><br><br>
<label id="room_msg">Comentários</label><br>
<textarea class="input-field" id="room_msg" name="room_msg" required></textarea><br>
    </div>
    <input type="submit" class="submit" value="Cadastrar">
</form>

</div>
</body>
</html>