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
$data_auditoria = $_SESSION['data_auditoria'];
$comentarios = $_SESSION['comentario_gerencial'];

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

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

//Gerencial - Receitas
$linhas_gerenciais = explode(';', $_SESSION['dados_gerencial']);

$receita_hospedagem_dia = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[0])) /100;
$receita_hospedagem_mes = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[1])) /100;
$receita_aeb_dia = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[2])) /100;
$receita_aeb_mes = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[3])) /100;
$receita_lavanderia_dia = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[4])) /100;
$receita_lavanderia_mes = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[5])) /100;
$receita_taxaiss_dia = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[6])) /100;
$receita_taxaiss_mes = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[7])) /100;
$receita_total_dia = $receita_hospedagem_dia + $receita_lavanderia_dia + $receita_taxaiss_dia;
$receita_total_mes = $receita_hospedagem_mes + $receita_lavanderia_mes + $receita_taxaiss_mes;

//Gerencial - Ocupação
$quartos_construidos_dia = $_SESSION['quartos_total_dia'];
$quartos_construidos_mes = $_SESSION['quartos_total_mes'];

$quartos_bloqueados_dia = $_SESSION['quartos_bloqueados_dia'];
$quartos_bloqueados_mes = $_SESSION['quartos_bloqueados_mes'];
$quartos_ocupados_dia = $_SESSION['quartos_ocupados_dia'];
$quartos_ocupados_mes = $_SESSION['quartos_ocupados_mes'];
$quartos_cortesia_dia = $_SESSION['quartos_cortesia_dia'];
$quartos_cortesia_mes = $_SESSION['quartos_cortesia_mes'];
$quartos_houseuse_dia = $_SESSION['quartos_houseuse_dia'];
$quartos_houseuse_mes = $_SESSION['quartos_houseuse_mes'];
$adultos_dia = $_SESSION['adultos_dia'];
$adultos_mes = $_SESSION['adultos_mes'];
$criancas_dia = $_SESSION['criancas_dia'];
$criancas_mes = $_SESSION['criancas_mes'];
$noshow_dia = $_SESSION['noshow_dia'];
$noshow_mes = $_SESSION['noshow_mes'];

//Forecast
$forecast_1 = $_SESSION['forecast_1'];
$forecast_2 = $_SESSION['forecast_2'];
$forecast_3 = $_SESSION['forecast_3'];
$forecast_pax_1 = $_SESSION['forecast_pax_1'];
$forecast_pax_2 = $_SESSION['forecast_pax_2'];
$forecast_pax_3 = $_SESSION['forecast_pax_3'];
$forecast_dm_1 = $_SESSION['forecast_dm_1'];
$forecast_dm_2 = $_SESSION['forecast_dm_2'];
$forecast_dm_3 = $_SESSION['forecast_dm_3'];

if($forecast_1 == ''){
    $forecast_occ_1 = 0;
}else{
    $forecast_occ_1 = floatval($forecast_1) / floatval($quartos_construidos_dia) * 100;
}
if($forecast_2 == ''){
    $forecast_occ_2 = 0;
}else{
    $forecast_occ_2 = floatval($forecast_2) / floatval($quartos_construidos_dia) * 100;
}
if($forecast_3 == ''){
    $forecast_occ_3 = 0;
}else{
    $forecast_occ_3 = floatval($forecast_3) / floatval($quartos_construidos_dia) * 100;
}

//Ocupação
if($quartos_ocupados_dia == '' || $quartos_ocupados_dia == 0){
    $quartos_occ_dia = 0;
    $dm_dia = 0;
}else{
    $quartos_occ_dia = floatval($quartos_ocupados_dia) / floatval($quartos_construidos_dia) * 100;
    $dm_dia = floatval($receita_hospedagem_dia) / floatval($quartos_ocupados_dia);
}

if($quartos_ocupados_mes == '' || $quartos_ocupados_mes == 0){
    $quartos_occ_mes = 0;
    $dm_mes = 0;
}else{
    $quartos_occ_mes = floatval($quartos_ocupados_mes) / floatval($quartos_construidos_mes) * 100;
    $dm_mes = floatval($receita_hospedagem_mes) / floatval($quartos_ocupados_mes);
}

$revpar_dia = floatval($receita_hospedagem_dia) / floatval($quartos_construidos_dia);
$revpar_mes = floatval($receita_hospedagem_mes) / floatval($quartos_construidos_mes);


//Orçado
$ano_atual = date('Y', strtotime("$data_auditoria"));
$mes_atual = strtolower(date('M', strtotime("$data_auditoria")));
$dia_atual = date('d', strtotime("$data_auditoria"));
$dia_ultimo = date('d', strtotime(date('Y-m-t', strtotime($data_auditoria))));

$meses = [
    'jan', 'feb', 'mar', 'apr', 'mai', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dec'
];

$poa_array = [];

    $query = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_poa WHERE data_poa = :data_poa");
    $query->execute(array('data_poa' => $ano_atual));
    $query_qtd = $query->rowCount();

    if($query_qtd > 0){


//POA
$query_poa = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_poa WHERE data_poa = :ano");
$query_poa->bindParam(':ano', $ano_atual);
$query_poa->execute();

while ($select = $query_poa->fetch(PDO::FETCH_ASSOC)) {
    $dados_poa = $select['dados_poa'];
}

// Chave de criptografia
$chave = $_SESSION['hotel'] . $chave;
$dados = base64_decode($dados_poa);
$dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);
$dados_array = explode(';', $dados_decifrados);

foreach ($meses as $mes) {
    ${"total_uhs_$mes"} = 0;
    ${"uhs_ocupadas_$mes"} = 0;
    ${"dm_$mes"} = 0.00;
    ${"total_hospedagem_$mes"} = 0.00;

    $mesIndex = array_search($mes, $meses);
    $startIndex = $mesIndex * 4; // Cada mês tem 4 valores

        $poa_array[$mes][] = [
            'total_uhs' => $dados_array[$startIndex],
            'uhs_ocupadas' => $dados_array[$startIndex + 1],
            'dm' => $dados_array[$startIndex + 2],
            'receita' => $dados_array[$startIndex + 3]
        ];

        // Atualiza as variáveis mensais
        ${"total_uhs_$mes"} += $poa_array[$mes][0]['total_uhs'];
        ${"uhs_ocupadas_$mes"} += $poa_array[$mes][0]['uhs_ocupadas'];
        ${"dm_$mes"} += $poa_array[$mes][0]['dm'];
        ${"total_hospedagem_$mes"} += $poa_array[$mes][0]['receita'];

}

    }else{

        foreach ($meses as $mes) {
            ${"total_uhs_$mes"} = 0;
            ${"uhs_ocupadas_$mes"} = 0;
            ${"dm_$mes"} = 0.00;
            ${"total_hospedagem_$mes"} = 0.00;
        }

    }

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
<legend> Manager Report (RDS)</legend>
<form action="acao.php" method="POST" id="formulario_auditoria">
    <br>
        <table>
<tr><td style="background-color: black" colspan="4"></td></tr>
<th colspan="4">Relatorio Gerencial - <?php echo date('d/m/Y', strtotime("$data_auditoria")) ?></th>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
    <td align="center"><b>Acumulado</b></td>
    <td align="center"><b>Orçado</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Quartos Bloqueados</b></td>
    <td><input class="input-field-auditoria replace-comma" type="number" name="quartos_bloqueados_dia" value="<?php echo $quartos_bloqueados_dia ?>" required></td>
    <td><input class="input-field-auditoria replace-comma" type="number" name="quartos_bloqueados_mes" value="<?php echo $quartos_bloqueados_mes ?>" required></td>
    <td align="center"><b>-</b></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td><input class="input-field-auditoria replace-comma" type="number" name="quartos_ocupados_dia" value="<?php echo $quartos_ocupados_dia ?>" required></td>
    <td><input class="input-field-auditoria replace-comma" type="number" name="quartos_ocupados_mes" value="<?php echo $quartos_ocupados_mes ?>" required></td>
    <td align="center"><b><?php echo number_format(${"uhs_ocupadas_$mes_atual"} * $dia_atual, 0, ',', '.'); ?></b></td>
</tr>
<tr class="darkgrey">
    <td><b>Quartos Cortesia</b></td>
    <td><input class="input-field-auditoria replace-comma" type="number" name="quartos_cortesia_dia" value="<?php echo $quartos_cortesia_dia ?>" required></td>
    <td><input class="input-field-auditoria replace-comma" type="number" name="quartos_cortesia_mes" value="<?php echo $quartos_cortesia_mes ?>" required></td>
    <td align="center"><b>-</b></td>
</tr>
<tr>
    <td><b>Quartos Uso da Casa</b></td>
    <td><input class="input-field-auditoria replace-comma" type="number" name="quartos_houseuse_dia" value="<?php echo $quartos_houseuse_dia ?>" required></td>
    <td><input class="input-field-auditoria replace-comma" type="number" name="quartos_houseuse_mes" value="<?php echo $quartos_houseuse_mes ?>" required></td>
    <td align="center"><b>-</b></td>
</tr>
<tr class="darkgrey">
    <td><b>Ocupação</b></td>
    <td align="center"><?php echo number_format($quartos_occ_dia, 2, ',', '.'); ?>%</td>
    <td align="center"><?php echo number_format($quartos_occ_mes, 2, ',', '.'); ?>%</td>
    <td align="center"><b><?php echo number_format(${"uhs_ocupadas_$mes_atual"} / ${"total_uhs_$mes_atual"} * 100, 2, ',', '.'); ?>%</b></td>
</tr>
<tr>
    <td><b>Diaria Média</b></td>
    <td align="center">R$<?php echo number_format($dm_dia, 2, ',', '.'); ?></td>
    <td align="center">R$<?php echo number_format($dm_mes, 2, ',', '.'); ?></td>
    <td align="center"><b>R$<?php echo number_format(${"dm_$mes_atual"}, 2, ',', '.'); ?></b></td>
</tr>
<tr class="darkgrey">
    <td><b>Rev Par</b></td>
    <td align="center">R$<?php echo number_format($revpar_dia, 2, ',', '.'); ?></td>
    <td align="center">R$<?php echo number_format($revpar_mes, 2, ',', '.'); ?></td>
    <td align="center"><b>R$<?php echo number_format(${"dm_$mes_atual"} * ${"uhs_ocupadas_$mes_atual"} / ${"total_uhs_$mes_atual"}, 2, ',', '.'); ?></b></td>
</tr>
<tr>
    <td><b>Adultos</b></td>
    <td><input class="input-field-auditoria replace-comma" type="number" name="adultos_dia" value="<?php echo $adultos_dia ?>" required></td>
    <td><input class="input-field-auditoria replace-comma" type="number" name="adultos_mes" value="<?php echo $adultos_mes ?>" required></td>
    <td align="center"><b>-</b></td>
</tr>
<tr class="darkgrey">
    <td><b>Crianças</b></td>
    <td><input class="input-field-auditoria replace-comma" type="number" name="criancas_dia" value="<?php echo $criancas_dia ?>" required></td>
    <td><input class="input-field-auditoria replace-comma" type="number" name="criancas_mes" value="<?php echo $criancas_mes ?>" required></td>
    <td align="center"><b>-</b></td>
</tr>
<tr>
    <td><b>No Shows/Cobrados</b></td>
    <td align="center"><?php echo $noshow_dia ?></td>
    <td align="center"><?php echo $noshow_mes ?></td>
    <td align="center"><b>-</b></td>
</tr>
<tr><td style="background-color: black" colspan="4"></td></tr>
<tr class="orange"><td align="center" colspan="4"><b>Forecast</b></td></tr>
<tr class="header">
    <td align="center"><b><?php echo date('d/m/Y', strtotime("$data_auditoria +1 day")) ?></b></td>
    <td align="center"><b><?php echo date('d/m/Y', strtotime("$data_auditoria +2 day")) ?></b></td>
    <td align="center"><b><?php echo date('d/m/Y', strtotime("$data_auditoria +3 day")) ?></b></td>
    <td align="center"><b>Orçado</b></td>
</tr>
<tr></tr>
<tr>
    <td align="center">Uhs<input class="input-field-auditoria replace-comma" type="number" name="forecast_1" value="<?php echo $forecast_1 ?>" required></td>
    <td align="center">Uhs<input class="input-field-auditoria replace-comma" type="number" name="forecast_2" value="<?php echo $forecast_2 ?>" required></td>
    <td align="center">Uhs<input class="input-field-auditoria replace-comma" type="number" name="forecast_3" value="<?php echo $forecast_3 ?>" required></td>
    <td align="center"><b><?php echo number_format(${"uhs_ocupadas_$mes_atual"}, 0, ',', '.'); ?></b></td>
</tr>
<tr class="darkgrey">
    <td align="center"><?php echo number_format($forecast_occ_1, 2, ',', '.'); ?>%</td>
    <td align="center"><?php echo number_format($forecast_occ_2, 2, ',', '.'); ?>%</td>
    <td align="center"><?php echo number_format($forecast_occ_3, 2, ',', '.'); ?>%</td>
    <td align="center"><b><?php echo number_format(${"uhs_ocupadas_$mes_atual"} / ${"total_uhs_$mes_atual"} * 100, 2, ',', '.'); ?>%</b></td>
</tr>
<tr>
    <td align="center">DM<input class="input-field-auditoria replace-comma" type="text" name="forecast_dm_1" value="<?php echo $forecast_dm_1 ?>" required></td>
    <td align="center">DM<input class="input-field-auditoria replace-comma" type="text" name="forecast_dm_2" value="<?php echo $forecast_dm_2 ?>" required></td>
    <td align="center">DM<input class="input-field-auditoria replace-comma" type="text" name="forecast_dm_3" value="<?php echo $forecast_dm_3 ?>" required></td>
    <td align="center"><b>R$<?php echo number_format(${"dm_$mes_atual"}, 2, ',', '.'); ?></b></td>
</tr>
<tr class="darkgrey">
    <td align="center">Pax<input class="input-field-auditoria replace-comma" type="number" name="forecast_pax_1" value="<?php echo $forecast_pax_1 ?>" required></td>
    <td align="center">Pax<input class="input-field-auditoria replace-comma" type="number" name="forecast_pax_2" value="<?php echo $forecast_pax_2 ?>" required></td>
    <td align="center">Pax<input class="input-field-auditoria replace-comma" type="number" name="forecast_pax_3" value="<?php echo $forecast_pax_3 ?>" required></td>
    <td align="center"><b>-</b></td>
</tr>
<tr><td style="background-color: black" colspan="4"></td></tr>
<tr class="orange"><td align="center" colspan="4"><b>Receitas</b></td></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
    <td align="center"><b>Acumulado</b></td>
    <td align="center"><b>Orçado</b></td>
</tr>
<tr></tr>
<tr>
    <td><b>Receita Hospedagem</b></td>
    <td align="center">R$<?php echo number_format($receita_hospedagem_dia, 2, ',', '.'); ?></td>
    <td align="center">R$<?php echo number_format($receita_hospedagem_mes, 2, ',', '.'); ?></td>
    <td align="center"><b>R$<?php echo number_format(${"total_hospedagem_$mes_atual"} / $dia_ultimo * $dia_atual, 2, ',', '.'); ?></b></td>
</tr>
<tr class="darkgrey">
    <td><b>Receita Taxa Iss</b></td>
    <td align="center">R$<?php echo number_format($receita_taxaiss_dia, 2, ',', '.'); ?></td>
    <td align="center">R$<?php echo number_format($receita_taxaiss_mes, 2, ',', '.'); ?></td>
    <td align="center"><b>R$<?php echo number_format(${"total_hospedagem_$mes_atual"} / $dia_ultimo * $dia_atual * 0.05, 2, ',', '.'); ?></b></td>
</tr>
<tr>
    <td><b>Receita A&B</b></td>
    <td align="center">R$<?php echo number_format($receita_aeb_dia, 2, ',', '.'); ?></td>
    <td align="center">R$<?php echo number_format($receita_aeb_mes, 2, ',', '.'); ?></td>
    <td align="center"><b>-</b></td>
</tr>
<tr class="darkgrey">
    <td><b>Receita Lavanderia</b></td>
    <td align="center">R$<?php echo number_format($receita_lavanderia_dia, 2, ',', '.'); ?></td>
    <td align="center">R$<?php echo number_format($receita_lavanderia_dia, 2, ',', '.'); ?></td>
    <td align="center"><b>-</b></td>
</tr>
<tr class="receitatotal">
    <td><b>Receita Total</b></td>
    <td align="center">R$<?php echo number_format($receita_total_dia, 2, ',', '.'); ?></td>
    <td align="center">R$<?php echo number_format($receita_total_mes, 2, ',', '.'); ?></td>
    <td align="center"><b>R$<?php echo number_format(${"total_hospedagem_$mes_atual"} / $dia_ultimo * $dia_atual * 1.05, 2, ',', '.'); ?></b></td>
</tr>
<tr><td style="background-color: black" colspan="4"></td></tr>
        </table>
<br><br>
<b>Comentarios:</b><br>
<textarea name="comentarios" id="" cols="100" rows="5"><?php echo $comentarios; ?></textarea><br>
<input type="hidden" name="id_job" value="gerencial">
<input type="submit" class="submit" value="Validar Dados">
</form>
</fieldset>
</div>
</body>
</html>
