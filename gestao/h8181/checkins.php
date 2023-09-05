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

//Reservas Checkins do Dia
$query_chegadas = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_arrivals WHERE id > 0 AND alteracao = 'Checkedin' ORDER BY room_number ASC");
$query_chegadas->execute();
$chegadas_qtd = $query_chegadas->rowCount();

?>

<!DOCTYPE html>
<html lang="pt-br">
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
<legend> (<?php echo $chegadas_qtd ?>) Checkins Realizados</legend>
<?php
while($select_chegadas = $query_chegadas->fetch(PDO::FETCH_ASSOC)){
    $id = $select_chegadas['id'];
    $guest_name = $select_chegadas['guest_name'];
    $noites = $select_chegadas['noites'];
    $adultos = $select_chegadas['adultos'];
    $criancas = $select_chegadas['criancas'];
    $room_type = $select_chegadas['room_type'];
    $room_ratecode = $select_chegadas['room_ratecode'];
    $room_msg = $select_chegadas['room_msg'];
    $room_number = $select_chegadas['room_number'];
    echo "$room_msg";
    ?>
<div class="appointment">
    <span class="name">[ <?php  echo $room_number?> - <?php echo $room_type ?> ]</span> <span class="time"><?php echo $guest_name ?> - <?php echo date('d/m/Y') ?> a <?php echo date('d/m/Y', strtotime("+$noites days")); ?> | <span class="name">Adulos [ <?php echo $adultos ?> ] Crianças [ <?php echo $criancas ?> ] | Ratecode [ <?php echo $room_ratecode ?> ]</span>
    <a href="acao.php?id=<?php echo base64_encode("Chegadas;CancelarIn;$id;$room_number") ?>"><button class="botao-rs-sujar">Cancelar Checkin</button></a>
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
