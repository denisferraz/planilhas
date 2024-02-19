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
if (isset($_POST['id_acao'])) {
$id_acao = mysqli_real_escape_string($conn_mysqli, $_POST['id_acao']);
}

if($id_acao == 'salvar_parcial' && $id_job != 'poa'){
    $_SESSION[$id_job] = 0;
}else if($id_job != 'poa'){
    $_SESSION[$id_job] = 1;
}

if($id_job == 'freestay'){

    $quantidade = $_POST['quantidade'];

    for ($diarias = 1; $diarias <= $quantidade; $diarias++) {
        $id = $_POST["id_$diarias"];
        $comentario = $_POST["comentarios_$diarias"];

        foreach ($_SESSION['dados_presentlist'] as &$item) {
            if ($item['id'] == $id) {
                $item['comentario_freestay'] = $comentario;
                break;
            }
        }
    }

echo   "<script>
    alert('Comentarios Salvos com Sucesso')
    window.location.replace('$id_job.php')
        </script>";
        exit();

}else if($id_job == 'caixa' || $id_job == 'caixa_n_conf' || $id_job == 'caixa_conf'){

    $quantidade = $_POST['quantidade'];

    for ($diarias = 1; $diarias <= $quantidade; $diarias++) {
        $id = $_POST["id_$diarias"];
        $comentario = $_POST["comentarios_$diarias"];

        foreach ($_SESSION['dados_caixa'] as &$item) {
            if ($item['id'] == $id) {
                $item['auditoria_conferido'] = $comentario;
                break;
            }
        }
    }

echo   "<script>
    alert('Comentarios Salvos com Sucesso')
    window.location.replace('$id_job.php')
        </script>";
        exit();

}else if($id_job == 'noshow'){

echo   "<script>
    alert('No Show validado com Sucesso')
    window.location.replace('$id_job.php')
        </script>";
        exit();

}else if($id_job == 'gerencial'){

    $comentarios = mysqli_real_escape_string($conn_mysqli, $_POST['comentarios']);

    $_SESSION['comentario_gerencial'] = $comentarios;
    $_SESSION['quartos_bloqueados_dia'] = mysqli_real_escape_string($conn_mysqli, $_POST['quartos_bloqueados_dia']);
    $_SESSION['quartos_bloqueados_mes'] = mysqli_real_escape_string($conn_mysqli, $_POST['quartos_bloqueados_mes']);
    $_SESSION['quartos_ocupados_dia'] = mysqli_real_escape_string($conn_mysqli, $_POST['quartos_ocupados_dia']);
    $_SESSION['quartos_ocupados_mes'] = mysqli_real_escape_string($conn_mysqli, $_POST['quartos_ocupados_mes']);
    $_SESSION['quartos_cortesia_dia'] = mysqli_real_escape_string($conn_mysqli, $_POST['quartos_cortesia_dia']);
    $_SESSION['quartos_cortesia_mes'] = mysqli_real_escape_string($conn_mysqli, $_POST['quartos_cortesia_mes']);
    $_SESSION['quartos_houseuse_dia'] = mysqli_real_escape_string($conn_mysqli, $_POST['quartos_houseuse_dia']);
    $_SESSION['quartos_houseuse_mes'] = mysqli_real_escape_string($conn_mysqli, $_POST['quartos_houseuse_mes']);
    $_SESSION['adultos_dia'] = mysqli_real_escape_string($conn_mysqli, $_POST['adultos_dia']);
    $_SESSION['adultos_mes'] = mysqli_real_escape_string($conn_mysqli, $_POST['adultos_mes']);
    $_SESSION['criancas_dia'] = mysqli_real_escape_string($conn_mysqli, $_POST['criancas_dia']);
    $_SESSION['criancas_mes'] = mysqli_real_escape_string($conn_mysqli, $_POST['criancas_mes']);
    $_SESSION['forecast_1'] = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_1']);
    $_SESSION['forecast_2'] = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_2']);
    $_SESSION['forecast_3'] = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_3']);
    $_SESSION['forecast_pax_1'] = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_pax_1']);
    $_SESSION['forecast_pax_2'] = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_pax_2']);
    $_SESSION['forecast_pax_3'] = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_pax_3']);
    $_SESSION['forecast_dm_1'] = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_dm_1']);
    $_SESSION['forecast_dm_2'] = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_dm_2']);
    $_SESSION['forecast_dm_3'] = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_dm_3']);

echo   "<script>
    alert('Comentarios Salvos com Sucesso')
    window.location.replace('$id_job.php')
        </script>";
        exit();
    
}else if($id_job == 'Garantias'){

    $comentarios = $_POST['comentarios'];

    $_SESSION['comentario_garantias'] = $comentarios;

    $quantidade = $_POST['quantidade'];

    for ($diarias = 1; $diarias <= $quantidade; $diarias++) {
        $id = $_POST["id_$diarias"];
        $comentario = $_POST["comentarios_$diarias"];

        foreach ($_SESSION['dados_presentlist'] as &$item) {
            if ($item['id'] == $id) {
                $item['auditoria_garantia'] = $_POST["auditoria_garantia_$diarias"];
                $item['auditoria_diarias'] = $_POST["auditoria_diarias_$diarias"];
                $item['comentario_checkins'] = $comentario;
                break;
            }
        }
    }

echo   "<script>
    alert('Comentarios Salvos com Sucesso')
    window.location.replace('controlegarantias.php')
        </script>";
        exit();
    
}else if($id_job == 'poa'){

    // Chave de criptografia
    $chave = $_SESSION['hotel'].$chave;

    $data_poa = mysqli_real_escape_string($conn_mysqli, $_POST['data_poa']);

    $query = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_poa WHERE data_poa = :data_poa");
    $query->execute(array('data_poa' => $data_poa));
    $query_qtd = $query->rowCount();

if($query_qtd > 0){
    $sql = "UPDATE $dir"."_excel_auditoria_poa SET dados_poa = ? WHERE data_poa = ?";
    $stmt = $conn_mysqli->prepare($sql);
    $stmt->bind_param("ss", $dados_final, $data_poa);
}else{
    $sql = "INSERT INTO $dir"."_excel_auditoria_poa (data_poa, dados_poa) VALUES (?, ?)";
    $stmt = $conn_mysqli->prepare($sql);
    $stmt->bind_param("ss", $data_poa, $dados_final);
}

    $meses = [
        'jan', 'fev', 'mar', 'abr', 'mai', 'jun', 'jul', 'ago', 'set', 'out', 'nov', 'dez'
    ];

    foreach ($meses as $mes) {
        ${"total_uhs_$mes"} = mysqli_real_escape_string($conn_mysqli, $_POST["total_uhs_$mes"]);
        ${"uhs_ocupadas_$mes"} = mysqli_real_escape_string($conn_mysqli, $_POST["uhs_ocupadas_$mes"]);
        ${"dm_$mes"} = mysqli_real_escape_string($conn_mysqli, $_POST["dm_$mes"]);
        ${"total_hospedagem_$mes"} = mysqli_real_escape_string($conn_mysqli, $_POST["total_hospedagem_$mes"]);
    }

    $dados_poa = 
    $total_uhs_jan.';'.$uhs_ocupadas_jan.';'.$dm_jan.';'.$total_hospedagem_jan.';'.
    $total_uhs_fev.';'.$uhs_ocupadas_fev.';'.$dm_fev.';'.$total_hospedagem_fev.';'.
    $total_uhs_mar.';'.$uhs_ocupadas_mar.';'.$dm_mar.';'.$total_hospedagem_mar.';'.
    $total_uhs_abr.';'.$uhs_ocupadas_abr.';'.$dm_abr.';'.$total_hospedagem_abr.';'.
    $total_uhs_mai.';'.$uhs_ocupadas_mai.';'.$dm_mai.';'.$total_hospedagem_mai.';'.
    $total_uhs_jun.';'.$uhs_ocupadas_jun.';'.$dm_jun.';'.$total_hospedagem_jun.';'.
    $total_uhs_jul.';'.$uhs_ocupadas_jul.';'.$dm_jul.';'.$total_hospedagem_jul.';'.
    $total_uhs_ago.';'.$uhs_ocupadas_ago.';'.$dm_ago.';'.$total_hospedagem_ago.';'.
    $total_uhs_set.';'.$uhs_ocupadas_set.';'.$dm_set.';'.$total_hospedagem_set.';'.
    $total_uhs_out.';'.$uhs_ocupadas_out.';'.$dm_out.';'.$total_hospedagem_out.';'.
    $total_uhs_nov.';'.$uhs_ocupadas_nov.';'.$dm_nov.';'.$total_hospedagem_nov.';'.
    $total_uhs_dez.';'.$uhs_ocupadas_dez.';'.$dm_dez.';'.$total_hospedagem_dez;

    $dados_criptografados = openssl_encrypt($dados_poa, $metodo, $chave, 0, $iv);
    $dados_final = base64_encode($dados_criptografados);

    // Execute the SQL statement
    $stmt->execute();
    $stmt->close();

    echo   "<script>
        alert('Budget [POA] Atualizado com Sucesso')
        window.location.replace('$id_job.php')
            </script>";
            exit();
    
    }
