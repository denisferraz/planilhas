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
$data_auditoria = $_SESSION['data_auditoria'];
$comentarios = $_SESSION['comentario_gerencial'];

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

//Gerencial
$linhas_gerenciais = array();

foreach ($_SESSION['dados_gerencial']  as $select) {
    $id = $select['id'];
    $item_nome = $select['item_nome'];
    $valor_dia = floatval($select['valor_dia']);
    $valor_mes = floatval($select['valor_mes']);
    $valor_ano = floatval($select['valor_ano']);

    $valor_dia = number_format($valor_dia, 2, ',', '.');
    $valor_mes = number_format($valor_mes, 2, ',', '.');
    $valor_ano = number_format($valor_ano, 2, ',', '.');

    $linha_gerencial = "$item_nome;$valor_dia;$valor_mes;$valor_ano";
    $linhas_gerenciais[] = $linha_gerencial;

}
$quartos_construidos = explode(';', $linhas_gerenciais[0]);
$quartos_bloqueados = explode(';', $linhas_gerenciais[1]);
$quartos_ocupados = explode(';', $linhas_gerenciais[3]);
$quartos_cortesia = explode(';', $linhas_gerenciais[4]);
$quartos_dayuse = explode(';', $linhas_gerenciais[5]);
$quartos_houseuse = explode(';', $linhas_gerenciais[6]);
$quartos_occ = (intval(str_replace(array(',', '.'), '', $quartos_ocupados[1]) / 100) - intval(str_replace(array(',', '.'), '', $quartos_houseuse[1]) / 100) + intval(str_replace(array(',', '.'), '', $quartos_dayuse[1]) / 100))/ intval(str_replace(array(',', '.'), '', $quartos_construidos[1]) / 100)*100;
$hospedes = explode(';', $linhas_gerenciais[19]);
$noshow = explode(';', $linhas_gerenciais[21]);
$cafe_incluso = explode(';', $linhas_gerenciais[25]);
$cafe_passante = explode(';', $linhas_gerenciais[26]);
$receita_hospedagem = explode(';', $linhas_gerenciais[22]);
$receita_aeb = explode(';', $linhas_gerenciais[28]);
$receita_eventos = explode(';', $linhas_gerenciais[29]);
$receita_diversos = explode(';', $linhas_gerenciais[30]);
$receita_total = explode(';', $linhas_gerenciais[31]);

//Sales Analyze
$dados_salesanalyze = $_SESSION['dados_salesanalyze'];

//Hospedagem
$sales_hospedagem_dia = 0;
$sales_hospedagem_mes = 0;
$sales_hospedagem_ano = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ($salesanalyze['item_nome'] === '15' || $salesanalyze['item_nome'] === '21') {
        $sales_hospedagem_dia += floatval($salesanalyze['valor_dia']);
        $sales_hospedagem_mes += floatval($salesanalyze['valor_mes']);
        $sales_hospedagem_ano += floatval($salesanalyze['valor_ano']);
    }
}
$sales_hospedagem_dia = '<b>R$</b>' . number_format($sales_hospedagem_dia, 2, ",", ".");
$sales_hospedagem_mes = '<b>R$</b>' . number_format($sales_hospedagem_mes, 2, ",", ".");
$sales_hospedagem_ano = '<b>R$</b>' . number_format($sales_hospedagem_ano, 2, ",", ".");


//AeB
$sales_aeb_dia = 0;
$sales_aeb_mes = 0;
$sales_aeb_ano = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ($salesanalyze['item_nome'] === '17') {
        $sales_aeb_dia += floatval($salesanalyze['valor_dia']);
        $sales_aeb_mes += floatval($salesanalyze['valor_mes']);
        $sales_aeb_ano += floatval($salesanalyze['valor_ano']);
    }
}
$sales_aeb_dia = '<b>R$</b>' . number_format($sales_aeb_dia, 2, ",", ".");
$sales_aeb_mes = '<b>R$</b>' . number_format($sales_aeb_mes, 2, ",", ".");
$sales_aeb_ano = '<b>R$</b>' . number_format($sales_aeb_ano, 2, ",", ".");


//Eventos
$sales_eventos_dia = 0;
$sales_eventos_mes = 0;
$sales_eventos_ano = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ($salesanalyze['item_nome'] === '51') {
        $sales_eventos_dia += floatval($salesanalyze['valor_dia']);
        $sales_eventos_mes += floatval($salesanalyze['valor_mes']);
        $sales_eventos_ano += floatval($salesanalyze['valor_ano']);
    }
}
$sales_eventos_dia = '<b>R$</b>' . number_format($sales_eventos_dia, 2, ",", ".");
$sales_eventos_mes = '<b>R$</b>' . number_format($sales_eventos_mes, 2, ",", ".");
$sales_eventos_ano = '<b>R$</b>' . number_format($sales_eventos_ano, 2, ",", ".");


//Outros
$sales_outros_dia = 0;
$sales_outros_mes = 0;
$sales_outros_ano = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ($salesanalyze['item_nome'] === '25' || $salesanalyze['item_nome'] === '3' || $salesanalyze['item_nome'] === '29') {
        $sales_outros_dia += floatval($salesanalyze['valor_dia']);
        $sales_outros_mes += floatval($salesanalyze['valor_mes']);
        $sales_outros_ano += floatval($salesanalyze['valor_ano']);
    }
}
$sales_outros_dia = '<b>R$</b>' . number_format($sales_outros_dia, 2, ",", ".");
$sales_outros_mes = '<b>R$</b>' . number_format($sales_outros_mes, 2, ",", ".");
$sales_outros_ano = '<b>R$</b>' . number_format($sales_outros_ano, 2, ",", ".");


//Total
$sales_total_dia = 0;
$sales_total_mes = 0;
$sales_total_ano = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    $sales_total_dia += floatval($salesanalyze['valor_dia']);
    $sales_total_mes += floatval($salesanalyze['valor_mes']);
    $sales_total_ano += floatval($salesanalyze['valor_ano']);
}
$sales_total_dia = '<b>R$</b>' . number_format($sales_total_dia, 2, ",", ".");
$sales_total_mes = '<b>R$</b>' . number_format($sales_total_mes, 2, ",", ".");
$sales_total_ano = '<b>R$</b>' . number_format($sales_total_ano, 2, ",", ".");


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
<fieldset>
<legend> Manager Report </legend>
<form action="acao.php" method="POST" id="formulario_auditoria">
    <br>
        <table>
<th colspan="4">Relatorio Gerencial - <?php echo date('d/m/Y', strtotime("$data_auditoria")) ?></th>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
    <td align="center"><b>Mês</b></td>
    <td align="center"><b>Ano</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Quartos Bloqueados</b></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_bloqueados[1]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_bloqueados[2]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_bloqueados[3]) / 100), 0, ',', '.'); ?></td>
</tr>
<tr>
    <td><b><?php echo $quartos_ocupados[0]; ?></b></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_ocupados[1]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_ocupados[2]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_ocupados[3]) / 100), 0, ',', '.'); ?></td>
</tr>
<tr class="darkgrey">
    <td><b><?php echo $quartos_cortesia[0]; ?></b></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_cortesia[1]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_cortesia[2]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_cortesia[3]) / 100), 0, ',', '.'); ?></td>
</tr>
<tr>
    <td><b><?php echo $quartos_houseuse[0]; ?></b></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_houseuse[1]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_houseuse[2]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_houseuse[3]) / 100), 0, ',', '.'); ?></td>
</tr>
<tr class="darkgrey">
    <td><b>Ocupação</b></td>
    <td align="center"><?php echo number_format((floatval(str_replace(array(',', '.'), '', $quartos_ocupados[1]) / 100) - intval(str_replace(array(',', '.'), '', $quartos_houseuse[1]) / 100) + intval(str_replace(array(',', '.'), '', $quartos_dayuse[1]) / 100))/ intval(str_replace(array(',', '.'), '', $quartos_construidos[1]) / 100) * 100, 2, '.'); ?>%</td>
    <td align="center"><?php echo number_format((floatval(str_replace(array(',', '.'), '', $quartos_ocupados[2]) / 100) - intval(str_replace(array(',', '.'), '', $quartos_houseuse[2]) / 100) + intval(str_replace(array(',', '.'), '', $quartos_dayuse[2]) / 100))/ intval(str_replace(array(',', '.'), '', $quartos_construidos[2]) / 100) * 100, 2, '.'); ?>%</td>
    <td align="center"><?php echo number_format((floatval(str_replace(array(',', '.'), '', $quartos_ocupados[3]) / 100) - intval(str_replace(array(',', '.'), '', $quartos_houseuse[3]) / 100) + intval(str_replace(array(',', '.'), '', $quartos_dayuse[3]) / 100))/ intval(str_replace(array(',', '.'), '', $quartos_construidos[3]) / 100) * 100, 2, '.'); ?>%</td>
</tr>
<tr>
    <td><b>Diaria Média</b></td>
    <td align="center"><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[1]) / 100) / intval(str_replace(array(',', '.'), '', $quartos_ocupados[1]) / 100), 2, ',', '.'); ?></td>
    <td align="center"><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[2]) / 100) / intval(str_replace(array(',', '.'), '', $quartos_ocupados[2]) / 100), 2, ',', '.'); ?></td>
    <td align="center"><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[3]) / 100) / intval(str_replace(array(',', '.'), '', $quartos_ocupados[3]) / 100), 2, ',', '.'); ?></td>
</tr>
<tr class="darkgrey">
    <td><b>Rev Par</b></td>
    <td align="center"><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[1]) / 100) / intval(str_replace(array(',', '.'), '', $quartos_construidos[1]) / 100), 2, ',', '.'); ?></td>
    <td align="center"><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[2]) / 100) / intval(str_replace(array(',', '.'), '', $quartos_construidos[2]) / 100), 2, ',', '.'); ?></td>
    <td align="center"><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[3]) / 100) / intval(str_replace(array(',', '.'), '', $quartos_construidos[3]) / 100), 2, ',', '.'); ?></td>
</tr>
<tr>
    <td><b>Total Hospedes</b></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $hospedes[1]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $hospedes[2]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $hospedes[3]) / 100), 0, ',', '.'); ?></td>
</tr>
<tr class="darkgrey">
    <td><b><?php echo $noshow[0]; ?></b></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $noshow[1]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $noshow[2]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $noshow[3]) / 100), 0, ',', '.'); ?></td>
</tr>
</table>
<br>
<table>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
    <td align="center"><b>Mês</b></td>
    <td align="center"><b>Ano</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Café da Manhã Incluso</b></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $cafe_incluso[1]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $cafe_incluso[2]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $cafe_incluso[3]) / 100), 2, ',', '.'); ?></td>
</tr>
<tr>
    <td><b>Café da Manhã Passante</b></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $cafe_passante[1]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $cafe_passante[2]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $cafe_passante[3]) / 100), 2, ',', '.'); ?></td>
</tr>
</table>
<br>
<table>

<tr class="orange"><td align="center" colspan="4"><b>Gerencial</b></td><td align="center" colspan="7"><b>Sales Analyze</b></td></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
    <td align="center"><b>Mês</b></td>
    <td align="center"><b>Ano</b></td>
    <td align="center"><b>Linha</b></td>
    <td colspan="2" align="center"><b>Dia</b></td>
    <td colspan="2" align="center"><b>Mês</b></td>
    <td colspan="2" align="center"><b>Ano</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b><?php echo $receita_hospedagem[0]; ?></b></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[1]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[2]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[3]) / 100), 2, ',', '.'); ?></td>

    <td><b><?php echo $receita_hospedagem[0]; ?></b></td>
    <td><?php echo $sales_hospedagem_dia; ?></td>
    <?php if($sales_hospedagem_dia == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[1]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
    <td><?php echo $sales_hospedagem_mes; ?></td>
    <?php if($sales_hospedagem_mes == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[2]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
    <td><?php echo $sales_hospedagem_ano; ?></td>
    <?php if($sales_hospedagem_ano == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[3]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
</tr>
<tr>
    <td><b><?php echo $receita_aeb[0]; ?></b></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[1]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[2]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[3]) / 100), 2, ',', '.'); ?></td>

    <td><b><?php echo $receita_aeb[0]; ?></b></td>
    <td><?php echo $sales_aeb_dia; ?></td>
    <?php if($sales_aeb_dia == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[1]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
    <td><?php echo $sales_aeb_mes; ?></td>
    <?php if($sales_aeb_mes == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[2]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
    <td><?php echo $sales_aeb_ano; ?></td>
    <?php if($sales_aeb_ano == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[3]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
</tr>
<tr class="darkgrey">
    <td><b><?php echo $receita_eventos[0]; ?></b></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_eventos[1]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_eventos[2]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_eventos[3]) / 100), 2, ',', '.'); ?></td>

    <td><b><?php echo $receita_eventos[0]; ?></b></td>
    <td><?php echo $sales_eventos_dia; ?></td>
    <?php if($sales_eventos_dia == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_eventos[1]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
    <td><?php echo $sales_eventos_mes; ?></td>
    <?php if($sales_eventos_mes == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_eventos[2]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
    <td><?php echo $sales_eventos_ano; ?></td>
    <?php if($sales_eventos_ano == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_eventos[3]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
</tr>
<tr>
    <td><b><?php echo $receita_diversos[0]; ?></b></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[1]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[2]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[3]) / 100), 2, ',', '.'); ?></td>

    <td><b><?php echo $receita_diversos[0]; ?></b></td>
    <td><?php echo $sales_outros_dia; ?></td>
    <?php if($sales_outros_dia == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[1]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
    <td><?php echo $sales_outros_mes; ?></td>
    <?php if($sales_outros_mes == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[2]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
    <td><?php echo $sales_outros_ano; ?></td>
    <?php if($sales_outros_ano == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[3]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
</tr>
<tr class="receitatotal">
    <td><b><?php echo $receita_total[0]; ?></b></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_total[1]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_total[2]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_total[3]) / 100), 2, ',', '.'); ?></td>

    <td><b><?php echo $receita_total[0]; ?></b></td>
    <td><?php echo $sales_total_dia; ?></td>
    <?php if($sales_total_dia == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_total[1]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
    <td><?php echo $sales_total_mes; ?></td>
    <?php if($sales_total_mes == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_total[2]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
    <td><?php echo $sales_total_ano; ?></td>
    <?php if($sales_total_ano == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_total[3]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
</tr>
        </table>
<br><br>
<b>Comentarios:</b><br>
<textarea name="comentarios" id="" cols="100" rows="5" required><?php echo $comentarios; ?></textarea><br>
<input type="hidden" name="id_job" value="gerencial">
<input type="submit" class="submit" value="Validar Dados">
</form>
</fieldset>
</div>
</body>
</html>
