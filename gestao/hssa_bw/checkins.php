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

//Reservas Checkins do Dia
$query_chegadas = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_arrivals WHERE id > 0");
$query_chegadas->execute();

// Chave de criptografia
$chave = $_SESSION['hotel'].$chave;

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
    'company' => $dados_array[9]
];

}

$filtered_array = [];
foreach ($arrivalslist_array as $item) {
    if ($item['alteracao'] === 'Checkedin') {
        if (!isset($item['room_number'])) {
            $item['room_number'] = '';
        }
        $filtered_array[] = $item;
    }
}

usort($filtered_array, function ($a, $b) {
    return strcmp($a['room_number'], $b['room_number']);
});
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

<div class="container">
<!-- Chegadas -->
<fieldset>
<legend> (<?php echo count($filtered_array) ?>) Checkins Realizados</legend>
<?php
foreach ($filtered_array as $select_chegadas) {
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
    echo $company;
    ?>
<div class="appointment">
    <span class="name">[ <?php  echo $room_number?> - <?php echo $room_type ?> ]</span> <span class="time"><?php echo $guest_name ?> - <?php echo date('d/m/Y') ?> a <?php echo date('d/m/Y', strtotime("+$noites days")); ?> | <span class="name">Hospedes [ <?php echo $adultos ?> ] | Diária [ <?php echo $room_ratecode.' - '.$room_msg ?> ]</span>
    <a href="acao.php?id=<?php echo base64_encode("Chegadas;Reinstate;$id;$room_number") ?>"><button class="botao-rs-sujar">Cancelar Checkin</button></a>
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
