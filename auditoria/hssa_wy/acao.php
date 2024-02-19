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

$data_auditoria = $_SESSION['data_auditoria'];

$query_status = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_auditorias WHERE id > 0 AND data_auditoria = '{$data_auditoria}'");
$query_status->execute();
while($select_status = $query_status->fetch(PDO::FETCH_ASSOC)){
    $status_auditoria = $select_status['auditoria_status'];
}


$chave = $_SESSION['hotel'].$chave;

//$_SESSION['dados_presentlist']
$query = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria WHERE data_auditoria = '{$data_auditoria}'");
$query->execute();

$dados_presentlist = [];
$dados_caixa = [];
$dados_noshow = [];
$dados_rds = [];
while($select = $query->fetch(PDO::FETCH_ASSOC)){
    $dados_auditoria = $select['dados_auditoria'];
    $id = $select['id'];

// Para descriptografar os dados
$dados = base64_decode($dados_auditoria);
$dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

$dados_array = explode(';', $dados_decifrados);

if($dados_array[0] == 'inhouse'){
$dados_presentlist[] = [
  'id' => $id,
  'reserva' => $dados_array[2],
  'room_number' => $dados_array[3],
  'guest_name' => $dados_array[4],
  'checkin' => $dados_array[5],
  'checkout' => $dados_array[6],
  'room_rate' => $dados_array[7],
  'comentario_checkins' => $dados_array[8],
  'comentario_freestay' => $dados_array[9],
  'auditoria_diarias' => $dados_array[10],
  'auditoria_garantia' => $dados_array[11]
];
}else if($dados_array[0] == 'caixa'){
$dados_caixa[] = [
  'id' => $id,
  'reserva' => $dados_array[2],
  'guest_name' => $dados_array[3],
  'data_lancamento' => $dados_array[4],
  'pgto_forma' => $dados_array[5],
  'pgto_valor' => $dados_array[6],
  'room_number' => $dados_array[7],
  'documento' => $dados_array[8],
  'auditoria_forma' => $dados_array[9],
  'auditoria_conferido' => $dados_array[10]
];
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

    //Update Inhouse no Database
    $sql = "UPDATE $dir"."_excel_auditoria SET dados_auditoria = ? WHERE id = ?";
    $stmt = $conn_mysqli->prepare($sql);
    $stmt->bind_param("ss", $dados_final, $id);

    for ($diarias = 1; $diarias <= $quantidade; $diarias++) {
        $id = $_POST["id_$diarias"];
        $comentario = $_POST["comentarios_$diarias"];

        foreach ($dados_presentlist as &$item) {
            if ($item['id'] == $id) {
                $item['comentario_freestay'] = $comentario;

                $reserva = $item['reserva'];
                $room_number = $item['room_number'];
                $guest_name = $item['guest_name'];
                $checkin = $item['checkin'];
                $checkout = $item['checkout'];
                $room_rate = $item['room_rate'];
                $comentario_checkins = $item['comentario_checkins'];
                $comentario_freestay = $item['comentario_freestay'];
                $auditoria_diarias = $item['auditoria_diarias'];
                $auditoria_garantia = $item['auditoria_garantia'];

                $dados_auditoria = 'inhouse;'.$id.';'.$reserva.';'.$room_number.';'.$guest_name.';'.$checkin.';'.$checkout.';'.$room_rate.';'.$comentario_checkins.';'.$comentario_freestay.';'.$auditoria_diarias.';'.$auditoria_garantia;
                $dados_criptografados = openssl_encrypt($dados_auditoria, $metodo, $chave, 0, $iv);
                $dados_final = base64_encode($dados_criptografados);
                $stmt->execute();

                break;
            }
        }
    }

    $stmt->close();

echo   "<script>
    alert('Comentarios Salvos com Sucesso')
    window.location.replace('$id_job.php')
        </script>";
        exit();

}else if($id_job == 'caixa' || $id_job == 'caixa_n_conf' || $id_job == 'caixa_conf'){

    $quantidade = $_POST['quantidade'];

    //Update Caixa no Database
    $sql = "UPDATE $dir"."_excel_auditoria SET dados_auditoria = ? WHERE id = ?";
    $stmt = $conn_mysqli->prepare($sql);
    $stmt->bind_param("ss", $dados_final, $id);

    for ($diarias = 1; $diarias <= $quantidade; $diarias++) {
        $id = $_POST["id_$diarias"];
        $comentario = $_POST["comentarios_$diarias"];

        foreach ($dados_caixa as &$item) {
            if ($item['id'] == $id) {
                $item['auditoria_conferido'] = $comentario;

                $reserva = $item['reserva'];
                $guest_name = $item['guest_name'];
                $data_lancamento = $item['data_lancamento'];
                $pgto_forma = $item['pgto_forma'];
                $pgto_valor = $item['pgto_valor'];
                $room_number = $item['room_number'];
                $documento = $item['documento'];
                $auditoria_forma = $item['auditoria_forma'];
                $auditoria_conferido = $item['auditoria_conferido'];

                $dados_auditoria = 'caixa;'.$id.';'.$reserva.';'.$guest_name.';'.$data_lancamento.';'.$pgto_forma.';'.$pgto_valor.';'.$room_number.';'.$documento.';'.$auditoria_forma.';'.$auditoria_conferido;
                $dados_criptografados = openssl_encrypt($dados_auditoria, $metodo, $chave, 0, $iv);
                $dados_final = base64_encode($dados_criptografados);
                $stmt->execute();

                break;
            }
        }
    }

    $stmt->close();

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

    $query = $conexao->prepare("UPDATE $dir"."_excel_auditoria_auditorias SET comentario_gerencial = '{$comentarios}' WHERE data_auditoria = '{$data_auditoria}'");
    $query->execute();

    $forecast_pax_1 = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_pax_1']);
    $forecast_pax_2 = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_pax_2']);
    $forecast_pax_3 = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_pax_3']);
    $forecast_dm_1 = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_dm_1']);
    $forecast_dm_2 = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_dm_2']);
    $forecast_dm_3 = mysqli_real_escape_string($conn_mysqli, $_POST['forecast_dm_3']);

    $dados_forecast = $forecast_pax_1.';'.$forecast_pax_2.';'.$forecast_pax_3.';'.$forecast_dm_1.';'.$forecast_dm_2.';'.$forecast_dm_3;

    //Update Gerencial no Database
    $sql = "UPDATE $dir"."_excel_auditoria SET dados_auditoria = ? WHERE id = ?";
    $stmt = $conn_mysqli->prepare($sql);
    $stmt->bind_param("ss", $dados_final, $id_rds);
    $dados_auditoria = 'forecast;'.$dados_forecast;
    $dados_criptografados = openssl_encrypt($dados_auditoria, $metodo, $chave, 0, $iv);
    $dados_final = base64_encode($dados_criptografados);
    $stmt->execute();
    $stmt->close();

echo   "<script>
    alert('Comentarios Salvos com Sucesso')
    window.location.replace('$id_job.php')
        </script>";
        exit();
    
}else if($id_job == 'Garantias'){

    $comentarios = $_POST['comentarios'];

    $query = $conexao->prepare("UPDATE $dir"."_excel_auditoria_auditorias SET comentario_garantias = '{$comentarios}' WHERE data_auditoria = '{$data_auditoria}'");
    $query->execute();

    $quantidade = $_POST['quantidade'];

     //Update Inhouse no Database
    $sql = "UPDATE $dir"."_excel_auditoria SET dados_auditoria = ? WHERE id = ?";
    $stmt = $conn_mysqli->prepare($sql);
    $stmt->bind_param("ss", $dados_final, $id);

    for ($diarias = 1; $diarias <= $quantidade; $diarias++) {
        $id = $_POST["id_$diarias"];
        $comentario = $_POST["comentarios_$diarias"];

        foreach ($dados_presentlist as &$item) {
            if ($item['id'] == $id) {
                $item['auditoria_garantia'] = $_POST["auditoria_garantia_$diarias"];
                $item['auditoria_diarias'] = $_POST["auditoria_diarias_$diarias"];
                $item['comentario_checkins'] = $comentario;

                $reserva = $item['reserva'];
                $room_number = $item['room_number'];
                $guest_name = $item['guest_name'];
                $checkin = $item['checkin'];
                $checkout = $item['checkout'];
                $room_rate = $item['room_rate'];
                $comentario_checkins = $item['comentario_checkins'];
                $comentario_freestay = $item['comentario_freestay'];
                $auditoria_diarias = $item['auditoria_diarias'];
                $auditoria_garantia = $item['auditoria_garantia'];

                $dados_auditoria = 'inhouse;'.$id.';'.$reserva.';'.$room_number.';'.$guest_name.';'.$checkin.';'.$checkout.';'.$room_rate.';'.$comentario_checkins.';'.$comentario_freestay.';'.$auditoria_diarias.';'.$auditoria_garantia;
                $dados_criptografados = openssl_encrypt($dados_auditoria, $metodo, $chave, 0, $iv);
                $dados_final = base64_encode($dados_criptografados);
                $stmt->execute();

                break;
            }
        }
    }

    $stmt->close();

echo   "<script>
    alert('Comentarios Salvos com Sucesso')
    window.location.replace('controlegarantias.php')
        </script>";
        exit();
    
}else if($id_job == 'poa'){

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
        'jan', 'feb', 'mar', 'apr', 'mai', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dec'
    ];

    foreach ($meses as $mes) {
        ${"total_uhs_$mes"} = mysqli_real_escape_string($conn_mysqli, $_POST["total_uhs_$mes"]);
        ${"uhs_ocupadas_$mes"} = mysqli_real_escape_string($conn_mysqli, $_POST["uhs_ocupadas_$mes"]);
        ${"dm_$mes"} = mysqli_real_escape_string($conn_mysqli, $_POST["dm_$mes"]);
        ${"total_hospedagem_$mes"} = mysqli_real_escape_string($conn_mysqli, $_POST["total_hospedagem_$mes"]);
    }

    $dados_poa = 
    $total_uhs_jan.';'.$uhs_ocupadas_jan.';'.$dm_jan.';'.$total_hospedagem_jan.';'.
    $total_uhs_feb.';'.$uhs_ocupadas_feb.';'.$dm_feb.';'.$total_hospedagem_feb.';'.
    $total_uhs_mar.';'.$uhs_ocupadas_mar.';'.$dm_mar.';'.$total_hospedagem_mar.';'.
    $total_uhs_apr.';'.$uhs_ocupadas_apr.';'.$dm_apr.';'.$total_hospedagem_apr.';'.
    $total_uhs_mai.';'.$uhs_ocupadas_mai.';'.$dm_mai.';'.$total_hospedagem_mai.';'.
    $total_uhs_jun.';'.$uhs_ocupadas_jun.';'.$dm_jun.';'.$total_hospedagem_jun.';'.
    $total_uhs_jul.';'.$uhs_ocupadas_jul.';'.$dm_jul.';'.$total_hospedagem_jul.';'.
    $total_uhs_ago.';'.$uhs_ocupadas_ago.';'.$dm_ago.';'.$total_hospedagem_ago.';'.
    $total_uhs_sep.';'.$uhs_ocupadas_sep.';'.$dm_sep.';'.$total_hospedagem_sep.';'.
    $total_uhs_oct.';'.$uhs_ocupadas_oct.';'.$dm_oct.';'.$total_hospedagem_oct.';'.
    $total_uhs_nov.';'.$uhs_ocupadas_nov.';'.$dm_nov.';'.$total_hospedagem_nov.';'.
    $total_uhs_dec.';'.$uhs_ocupadas_dec.';'.$dm_dec.';'.$total_hospedagem_dec;

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
