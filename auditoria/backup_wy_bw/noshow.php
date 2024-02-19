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

$status_auditoria = $_SESSION['status_auditoria'];

if($status_auditoria == 'Em Andamento Pre'){
    echo "<script>
    alert('Relatorios Pos Auditoria não Importados!')
    window.location.replace('controlegarantias.php')
    </script>";
    exit();
}else if($status_auditoria == 'Concluida'){
    echo "<script>
    alert('Auditoria não foi Iniciada!')
    top.location.replace('index.php')
    </script>";
    exit();
}

$dados_noshow = $_SESSION['dados_noshow'];

$quantidade_dados = count($dados_noshow);

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
    <title>Auditoria Digital</title>
</head>
<body>

<div class="container">
<!-- Diarias -->
<fieldset>
<legend>No Show</legend>
<form action="acao.php" method="POST" id="formulario_auditoria">
<table>
<th colspan="7">No Show</th>
<tr><td style="background-color: black" colspan="7"></td></tr>
<tr><td align="center" colspan="7">No Shows: <b><?php echo $quantidade_dados ?></b></td>
<tr><td style="background-color: black" colspan="7"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Qtd</b></td>
    <td align="center"><b>Reserva</b></td>
    <td align="center"><b>Hospede</b></td>
    <td align="center"><b>Checkin</b></td>
    <td align="center"><b>Checkout</b></td>
    <td align="center"><b>Diária</b></td>
    <td align="center"><b>Cobrado</b></td>
</tr>


<?php
$qtd = 0;
$quantidade = 0;
foreach ($dados_noshow as $select) {
    $id = $select['id'];
    $guest_name = $select['guest_name'];
    $reserva = $select['reserva'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $room_rate = $select['room_rate'];
    $cobrado = $select['cobrado'];

    $qtd++;
    $quantidade++;

    if($qtd % 2 == 0){
    $cor_tr = 'darkgrey';
    }else{
    $cor_tr = 'white';  
    }
    ?>
<tr style="background-color: <?php echo $cor_tr; ?>">
    <td align="center"><b><?php echo $qtd; ?></b></td>
    <td align="center"><?php echo $reserva; ?></td>
    <td><?php echo $guest_name; ?></td>
    <td align="center"><?php echo date('d/m/Y', strtotime("$checkin")); ?></td>
    <td align="center"><?php echo date('d/m/Y', strtotime("$checkout")); ?></td>
    <td>R$<?php echo number_format($room_rate, 2, ',', '.'); ?></td>
    <td>R$<?php echo number_format($cobrado, 2, ',', '.'); ?></td>
</tr>
<?php } ?>
</table>
<br><br>
<input type="hidden" name="id_job" value="noshow">
<input type="submit" class="submit" value="Validar Dados">
</form>
</fieldset>
</div>
</body>
</html>
