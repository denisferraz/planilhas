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

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

$id_job = mysqli_real_escape_string($conn_mysqli, $_POST['id_job']);

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

<?php
if($id_job == 'pontos'){

    $colaborador = mysqli_real_escape_string($conn_mysqli, $_POST['colaborador']);
    $pontos = mysqli_real_escape_string($conn_mysqli, $_POST['pontos']);
    $pontos_tipo = mysqli_real_escape_string($conn_mysqli, $_POST['pontos_tipo']);
    $comentarios = mysqli_real_escape_string($conn_mysqli, $_POST['comentarios']);

    if($pontos_tipo == 'Saida'){
        $pontos *= (-1);
    }

    $query = $conexao->prepare("INSERT INTO $dir"."_excel_comboedas (colaborador, pontos, pontos_tipo, pontos_obs) VALUES (:colaborador, :pontos, :pontos_tipo, :pontos_obs)");
    $query->execute(array('colaborador' => $colaborador, 'pontos' => $pontos, 'pontos_tipo' => $pontos_tipo, 'pontos_obs' => $comentarios));

    echo   "<script>
    alert('$pontos_tipo de $pontos ponto(s) Registrado com Sucesso para $colaborador')
    window.location.replace('$id_job.php')
        </script>";
        exit();

}else if($id_job == 'extrato_all'){

    $id = mysqli_real_escape_string($conn_mysqli, $_POST['id']);

    $query = $conexao->prepare("DELETE FROM $dir"."_excel_comboedas WHERE id = :id");
    $query->execute(array('id' => $id));

    echo   "<script>
    alert('Pontos Excluidos com Sucesso')
    window.location.replace('$id_job.php')
        </script>";
        exit();

}else if($id_job == 'loja'){

    $acao = mysqli_real_escape_string($conn_mysqli, $_POST['acao']);

    if($acao == 'Cadastrar'){

    $item = mysqli_real_escape_string($conn_mysqli, $_POST['item']);
    $pontos = mysqli_real_escape_string($conn_mysqli, $_POST['pontos']);
    $limite_mensal = mysqli_real_escape_string($conn_mysqli, $_POST['limite_mensal']);

    $query = $conexao->prepare("INSERT INTO $dir"."_excel_comboedas_lojinha (item, pontos, status_item, limite_mensal) VALUES (:item, :pontos, :status_item, :limite_mensal)");
    $query->execute(array('item' => $item, 'pontos' => $pontos, 'status_item' => 'Ativo', 'limite_mensal' => $limite_mensal));

    echo   "<script>
    alert('Opção de Resgate Cadastrado com Sucesso')
    window.location.replace('$id_job.php')
        </script>";
        exit();


    }else if($acao == 'Excluir'){
    $id = mysqli_real_escape_string($conn_mysqli, $_POST['id']);

    $query = $conexao->prepare("DELETE FROM $dir"."_excel_comboedas_lojinha WHERE id = :id");
    $query->execute(array('id' => $id));

    echo   "<script>
    alert('Opção de Resgate Excluido com Sucesso')
    window.location.replace('$id_job.php')
        </script>";
        exit();
    }

}
?>

</div>
</body>
</html>