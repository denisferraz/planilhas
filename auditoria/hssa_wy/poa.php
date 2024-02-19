<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

$dir = substr(__DIR__, -7);

if($dir != $_SESSION['hotel'] || $_SESSION['hierarquia'] == 'Colaborador'){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    top.location.replace('../../index.html');
    </script>";
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//error_reporting(0);

$ano_atual = '2024';

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
        ${"total_uhs_$mes"} += intval($poa_array[$mes][0]['total_uhs']);
        ${"uhs_ocupadas_$mes"} += intval($poa_array[$mes][0]['uhs_ocupadas']);
        ${"dm_$mes"} += floatval($poa_array[$mes][0]['dm']);
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
    <title>Auditoria Digital - Budget [POA]</title>
</head>
<body>

<div class="container">
<fieldset>
<legend>Budget [POA]</legend>
<form action="acao.php" method="POST" id="formulario_auditoria">
    <br>
<table>
<tr>
<!-- Janeiro -->
<td>
<table>
<tr><td style="background-color: black" colspan="2"></td></tr>
<th colspan="2">Janeiro <?php echo $ano_atual; ?></th>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Total Uhs</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_uhs_jan" value="<?php echo $total_uhs_jan ?>" required></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td><input class="input-field-auditoria" type="number" name="uhs_ocupadas_jan" value="<?php echo $uhs_ocupadas_jan ?>" required></td>
</tr>
<tr class="darkgrey">
    <td><b>Diária Média</b></td>
    <td><input class="input-field-auditoria" type="number" name="dm_jan" value="<?php echo $dm_jan ?>" required></td>
</tr>
<tr>
    <td><b>Receita Hospedagem</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_hospedagem_jan" value="<?php echo $total_hospedagem_jan ?>" required></td>
</tr>
<tr><td style="background-color: black" colspan="2"></td></tr>
</table>
</td>

<!-- Fevereiro -->
<td>
<table>
<tr><td style="background-color: black" colspan="2"></td></tr>
<th colspan="2">Fevereiro <?php echo $ano_atual; ?></th>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Total Uhs</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_uhs_feb" value="<?php echo $total_uhs_feb ?>" required></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td><input class="input-field-auditoria" type="number" name="uhs_ocupadas_feb" value="<?php echo $uhs_ocupadas_feb ?>" required></td>
</tr>
<tr class="darkgrey">
    <td><b>Diária Média</b></td>
    <td><input class="input-field-auditoria" type="number" name="dm_feb" value="<?php echo $dm_feb ?>" required></td>
</tr>
<tr>
    <td><b>Receita Hospedagem</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_hospedagem_feb" value="<?php echo $total_hospedagem_feb ?>" required></td>
</tr>
<tr><td style="background-color: black" colspan="2"></td></tr>
</table>
</td>

<!-- Março -->
<td>
<table>
<tr><td style="background-color: black" colspan="2"></td></tr>
<th colspan="2">Março <?php echo $ano_atual; ?></th>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Total Uhs</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_uhs_mar" value="<?php echo $total_uhs_mar ?>" required></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td><input class="input-field-auditoria" type="number" name="uhs_ocupadas_mar" value="<?php echo $uhs_ocupadas_mar ?>" required></td>
</tr>
<tr class="darkgrey">
    <td><b>Diária Média</b></td>
    <td><input class="input-field-auditoria" type="number" name="dm_mar" value="<?php echo $dm_mar ?>" required></td>
</tr>
<tr>
    <td><b>Receita Hospedagem</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_hospedagem_mar" value="<?php echo $total_hospedagem_mar ?>" required></td>
</tr>
<tr><td style="background-color: black" colspan="2"></td></tr>
</table>
</td>

<!-- Abril -->
<td>
<table>
<tr><td style="background-color: black" colspan="2"></td></tr>
<th colspan="2">Abril <?php echo $ano_atual; ?></th>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Total Uhs</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_uhs_apr" value="<?php echo $total_uhs_apr ?>" required></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td><input class="input-field-auditoria" type="number" name="uhs_ocupadas_apr" value="<?php echo $uhs_ocupadas_apr ?>" required></td>
</tr>
<tr class="darkgrey">
    <td><b>Diária Média</b></td>
    <td><input class="input-field-auditoria" type="number" name="dm_apr" value="<?php echo $dm_apr ?>" required></td>
</tr>
<tr>
    <td><b>Receita Hospedagem</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_hospedagem_apr" value="<?php echo $total_hospedagem_apr ?>" required></td>
</tr>
<tr><td style="background-color: black" colspan="2"></td></tr>
</table>
</td>
</tr>
<tr><td style="background-color: grey" colspan="8"></td></tr>
<tr>
<!-- Maio -->
<td>
<table>
<tr><td style="background-color: black" colspan="2"></td></tr>
<th colspan="2">Maio <?php echo $ano_atual; ?></th>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Total Uhs</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_uhs_mai" value="<?php echo $total_uhs_mai ?>" required></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td><input class="input-field-auditoria" type="number" name="uhs_ocupadas_mai" value="<?php echo $uhs_ocupadas_mai ?>" required></td>
</tr>
<tr class="darkgrey">
    <td><b>Diária Média</b></td>
    <td><input class="input-field-auditoria" type="number" name="dm_mai" value="<?php echo $dm_mai ?>" required></td>
</tr>
<tr>
    <td><b>Receita Hospedagem</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_hospedagem_mai" value="<?php echo $total_hospedagem_mai ?>" required></td>
</tr>
<tr><td style="background-color: black" colspan="2"></td></tr>
</table>
</td>

<!-- Junho -->
<td>
<table>
<tr><td style="background-color: black" colspan="2"></td></tr>
<th colspan="2">Junho <?php echo $ano_atual; ?></th>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Total Uhs</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_uhs_jun" value="<?php echo $total_uhs_jun ?>" required></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td><input class="input-field-auditoria" type="number" name="uhs_ocupadas_jun" value="<?php echo $uhs_ocupadas_jun ?>" required></td>
</tr>
<tr class="darkgrey">
    <td><b>Diária Média</b></td>
    <td><input class="input-field-auditoria" type="number" name="dm_jun" value="<?php echo $dm_jun ?>" required></td>
</tr>
<tr>
    <td><b>Receita Hospedagem</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_hospedagem_jun" value="<?php echo $total_hospedagem_jun ?>" required></td>
</tr>
<tr><td style="background-color: black" colspan="2"></td></tr>
</table>
</td>

<!-- Julho -->
<td>
<table>
<tr><td style="background-color: black" colspan="2"></td></tr>
<th colspan="2">Julho <?php echo $ano_atual; ?></th>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Total Uhs</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_uhs_jul" value="<?php echo $total_uhs_jul ?>" required></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td><input class="input-field-auditoria" type="number" name="uhs_ocupadas_jul" value="<?php echo $uhs_ocupadas_jul ?>" required></td>
</tr>
<tr class="darkgrey">
    <td><b>Diária Média</b></td>
    <td><input class="input-field-auditoria" type="number" name="dm_jul" value="<?php echo $dm_jul ?>" required></td>
</tr>
<tr>
    <td><b>Receita Hospedagem</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_hospedagem_jul" value="<?php echo $total_hospedagem_jul ?>" required></td>
</tr>
<tr><td style="background-color: black" colspan="2"></td></tr>
</table>
</td>

<!-- Agosto -->
<td>
<table>
<tr><td style="background-color: black" colspan="2"></td></tr>
<th colspan="2">Agosto <?php echo $ano_atual; ?></th>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Total Uhs</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_uhs_ago" value="<?php echo $total_uhs_ago ?>" required></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td><input class="input-field-auditoria" type="number" name="uhs_ocupadas_ago" value="<?php echo $uhs_ocupadas_ago ?>" required></td>
</tr>
<tr class="darkgrey">
    <td><b>Diária Média</b></td>
    <td><input class="input-field-auditoria" type="number" name="dm_ago" value="<?php echo $dm_ago ?>" required></td>
</tr>
<tr>
    <td><b>Receita Hospedagem</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_hospedagem_ago" value="<?php echo $total_hospedagem_ago ?>" required></td>
</tr>
<tr><td style="background-color: black" colspan="2"></td></tr>
</table>
</td>
</tr>
<tr><td style="background-color: grey" colspan="8"></td></tr>
<tr>
<!-- Setembro -->
<td>
<table>
<tr><td style="background-color: black" colspan="2"></td></tr>
<th colspan="2">Setembro <?php echo $ano_atual; ?></th>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Total Uhs</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_uhs_sep" value="<?php echo $total_uhs_sep ?>" required></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td><input class="input-field-auditoria" type="number" name="uhs_ocupadas_sep" value="<?php echo $uhs_ocupadas_sep ?>" required></td>
</tr>
<tr class="darkgrey">
    <td><b>Diária Média</b></td>
    <td><input class="input-field-auditoria" type="number" name="dm_sep" value="<?php echo $dm_sep ?>" required></td>
</tr>
<tr>
    <td><b>Receita Hospedagem</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_hospedagem_sep" value="<?php echo $total_hospedagem_sep ?>" required></td>
</tr>
<tr><td style="background-color: black" colspan="2"></td></tr>
</table>
</td>

<!-- Outubro -->
<td>
<table>
<tr><td style="background-color: black" colspan="2"></td></tr>
<th colspan="2">Outubro <?php echo $ano_atual; ?></th>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Total Uhs</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_uhs_oct" value="<?php echo $total_uhs_oct ?>" required></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td><input class="input-field-auditoria" type="number" name="uhs_ocupadas_oct" value="<?php echo $uhs_ocupadas_oct ?>" required></td>
</tr>
<tr class="darkgrey">
    <td><b>Diária Média</b></td>
    <td><input class="input-field-auditoria" type="number" name="dm_oct" value="<?php echo $dm_oct ?>" required></td>
</tr>
<tr>
    <td><b>Receita Hospedagem</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_hospedagem_oct" value="<?php echo $total_hospedagem_oct ?>" required></td>
</tr>
<tr><td style="background-color: black" colspan="2"></td></tr>
</table>
</td>

<!-- Novembro -->
<td>
<table>
<tr><td style="background-color: black" colspan="2"></td></tr>
<th colspan="2">Novembro <?php echo $ano_atual; ?></th>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Total Uhs</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_uhs_nov" value="<?php echo $total_uhs_nov ?>" required></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td><input class="input-field-auditoria" type="number" name="uhs_ocupadas_nov" value="<?php echo $uhs_ocupadas_nov ?>" required></td>
</tr>
<tr class="darkgrey">
    <td><b>Diária Média</b></td>
    <td><input class="input-field-auditoria" type="number" name="dm_nov" value="<?php echo $dm_nov ?>" required></td>
</tr>
<tr>
    <td><b>Receita Hospedagem</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_hospedagem_nov" value="<?php echo $total_hospedagem_nov ?>" required></td>
</tr>
<tr><td style="background-color: black" colspan="2"></td></tr>
</table>
</td>

<!-- Dezembro -->
<td>
<table>
<tr><td style="background-color: black" colspan="2"></td></tr>
<th colspan="2">Dezembro <?php echo $ano_atual; ?></th>
<tr></tr>
<tr class="header">
    <td align="center"><b>Linha</b></td>
    <td align="center"><b>Dia</b></td>
</tr>
<tr></tr>
<tr class="darkgrey">
    <td><b>Total Uhs</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_uhs_dec" value="<?php echo $total_uhs_dec ?>" required></td>
</tr>
<tr>
    <td><b>Quartos Ocupados</b></td>
    <td><input class="input-field-auditoria" type="number" name="uhs_ocupadas_dec" value="<?php echo $uhs_ocupadas_dec ?>" required></td>
</tr>
<tr class="darkgrey">
    <td><b>Diária Média</b></td>
    <td><input class="input-field-auditoria" type="number" name="dm_dec" value="<?php echo $dm_dec ?>" required></td>
</tr>
<tr>
    <td><b>Receita Hospedagem</b></td>
    <td><input class="input-field-auditoria" type="number" name="total_hospedagem_dec" value="<?php echo $total_hospedagem_dec ?>" required></td>
</tr>
<tr><td style="background-color: black" colspan="2"></td></tr>
</table>
</td>

</tr>
</table>
<input type="hidden" name="data_poa" value="<?php echo $ano_atual ?>">
<input type="hidden" name="id_job" value="poa">
</form>
</fieldset>
</div>
</body>
</html>
