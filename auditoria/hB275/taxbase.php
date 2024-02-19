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
$comentarios = $_SESSION['comentario_taxbase'];

if($status_auditoria == 'Em Andamento Pre'){
    echo "<script>
    alert('Relatorios Pos Auditoria não Importados!')
    window.location.replace('ratecheck.php')
    </script>";
    exit();
}else if($status_auditoria == 'Concluida'){
    echo "<script>
    alert('Auditoria não foi Iniciada!')
    top.location.replace('index.php')
    </script>";
    exit();
}

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

//Todas os Apartamentos
$quantidade_dados = count($_SESSION['dados_taxbase']);

$sum_valor_base_iss = 0;
$sum_valor_iss = 0;
$sum_valor_iss_retido = 0;
$sum_valor_iss_esperado = 0;
$sum_diferenca = 0;

foreach ($_SESSION['dados_taxbase'] as $taxbase) {
    $sum_valor_base_iss += floatval($taxbase['valor_base_iss']);
    $sum_valor_iss += floatval($taxbase['valor_iss']);
    $sum_valor_iss_retido += floatval($taxbase['valor_iss_retido']);
}

$sum_valor_iss_esperado = $sum_valor_base_iss * 0.05;
$sum_diferenca = $sum_valor_iss_esperado - $sum_valor_iss;

$sum_valor_base_iss = '<b>R$</b>' . number_format($sum_valor_base_iss, 2, ",", ".");
$sum_valor_iss = '<b>R$</b>' . number_format($sum_valor_iss, 2, ",", ".");
$sum_valor_iss_retido = '<b>R$</b>' . number_format($sum_valor_iss_retido, 2, ",", ".");
$sum_valor_iss_esperado = '<b>R$</b>' . number_format($sum_valor_iss_esperado, 2, ",", ".");
$sum_diferenca = '<b>R$</b>' . number_format($sum_diferenca, 2, ",", ".");

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
<legend>Tax Base</legend>
<form action="acao.php" method="POST" id="formulario_auditoria">
<table>
<th colspan="12">Tax Base Calculaiton</th>
<tr><td style="background-color: black" colspan="12"></td></tr>
<tr><td align="center" colspan="12">RPS Emitadas: <b><?php echo $quantidade_dados; ?></b> | Valor Total Emitido: <?php echo $sum_valor_base_iss; ?> | Valor Total Iss: <?php echo $sum_valor_iss; ?> | Valor Total Iss Esperado: <?php echo $sum_valor_iss_esperado; ?> | Diferença: <b><?php echo $sum_diferenca; ?></b></td>
<tr><td style="background-color: black" colspan="12"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Qtd</b></td>
    <td align="center"><b>RPS</b></td>
    <td align="center"><b>Situação</b></td>
    <td align="center"><b>Data</b></td>
    <td align="center"><b>Hospede</b></td>
    <td align="center"><b>Empresa</b></td>
    <td align="center"><b>Quarto</b></td>
    <td align="center"><b>Total</b></td>
    <td align="center"><b>Base Iss</b></td>
    <td align="center"><b>Iss</b></td>
    <td align="center" colspan="2"><b>Iss Esperado</b></td>

</tr>


<?php
$qtd = 0;
foreach ($_SESSION['dados_taxbase'] as $select) {
    $rps_num = $select['rps_num'];
    $situacao = $select['situacao'];
    $data_emissao = $select['data_emissao'];
    $guest_name = $select['guest_name'];
    $guest_empresa = $select['guest_empresa'];
    $room_number = $select['room_number'];
    $valor_nf = floatval($select['valor_nf']);
    $valor_base_iss = round(floatval($select['valor_base_iss']), 2);
    $valor_iss = round(floatval($select['valor_iss']), 2);

    $valor_iss_esperado = round($valor_base_iss * 0.05, 2);
    $diferenca_iss = round($valor_iss_esperado - $valor_iss, 2);

    $qtd++;

    if($qtd % 2 == 0){
    $cor_tr = 'darkgrey';
    }else{
    $cor_tr = 'white';  
    }

    if($valor_iss_retido == 0){
        if($qtd % 2 == 0){
            $cor_td = 'darkgrey';
            }else{
            $cor_td = 'white';  
            }
    }else{
    $cor_td = 'magenta'; 
    }

    if($diferenca_iss != 0){
    $cor_td_2 = 'rgb(255, 50, 50)';
    }else{
    $cor_td_2 = 'lightgreen';
    }

    ?>
<tr style="background-color: <?php echo $cor_tr; ?>">
    <td align="center"><b><?php echo $qtd; ?></b></td>
    <td align="center"><?php echo $rps_num; ?></td>
    <td><?php echo $situacao; ?></td>
    <td align="center"><?php echo date('d/m/Y', strtotime("$data_emissao")); ?></td>
    <td><?php echo $guest_name; ?></td>
    <td><?php echo $guest_empresa; ?></td>
    <td><?php echo $room_number; ?></td>
    <td>R$<?php echo number_format($valor_nf ,2,",","."); ?></td>
    <td>R$<?php echo number_format($valor_base_iss ,2,",","."); ?></td>
    <td>R$<?php echo number_format($valor_iss ,2,",","."); ?></td>
    <td>R$<?php echo number_format($valor_iss_esperado ,2,",","."); ?></td>
    <td style="background-color: <?php echo $cor_td_2; ?>"></td>
</tr>
<?php } ?>
</table>
<br><br>
<b>Comentarios:</b><br>
<textarea name="comentarios" id="" cols="100" rows="5" required><?php echo $comentarios; ?></textarea><br>
<input type="hidden" name="id_job" value="taxbase">
<input type="submit" class="submit" value="Validar Dados">
</form>
</fieldset>
</div>
</body>
</html>
