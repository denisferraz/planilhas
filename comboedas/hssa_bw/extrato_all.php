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

//Todas Pontuações Lançadas
$query_pontos = $conexao->prepare("SELECT * FROM $dir"."_excel_comboedas WHERE id > 0 ORDER BY id DESC");
$query_pontos->execute();

while($select = $query_pontos->fetch(PDO::FETCH_ASSOC)){

    $colaboradores[] = [
        'id' => $select['id'],
        'colaborador' => $select['colaborador'],
        'pontos' => $select['pontos'],
        'pontos_tipo' => $select['pontos_tipo'],
        'pontos_data' => $select['pontos_data'],
        'pontos_obs' => $select['pontos_obs']
    ];
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
<th colspan="6">Extrato Geral</th>
<tr><td style="background-color: black" colspan="6"></td></tr>
<tr><td align="center" colspan="6">Pontuações</td>
<tr><td style="background-color: black" colspan="6"></td></tr>
<tr style="background-color: grey">
    <td align="center"><b>Colaborador</b></td>
    <td align="center"><b>Data</b></td>
    <td align="center"><b>Pontos</b></td>
    <td align="center"><b>Tipo</b></td>
    <td align="center"><b>Comentario</b></td>
    <td align="center"><b>Excluir</b></td>
</tr>
<?php
$qtd = 0;
foreach ($colaboradores  as $registro) {

    $qtd++;
    if($qtd % 2 == 0){
        $cor_tr = 'darkgrey';
        }else{
        $cor_tr = 'white';  
        }

$pontos_data = $registro['pontos_data'];
?>

<tr style="background-color: <?php echo $cor_tr; ?>">
    <td align="center"><?php echo $registro['colaborador']; ?></td>
    <td align="center"><?php echo date('d/m/Y', strtotime("$pontos_data")); ?></td>
    <td align="center"><?php echo $registro['pontos']; ?></td>
    <td align="center"><?php echo $registro['pontos_tipo']; ?></td>
    <td align="center"><?php echo $registro['pontos_obs']; ?></td>
    <td align="center">
    <form action="acao.php" method="POST">
    <input type="hidden" name="id_job" value="extrato_all">
    <input type="hidden" name="id" value="<?php echo $registro['id']; ?>">
    <input type="submit" value="Excluir">
    </form>
</td>
</tr>

<?php } ?>
</table>
</div>

</body>
</html>
