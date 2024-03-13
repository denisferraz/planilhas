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

// Chave de criptografia
$chave = $_SESSION['hotel'].$chave;

//In house
$query_inhouse = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_inhouse WHERE id > 0");
$query_inhouse->execute();

$presentlist_array = [];
while($select = $query_inhouse->fetch(PDO::FETCH_ASSOC)){
    $dados_presentlist = $select['dados_presentlist'];
    $id = $select['id'];

// Para descriptografar os dados
$dados = base64_decode($dados_presentlist);
$dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

$dados_array = explode(';', $dados_decifrados);

$presentlist_array[] = [
    'id' => $id,
    'guest_name' => $dados_array[0],
    'checkin' => $dados_array[1],
    'checkout' => $dados_array[2],
    'noites' => $dados_array[3],
    'adultos' => $dados_array[4],
    'criancas' => $dados_array[5],
    'room_ratecode' => $dados_array[6],
    'room_msg' => $dados_array[9],
    'room_number' => $dados_array[8],
    'room_company' => $dados_array[10],
    'room_balance' => $dados_array[7],
    'alteracao' => $dados_array[11],
    'reserva' => $dados_array[12],
    'room_number_antigo' => $dados_array[13]
];

}

usort($presentlist_array, function ($a, $b) {
    return $a['room_number'] - $b['room_number'];
});

$Checkedout_array = [];
$Prorrogado_array = [];
$RoomMove_array = [];
foreach ($presentlist_array as $item) {
    if ($item['alteracao'] === 'Checkedout') {
        $Checkedout_array[] = $item;
    }else if ($item['alteracao'] === 'Prorrogado') {
        $Prorrogado_array[] = $item;
    }
    
    if ($item['room_number_antigo'] !== Null) {
        $RoomMove_array[] = $item;
    }
}


//Arrivals
$query_chegadas = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_arrivals WHERE id > 0");
$query_chegadas->execute();

$arrivalslist_array = [];
while($select = $query_chegadas->fetch(PDO::FETCH_ASSOC)){
    $dados_arrivals = $select['dados_arrivals'];
    $id = $select['id'];

// Para descriptografar os dados
$dados = base64_decode($dados_arrivals);
$dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

$dados_array = explode(';', $dados_decifrados);

$arrivalslist_array[] = [
    'id' => $id,
    'guest_name' => $dados_array[0],
    'noites' => $dados_array[1],
    'adultos' => $dados_array[2],
    'criancas' => $dados_array[3],
    'room_type' => $dados_array[4],
    'room_ratecode' => $dados_array[5],
    'room_msg' => $dados_array[6],
    'room_number' => $dados_array[7],
    'alteracao' => $dados_array[8],
    'company' => $dados_array[9],
    'checkin' => $dados_array[10],
    'checkout' => $dados_array[11]
];

}

usort($arrivalslist_array, function ($a, $b) {
    return strcmp($a['room_number'], $b['room_number']);
});

$Checkedin_array = [];
$Cancelada_array = [];
foreach ($arrivalslist_array as $item) {
    if ($item['alteracao'] === 'Checkedin') {
        if (!isset($item['room_number'])) {
            $item['room_number'] = '';
        }
        $Checkedin_array[] = $item;
    }else if ($item['alteracao'] === 'Cancelada') {
        if (!isset($item['room_number'])) {
            $item['room_number'] = '';
        }
        $Cancelada_array[] = $item;
    }
}

//Saldos
$query_saldos = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_saldos WHERE id > 0");
$query_saldos->execute();

$saldos_array = [];
while($select_saldos = $query_saldos->fetch(PDO::FETCH_ASSOC)){
    $dados_saldos = $select_saldos['dados_saldos'];
    $id = $select_saldos['id'];

// Para descriptografar os dados
$dados = base64_decode($dados_saldos);
$dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

$dados_array = explode(';', $dados_decifrados);

$saldos_array[] = [
    'id' => $id,
    'reserva' => $dados_array[0],
    'diarias' => $dados_array[1],
    'aeb' => $dados_array[2],
    'credito' => $dados_array[3],
    'saldo' => $dados_array[4]
];

}

//Lançamentos
$query_lancamentos = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_cashier WHERE id > 0 ORDER BY tipo_lancamento,id");
$query_lancamentos->execute();
$query_qtd_lancamentos = $query_lancamentos->rowCount();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/style_tabela.css">
    <link rel="icon" type="image/x-icon" href="../../images/favicon.ico">
    <link rel="shortcut icon" href="../../images/favicon.ico" type="image/x-icon">
    <title>Gestão Recepção - Downtime</title>
</head>
<body>

<!-- Checkouts -->
<div class="container">
<fieldset>
<legend> (<?php echo count($Checkedout_array) ?>) Checkouts Realizados </legend>
<?php
foreach ($Checkedout_array as $select_inhouse) {
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
    $room_balance = floatval($select_inhouse['room_balance']);
    $room_balance = number_format($room_balance, 2, ',', '.');
    $reserva = $select_inhouse['reserva'];

    //Pegar o Room Type
    $query_roomtype = $conexao->prepare("SELECT room_type FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE room_number = '{$room_number}'");
    $query_roomtype->execute();
    $resultado_roomtype = $query_roomtype->fetch(PDO::FETCH_ASSOC);
    $room_type = $resultado_roomtype['room_type'];

    ?>
<div class="appointment">
    <span class="name">[ <?php echo $room_number ?> - <?php echo $room_type ?> ]</span> <span class="time"><?php echo $guest_name ?> - <?php echo date('d/m/Y', strtotime("$checkin")) ?> a <?php echo date('d/m/Y', strtotime("$checkout")); ?> | <span class="name">Adultos [ <?php echo $adultos ?> ] Crianças [ <?php echo $criancas ?> ] | Diaria [ R$<?php echo $room_ratecode ?> ] | Company [ <?php echo $room_company ?> ]</span>
    <a href="acao.php?id=<?php echo base64_encode("Inhouse;Reinstate;$id;$room_number") ?>"><button class="botao-rs-sujar">Reinstate</button></a>
</div>

<?php
} ?>
</fieldset>
</div>
<br>
<!-- Prorrogados -->
<div class="container">
<fieldset>
<legend> (<?php echo count($Prorrogado_array) ?>) Reservas Prorrogadas </legend>
<?php
foreach ($Prorrogado_array as $select_inhouse) {
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
    $reserva = $select_inhouse['reserva'];
    $room_balance = floatval($select_inhouse['room_balance']);
    $room_balance = number_format($room_balance, 2, ',', '.');

    echo 'Checkout alterado para: '.date('d/m/Y', strtotime("$checkout"));

    //Pegar o Room Type
    $query_roomtype = $conexao->prepare("SELECT room_type FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE room_number = '{$room_number}'");
    $query_roomtype->execute();
    $resultado_roomtype = $query_roomtype->fetch(PDO::FETCH_ASSOC);
    $room_type = $resultado_roomtype['room_type'];

    ?>
<div class="appointment">
    <span class="name">[ <?php echo $room_number ?> - <?php echo $room_type ?> ]</span> <span class="time"><?php echo $guest_name ?> - <?php echo date('d/m/Y', strtotime("$checkin")) ?> a <?php echo date('d/m/Y', strtotime("$checkout")); ?> | <span class="name">Adulos [ <?php echo $adultos ?> ] Crianças [ <?php echo $criancas ?> ] | Company [ <?php echo $room_company ?> ]</span>
</div>

<?php
} ?>
</fieldset>
</div>
<br>
<!-- Room Move -->
<div class="container">
<fieldset>
<legend> (<?php echo count($RoomMove_array) ?>) Trocas de Uh </legend>
<?php
foreach ($RoomMove_array as $select_inhouse) {
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
    $room_number_antigo = $select_inhouse['room_number_antigo'];
    $room_company = $select_inhouse['room_company'];
    $reserva = $select_inhouse['reserva'];
    $room_balance = floatval($select_inhouse['room_balance']);
    $room_balance = number_format($room_balance, 2, ',', '.');

    echo 'Quarto Original: '.$room_number_antigo.' Mudou para o Quarto: '.$room_number;

    //Pegar o Room Type
    $query_roomtype = $conexao->prepare("SELECT room_type FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE room_number = '{$room_number}'");
    $query_roomtype->execute();
    $resultado_roomtype = $query_roomtype->fetch(PDO::FETCH_ASSOC);
    $room_type = $resultado_roomtype['room_type'];

    ?>
<div class="appointment">
    <span class="name">[ <?php echo $room_number ?> - <?php echo $room_type ?> ]</span> <span class="time"><?php echo $guest_name ?> - <?php echo date('d/m/Y', strtotime("$checkin")) ?> a <?php echo date('d/m/Y', strtotime("$checkout")); ?> | <span class="name">Adulos [ <?php echo $adultos ?> ] Crianças [ <?php echo $criancas ?> ] | Company [ <?php echo $room_company ?> ]</span>
</div>

<?php
} ?>
</fieldset>
</div>
<br>
<!-- Chegadas -->
<div class="container">
<fieldset>
<legend> (<?php echo count($Checkedin_array) ?>) Checkins Realizados</legend>
<?php
foreach ($Checkedin_array as $select_chegadas) {
    $id = $select_chegadas['id'];
    $guest_name = $select_chegadas['guest_name'];
    $noites = $select_chegadas['noites'];
    $adultos = $select_chegadas['adultos'];
    $criancas = $select_chegadas['criancas'];
    $room_type = $select_chegadas['room_type'];
    $room_ratecode = $select_chegadas['room_ratecode'];
    $room_msg = $select_chegadas['room_msg'];
    $room_number = $select_chegadas['room_number'];
    $company = $select_chegadas['company'];
    $checkin = $select_chegadas['checkin'];
    $checkout = $select_chegadas['checkout'];
    ?>
<div class="appointment">
    <span class="name">[ <?php  echo $room_number?> - <?php echo $room_type ?> ]</span> <span class="time"><?php echo $guest_name ?> - <?php echo date('d/m/Y', strtotime($checkin)) ?> a <?php echo date('d/m/Y', strtotime($checkout)); ?> | <span class="name">Hospedes [ <?php echo $adultos ?> ] | Diária [ <?php echo $room_ratecode.' - '.$room_msg ?> ]</span>
    <a href="acao.php?id=<?php echo base64_encode("Chegadas;Reinstate;$id;$room_number") ?>"><button class="botao-rs-sujar">Cancelar Checkin</button></a>
</div>
<?php
} ?>
</fieldset>
</div>
<br>
<!-- Canceladas -->
<div class="container">
<fieldset>
<legend> (<?php echo count($Cancelada_array) ?>) Cancelamentos Realizados</legend>
<?php
foreach ($Cancelada_array as $select_chegadas) {
    $id = $select_chegadas['id'];
    $guest_name = $select_chegadas['guest_name'];
    $noites = $select_chegadas['noites'];
    $adultos = $select_chegadas['adultos'];
    $criancas = $select_chegadas['criancas'];
    $room_type = $select_chegadas['room_type'];
    $room_ratecode = $select_chegadas['room_ratecode'];
    $room_msg = $select_chegadas['room_msg'];
    $room_number = $select_chegadas['room_number'];
    $company = $select_chegadas['company'];
    $checkin = $select_chegadas['checkin'];
    $checkout = $select_chegadas['checkout'];
    ?>
<div class="appointment">
    <span class="name">[ <?php  echo $room_number?> - <?php echo $room_type ?> ]</span> <span class="time"><?php echo $guest_name ?> - <?php echo date('d/m/Y', strtotime($checkin)) ?> a <?php echo date('d/m/Y', strtotime($checkout)); ?> | <span class="name">Hospedes [ <?php echo $adultos ?> ] | Diária [ R$<?php echo $room_ratecode.' - '.$room_msg ?> ]</span>
    <a href="acao.php?id=<?php echo base64_encode("Chegadas;Reinstate;$id;$room_number") ?>"><button class="botao-rs-sujar">Reinstate</button></a>
</div>
<?php
} ?>
</fieldset>
</div>
<br>
<!-- Cashier -->
<div class="container">
<fieldset>
<legend> (<?php echo $query_qtd_lancamentos ?>) Lançamentos</legend>
<?php
while($select = $query_lancamentos->fetch(PDO::FETCH_ASSOC)){
    $tipo_lancamento = $select['tipo_lancamento'];
    $id = $select['id'];
    $pagamento_tipo = $select['pagamento_tipo'];
    $pagamento_valor = $select['pagamento_valor'];
    $reserva_id = $select['reserva_id'];
    $pagamento_valor = number_format($pagamento_valor, 2, ',', '.');
    $origem = $select['origem'];
    $username = $select['username'];

    if($origem == 'arrivals'){
        $lista = 'arrivals';
        $id_dados = 7;
    }else{
        $lista = 'presentlist';
        $id_dados = 8;
    }

$query2 = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_$origem WHERE id = '{$reserva_id}'");
$query2->execute();
while($select2 = $query2->fetch(PDO::FETCH_ASSOC)){
    $dados_cripto = $select2['dados_'.$lista];

    // Para descriptografar os dados
    $dados = base64_decode($dados_cripto);
    $dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

    $dados_array = explode(';', $dados_decifrados);

    $guest_name = $dados_array[0];
    $room_number = $dados_array[$id_dados];
}

if($tipo_lancamento == 'Pagamento'){
    echo 'Pagamentos';
    ?>
<div class="appointment">
    <span class="time"><?php  echo $username ?> - [ <?php  echo $room_number?> ]</span> <?php echo $pagamento_tipo ?> <span class="time">R$[ <?php  echo $pagamento_valor ?> ]</span> <span class="name"><?php echo $guest_name ?></span>
</div>
<?php
}else{ 
    echo 'Produtos';
    ?>
<div class="appointment">
    <span class="time"><?php  echo $username ?> - [ <?php  echo $room_number?> ]</span> <?php echo $pagamento_tipo ?> <span class="time">R$[ <?php  echo $pagamento_valor ?> ]</span> <span class="name"><?php echo $guest_name ?></span>
</div>
<?php
}} ?>
</fieldset>
</div>

</body>
</html>
