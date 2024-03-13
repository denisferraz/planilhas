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

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

use PhpOffice\PhpSpreadsheet\IOFactory;

// Check if the XML files were uploaded successfully
if (!empty($_FILES["xlsFile"]["name"]) && count($_FILES["xlsFile"]["name"]) == 4) {
    $file_names = $_FILES["xlsFile"]["name"];
    $file_tmp_names = $_FILES["xlsFile"]["tmp_name"];

// Chave de criptografia
$chave = $_SESSION['hotel'].$chave;

    // Define the table names
    $tables = array("$dir"."_excel_gestaorecepcao_cashier");

    // Loop through the tables
    foreach ($tables as $table) {
    // Delete existing records from the table
    $sql = "DELETE FROM $table WHERE id != -1";
    $stmt = $conn_mysqli->prepare($sql);
    $stmt->execute();

    // Reset the auto-increment value for ID
    $sql = "ALTER TABLE $table AUTO_INCREMENT = 1";
    $stmt = $conn_mysqli->prepare($sql);
    $stmt->execute();
    }

    for ($i = 0; $i < count($file_names); $i++) {
        $file_name = $file_names[$i];
        $tmp_name = $file_tmp_names[$i];

        // Check if the uploaded file is a XML
        if (strtolower(pathinfo($file_name, PATHINFO_EXTENSION)) == "xls") {
            // Read the XML file
            $file_handle = fopen($tmp_name, "r");
            if ($file_handle !== FALSE) {
                $skip_first_line = true;

                if (strpos($file_name, "quartos") !== false) {
                    $tabela_excel = "$dir"."_excel_gestaorecepcao_roomstatus";
                }else if (strpos($file_name, "checkin") !== false) {
                    $tabela_excel = "$dir"."_excel_gestaorecepcao_arrivals";
                }else if (strpos($file_name, "inhouse") !== false) {
                    $tabela_excel = "$dir"."_excel_gestaorecepcao_inhouse";
                }else if (strpos($file_name, "saldos") !== false) {
                    $tabela_excel = "$dir"."_excel_gestaorecepcao_saldos";
                }
    
                // Reseta Tabela
                $sql = "TRUNCATE $tabela_excel";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->execute();
    
                //Importar Room Status
                if (strpos($file_name, "quartos") !== false) {
                // Prepare the SQL statement for inserting data into the database
                $sql = "INSERT INTO $tabela_excel (room_number, room_status, room_type) VALUES (?, ?, ?)";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->bind_param("sss", $colunaA, $colunaB, $colunaC);
    
                // Carrega o arquivo Excel
                $spreadsheet = IOFactory::load($tmp_name);

                // Seleciona a primeira planilha (índice 0)
                $worksheet = $spreadsheet->getActiveSheet();

                // Obtém todas as células da planilha como uma matriz
                $datas = $worksheet->toArray();

                foreach ($datas as $data) {

                if (!is_numeric($data[0])) {
                    continue;
                }

                if($data[4] == 'Bloqueado'){
                    $status_quarto = 'Bloqueado';
                }else if($data[5] == 'Cama Junta' || $data[5] == 'Cama Separada' || $data[5] == 'Limpo' || $data[5] == 'Reservado' || $data[5] == 'Inspeção' || $data[5] == 'Site Inspection' || $data[5] == 'Make a Green Choice'){
                    $status_quarto = 'Limpo';
                }else{
                    $status_quarto = 'Sujo';
                }

                if($data[14] >= 1){
                    $status_quarto = 'Ocupado';
                }

                $colunaA = $data[0];
                $colunaB = $status_quarto;
                $colunaC = $data[1];

                    // Execute the SQL statement
                    $stmt->execute();

            }

                // Deleta duplicidades
                $sql = "DELETE t1 FROM $tabela_excel t1
                INNER JOIN $tabela_excel t2
                WHERE t1.id < t2.id
                  AND t1.room_number = t2.room_number
                  AND t1.room_number = t2.room_number";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->execute();

                // Close the file handle and statement
                $stmt->close();
            }
            //Importar Arrivals
            else if (strpos($file_name, "checkin") !== false) {
                // Prepare the SQL statement for inserting data into the database
                $sql = "INSERT INTO $tabela_excel (dados_arrivals) VALUES (?)";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->bind_param("s", $dados_final);
    
                // Carrega o arquivo Excel
                $spreadsheet = IOFactory::load($tmp_name);

                // Seleciona a primeira planilha (índice 0)
                $worksheet = $spreadsheet->getActiveSheet();

                // Obtém todas as células da planilha como uma matriz
                $datas = $worksheet->toArray();

                foreach ($datas as $data) {

                if ($data[4] != '--x--') {
                    continue;
                }

                $minhaString = $data[6];
                if (strlen($minhaString) == 4) {

                    $colunaB2 = str_replace('/', '-', $data[7]);
                    $colunaC2 = str_replace('/', '-', $data[9]);
                    $pax_total = explode('/', $data[11]);
    
                    $colunaB_partes = explode("-", $colunaB2);
                    if($colunaB_partes[1] >= 13){
                    $colunaB_formatada = $colunaB_partes[2] . "-" . $colunaB_partes[0] . "-" . $colunaB_partes[1];
                    }else{
                    $colunaB_formatada = $colunaB_partes[2] . "-" . $colunaB_partes[1] . "-" . $colunaB_partes[0];  
                    }
                    $colunaC_partes = explode("-", $colunaC2);
                    if($colunaC_partes[1] >= 13){
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[0] . "-" . $colunaC_partes[1];
                    }else{
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[1] . "-" . $colunaC_partes[0]; 
                    }
    
                    $room_number = $data[6];
                    $noites = (strtotime($colunaC_formatada) - strtotime($colunaB_formatada)) / 86400;
    
                    $colunaF = $data[14];

                }else{

                    $colunaB2 = str_replace('/', '-', $data[6]);
                    $colunaC2 = str_replace('/', '-', $data[9]);
                    $pax_total = explode('/', $data[11]);
    
                    $colunaB_partes = explode("-", $colunaB2);
                    if($colunaB_partes[1] >= 13){
                    $colunaB_formatada = $colunaB_partes[2] . "-" . $colunaB_partes[0] . "-" . $colunaB_partes[1];
                    }else{
                    $colunaB_formatada = $colunaB_partes[2] . "-" . $colunaB_partes[1] . "-" . $colunaB_partes[0];  
                    }
                    $colunaC_partes = explode("-", $colunaC2);
                    if($colunaC_partes[1] >= 13){
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[0] . "-" . $colunaC_partes[1];
                    }else{
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[1] . "-" . $colunaC_partes[0]; 
                    }
    
                    $room_number = '';
                    $noites = (strtotime($colunaC_formatada) - strtotime($colunaB_formatada)) / 86400;
    
                    $colunaF = $data[14];

                }

                if($data[17] == 'C'){
                    $room_msg = 'Café Incluso';
                }else{
                    $room_msg = 'Sem Café da Manhã';
                }

                if($data[0] == '*'){
                    $colunaA = $data[1];
                }else{
                    $colunaA = $data[0];
                }

                $checkin = $colunaB_formatada;
                $checkout = $colunaC_formatada;
                $colunaB = $noites;
                $colunaC = $pax_total[0];
                $colunaD = intval($pax_total[1]) + intval($pax_total[2]);
                $colunaE = $data[5];
                $colunaG = $room_msg;
                $colunaH = $room_number;
                $colunaI = 'Pendente'; //alteração
                $colunaJ = $data[20];

                $dados_arrivalslist = $colunaA.';'.$colunaB.';'.$colunaC.';'.$colunaD.';'.$colunaE.';'.$colunaF.';'.$colunaG.';'.$colunaH.';'.$colunaI.';'.$colunaJ.';'.$checkin.';'.$checkout;
                $dados_criptografados = openssl_encrypt($dados_arrivalslist, $metodo, $chave, 0, $iv);
                $dados_final = base64_encode($dados_criptografados);

                    // Execute the SQL statement
                        $stmt->execute();
                    
                }
    
                // Close the file handle and statement
                $stmt->close();
            }
            //Importar In House
            else if (strpos($file_name, "inhouse") !== false) {
                // Prepare the SQL statement for inserting data into the database
                $sql = "INSERT INTO $tabela_excel (dados_presentlist) VALUES (?)";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->bind_param("s", $dados_final);
    
                //Duplicidades
                $colunaA = '';
                $colunaB = '';
                $colunaC = '';
                $colunaD = '';
                $colunaE = 0; //Adultos
                $colunaF = '';
                $colunaG = '';
                $colunaH = 0; //Balance
                $colunaI = 0; //Room
                $colunaJ = '';
                $colunaK = '';
                $colunaL = '';
                $colunaM = '';
                $id_inhouse = 0;

                // Carrega o arquivo Excel
                $spreadsheet = IOFactory::load($tmp_name);

                // Seleciona a primeira planilha (índice 0)
                $worksheet = $spreadsheet->getActiveSheet();

                // Obtém todas as células da planilha como uma matriz
                $datas = $worksheet->toArray();

                foreach ($datas as $data) {

                if (!is_numeric($data[6])) {
                    continue;
                }

                $colunaB2 = str_replace('/', '-', $data[15]);
                $colunaC2 = str_replace('/', '-', $data[16]);

                $colunaB_partes = explode("-", $colunaB2);
                if($colunaB_partes[1] >= 13){
                $colunaB_formatada = $colunaB_partes[2] . "-" . $colunaB_partes[0] . "-" . $colunaB_partes[1];
                }else{
                $colunaB_formatada = $colunaB_partes[2] . "-" . $colunaB_partes[1] . "-" . $colunaB_partes[0];  
                }
                $colunaC_partes = explode("-", $colunaC2);
                if($colunaC_partes[1] >= 13){
                $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[0] . "-" . $colunaC_partes[1];
                }else{
                $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[1] . "-" . $colunaC_partes[0]; 
                }

                    if($data[6] == $colunaI){

                    $query =  $conexao->prepare("DELETE FROM $tabela_excel WHERE id = :id");
                    $query->execute(array('id' => $id_inhouse));

                    if($data[0] == '*'){
                        $colunaAa = $data[1];
                    }else{
                        $colunaAa = $data[0];
                    }

                    $FULL_NAME = $colunaA.' - '.$colunaAa;
                    $Checkin = $colunaB;
                    $Checkout = $colunaC;
                    $noites = $colunaD;
                    $ADULTS = $colunaE + 1;
                    $CHILDREN = $colunaF;
                    $RATE_CODE = $colunaG;
                    $BALANCE = 0;
                    $ROOM = $colunaI;
                    $Comentarios = $colunaJ;
                    $COMPANY_NAME = $colunaK;
                    $Alteracao = $colunaL;

                    }else{

                    if($data[0] == '*'){
                        $colunaAa = $data[1];
                    }else{
                        $colunaAa = $data[0];
                    }

                    $FULL_NAME = $colunaAa;
                    $Checkin = $colunaB_formatada;
                    $Checkout = $colunaC_formatada;
                    $noites = 1;
                    $ADULTS = 1;
                    $CHILDREN = 0;
                    $RATE_CODE = $data[12];
                    $BALANCE = 0;
                    $ROOM = $data[6];
                    $ROOM_TYPE = $data[7];
                    $Comentarios = '';
                    $COMPANY_NAME = $data[9];
                    $Alteracao = 'Pendente'; //alteração

                    }

                    $id_inhouse++;

                    $colunaA = $FULL_NAME;
                    $colunaB = $Checkin;
                    $colunaC = $Checkout;
                    $colunaD = $noites;
                    $colunaE = $ADULTS;
                    $colunaF = $CHILDREN;
                    $colunaG = $RATE_CODE;
                    $colunaH = $BALANCE;
                    $colunaI = $ROOM;
                    $colunaJ = $Comentarios;
                    $colunaK = $COMPANY_NAME;
                    $colunaL = $Alteracao;
                    $colunaM = $ROOM_TYPE;
                    $colunaN = $data[4];

                    $dados_presentlist = $colunaA.';'.$colunaB.';'.$colunaC.';'.$colunaD.';'.$colunaE.';'.$colunaF.';'.$colunaG.';'.$colunaH.';'.$colunaI.';'.$colunaJ.';'.$colunaK.';'.$colunaL.';'.$colunaN;
                    $dados_criptografados = openssl_encrypt($dados_presentlist, $metodo, $chave, 0, $iv);
                    $dados_final = base64_encode($dados_criptografados);
                    
                    // Execute the SQL statement
                    $stmt->execute();
                }

                $stmt->close();
            }
            //Importar Arrivals
            else if (strpos($file_name, "saldos") !== false) {
                // Prepare the SQL statement for inserting data into the database
                $sql = "INSERT INTO $tabela_excel (dados_saldos, reserva) VALUES (?, ?)";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->bind_param("ss", $dados_final, $reserva_id);
    
                // Carrega o arquivo Excel
                $spreadsheet = IOFactory::load($tmp_name);

                // Seleciona a primeira planilha (índice 0)
                $worksheet = $spreadsheet->getActiveSheet();

                // Obtém todas as células da planilha como uma matriz
                $datas = $worksheet->toArray();

                $colunaA = '';
                $colunaB = 0; //Diarias
                $colunaC = 0; //AeB
                $colunaD = 0; //Credito
                $colunaE = 0; //Saldo
                $id_saldo = 0;

                foreach ($datas as $data) {

                if (!is_numeric($data[1]) || $data[4] != 'Check-In') {
                    continue;
                }

                if($data[1] == $colunaA){

                    $query =  $conexao->prepare("DELETE FROM $tabela_excel WHERE id = :id");
                    $query->execute(array('id' => $id_saldo));

                    $reserva = $colunaA; //Reserva
                    $diarias = floatval($colunaB) + floatval(str_replace(',', '', $data[5])); //Diarias
                    $aeb = floatval($colunaC) + floatval(str_replace(',', '', $data[8]))+floatval(str_replace(',', '', $data[9]))+floatval(str_replace(',', '', $data[10]))+floatval(str_replace(',', '', $data[11]))+floatval(str_replace(',', '', $data[12]))+floatval(str_replace(',', '', $data[13]))+floatval(str_replace(',', '', $data[14]))+floatval(str_replace(',', '', $data[15]))+floatval(str_replace(',', '', $data[17])); //AeB
                    $credito = floatval($colunaD) + floatval(str_replace(',', '', $data[23])) + floatval(str_replace(',', '', $data[21])); //Credito
                    $saldo = floatval($colunaE) + floatval(str_replace(',', '', $data[24])); //Saldo

                }else{

                    $reserva = $data[1]; //Reserva
                    $diarias = str_replace(',', '', $data[5]); //Diarias
                    $aeb = floatval(str_replace(',', '', $data[8]))+floatval(str_replace(',', '', $data[9]))+floatval(str_replace(',', '', $data[10]))+floatval(str_replace(',', '', $data[11]))+floatval(str_replace(',', '', $data[12]))+floatval(str_replace(',', '', $data[13]))+floatval(str_replace(',', '', $data[14]))+floatval(str_replace(',', '', $data[15]))+floatval(str_replace(',', '', $data[17])); //AeB
                    $credito = floatval(str_replace(',', '', $data[23]))+floatval(str_replace(',', '', $data[21])); //Credito
                    $saldo = str_replace(',', '', $data[24]); //Saldo

                }

                $id_saldo++;

                $colunaA = $reserva; //Reserva
                $reserva_id = $reserva; //Reserva
                $colunaB = $diarias; //Diarias
                $colunaC = $aeb; //AeB
                $colunaD = $credito; //Credito
                $colunaE = $saldo; //Saldo
                $colunaF = '0,00'; //Outros

                $dados_saldos = $colunaA.';'.number_format(floatval($colunaB), 2, ',', '.').';'.number_format(floatval($colunaC), 2, ',', '.').';'.number_format(floatval($colunaD), 2, ',', '.').';'.number_format(floatval($colunaE), 2, ',', '.').';'.$colunaF;
                $dados_criptografados = openssl_encrypt($dados_saldos, $metodo, $chave, 0, $iv);
                $dados_final = base64_encode($dados_criptografados);

                    // Execute the SQL statement
                    $stmt->execute();
                }
    
                // Close the file handle and statement
                $stmt->close();
            }
            
            } else {
                echo "Erro ao importar o arquivo $file_name.";
            }
        } else {
            echo "Invalid file format. Only CSV files are allowed.";
        }
    }

    //Designa de acordo aos Arrivals
    $query = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_arrivals WHERE id > 0");
    $query->execute();

        while($select = $query->fetch(PDO::FETCH_ASSOC)){
            $dados_arrivals = $select['dados_arrivals'];

            // Para descriptografar os dados
            $dados = base64_decode($dados_arrivals);
            $dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

            $dados_array = explode(';', $dados_decifrados);

            if($dados_array[7] != ''){
            $query2 = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_roomstatus SET room_status = :room_status WHERE room_status != :room_ocupado AND room_number LIKE :room_number");
            $query2->execute(array('room_status' => 'Designado', 'room_ocupado' => 'Ocupado', 'room_number' => '%'.$dados_array[7].'%'));
            }

        }

        //Cadastrar Room Types
        $query = $conexao->prepare("TRUNCATE $dir"."_excel_gestaorecepcao_roomtypes");
        $query->execute();

        $query = $conexao->prepare("SELECT room_type, COUNT(*) as count FROM $dir"."_excel_gestaorecepcao_roomstatus WHERE id > 0 GROUP BY room_type");
        $query->execute();
        while($select = $query->fetch(PDO::FETCH_ASSOC)){

        $query_insert = $conexao->prepare("INSERT INTO $dir"."_excel_gestaorecepcao_roomtypes (room_type, room_type_qtd) VALUES (:room_type, :room_type_qtd)");
        $query_insert->execute(array('room_type' => $select['room_type'], 'room_type_qtd' => $select['count']));

        }

        //Por Saldo em todos os guests in house
        $query_inhouse = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_inhouse WHERE id > 0");
        $query_inhouse->execute();
        while($select = $query_inhouse->fetch(PDO::FETCH_ASSOC)){
            $dados_presentlist = $select['dados_presentlist'];

        // Para descriptografar os dados
        $dados = base64_decode($dados_presentlist);
        $dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

        $dados_array = explode(';', $dados_decifrados);

        $reserva_pagamento_diaria = 0.00;
        $reserva_pagamento_aeb = 0.00;
        $reserva_pagamento_valor = 0.00;
        $reserva_pagamento_outros = 0.00;
        $reserva_pagamento_saldo = 0.00;
        $reserva_id = $dados_array[12];
        $dados_saldos = $reserva_id.';'.number_format(floatval($reserva_pagamento_diaria), 2, ',', '.').';'.number_format(floatval($reserva_pagamento_aeb), 2, ',', '.').';'.number_format(floatval($reserva_pagamento_valor), 2, ',', '.').';'.number_format(floatval($reserva_pagamento_saldo), 2, ',', '.').';'.number_format(floatval($reserva_pagamento_outros), 2, ',', '.');
        $dados_criptografados = openssl_encrypt($dados_saldos, $metodo, $chave, 0, $iv);
        $dados_final = base64_encode($dados_criptografados);

        $query_saldos = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_saldos WHERE reserva = '{$reserva_id}'");
        $query_saldos->execute();

        if($query_saldos->rowCount() == 0){
        $query = $conexao->prepare("INSERT INTO $dir"."_excel_gestaorecepcao_saldos (dados_saldos, reserva) VALUES (:dados_saldos, :reserva)");
        $query->execute(array('dados_saldos' => $dados_final, 'reserva' => $dados_array[12]));
        }
    }

    $query = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_saldos SET reserva = :reserva WHERE id > :id");
    $query->execute(array('reserva' => null, 'id' => 0));

    echo "<script>
        window.location.replace('gestao.php')
        </script>";
    exit();
} else {
    echo "<script>
        alert('Selecione todos os arquivos para gerar o Downtime')
        window.location.replace('index.php')
        </script>";
    exit();
}

// Close the database connection
$conn_mysqli->close();
?>
