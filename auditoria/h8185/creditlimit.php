<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

$dir = substr(__DIR__, -5);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    top.location.replace('../../index.html')
    </script>";
    exit();
}

$status_auditoria = $_SESSION['status_auditoria'];
$limite_credito = $_SESSION['limite_credito'];

if($status_auditoria == 'Concluida'){
    echo "<script>
    alert('Auditoria não foi Iniciada!')
    top.location.replace('index.php')
    </script>";
    exit();
}

$limite_creditos = number_format($limite_credito, 2, ',', '.');
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
    <title>Auditoria Digital</title>
</head>
<body>

<div class="container">
<!-- Diarias -->
<fieldset>
<legend>Saldo Elevado</legend>
<form action="acao.php" method="POST">
<table>
<th colspan="6">Saldo Elevado</th>
<tr><td style="background-color: black" colspan="6"></td></tr>
<tr><td align="center" colspan="6">Limite de Crédito: <b>R$<?php echo $limite_creditos ?></b></td>
<tr><td style="background-color: black" colspan="6"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Apto.</b></td>
    <td align="center"><b>Hospede</b></td>
    <td align="center"><b>Checkin</b></td>
    <td align="center"><b>Checkout</b></td>
    <td align="center"><b>Balance</b></td>
    <td align="center"><b>Comentario</b></td>
</tr>


<?php
$qtd = 0;
$quantidade = 0;

$dados_creditlimit = array_filter($_SESSION['dados_creditlimit'], function($select) use ($limite_credito) {
    return $select['balance'] > $limite_credito && $select['room_number'] > 0;
});

$quantidade_dados = count($dados_creditlimit);

foreach ($dados_creditlimit as $select) {
    $id = $select['id'];
    $room_number = $select['room_number'];
    $guest_name = $select['guest_name'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $balance = $select['balance'];
    $comentario = $select['comentario'];

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
    <td><?php echo $guest_name; ?></td>
    <td align="center"><?php echo date('d/m/Y', strtotime("$checkin")); ?></td>
    <td align="center"><?php echo date('d/m/Y', strtotime("$checkout")); ?></td>
    <td>R$<?php echo number_format($balance ,2,",","."); ?></td>
    <td>
        <input class="input-field" type="text" name="comentarios_<?php echo $quantidade ?>" value="<?php echo $comentario ?>" required>
    </td>
</tr>
<input type="hidden" name="id_<?php echo $quantidade ?>" value="<?php echo $id ?>">
<?php } ?>
</table>
<br><br>
<input type="hidden" name="quantidade" value="<?php echo $quantidade_dados ?>">
<input type="hidden" name="id_job" value="creditlimit">
<input type="submit" class="submit" value="Salvar Dados">
</form>
</fieldset>
</div>
</body>
</html>
