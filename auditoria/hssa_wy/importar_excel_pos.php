<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');
require '../../../vendor/autoload.php';

$dir = substr(__DIR__, -7);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    window.location.replace('../../index.html')
    </script>";
    exit();
}

use PhpOffice\PhpSpreadsheet\IOFactory;

$data_auditoria = $_SESSION['data_auditoria'];

$query_status = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_auditorias WHERE id > 0 AND data_auditoria = '{$data_auditoria}'");
$query_status->execute();
while($select_status = $query_status->fetch(PDO::FETCH_ASSOC)){
    $status_auditoria = $select_status['auditoria_status'];
}

if($status_auditoria != 'Em Andamento Pre'){
    echo "<script>
    window.location.replace('auditoria.php')
    </script>";
    exit();
}

$arquivo_gerencial = 0;
$arquivo_noshow = 0;
$dados_noshow = [];

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

// Check if the CSV files were uploaded successfully
if (!empty($_FILES["xlsFile"]["name"]) && (count($_FILES["xlsFile"]["name"]) == 2 || count($_FILES["xlsFile"]["name"]) == 1)) {
    $file_names = $_FILES["xlsFile"]["name"];
    $file_tmp_names = $_FILES["xlsFile"]["tmp_name"];

    $chave = $_SESSION['hotel'].$chave;

    for ($i = 0; $i < count($file_names); $i++) {
        $file_name = $file_names[$i];
        $tmp_name = $file_tmp_names[$i];

        // Check if the uploaded file is a CSV or XML
        if (strtolower(pathinfo($file_name, PATHINFO_EXTENSION)) == "xls") {

            // Read the CSV file
            $file_handle = fopen($tmp_name, "r");
            if ($file_handle !== FALSE) {
                $skip_first_line = true;

                if (strpos($file_name, "rds") !== false) {
                    $arquivo_gerencial = 1;
                }else if (strpos($file_name, "noshow") !== false) {
                    $arquivo_noshow = 1;
                }else{
                    echo "<script>
                    alert('Arquivo Selecionado Invalido')
                    window.location.replace('index.php')
                    </script>";
                    exit();
                }
    
                //Importar Rate Check
                if (strpos($file_name, "rds") !== false) {
                    $id = 0;

                    $diaria_dia = 0;
                    $diaria_mes = 0;
                    $restaurante_dia = 0;
                    $restaurante_mes = 0;
                    $eventos_dia = 0;
                    $eventos_mes = 0;
                    $lavanderia_dia = 0;
                    $lavanderia_mes = 0;
                    $taxaiss_dia = 0;
                    $taxaiss_mes = 0;
                    $diversos_dia = 0;
                    $diversos_mes = 0;
                    $quartos_total_dia = 0;
                    $quartos_total_mes = 0;
                    $quartos_bloqueados_dia = 0;
                    $quartos_bloqueados_mes = 0;
                    $quartos_ocupados_dia = 0;
                    $quartos_ocupados_mes = 0;
                    $quartos_cortesia_dia = 0;
                    $quartos_cortesia_mes = 0;
                    $quartos_houseuse_dia = 0;
                    $quartos_houseuse_mes = 0;
                    $adultos_dia = 0;
                    $adultos_mes = 0;
                    $criancas_dia = 0;
                    $criancas_mes = 0;
                    $noshow_dia = 0;
                    $noshow_mes = 0;
                    $forecast_1 = 0;
                    $forecast_2 = 0;
                    $forecast_3 = 0;

                    // Carrega o arquivo Excel
                    $spreadsheet = IOFactory::load($tmp_name);

                    // Seleciona a primeira planilha (índice 0)
                    $worksheet = $spreadsheet->getActiveSheet();

                    // Obtém todas as células da planilha como uma matriz
                    $datas = $worksheet->toArray();

                    foreach ($datas as $data) {

                        //Diárias
                    if ($data[0] == 'Diárias') {
                        $diaria_dia = str_replace(',', '', $data[9]);
                        $diaria_mes = str_replace(',', '', $data[15]);
                    }
                    if ($data[0] == 'No-Show') {
                        $diaria_dia += str_replace(',', '', $data[9]);
                        $diaria_mes += str_replace(',', '', $data[15]);
                    }

                        //Restaurante
                    if ($data[0] == 'Restaurante') {
                        $restaurante_dia += str_replace(',', '', $data[9]);
                        $restaurante_mes += str_replace(',', '', $data[15]);
                    }
                    if ($data[0] == 'Restaurante 2') {
                        $restaurante_dia += str_replace(',', '', $data[9]);
                        $restaurante_mes += str_replace(',', '', $data[15]);
                    }
                    if ($data[0] == 'Bar') {
                        $restaurante_dia += str_replace(',', '', $data[9]);
                        $restaurante_mes += str_replace(',', '', $data[15]);
                    }
                    if ($data[0] == 'Frigobar') {
                        $restaurante_dia += str_replace(',', '', $data[9]);
                        $restaurante_mes += str_replace(',', '', $data[15]);
                    }

                        //Eventos
                    if ($data[0] == 'Al.Salas') {
                        $eventos_dia = str_replace(',', '', $data[9]);
                        $eventos_mes = str_replace(',', '', $data[15]);
                    }
                    if ($data[0] == 'Al.Equip.') {
                        $eventos_dia += str_replace(',', '', $data[9]);
                        $eventos_mes += str_replace(',', '', $data[15]);
                    }

                        //Lavanderia
                    if ($data[0] == 'Lavanderia') {
                        $lavanderia_dia = str_replace(',', '', $data[9]);
                        $lavanderia_mes = str_replace(',', '', $data[15]);
                    }

                        //Diversos
                    if ($data[0] == 'Diversos') {
                        $diversos_dia = str_replace(',', '', $data[9]);
                        $diversos_mes = str_replace(',', '', $data[15]);
                    }

                        //Taxas
                    if ($data[0] == 'Taxa de ISS - Diversos') {
                        $taxaiss_dia = str_replace(',', '', $data[9]);
                        $taxaiss_mes = str_replace(',', '', $data[15]);
                    }
                    if ($data[0] == 'Taxa Iss - Hospedagem') {
                        $taxaiss_dia = str_replace(',', '', $data[9]);
                        $taxaiss_mes = str_replace(',', '', $data[15]);
                    }
                    if ($data[0] == 'Taxa de Turismo') {
                        $taxaiss_dia = str_replace(',', '', $data[9]);
                        $taxaiss_mes = str_replace(',', '', $data[15]);
                    }

                    //Gerencial
                    if ($data[0] == 'UH\'s do Hotel..................................:') {
                        $quartos_total_dia = intval($data[8]);
                        $quartos_total_mes = intval($data[13]);
                    }
                    if ($data[0] == 'UH\'s Bloqueadas................................:') {
                        $quartos_bloqueados_dia = intval($data[8]);
                        $quartos_bloqueados_mes = intval($data[13]);
                    }
                    if ($data[0] == 'UH\'s Alugadas..................................:') {
                        $quartos_ocupados_dia = intval($data[8]);
                        $quartos_ocupados_mes = intval($data[13]);
                    }
                    if ($data[0] == 'UH\'s Cortesia..................................:') {
                        $quartos_cortesia_dia = intval($data[8]);
                        $quartos_cortesia_mes = intval($data[13]);
                    }
                    if ($data[0] == 'UH\'s Uso da Casa...............................:') {
                        $quartos_houseuse_dia = intval($data[8]);
                        $quartos_houseuse_mes = intval($data[13]);
                    }
                    if ($data[0] == 'Hóspedes Adultos/Crianças 1/Crianças 2 ........:') {
                        $pax_dia = explode('/', $data[8]);
                        $pax_mes = explode('/', $data[12]);
                    }
                    if ($data[0] == 'UH\'s No Show / No Show Cobrados................:') {
                        $noshow_dia = $data[8];
                        $noshow_mes = $data[13];
                    }
                    if ($data[0] == 'Ocupadas') {
                        $forecast_1 = $data[1];
                        $forecast_2 = $data[2];
                        $forecast_3 = $data[3];
                    }

                }

            }else

            //Importar No Shows
            if (strpos($file_name, "noshow") !== false) {

                $id = 0;
    
                    // Carrega o arquivo Excel
                    $spreadsheet = IOFactory::load($tmp_name);

                    // Seleciona a primeira planilha (índice 0)
                    $worksheet = $spreadsheet->getActiveSheet();

                    // Obtém todas as células da planilha como uma matriz
                    $datas = $worksheet->toArray();

                    foreach ($datas as $data) {

                    if (!is_numeric($data[16])) {
                        continue;
                    }


                    $colunaA = $data[0];
                    $colunaB = str_replace('/', '-', $data[9]);
                    $colunaC = str_replace('/', '-', $data[11]);
                    $colunaD = str_replace(',', '', $data[13]);
                    $colunaE = $data[16];
                    $colunaF = str_replace(',', '', $data[15]);

                    $colunaB_partes = explode("-", $colunaB);
                    if($colunaB_partes[1] >= 13){
                    $colunaB_formatada = $colunaB_partes[2] . "-" . $colunaB_partes[0] . "-" . $colunaB_partes[1];
                    }else{
                    $colunaB_formatada = $colunaB_partes[2] . "-" . $colunaB_partes[1] . "-" . $colunaB_partes[0];  
                    }
                    $colunaC_partes = explode("-", $colunaC);
                    if($colunaC_partes[1] >= 13){
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[0] . "-" . $colunaC_partes[1];
                    }else{
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[1] . "-" . $colunaC_partes[0]; 
                    }
    
                    $id++;
                    // Adicione os dados a um array associativo
                    $dados_noshow[] = [
                        'id' => $id,
                        'reserva' => $colunaE,
                        'guest_name' => $colunaA,
                        'checkin' => $colunaB_formatada,
                        'checkout' => $colunaC_formatada,
                        'room_rate' => $colunaD,
                        'cobrado' => $colunaF
                    ];
                }

                // Criar um array associativo para armazenar o maior "room_rate" e "guest_name" por "room_number"
                $maioresValoresPorRoomNumber = [];

                foreach ($dados_noshow as $dados) {
                    $roomNumber = $dados['reserva'];

                    // Se ainda não tivermos uma entrada para este room_number ou se o room_rate atual for maior
                    if (!isset($maioresValoresPorRoomNumber[$roomNumber]) || $dados['room_rate'] > $maioresValoresPorRoomNumber[$roomNumber]['room_rate']) {
                        $maioresValoresPorRoomNumber[$roomNumber] = [
                            'room_rate' => $dados['room_rate'],
                            'cobrado' => $dados['cobrado'],
                            'guest_name' => $dados['guest_name']
                        ];
                    }
                }

                // Atualizar os itens no array original com os maiores valores por room_number
                foreach ($dados_noshow as &$dados) {
                    $roomNumber = $dados['reserva'];
                    
                    if (isset($maioresValoresPorRoomNumber[$roomNumber])) {
                        $dados['room_rate'] = $maioresValoresPorRoomNumber[$roomNumber]['room_rate'];
                        $dados['cobrado'] = $maioresValoresPorRoomNumber[$roomNumber]['cobrado'];
                        $dados['guest_name'] = $maioresValoresPorRoomNumber[$roomNumber]['guest_name'];
                    }
                }

                // Remova a referência para evitar efeitos colaterais indesejados
                unset($dados);

                // Deleta duplicidades
                $indicesParaExcluir = [];

                // Itere sobre os dados_noshow para encontrar índices de linhas a serem excluídas
                for ($k = 0; $k < count($dados_noshow); $k++) {
                    for ($j = $k + 1; $j < count($dados_noshow); $j++) {
                        if (
                            $dados_noshow[$k]['reserva'] === $dados_noshow[$j]['reserva']
                        ) {
                            // Adicione o índice da linha a ser excluída ao array
                            $indicesParaExcluir[] = $j;
                        }
                    }
                }

                // Remova as linhas duplicadas com base nos índices coletados
                foreach ($indicesParaExcluir as $indice) {
                    unset($dados_noshow[$indice]);
                }

                fclose($file_handle);

            }
            
            } else {
                echo "Erro ao importar o arquivo $file_name.";
            }
        } else {
            echo "Invalid file format. Only CSV files are allowed.";
        }
    }

}else {
    echo "<script>
    alert('Selecione todos os Arquivos')
    window.location.replace('index.php')
    </script>";
    exit();
}

$dados_noshow = array_values($dados_noshow);

$arquivos_selecionados = $arquivo_gerencial + $arquivo_noshow;

if($arquivos_selecionados == 2 || $arquivos_selecionados == 1){

$query = $conexao->prepare("UPDATE $dir"."_excel_auditoria_auditorias SET auditoria_status = 'Em Andamento Pos' WHERE data_auditoria = '{$data_auditoria}'");
$query->execute();

 //Insere Noshow no Database
 $sql = "INSERT INTO $dir"."_excel_auditoria (data_auditoria, dados_auditoria) VALUES (?, ?)";
 $stmt = $conn_mysqli->prepare($sql);
 $stmt->bind_param("ss", $data_auditoria, $dados_final);
 foreach($dados_noshow as $select) {
     $id = $select['id'];
     $reserva = $select['reserva'];
     $guest_name = $select['guest_name'];
     $checkin = $select['checkin'];
     $checkout = $select['checkout'];
     $room_rate = $select['room_rate'];
     $cobrado = $select['cobrado'];

     $dados_auditoria = 'noshow;'.$id.';'.$reserva.';'.$guest_name.';'.$checkin.';'.$checkout.';'.$room_rate.';'.$cobrado;
     $dados_criptografados = openssl_encrypt($dados_auditoria, $metodo, $chave, 0, $iv);
     $dados_final = base64_encode($dados_criptografados);
     $stmt->execute();
 }
     $stmt->close();

$diaria_dia = number_format($diaria_dia, 2, '.', '');
$diaria_mes = number_format($diaria_mes, 2, '.', '');
$restaurante_dia = number_format($restaurante_dia, 2, '.', '');
$restaurante_mes = number_format($restaurante_mes, 2, '.', '');
$lavanderia_dia = number_format($lavanderia_dia, 2, '.', '');
$lavanderia_mes = number_format($lavanderia_mes, 2, '.', '');
$taxaiss_dia = number_format($taxaiss_dia, 2, '.', '');
$taxaiss_mes = number_format($taxaiss_mes, 2, '.', '');
$eventos_dia = number_format($eventos_dia, 2, '.', '');
$eventos_mes = number_format($eventos_mes, 2, '.', '');
$diversos_dia = number_format($diversos_dia, 2, '.', '');
$diversos_mes = number_format($diversos_mes, 2, '.', '');

$dados_gerencial = $diaria_dia.';'.$diaria_mes.';'.$restaurante_dia.';'.$restaurante_mes.';'.$lavanderia_dia.';'.$lavanderia_mes.';'.$taxaiss_dia.';'.$taxaiss_mes.';'.$eventos_dia.';'.$eventos_mes.';'.$diversos_dia.';'.$diversos_mes;
$dados_gerencial_occ = '0';

$dados_quartos = $quartos_total_dia.';'.$quartos_total_mes.';'.$quartos_bloqueados_dia.';'.$quartos_bloqueados_mes.';'.$quartos_ocupados_dia.';'.$quartos_ocupados_mes.';'.$quartos_cortesia_dia.';'.$quartos_cortesia_mes.';'.$quartos_houseuse_dia.';'.$quartos_houseuse_mes;
$dados_paxs = intval($pax_dia[0]).';'.intval($pax_mes[0]).';'.intval($pax_dia[1]) + intval($pax_dia[2]).';'.intval($pax_mes[1]) + intval($pax_mes[2]);
$dados_outros = $noshow_dia.';'.$noshow_mes.';'.$forecast_1.';'.$forecast_2.';'.$forecast_3;

//Insere Manager Report no Database
$sql = "INSERT INTO $dir"."_excel_auditoria (data_auditoria, dados_auditoria) VALUES (?, ?)";
$stmt = $conn_mysqli->prepare($sql);
$stmt->bind_param("ss", $data_auditoria, $dados_final);
$dados_auditoria = 'rds;'.$dados_gerencial.';'.$dados_gerencial_occ.';'.$dados_quartos.';'.$dados_paxs.';'.$dados_outros;
$dados_criptografados = openssl_encrypt($dados_auditoria, $metodo, $chave, 0, $iv);
$dados_final = base64_encode($dados_criptografados);
$stmt->execute();
$stmt->close();


//Insere Forecast no Database
$dados_forecast = '0;0;0;0;0;0';
$sql = "INSERT INTO $dir"."_excel_auditoria (data_auditoria, dados_auditoria) VALUES (?, ?)";
$stmt = $conn_mysqli->prepare($sql);
$stmt->bind_param("ss", $data_auditoria, $dados_final);
$dados_auditoria = 'forecast;'.$dados_forecast;
$dados_criptografados = openssl_encrypt($dados_auditoria, $metodo, $chave, 0, $iv);
$dados_final = base64_encode($dados_criptografados);
$stmt->execute();
$stmt->close();

$_SESSION['gerencial'] = 0;
$_SESSION['noshow'] = 0;

echo "<script>
    top.location.replace('auditoria.php')
    </script>";
    exit();

}else{

    echo "<script>
    alert('Selecione todos os Arquivos')
    window.location.replace('index.php')
    </script>";
    exit();

}

?>