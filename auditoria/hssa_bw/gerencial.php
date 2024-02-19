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

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//error_reporting(0);

$data_auditoria = $_SESSION['data_auditoria'];

$query_status = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_auditorias WHERE id > 0 AND data_auditoria = '{$data_auditoria}'");
$query_status->execute();
while($select_status = $query_status->fetch(PDO::FETCH_ASSOC)){
    $status_auditoria = $select_status['auditoria_status'];
    $comentarios = $select_status['comentario_gerencial'];
}

if($status_auditoria == 'Em Andamento Pre'){
    echo "<script>
    alert('Relatorios Pos Auditoria não Importados!')
    window.location.replace('controlegarantias.php')
    </script>";
    exit();
}else if($status_auditoria == 'Pendente'){
    echo "<script>
    alert('Auditoria não foi Iniciada!')
    top.location.replace('index.php')
    </script>";
    exit();
}

$chave = $_SESSION['hotel'].$chave;

//$dados_presentlist
$query = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria WHERE data_auditoria = '{$data_auditoria}'");
$query->execute();

while($select = $query->fetch(PDO::FETCH_ASSOC)){
    $dados_auditoria = $select['dados_auditoria'];
    $id = $select['id'];

// Para descriptografar os dados
$dados = base64_decode($dados_auditoria);
$dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

$dados_array = explode(';', $dados_decifrados);

if($dados_array[0] == 'rds'){
  $dados_gerencial = $dados_array[1].';'.$dados_array[2].';'.$dados_array[3].';'.$dados_array[4].';'.$dados_array[5].';'.$dados_array[6].';'.$dados_array[7].';'.$dados_array[8].';'.$dados_array[9].';'.$dados_array[10].';'.$dados_array[11].';'.$dados_array[12];
  $dados_gerencial_occ = $dados_array[13];
  $quartos_construidos_dia = $dados_array[14];
  $quartos_construidos_mes = $dados_array[15];
  $quartos_bloqueados_dia = $dados_array[16];
  $quartos_bloqueados_mes = $dados_array[17];
  $quartos_ocupados_dia = $dados_array[18];
  $quartos_ocupados_mes = $dados_array[19];
  $quartos_cortesia_dia = $dados_array[20];
  $quartos_cortesia_mes = $dados_array[21];
  $quartos_houseuse_dia = $dados_array[22];
  $quartos_houseuse_mes = $dados_array[23];
  $adultos_dia = $dados_array[24];
  $adultos_mes = $dados_array[25];
  $criancas_dia = $dados_array[26];
  $criancas_mes = $dados_array[27];
  $noshow_dia = $dados_array[28];
  $noshow_mes = $dados_array[29];
  $forecast_1 = $dados_array[30];
  $forecast_2 = $dados_array[31];
  $forecast_3 = $dados_array[32];
}else if($dados_array[0] == 'forecast'){
  $id_rds = $id;
  $forecast_pax_1 = $dados_array[1];
  $forecast_pax_2 = $dados_array[2];
  $forecast_pax_3 = $dados_array[3];
  $forecast_dm_1 = $dados_array[4];
  $forecast_dm_2 = $dados_array[5];
  $forecast_dm_3 = $dados_array[6];
}
}

//Gerencial - Receitas
$linhas_gerenciais = explode(';', $dados_gerencial);

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

    $query_poa = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_poa WHERE data_poa = :data_poa");
    $query_poa->execute(array('data_poa' => $ano_atual));
    $query_qtd = $query_poa->rowCount();

    if($query_qtd > 0){


//POA
while ($select = $query_poa->fetch(PDO::FETCH_ASSOC)) {
    $dados_poa = $select['dados_poa'];
}

$dados = base64_decode($dados_poa);
$dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);
$dados_array = explode(';', $dados_decifrados);

foreach ($meses as $mes) {
    ${"total_uhs_$mes"} = 0;
    ${"uhs_ocupadas_$mes"} = 0;
    ${"dm_$mes"} = 0.00;
    ${"total_hospedagem_$mes"} = 0;

    $mesIndex = array_search($mes, $meses);
    $startIndex = $mesIndex * 4; // Cada mês tem 4 valores

        $poa_array[$mes][] = [
            'total_uhs' => $dados_array[$startIndex],
            'uhs_ocupadas' => $dados_array[$startIndex + 1],
            'dm' => $dados_array[$startIndex + 2],
            'receita' => $dados_array[$startIndex + 3]
        ];

        // Atualiza as variáveis mensais
        ${"total_uhs_$mes"} += intval($poa_array[$mes][0]['total_uhs']);
        ${"uhs_ocupadas_$mes"} += intval($poa_array[$mes][0]['uhs_ocupadas']);
        ${"dm_$mes"} += $poa_array[$mes][0]['dm'];
        ${"total_hospedagem_$mes"} += intval($poa_array[$mes][0]['receita']);

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
<?php if($status_auditoria != 'Finalizada'){ ?>
<form action="acao.php" method="POST" id="formulario_auditoria">
<?php } ?>
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
    <td align="center"><?php echo $quartos_bloqueados_dia ?></td>
    <td align="center"><?php echo $quartos_bloqueados_mes ?></td>
    <td align="center"><b>-</b></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td align="center"><?php echo $quartos_ocupados_dia ?></td>
    <td align="center"><?php echo $quartos_ocupados_mes ?></td>
    <td align="center"><b><?php echo number_format(${"uhs_ocupadas_$mes_atual"} * $dia_atual, 0, ',', '.'); ?></b></td>
</tr>
<tr class="darkgrey">
    <td><b>Quartos Cortesia</b></td>
    <td align="center"><?php echo $quartos_cortesia_dia ?></td>
    <td align="center"><?php echo $quartos_cortesia_mes ?></td>
    <td align="center"><b>-</b></td>
</tr>
<tr>
    <td><b>Quartos Uso da Casa</b></td>
    <td align="center"><?php echo $quartos_houseuse_dia ?></td>
    <td align="center"><?php echo $quartos_houseuse_mes ?></td>
    <td align="center"><b>-</b></td>
</tr>
<tr class="darkgrey">
    <td><b>Ocupação</b></td>
    <td align="center"><?php echo number_format($quartos_occ_dia, 2, ',', '.'); ?>%</td>
    <td align="center"><?php echo number_format($quartos_occ_mes, 2, ',', '.'); ?>%</td>
    <td align="center"><b><?php echo number_format(${"uhs_ocupadas_$mes_atual"} / ${"total_uhs_$mes_atual"} * 100, 0, ',', '.'); ?>,00%</b></td>
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
    <td align="center"><?php echo $adultos_dia ?></td>
    <td align="center"><?php echo $adultos_mes ?></td>
    <td align="center"><b>-</b></td>
</tr>
<tr class="darkgrey">
    <td><b>Crianças</b></td>
    <td align="center"><?php echo $criancas_dia ?></td>
    <td align="center"><?php echo $criancas_mes ?></td>
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
    <td align="center">Uhs <?php echo $forecast_1 ?></td>
    <td align="center">Uhs <?php echo $forecast_2 ?></td>
    <td align="center">Uhs <?php echo $forecast_3 ?></td>
    <td align="center">Uhs <b><?php echo number_format(${"uhs_ocupadas_$mes_atual"}, 0, ',', '.'); ?></b></td>
</tr>
<tr class="darkgrey">
    <td align="center">Occ <?php echo number_format($forecast_occ_1, 2, ',', '.'); ?>%</td>
    <td align="center">Occ <?php echo number_format($forecast_occ_2, 2, ',', '.'); ?>%</td>
    <td align="center">Occ <?php echo number_format($forecast_occ_3, 2, ',', '.'); ?>%</td>
    <td align="center">Occ <b><?php echo number_format(${"uhs_ocupadas_$mes_atual"} / ${"total_uhs_$mes_atual"} * 100, 0, ',', '.'); ?>,00%</b></td>
</tr>
<tr>
    <td align="center">DM<input class="input-field-auditoria replace-comma" type="text" name="forecast_dm_1" value="<?php echo $forecast_dm_1 ?>" required></td>
    <td align="center">DM<input class="input-field-auditoria replace-comma" type="text" name="forecast_dm_2" value="<?php echo $forecast_dm_2 ?>" required></td>
    <td align="center">DM<input class="input-field-auditoria replace-comma" type="text" name="forecast_dm_3" value="<?php echo $forecast_dm_3 ?>" required></td>
    <td align="center">DM <b>R$<?php echo number_format(${"dm_$mes_atual"}, 2, ',', '.'); ?></b></td>
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
<?php if($status_auditoria != 'Finalizada'){ ?>
<input type="hidden" name="id_job" value="gerencial">
<input type="submit" class="submit" value="Validar Dados">
<?php } ?>
</form>
</fieldset>
</div>
</body>
</html>
