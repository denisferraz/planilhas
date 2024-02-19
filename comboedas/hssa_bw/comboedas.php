<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

$dir = substr(__DIR__, -7);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    window.location.replace('../../index.html')
    </script>";
    exit();
}

error_reporting(0);

$colaboradores = [];
//Pegar todos Colaboradores
$query_colaborador = $conexao->prepare("SELECT * FROM excel_users WHERE hotel LIKE '%" . $_SESSION['hotel'] . "%'");
$query_colaborador->execute();
while($select_colaborador = $query_colaborador->fetch(PDO::FETCH_ASSOC)){
    $colaborador = $select_colaborador['nome'];

    $colaboradores[] = [
        'colaborador' => $colaborador,
        'pontos' => 0
    ];

//Todas Pontuações Lançadas
$query_pontos = $conexao->prepare("SELECT * FROM $dir"."_excel_comboedas WHERE colaborador = '{$colaborador}'");
$query_pontos->execute();

while($select = $query_pontos->fetch(PDO::FETCH_ASSOC)){

    $colaboradores[] = [
        'colaborador' => $select['colaborador'],
        'pontos' => $select['pontos']
    ];
}
}

$colaboradoresSomaPontos = [];

foreach ($colaboradores as $registro) {
    $colaborador = $registro['colaborador'];
    $pontos = $registro['pontos'];

    if (isset($colaboradoresSomaPontos[$colaborador])) {
        $colaboradoresSomaPontos[$colaborador] += $pontos;
    } else {
        $colaboradoresSomaPontos[$colaborador] = $pontos;
    }
}

function compararPorPontos($a, $b) {
    return $b['pontos'] - $a['pontos'];
}

$colaboradoresOrdenados = [];
foreach ($colaboradoresSomaPontos as $colaborador => $pontos) {
    $colaboradoresOrdenados[] = ['colaborador' => $colaborador, 'pontos' => $pontos];
}

usort($colaboradoresOrdenados, 'compararPorPontos');

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/style_tabela.css">
    <link rel="icon" type="image/x-icon" href="../../images/favicon.ico">
    <link rel="shortcut icon" href="../../images/favicon.ico" type="image/x-icon">
    <title>Comboedas</title>
</head>
<body>

<div class="container">
    <center>
<table>
<th colspan="3">Dashboard</th>
<tr><td style="background-color: black" colspan="3"></td></tr>
<tr><td align="center" colspan="3">Pontuações dos Colaboradores</td>
<tr><td style="background-color: black" colspan="3"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Posição</b></td>
    <td align="center"><b>Colaborador</b></td>
    <td align="center"><b>Pontos</b></td>
</tr>
<?php
$qtd = 0;
foreach ($colaboradoresOrdenados  as $registro) {

    $qtd++;
    if($qtd % 2 == 0){
        $cor_tr = 'darkgrey';
        }else{
        $cor_tr = 'white';  
        }
?>

<tr style="background-color: <?php echo $cor_tr; ?>">
    <td align="center"><b><?php echo $qtd; ?>º</b></td>
    <td align="center"><?php echo $registro['colaborador']; ?></td>
    <td align="center"><?php echo $registro['pontos']; ?></td>
</tr>

<?php } ?>
</table>
</div>

</body>
</html>
