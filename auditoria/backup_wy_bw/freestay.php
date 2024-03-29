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

if($status_auditoria == 'Concluida'){
    echo "<script>
    alert('Auditoria não foi Iniciada!')
    top.location.replace('index.php')
    </script>";
    exit();
}

$dados_presentlist = $_SESSION['dados_presentlist'];

$dados_filtrados = array_filter($dados_presentlist, function($item) {
    return $item['room_rate'] == 0;
});

// Ordenar o array por 'room_number'
usort($dados_filtrados, function($a, $b) {
    return $a['room_number'] <=> $b['room_number'];
});

$quantidade_dados = count($dados_filtrados);

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
<legend>Free Stays</legend>
<form action="acao.php" method="POST" id="formulario_auditoria">
<table>
<th colspan="6">Cortesias e Uso da Casa</th>
<tr><td style="background-color: black" colspan="6"></td></tr>
<tr><td align="center" colspan="6">Free Stays: <b><?php echo $quantidade_dados ?></b></td>
<tr><td style="background-color: black" colspan="6"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Qtd</b></td>
    <td align="center"><b>Apto.</b></td>
    <td align="center"><b>Hospede</b></td>
    <td align="center"><b>Checkin</b></td>
    <td align="center"><b>Checkout</b></td>
    <td align="center"><b>Comentario</b></td>
</tr>


<?php
$qtd = 0;
$quantidade = 0;
foreach ($dados_filtrados as $select) {
    $id = $select['id'];
    $guest_name = $select['guest_name'];
    $reserva = $select['reserva'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $room_number = $select['room_number'];
    $comentario = $select['comentario_freestay'];

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
    <td align="center"><?php echo $room_number; ?></td>
    <td><?php echo $guest_name; ?></td>
    <td align="center"><?php echo date('d/m/Y', strtotime("$checkin")); ?></td>
    <td align="center"><?php echo date('d/m/Y', strtotime("$checkout")); ?></td>
    <td><input class="input-field" type="text" name="comentarios_<?php echo $quantidade ?>" value="<?php echo $comentario ?>" required ></td>
</tr>
<input type="hidden" name="id_<?php echo $quantidade ?>" value="<?php echo $id ?>">
<?php } ?>
</table>
<br><br>
<input type="hidden" name="quantidade" value="<?php echo $quantidade_dados ?>">
<input type="hidden" name="id_job" value="freestay">
<input type="submit" class="submit" value="Validar Dados">
</form>
</fieldset>
</div>
</body>
</html>
