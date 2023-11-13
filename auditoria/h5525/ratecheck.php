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

if($status_auditoria == 'Concluida'){
    echo "<script>
    alert('Auditoria não foi Iniciada!')
    top.location.replace('index.php')
    </script>";
    exit();
}

$quantidade_dados = count($_SESSION['dados_ratecheck']);

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
<legend>Rate Check</legend>
<form action="acao.php" method="POST" id="formulario_auditoria">
<table>
<th colspan="7">Conferência de Diárias</th>
<tr><td style="background-color: black" colspan="7"></td></tr>
<tr><td align="center" colspan="7">Apartamentos Ocupados: <b><?php echo $quantidade_dados ?></b></td>
<tr><td style="background-color: black" colspan="7"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Apto.</b></td>
    <td align="center"><b>Hospede</b></td>
    <td align="center"><b>Checkin</b></td>
    <td align="center"><b>Checkout</b></td>
    <td align="center"><b>Rate Code</b></td>
    <td align="center"><b>Diária</b></td>
    <td align="center"><b>Comentario</b></td>
</tr>


<?php
$qtd = 0;
$quantidade = 0;

$dados_ratecheck = $_SESSION['dados_ratecheck'];
$data_auditoria = $_SESSION['data_auditoria'];


// Ordenar o array por 'room_number' em ordem ascendente ou descendente
usort($dados_ratecheck, function($a, $b) use ($ordem_query) {
        return $a['room_number'] <=> $b['room_number'];
});

foreach ($dados_ratecheck as $select) {
    $id = $select['id'];
    $room_number = $select['room_number'];
    $guest_name = $select['guest_name'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $ratecode = $select['ratecode'];
    $room_rate = $select['room_rate'];
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
    <td><?php echo $ratecode; ?></td>
    <td>R$<?php echo number_format($room_rate ,2,",","."); ?></td>
    <td>
    <input class="input-field" type="text" name="comentarios_<?php echo $quantidade ?>" value="<?php echo $comentario ?>" <?php echo ($data_auditoria == $checkin) ? 'required' : ''; ?>>
    </td>
</tr>
<input type="hidden" name="id_<?php echo $quantidade ?>" value="<?php echo $id ?>">
<?php } ?>
</table>
<br><br>
<input type="hidden" name="quantidade" value="<?php echo $quantidade_dados ?>">
<input type="hidden" name="id_job" value="ratecheck">
<input type="submit" class="submit" value="Validar Dados">
</form>
</fieldset>
</div>
</body>
</html>
