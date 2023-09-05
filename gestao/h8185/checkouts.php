<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

$dir = substr(__DIR__, -5);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    window.location.replace('../../index.html')
    </script>";
    exit();
}

$hoje = date('Y-m-d');

//Checkouts do Dia
$query_inhouse = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_inhouse WHERE id > 0 AND alteracao = 'Checkedout' ORDER BY room_number ASC");
$query_inhouse->execute();
$inhouse_qtd = $query_inhouse->rowCount();

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="../../css/style_tabela.css">
    <title>Gestão Recepção - Downtime</title>
</head>
<body>

<div class="container">
<!-- Chegadas -->
<fieldset>
<legend> (<?php echo $inhouse_qtd ?>) Checkouts Realizados </legend>
<?php
while($select_inhouse = $query_inhouse->fetch(PDO::FETCH_ASSOC)){
    $id = $select_inhouse['id'];
    $guest_name = $select_inhouse['guest_name'];
    $checkin = $select_inhouse['checkin'];
    $checkout = $select_inhouse['checkout'];
    $noites = $select_inhouse['noites'];
    $adultos = $select_inhouse['adultos'];
    $criancas = $select_inhouse['criancas'];
    $room_ratecode = $select_inhouse['room_ratecode'];
    $room_msg = $select_inhouse['room_msg'];
    $room_number = $select_inhouse['room_number'];
    $room_company = $select_inhouse['room_company'];
    $room_balance = $select_inhouse['room_balance'];
    $room_balance = number_format($room_balance, 2, ',', '.');
    echo "$room_msg";

    //Pegar o Room Type
    $query_roomtype = $conexao->prepare("SELECT room_type FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE room_number = '{$room_number}'");
    $query_roomtype->execute();
    $resultado_roomtype = $query_roomtype->fetch(PDO::FETCH_ASSOC);
    $room_type = $resultado_roomtype['room_type'];

    ?>
<div class="appointment">
    <span class="name">[ <?php echo $room_number ?> - <?php echo $room_type ?> ]</span> <span class="time"><?php echo $guest_name ?> - <?php echo date('d/m/Y', strtotime("$checkin")) ?> a <?php echo date('d/m/Y', strtotime("$checkout")); ?> | <span class="name">Balance: R$</span><span class="time"><?php echo $room_balance ?></span> | <span class="name">Adulos [ <?php echo $adultos ?> ] Crianças [ <?php echo $criancas ?> ] | Ratecode [ <?php echo $room_ratecode ?> ] | Company [ <?php echo $room_company ?> ]</span>
    <a href="acao.php?id=<?php echo base64_encode("Inhouse;Reinstate;$id;$room_number") ?>"><button class="botao-rs-sujar">Reinstate</button></a>
</div>

<?php
} ?>
</fieldset>
</div>
</body>
</html>
