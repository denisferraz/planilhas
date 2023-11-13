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

// Check if the XML files were uploaded successfully
if (!empty($_FILES["xmlFile"]["name"]) && count($_FILES["xmlFile"]["name"]) == 3) {
    $file_names = $_FILES["xmlFile"]["name"];
    $file_tmp_names = $_FILES["xmlFile"]["tmp_name"];

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
        if (strtolower(pathinfo($file_name, PATHINFO_EXTENSION)) == "xml") {
            // Read the XML file
            $file_handle = fopen($tmp_name, "r");
            if ($file_handle !== FALSE) {
                $skip_first_line = true;

                if (strpos($file_name, "hkroomstatusperroom_") !== false) {
                    $tabela_excel = "$dir"."_excel_gestaorecepcao_roomstatus";
                }else if (strpos($file_name, "res_detail_") !== false) {
                    $tabela_excel = "$dir"."_excel_gestaorecepcao_arrivals";
                }else if (strpos($file_name, "gibyroom_") !== false) {
                    $tabela_excel = "$dir"."_excel_gestaorecepcao_inhouse";
                }
    
                // Delete existing records from the table
                $sql = "DELETE FROM $tabela_excel WHERE id != -1";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->execute();
    
                // Reset the auto-increment value for ID
                $sql = "ALTER TABLE $tabela_excel AUTO_INCREMENT = 1";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->execute();
    
                //Importar Room Status
                if (strpos($file_name, "hkroomstatusperroom_") !== false) {
                // Prepare the SQL statement for inserting data into the database
                $sql = "INSERT INTO $tabela_excel (room_number, room_status, room_type) VALUES (?, ?, ?)";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->bind_param("sss", $colunaA, $colunaB, $colunaC);
    
                // Process each row in the XML file
                $xml = simplexml_load_file($tmp_name);
                foreach ($xml->LIST_G_ROOM_STATUS->G_ROOM_STATUS as $row1) {
                    foreach ($row1->LIST_G_ROOM_NUMBER->G_ROOM_NUMBER as $row2) {

                        if((string)$row2->LIST_G_INFO->G_INFO->RESORT != strtoupper($dir)){
                            continue;
                        }

                    if ((string)$row2->LIST_G_INFO->G_INFO->HK_STATUS == 'OCC'){
                        $HK_STATUS = 'Ocupado';
                    }else{
                        $HK_STATUS = 'Vago';
                    }

                    if ((string)$row2->LIST_G_INFO->G_INFO->ROOM_STATUS == 'CL' && $HK_STATUS == 'Vago'){
                        $ROOM_STATUS = 'Limpo';
                    }else if ((string)$row2->LIST_G_INFO->G_INFO->ROOM_STATUS == 'DI' || $HK_STATUS == 'Ocupado'){
                        $ROOM_STATUS = 'Sujo';
                    }else{
                        $ROOM_STATUS = 'Bloqueado';
                    }

                    $colunaA = (string)$row2->ROOM;
                    $colunaB = $ROOM_STATUS;
                    $colunaC = (string)$row2->LIST_G_INFO->G_INFO->ROOM_TYPE;

                    // Execute the SQL statement
                    $stmt->execute();
                }}

    
                // Close the file handle and statement
                $stmt->close();
            }
            //Importar Arrivals
            else if (strpos($file_name, "res_detail_") !== false) {
                // Prepare the SQL statement for inserting data into the database
                $sql = "INSERT INTO $tabela_excel (dados_arrivals) VALUES (?)";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->bind_param("s", $dados_final);
    
                // Process each row in the XML file
                $xml = simplexml_load_file($tmp_name);
                foreach ($xml->LIST_G_GROUP_BY1->G_GROUP_BY1->LIST_G_RESERVATION->G_RESERVATION as $row) {

                    if((string)$row->RESORT != strtoupper($dir)){
                        continue;
                    }

                    // Extract the date strings
                    $truncBegin = (string)$row->TRUNC_BEGIN;
                    $truncEnd = (string)$row->TRUNC_END;

                    // Convert the date strings to DateTime objects
                    $beginDate = DateTime::createFromFormat('d-M-y', $truncBegin);
                    $endDate = DateTime::createFromFormat('d-M-y', $truncEnd);

                    // Calculate the time difference
                    $timeDifference = $endDate->diff($beginDate);

                    // Access the number of nights (days)
                    $noites = $timeDifference->days;

                    $colunaA = (string)$row->FULL_NAME;
                    $colunaB = $noites;
                    $colunaC = (string)$row->ADULTS;
                    $colunaD = (string)$row->CHILDREN;
                    $colunaE = (string)$row->ROOM_CATEGORY_LABEL;
                    $colunaF = (string)$row->RATE_CODE;
                    $colunaG = "";
                    $colunaH = (string)$row->DISP_ROOM_NO;
                    $colunaI = 'Pendente'; //alteração

                    $dados_arrivalslist = $colunaA.';'.$colunaB.';'.$colunaC.';'.$colunaD.';'.$colunaE.';'.$colunaF.';'.$colunaG.';'.$colunaH.';'.$colunaI;
                    $dados_criptografados = openssl_encrypt($dados_arrivalslist, $metodo, $chave, 0, $iv);
                    $dados_final = base64_encode($dados_criptografados);

                    // Execute the SQL statement
                    $stmt->execute();
                }
    
                // Close the file handle and statement
                $stmt->close();
            }
            //Importar In House
            else if (strpos($file_name, "gibyroom_") !== false) {
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
                $id_inhouse = 0;

                // Process each row in the XML file
                $xml = simplexml_load_file($tmp_name);
                foreach ($xml->LIST_G_ROOM->G_ROOM as $row) {

                    if((string)$row->RESORT != strtoupper($dir)){
                        continue;
                    }

                    // Extract the date strings
                    $truncBegin = (string)$row->ARRIVAL;
                    $truncEnd = (string)$row->DEPARTURE;

                    // Convert the date strings to DateTime objects
                    $beginDate = DateTime::createFromFormat('d-m-y', $truncBegin);
                    $endDate = DateTime::createFromFormat('d-m-y', $truncEnd);

                    if((string)$row->ROOM == $colunaI){

                    $query =  $conexao->prepare("DELETE FROM $tabela_excel WHERE id = :id");
                    $query->execute(array('id' => $id_inhouse));

                    $FULL_NAME = $colunaA.' - '.(string)$row->FULL_NAME;
                    $Checkin = $colunaB;
                    $Checkout = $colunaC;
                    $noites = $colunaD;
                    $ADULTS = $colunaE + (string)$row->ADULTS;
                    $CHILDREN = $colunaF;
                    $RATE_CODE = $colunaG;
                    $BALANCE = $colunaH + (string)$row->BALANCE;
                    $ROOM = $colunaI;
                    $Comentarios = $colunaJ;
                    $COMPANY_NAME = $colunaK;
                    $Alteracao = $colunaL;

                    }else{

                    $FULL_NAME = (string)$row->FULL_NAME;
                    $Checkin = $beginDate->format('Y-m-d');
                    $Checkout = $endDate->format('Y-m-d');
                    $noites = 1;
                    $ADULTS = (string)$row->ADULTS;
                    $CHILDREN = (string)$row->CHILDREN;
                    $RATE_CODE = (string)$row->RATE_CODE;
                    $BALANCE = (string)$row->BALANCE;
                    $ROOM = (string)$row->ROOM;
                    $Comentarios = "";
                    $COMPANY_NAME = (string)$row->COMPANY_NAME;
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

                    $dados_presentlist = $colunaA.';'.$colunaB.';'.$colunaC.';'.$colunaD.';'.$colunaE.';'.$colunaF.';'.$colunaG.';'.$colunaH.';'.$colunaI.';'.$colunaJ.';'.$colunaK.';'.$colunaL;
                    $dados_criptografados = openssl_encrypt($dados_presentlist, $metodo, $chave, 0, $iv);
                    $dados_final = base64_encode($dados_criptografados);
                    
                    // Execute the SQL statement
                    $stmt->execute();
                }

                $stmt->close();
            }
            
            } else {
                echo "Erro ao importar o arquivo $file_name.";
            }
        } else {
            echo "Invalid file format. Only CSV files are allowed.";
        }
    }

    $query = $conexao->prepare("SELECT * FROM $dir"."_excel_gestaorecepcao_arrivals WHERE id > 0");
    $query->execute();
    $query_qtd = $query->rowCount();

    if($query_qtd > 0){
        while($select = $query->fetch(PDO::FETCH_ASSOC)){
            $dados_arrivals = $select['dados_arrivals'];

            // Para descriptografar os dados
            $dados = base64_decode($dados_arrivals);
            $dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

            $dados_array = explode(';', $dados_decifrados);
            $room_number = $dados_array[7];

            if($room_number != ''){
            $query2 = $conexao->prepare("UPDATE $dir"."_excel_gestaorecepcao_roomstatus SET room_status = 'Designado' WHERE room_number = '{$room_number}'");
            $query2->execute();
            }
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
