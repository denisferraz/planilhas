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

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

$id_job = mysqli_real_escape_string($conn_mysqli, $_POST['id_job']);
if (isset($_POST['id_acao'])) {
$id_acao = mysqli_real_escape_string($conn_mysqli, $_POST['id_acao']);
}

if($id_job == 'camareiras'){

    $quantidade = $_POST['quantidade'];

    for ($registros = 1; $registros <= $quantidade; $registros++) {

        $camareira = $_POST["camareira_$registros"];

    $_SESSION['camareira_'.$registros] = $camareira;

    }

echo   "<script>
    alert('Camareiras Registradas com Sucesso')
    top.location.replace('plano.php')
        </script>";
        exit();

}else if($id_job == 'gerar_plano'){

    $comentarios = $_POST['comentarios'];

    $quantidade = $_POST['quantidade'];

    for ($registros = 1; $registros <= $quantidade; $registros++) {
        $id = $_POST["id_$registros"];

        foreach ($_SESSION['dados_roomstatus'] as &$item) {
            if ($item['id'] == $id) {
                $item['id_camareira'] = $_POST["camareira_$registros"];
                break;
            }
        }
    }

echo   "<script>
    alert('Plano Gerado com Sucesso')
    window.location.replace('dashboard.php')
        </script>";
        exit();
    
}
