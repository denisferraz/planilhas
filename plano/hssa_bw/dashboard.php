<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

$dir = substr(__DIR__, -7);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    top.location.replace('../../index.html')
    </script>";
    exit();
}

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

//Todas os Apartamentos
$dados_roomstatus = $_SESSION['dados_roomstatus'];

$dados_vago_sujo = array_filter($dados_roomstatus, function($item) {
    return $item['room_status_1'] == "Vago" && $item['room_status_2'] == "Sujo";
});

$dados_vago_limpo = array_filter($dados_roomstatus, function($item) {
    return $item['room_status_1'] == "Vago" && $item['room_status_2'] == "Limpo";
});

$dados_ocupado = array_filter($dados_roomstatus, function($item) {
    return $item['room_status_1'] == "Ocupado";
});

$dados_bloqueado = array_filter($dados_roomstatus, function($item) {
    return $item['room_status_2'] == "Bloqueado";
});

$dados_prevista = array_filter($dados_roomstatus, function($item) {
    return $item['room_stay_status'] == "Prevista";
});

// Ordenar o array por 'room_number'
usort($dados_roomstatus, function($a, $b) {
    return $a['room_number'] <=> $b['room_number'];
});

$quantidade_dados = count($dados_roomstatus);

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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Plano de Quarto</title>
</head>
<body>

<div class="container">
<!-- Diarias -->
<fieldset>
<legend>Plano de Quartos</legend>
<form action="imprimir.php" method="POST" id="formulario_plano">
<table>
<th colspan="8">Dashboard</th>
<tr><td style="background-color: black" colspan="8"></td></tr>
<tr><td align="center" colspan="8">Total (<b><?php echo $quantidade_dados ?></b>) | Vago Limpo (<b><?php echo count($dados_vago_limpo) ?></b>) | Vago Sujo (<b><?php echo count($dados_vago_sujo) ?></b>) | Previstas (<b><?php echo count($dados_prevista) ?></b>) | Ocupados (<b><?php echo count($dados_ocupado)-count($dados_prevista) ?></b>) | Bloqueados (<b><?php echo count($dados_bloqueado) ?></b>)</td></tr>
<tr><td style="background-color: black" colspan="8"></td></tr>
<tr>
    <td align="center"><b>Imprimir</b></td>
    <td align="center"><b>Camareira</b></td>
    <td align="center"><b>Total</b></td>
    <td align="center"><b>Bloqueados</b></td>
    <td align="center"><b>Vagos Limpos</b></td>
    <td align="center"><b>Ocupados</b></td>
    <td align="center"><b>Previstos</b></td>
    <td align="center"><b>Vagos Sujos</b></td>
</tr>
<?php
for($camareiras = -2; $camareiras <= $_SESSION['qtd_camareira']; $camareiras++){

    if($camareiras == 0){
        continue;
    }

//Todas os Apartamentos
$dados_roomstatus_camareira_pre = $dados_roomstatus;

$dados_roomstatus_camareira = array_filter($dados_roomstatus_camareira_pre, function($item) use($camareiras) {
    return $item['id_camareira'] == $camareiras;
});

$dados_vago_sujo_camareira = array_filter($dados_roomstatus_camareira, function($item) {
    return $item['room_status_1'] == "Vago" && $item['room_status_2'] == "Sujo";
});

$dados_vago_limpo_camareira = array_filter($dados_roomstatus_camareira, function($item) {
    return $item['room_status_1'] == "Vago" && $item['room_status_2'] == "Limpo";
});

$dados_ocupado_camareira = array_filter($dados_roomstatus_camareira, function($item) {
    return $item['room_status_1'] == "Ocupado";
});

$dados_bloqueado_camareira = array_filter($dados_roomstatus_camareira, function($item) {
    return $item['room_status_2'] == "Bloqueado";
});

$dados_prevista_camareira = array_filter($dados_roomstatus_camareira, function($item) {
    return $item['room_stay_status'] == "Prevista";
});

// Ordenar o array por 'room_number'
usort($dados_roomstatus_camareira, function($a, $b) {
    return $a['room_number'] <=> $b['room_number'];
});

$quantidade_dados_camareira = count($dados_roomstatus_camareira);
?>
<tr>
    <td align="center"><input type="checkbox" name="checkbox_camareiras[]" value="<?php echo $camareiras; ?>"></td>
    <td align="center"><b><?php echo $_SESSION['camareira_'.$camareiras]; ?></b></td>
    <td align="center"><b><?php echo $quantidade_dados_camareira; ?></b></td>
    <td align="center"><?php echo count($dados_bloqueado_camareira); ?></td>
    <td align="center"><?php echo count($dados_vago_limpo_camareira); ?></td>
    <td align="center"><?php echo count($dados_ocupado_camareira); ?></td>
    <td align="center"><?php echo count($dados_prevista_camareira); ?></td>
    <td align="center"><?php echo count($dados_vago_sujo_camareira); ?></td>
</tr>
<?php
}
?>
</table>
<input type="hidden" name="id_job" value="imprimir_plano">
</form>
<br>
<form action="acao.php" method="POST" id="formulario_plano">
<table>
<th colspan="6">Apartamentos</th>
<tr><td style="background-color: black" colspan="6"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Quarto</b></td>
    <td align="center"><b>Tipo</b></td>
    <td align="center"><b>Hospede</b></td>
    <td align="center"><b>Prevista</b></td>
    <td align="center"><b>Room Status</b></td>
    <td align="center"><b>Camareira</b></td>
</tr>
<?php
$qtd = 0;
$quantidade = 0;
foreach ($dados_roomstatus as $select) {
    $id = $select['id'];
    $id_camareira = $select['id_camareira'];
    $room_number = $select['room_number'];
    $guest_name = $select['guest_name'];
    $room_stay_status = $select['room_stay_status'];
    $room_status_1 = $select['room_status_1'];
    $room_status_2 = $select['room_status_2'];
    $room_type = $select['room_type'];
    $qtd++;
    $quantidade++;

    if($qtd % 2 == 0){
    $cor_tr = 'darkgrey';
    }else{
    $cor_tr = 'white';  
    }
    ?>
<tr style="background-color: <?php echo $cor_tr; ?>">
    <td align="center"><?php echo $room_number; ?></td>
    <td align="center"><?php echo $room_type; ?></td>
    <td><?php echo $guest_name; ?></td>
    <td><?php echo $room_stay_status; ?></td>
    <td><?php echo $room_status_1; ?> - <?php echo $room_status_2; ?></td>
    <td><?php echo $_SESSION['camareira_'.$id_camareira]; ?></td>
</tr>
<input type="hidden" name="id_<?php echo $quantidade ?>" value="<?php echo $id ?>">
<?php } ?>
</table>
<input type="hidden" name="quantidade" value="<?php echo $quantidade_dados ?>">
<input type="hidden" name="id_job" value="gerar_plano">
</form>
</fieldset>
</div>
</body>
</html>
