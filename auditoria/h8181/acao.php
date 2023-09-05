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

if($id_job == 'ratecheck' || $id_job == 'creditlimit' || $id_job == 'freestay'){

    $quantidade = $_POST['quantidade'];

    for ($diarias = 1; $diarias <= $quantidade; $diarias++) {
        $id = $_POST["id_$diarias"];
        $comentario = $_POST["comentarios_$diarias"];

        foreach ($_SESSION['dados_'.$id_job] as &$item) {
            if ($item['id'] == $id) {
                $item['comentario'] = $comentario;
                break;
            }
        }
    }

echo   "<script>
    alert('Comentarios Salvos com Sucesso')
    window.location.replace('$id_job.php')
        </script>";
        exit();

}else if($id_job == 'gerencial' || $id_job == 'taxbase'){

    $comentarios = mysqli_real_escape_string($conn_mysqli, $_POST['comentarios']);

    $_SESSION['comentario_'.$id_job] = $comentarios;

echo   "<script>
    alert('Comentarios Salvos com Sucesso')
    window.location.replace('$id_job.php')
        </script>";
        exit();
    
}else if($id_job == 'controlebac'){

    $comentarios = $_POST['comentarios'];

    $_SESSION['comentario_bac'] = $comentarios;

    $quantidade = $_POST['quantidade'];

    for ($diarias = 1; $diarias <= $quantidade; $diarias++) {
        $id = $_POST["id_$diarias"];

        foreach ($_SESSION['dados_presentlist'] as &$item) {
            if ($item['id'] == $id) {
                $item['auditoria_diarias'] = $_POST["auditoria_diarias_$diarias"];
                $item['auditoria_extras'] = $_POST["auditoria_extras_$diarias"];
                $item['auditoria_garantia'] = $_POST["auditoria_garantia_$diarias"];
                $item['auditoria_valor'] = $_POST["auditoria_valor_$diarias"];
                $item['auditoria_pasta_limpa'] = $_POST["auditoria_pasta_limpa_$diarias"];
                $item['auditoria_pasta_pdv'] = $_POST["auditoria_pasta_pdv_$diarias"];
                $item['auditoria_pasta_pasta'] = $_POST["auditoria_pasta_pasta_$diarias"];
                $item['auditoria_pasta_ass'] = $_POST["auditoria_pasta_ass_$diarias"];
                $item['auditoria_fnrh'] = $_POST["auditoria_fnrh_$diarias"];
                $item['auditoria_doc'] = $_POST["auditoria_doc_$diarias"];
                break;
            }
        }
    }

echo   "<script>
    alert('Comentarios Salvos com Sucesso')
    window.location.replace('controlebac.php')
        </script>";
        exit();
    
}else if($id_job == 'Garantias'){

    $comentarios = $_POST['comentarios'];

    $_SESSION['comentario_garantias'] = $comentarios;

    $quantidade = $_POST['quantidade'];

    for ($diarias = 1; $diarias <= $quantidade; $diarias++) {
        $id = $_POST["id_$diarias"];

        foreach ($_SESSION['dados_presentlist'] as &$item) {
            if ($item['id'] == $id) {
                $item['auditoria_garantia'] = $_POST["auditoria_garantia_$diarias"];
                $item['auditoria_diarias'] = $_POST["auditoria_diarias_$diarias"];
                break;
            }
        }
    }

echo   "<script>
    alert('Comentarios Salvos com Sucesso')
    window.location.replace('controlegarantias.php')
        </script>";
        exit();
    
}