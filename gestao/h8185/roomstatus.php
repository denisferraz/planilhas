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

//Room Status
$query_roomstatus = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE id > 0");
$query_roomstatus->execute();

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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Gestão Recepção - Downtime</title>
</head>
<body>

<div class="container">
<!-- Chegadas -->
<fieldset>
<legend> Room Status </legend>
<?php
while($select_roomstatus = $query_roomstatus->fetch(PDO::FETCH_ASSOC)){
    $room_number = $select_roomstatus['room_number'];
    $room_status = $select_roomstatus['room_status'];
    $room_type = $select_roomstatus['room_type'];
    ?>
<div class="appointment" onclick="selecionarRadio(this)">
    <span class="name">[ <?php echo $room_number ?> - <?php echo $room_type ?> ]</span> <span class="time"><?php echo $room_status ?></span>
    <a href="acao.php?id=<?php echo base64_encode("RoomStatus;Limpo;$room_number") ?>"><button class="botao-rs-limpar">Limpar</button></a>  <a href="acao.php?id=<?php echo base64_encode("RoomStatus;Sujo;$room_number") ?>"><button class="botao-rs-sujar">Sujar</button></a>  <a href="acao.php?id=<?php echo base64_encode("RoomStatus;Bloqueado;$room_number") ?>"><button class="botao-rs-bloquear">Bloquear</button></a>
</div>

<?php
} ?>
</fieldset>
</div>
</body>
</html>
