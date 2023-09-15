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

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

$hoje = date('Y-m-d');

//Saidas do dia
$query_saidas = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_inhouse WHERE id > 0");
$query_saidas->execute();

// Chave de criptografia
$chave = $_SESSION['hotel'].$chave;

$presentlist_array = [];
while($select = $query_saidas->fetch(PDO::FETCH_ASSOC)){
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
    'alteracao' => $dados_array[11]
];

}

$filtered_array = [];
foreach ($presentlist_array as $item) {
    if (($item['alteracao'] === 'Pendente' || $item['alteracao'] === 'Prorrogado') && $item['checkout'] === $hoje) {
        $filtered_array[] = $item;
    }
}

usort($filtered_array, function ($a, $b) {
    return $a['room_number'] - $b['room_number'];
});
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
<legend> (<?php echo count($filtered_array) ?>) Saidas </legend>
<form action="acao.php?id=<?php echo base64_encode("Inhouse;123") ?>" id="Inhouse" method="post">
<?php
foreach ($filtered_array as $select_saidas) {
    $id = $select_saidas['id'];
    $guest_name = $select_saidas['guest_name'];
    $checkin = $select_saidas['checkin'];
    $checkout = $select_saidas['checkout'];
    $noites = $select_saidas['noites'];
    $adultos = $select_saidas['adultos'];
    $criancas = $select_saidas['criancas'];
    $room_ratecode = $select_saidas['room_ratecode'];
    $room_msg = $select_saidas['room_msg'];
    $room_number = $select_saidas['room_number'];
    $room_company = $select_saidas['room_company'];
    $room_balance = floatval($select_saidas['room_balance']);
    $room_balance = number_format($room_balance, 2, ',', '.');
    echo "$room_msg";

    //Pegar o Room Type
    $query_roomtype = $conexao->prepare("SELECT room_type FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE room_number = '{$room_number}'");
    $query_roomtype->execute();
    $resultado_roomtype = $query_roomtype->fetch(PDO::FETCH_ASSOC);
    $room_type = $resultado_roomtype['room_type'];

    ?>
<div class="appointment-saidas" onclick="selecionarRadio(this)">
    <input type="radio" name="reserva_id" value="<?php echo $id ?>">
    <span class="name">[ <?php echo $room_number ?> - <?php echo $room_type ?> ]</span> <span class="time"><?php echo $guest_name ?> - <?php echo date('d/m/Y', strtotime("$checkin")) ?> a <?php echo date('d/m/Y', strtotime("$checkout")); ?> | <span class="name">Balance: R$</span><span class="time"><?php echo $room_balance ?></span> | <span class="name">Adulos [ <?php echo $adultos ?> ] Crianças [ <?php echo $criancas ?> ] | Ratecode [ <?php echo $room_ratecode ?> ] | Company [ <?php echo $room_company ?> ]</span>
</div>

<?php
} ?>
</form>
</fieldset>
</div>
<script>
function selecionarRadio(element) {
  var radio = element.querySelector('input[type="radio"]');
  radio.checked = true;
}
</script>
</body>
</html>
