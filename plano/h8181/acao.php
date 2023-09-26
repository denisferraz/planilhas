<?php
session_start();

require('../../conexao.php');
require('../../verifica_login.php');

$dir = substr(__DIR__, -5);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    window.location.replace('../../index.html')
    </script>";
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//error_reporting(0);

$id_job = mysqli_real_escape_string($conn_mysqli, $_POST['id_job']);
if (isset($_POST['id_acao'])) {
$id_acao = mysqli_real_escape_string($conn_mysqli, $_POST['id_acao']);
}

if($id_job == 'camareiras'){

    $quantidade = $_POST['quantidade'];

    $query_camareiras = $conexao->prepare("UPDATE $dir"."_excel_plano_camareiras SET camareira = :camareira WHERE id_camareira = :camareira_id");

    for ($registros = 1; $registros <= $quantidade; $registros++) {

        $camareira = $_POST["camareira_$registros"];

        $query_camareiras->execute(array('camareira' => $camareira, 'camareira_id' => $registros));

    }

echo   "<script>
    alert('Camareiras Registradas com Sucesso')
    top.location.replace('plano.php')
        </script>";
        exit();

}else if($id_job == 'gerar_plano'){

    $quantidade = $_POST['quantidade'];

    $query_quartos = $conexao->prepare("UPDATE $dir"."_excel_plano_quartos SET id_camareira = :id_camareira WHERE id = :id");

    for ($registros = 1; $registros <= $quantidade; $registros++) {
        $id = $_POST["id_$registros"];
        $id_camareira = $_POST["camareira_$registros"];
        
        $query_quartos->execute(array('id_camareira' => $id_camareira, 'id' => $id));
    }

echo   "<script>
    alert('Plano Gerado com Sucesso')
    top.location.replace('plano.php')
        </script>";
        exit();
    
}
