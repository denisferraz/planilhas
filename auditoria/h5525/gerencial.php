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
$quartos_bloqueados = explode(';', $linhas_gerenciais[11]);
$quartos_bloqueados_2 = explode(';', $linhas_gerenciais[12]);
$quartos_ocupados = explode(';', $linhas_gerenciais[1]);
$quartos_cortesia = explode(';', $linhas_gerenciais[5]);
$quartos_dayuse = explode(';', $linhas_gerenciais[10]);
$quartos_houseuse = explode(';', $linhas_gerenciais[6]);
$quartos_occ = explode(';', $linhas_gerenciais[29]);
$hospedes = explode(';', $linhas_gerenciais[15]);
$noshow = explode(';', $linhas_gerenciais[55]);
//$cafe_incluso = explode(';', $linhas_gerenciais[]);
//$cafe_passante = explode(';', $linhas_gerenciais[]);
$receita_hospedagem = explode(';', $linhas_gerenciais[76]);
$receita_aeb = explode(';', $linhas_gerenciais[77]);
//$receita_eventos = explode(';', $linhas_gerenciais[]);
$receita_diversos = explode(';', $linhas_gerenciais[78]);
$receita_total = explode(';', $linhas_gerenciais[79]);

//Sales Analyze
$dados_salesanalyze = $_SESSION['dados_salesanalyze'];

//Hospedagem
$sales_hospedagem_dia = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ( ($salesanalyze['item_nome'] >= 1000 && $salesanalyze['item_nome'] <= 1018) || ($salesanalyze['item_nome'] >= 6000 && $salesanalyze['item_nome'] <= 6018) || ($salesanalyze['item_nome'] >= 7500 && $salesanalyze['item_nome'] <= 7550)     ) {
        $sales_hospedagem_dia += floatval($salesanalyze['valor_dia']);
    }
}
$sales_hospedagem_dia = '<b>R$</b>' . number_format($sales_hospedagem_dia, 2, ",", ".");


//AeB
$sales_aeb_dia = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ( ($salesanalyze['item_nome'] >= 2042 && $salesanalyze['item_nome'] <= 2600) || ($salesanalyze['item_nome'] >= 6142 && $salesanalyze['item_nome'] <= 6600) ) {
        $sales_aeb_dia += floatval($salesanalyze['valor_dia']);
    }
}
$sales_aeb_dia = '<b>R$</b>' . number_format($sales_aeb_dia, 2, ",", ".");

//Outros
$sales_outros_dia = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ($salesanalyze['item_nome'] >= 5000 && $salesanalyze['item_nome'] <= 5999) {
        $sales_outros_dia += floatval($salesanalyze['valor_dia']);
    }
}
$sales_outros_dia = '<b>R$</b>' . number_format($sales_outros_dia, 2, ",", ".");


//Total
$sales_total_dia = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ( $salesanalyze['item_nome'] >= 1000 && $salesanalyze['item_nome'] <= 8999) {
        $sales_total_dia += floatval($salesanalyze['valor_dia']);
    }
}
$sales_total_dia = '<b>R$</b>' . number_format($sales_total_dia, 2, ",", ".");

//Pagamentos
//Dinheiro
$sales_dinheiro_dia = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ( $salesanalyze['item_nome'] == 9000) {
        $sales_dinheiro_dia += floatval($salesanalyze['valor_pgto']);
    }
}
$sales_dinheiro_dia = '<b>R$</b>' . number_format($sales_dinheiro_dia * (-1), 2, ",", ".");

//Cartão
$sales_cartao_dia = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ( $salesanalyze['item_nome'] >= 9080 && $salesanalyze['item_nome'] <= 9199) {
        $sales_cartao_dia += floatval($salesanalyze['valor_pgto']);
    }
}
$sales_cartao_dia = '<b>R$</b>' . number_format($sales_cartao_dia * (-1), 2, ",", ".");

//Outros
$sales_outros_pgto_dia = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ( $salesanalyze['item_nome'] == 9020 || ($salesanalyze['item_nome'] >= 9200 && $salesanalyze['item_nome'] <= 9900)) {
        $sales_outros_pgto_dia += floatval($salesanalyze['valor_pgto']);
    }
}
$sales_outros_pgto_dia = '<b>R$</b>' . number_format($sales_outros_pgto_dia * (-1), 2, ",", ".");

//Total
$sales_total_pgto_dia = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ( $salesanalyze['item_nome'] >= 9000 && $salesanalyze['item_nome'] <= 9900) {
        $sales_total_pgto_dia += floatval($salesanalyze['valor_pgto']);
    }
}
$sales_total_pgto_dia = '<b>R$</b>' . number_format($sales_total_pgto_dia * (-1), 2, ",", ".");

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
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_bloqueados[1]) / 100), 0, ',', '.') + number_format(floatval(str_replace(array(',', '.'), '', $quartos_bloqueados_2[1]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_bloqueados[2]) / 100), 0, ',', '.') + number_format(floatval(str_replace(array(',', '.'), '', $quartos_bloqueados_2[2]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_bloqueados[3]) / 100), 0, ',', '.') + number_format(floatval(str_replace(array(',', '.'), '', $quartos_bloqueados_2[3]) / 100), 0, ',', '.'); ?></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_ocupados[1]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_ocupados[2]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_ocupados[3]) / 100), 0, ',', '.'); ?></td>
</tr>
<tr class="darkgrey">
    <td><b>Quartos Cortesia</b></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_cortesia[1]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_cortesia[2]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $quartos_cortesia[3]) / 100), 0, ',', '.'); ?></td>
</tr>
<tr>
    <td><b>Quartos House Use</b></td>
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
    <td><b>No Shows</b></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $noshow[1]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $noshow[2]) / 100), 0, ',', '.'); ?></td>
    <td align="center"><?php echo number_format(floatval(str_replace(array(',', '.'), '', $noshow[3]) / 100), 0, ',', '.'); ?></td>
</tr>
</table>
<br>
<table>

<tr class="orange"><td align="center" colspan="4"><b>Gerencial</b></td><td align="center" colspan="9"><b>Trial Balance</b></td></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
    <td align="center"><b>Mês</b></td>
    <td align="center"><b>Ano</b></td>
    <td align="center"><b>Linha</b></td>
    <td colspan="2" align="center"><b>Dia</b></td>
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
</tr>
<tr></tr>
<tr>
    <td><b>Receita Hospedagem</b></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[1]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[2]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[3]) / 100), 2, ',', '.'); ?></td>

    <td><b>Receita Hospedagem</b></td>
    <td><?php echo $sales_hospedagem_dia; ?></td>
    <?php if($sales_hospedagem_dia == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[1]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
    <td><b>Pagamento Dinheiro</b></td>
    <td><?php echo $sales_dinheiro_dia; ?></td>
</tr>
<tr class="darkgrey">
    <td><b>Receita A&B</b></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[1]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[2]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[3]) / 100), 2, ',', '.'); ?></td>

    <td><b>Receita A&B</b></td>
    <td><?php echo $sales_aeb_dia; ?></td>
    <?php if($sales_aeb_dia == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[1]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
    <td><b>Pagamento Cartão</b></td>
    <td><?php echo $sales_cartao_dia; ?></td>
</tr>
<tr>
    <td><b>Receita Diversos</b></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[1]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[2]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[3]) / 100), 2, ',', '.'); ?></td>

    <td><b>Receita Diversos</b></td>
    <td><?php echo $sales_outros_dia; ?></td>
    <?php if($sales_outros_dia == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[1]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
    <td><b>Pagamento Outros</b></td>
    <td><?php echo $sales_outros_pgto_dia; ?></td>
</tr>
<tr class="receitatotal">
    <td><b>Receita Total</b></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_total[1]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_total[2]) / 100), 2, ',', '.'); ?></td>
    <td><b>R$</b><?php echo number_format(floatval(str_replace(array(',', '.'), '', $receita_total[3]) / 100), 2, ',', '.'); ?></td>

    <td><b>Receita Total</b></td>
    <td><?php echo $sales_total_dia; ?></td>
    <?php if($sales_total_dia == '<b>R$</b>'.number_format(floatval(str_replace(array(',', '.'), '', $receita_total[1]) / 100), 2, ',', '.')){
        $color_sales = 'darkgreen'; }else{  $color_sales = 'darkred'; } ?>
    <td style="background-color: <?php echo $color_sales; ?>" align="center"><b></b></td>
    <td><b>Pagamento Total</b></td>
    <td><?php echo $sales_total_pgto_dia; ?></td>
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
