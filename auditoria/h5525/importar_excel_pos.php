<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');
require '../../../vendor/autoload.php';

$dir = substr(__DIR__, -5);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    window.location.replace('../../index.html')
    </script>";
    exit();
}


$arquivo_gerencial_1 = 0;
$arquivo_salesanalyze = 0;
$arquivo_inhouse = 0;
$arquivo_noshow = 0;
$arquivo_freestay_1 = 0;
$arquivo_taxbase = 0;

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

// Check if the CSV files were uploaded successfully
if (!empty($_FILES["xmlFile"]["name"]) && count($_FILES["xmlFile"]["name"]) == 6) {
    $file_names = $_FILES["xmlFile"]["name"];
    $file_tmp_names = $_FILES["xmlFile"]["tmp_name"];

    for ($i = 0; $i < count($file_names); $i++) {
        $file_name = $file_names[$i];
        $tmp_name = $file_tmp_names[$i];

        // Check if the uploaded file is a CSV or XML
        if (strtolower(pathinfo($file_name, PATHINFO_EXTENSION)) == "xml") {
            // Read the CSV file
            $file_handle = fopen($tmp_name, "r");
            if ($file_handle !== FALSE) {
                $skip_first_line = true;

                if (strpos($file_name, "manager_report_") !== false) {
                    $arquivo_gerencial_1 = 1;
                }else if (strpos($file_name, "trial_balance_") !== false) {
                    $arquivo_salesanalyze = 1;
                }else if (strpos($file_name, "gibyroom_") !== false) {
                    $arquivo_inhouse = 1;
                }else if (strpos($file_name, "nanoshow_") !== false) {
                    $arquivo_noshow = 1;
                }else if (strpos($file_name, "gi_c_h_") !== false) {
                    $arquivo_freestay_1 = 1;
                }else if (strpos($file_name, "foliotax_04_") !== false) {
                    $arquivo_taxbase = 1;
                }else{
                    echo "<script>
                    alert('Arquivo Selecionado Invalido')
                    window.location.replace('index.php')
                    </script>";
                    exit();
                }
    
                //Importar Manager Report 1
                if (strpos($file_name, "manager_report_") !== false) {
                $id = 0;
                $dados_gerencial1 = [];
    
                // Process each row in the XML file
                $xml = simplexml_load_file($tmp_name);
                foreach ($xml->LIST_G_MASTER_VALUE_ORDER->G_MASTER_VALUE_ORDER as $row1) {

                    if((string)$row1->RESORT != strtoupper($dir)){
                        continue;
                    }

                    foreach ($row1->LIST_G_LAST_YEAR_01->G_LAST_YEAR_01->LIST_G_CROSS->G_CROSS->LIST_G_MASTER_VALUE->G_MASTER_VALUE as $row) {

                    $id++;
                    $colunaA = $_SESSION['data_auditoria'];
                    $colunaB = (string)$row->DESCRIPTION;

                    $qtd_id = 1;
                    foreach ($row->LIST_G_HEADING_1_ORDER->G_HEADING_1_ORDER as $row2) {
                        if($qtd_id == 1){
                    $colunaC = (string)$row2->LIST_G_SUM_AMOUNT->G_SUM_AMOUNT->SUM_AMOUNT;
                        }else if($qtd_id == 2){
                    $colunaD = (string)$row2->LIST_G_SUM_AMOUNT->G_SUM_AMOUNT->SUM_AMOUNT;
                        }else if($qtd_id == 3){
                    $colunaE = (string)$row2->LIST_G_SUM_AMOUNT->G_SUM_AMOUNT->SUM_AMOUNT;
                        }else{
                            continue;
                        }
                    $qtd_id++;
                    }
    
                    // Adicione os dados a um array associativo
                    $dados_gerencial1[] = [
                        'id' => $id,
                        'data_importacao' => $colunaA,
                        'item_nome' => $colunaB,
                        'valor_dia' => $colunaC,
                        'valor_mes' => $colunaD,
                        'valor_ano' => $colunaE
                    ];
                }}

                fclose($file_handle);

            }else
            
            //Importar Sales Analyze
            if (strpos($file_name, "trial_balance_") !== false) {
                $dados_salesanalyze = [];

                // Process each row in the XML file
                $xml = simplexml_load_file($tmp_name);
                foreach ($xml->LIST_G_TRX_TYPE->G_TRX_TYPE as $row1) {
                    foreach ($row1->LIST_G_TRX_CODE->G_TRX_CODE as $row) {

                    $colunaA = $_SESSION['data_auditoria'];
                    $colunaB = (string)$row->TRX_CODE;
                    $colunaC = str_replace(',','.', (string)$row->NET_AMOUNT);
                    $colunaD = str_replace(',','.', (string)$row->TB_AMOUNT);
    
                    // Adicione os dados a um array associativo
                    $dados_salesanalyze[] = [
                        'data_importacao' => $colunaA,
                        'item_nome' => $colunaB,
                        'valor_dia' => $colunaC,
                        'valor_pgto' => $colunaD
                    ];
                }}

                $_SESSION['dados_salesanalyze'] = $dados_salesanalyze;
                fclose($file_handle);

            }else
            
            //Importar List of In House
            if (strpos($file_name, "gibyroom_") !== false) {
                $dados_presentlist = [];

                $id = 0;

                //Duplicidades
                $colunaA = '';
                $colunaB = '';
                $colunaC = '';
                $colunaE = 0; //Adultos
                $colunaF = '';
                $colunaH = 0; //Balance
                $colunaI = 0; //Room
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

                    $indexToDelete = array_search($id_inhouse, array_column($dados_presentlist, 'id'));
                    unset($dados_presentlist[$indexToDelete]);

                    $FULL_NAME = $colunaA;
                    $Checkin = $colunaB;
                    $Checkout = $colunaC;
                    $ADULTS = $colunaE + (string)$row->ADULTS;
                    $CHILDREN = $colunaF;
                    $BALANCE = $colunaH + (string)$row->BALANCE;
                    $ROOM = $colunaI;

                    }else{

                    $FULL_NAME = (string)$row->FULL_NAME;
                    $Checkin = $beginDate->format('Y-m-d');
                    $Checkout = $endDate->format('Y-m-d');
                    $ADULTS = (string)$row->ADULTS;
                    $CHILDREN = (string)$row->CHILDREN;
                    $BALANCE = (string)$row->BALANCE;
                    $ROOM = (string)$row->ROOM;

                    }

                    $id_inhouse++;

                    $colunaA = $FULL_NAME;
                    $colunaB = $Checkin;
                    $colunaC = $Checkout;
                    $colunaE = $ADULTS;
                    $colunaF = $CHILDREN;
                    $colunaH = $BALANCE;
                    $colunaI = $ROOM;

                    $id++;
                    // Adicione os dados a um array associativo
                    $dados_presentlist[] = [
                        'id' => $id,
                        'guest_name' => $colunaA,
                        'checkin' => $colunaB,
                        'checkout' => $colunaC,
                        'adultos' => $colunaE,
                        'criancas' => $colunaF,
                        'room_balance' => $colunaH,
                        'room_number' => $colunaI,
                        'auditoria_diarias' => '',
                        'auditoria_extras' => '',
                        'auditoria_garantia' => '',
                        'auditoria_valor' => '',
                        'auditoria_pasta_limpa' => 'Sim',
                        'auditoria_pasta_pdv' => 0,
                        'auditoria_pasta_pasta' => 0,
                        'auditoria_pasta_ass' => 0,
                        'auditoria_fnrh' => 'Sim',
                        'auditoria_doc' => 'Sim'
                    ];
                }

                fclose($file_handle);

            }else
            
            //Importar No Show
            if (strpos($file_name, "nanoshow_") !== false) {
                $dados_noshow = [];
    
                // Process each row in the XML file
                $xml = simplexml_load_file($tmp_name);
                foreach ($xml->LIST_G_ROOM_CLASS->G_ROOM_CLASS->LIST_G_RESV_NAME_ID->G_RESV_NAME_ID as $row) {

                    if((string)$row->RESORT != strtoupper($dir)){
                        continue;
                    }

                    // Extract the date strings
                    $truncBegin = (string)$row->ARRIVAL;
                    $truncEnd = (string)$row->DEPARTURE;

                    // Convert the date strings to DateTime objects
                    $beginDate = DateTime::createFromFormat('d-m-y', $truncBegin);
                    $endDate = DateTime::createFromFormat('d-m-y', $truncEnd);

                    $colunaA = (string)$row->FULL_NAME;
                    $colunaB = (string)$row->CONFIRMATION_NO;
                    $colunaC = $beginDate->format('Y-m-d');
                    $colunaD = $endDate->format('Y-m-d');
                    $colunaE = (string)$row->SHARE_AMOUNT;

                    // Adicione os dados a um array associativo
                    $dados_noshow[] = [
                        'guest_name' => $colunaA,
                        'reserva' => $colunaB,
                        'checkin' => $colunaC,
                        'checkout' => $colunaD,
                        'room_balance' => $colunaE
                    ];
                }
    
                $_SESSION['dados_noshow'] = $dados_noshow;
                fclose($file_handle);

            }else
            
            //Importar Free Stay 
            if (strpos($file_name, "gi_c_h_") !== false) {
                $dados_freestay = [];
                $id = 0;

                // Process each row in the XML file
                $xml = simplexml_load_file($tmp_name);
                if ($xml->LIST_G_COMP_HOUSE->count() > 0) {
                foreach ($xml->LIST_G_COMP_HOUSE as $row) {

                    if((string)$row->RESORT != strtoupper($dir)){
                        continue;
                    }

                    // Extract the date strings
                    $truncBegin = (string)$row->ARRIVAL;
                    $truncEnd = (string)$row->DEPARTURE;

                    // Convert the date strings to DateTime objects
                    $beginDate = DateTime::createFromFormat('d-m-y', $truncBegin);
                    $endDate = DateTime::createFromFormat('d-m-y', $truncEnd);
    
                    $colunaA = (string)$row->FULL_NAME;
                    $colunaB = (string)$row->FULL_NAME;
                    $colunaC = $beginDate->format('Y-m-d');
                    $colunaD = $endDate->format('Y-m-d');
                    $colunaE = (string)$row->ROOM;
                    $colunaF = (string)$row->FULL_NAME;

                    $id++;

                    // Adicione os dados a um array associativo
                    $dados_freestay[] = [
                        'id' => $id,
                        'guest_name' => $colunaA,
                        'reserva' => $colunaB,
                        'checkin' => $colunaC,
                        'checkout' => $colunaD,
                        'room_number' => $colunaE,
                        'tipo_cortesia' => $colunaF,
                        'comentario' => ''
                    ];
                }}

                $_SESSION['dados_freestay'] = $dados_freestay;
                fclose($file_handle);

            }else
            
            //Importar Tax Base
            if (strpos($file_name, "foliotax_04_") !== false) {
                $dados_taxbase = [];

                // Process each row in the XML file
                $xml = simplexml_load_file($tmp_name);
                foreach ($xml->LIST_G_CROSS->G_CROSS->LIST_G_BILL_NO->G_BILL_NO as $row) {

                    if((string)$row->RESORT != strtoupper($dir)){
                        continue;
                    }

                    // Extract the date strings
                    $truncBegin = (string)$row->BUSINESS_DATE;

                    // Convert the date strings to DateTime objects
                    $beginDate = DateTime::createFromFormat('d-M-y', $truncBegin);
    
                    $colunaA = (string)$row->BILL_NO;
                    $colunaB = (string)$row->FOLIO_TYPE;
                    $colunaC = $beginDate->format('Y-m-d');
                    $colunaD = (string)$row->DISPLAY_NAME;
                    $colunaE = (string)$row->C_T_S_NAME;
                    $colunaF = '';
                    $colunaG = (string)$row->ROOM;
                    $colunaH = (string)$row->TOTAL_NET;
                    $colunaI = (string)$row->LIST_G_TAX_GROUP->G_TAX_GROUP->LIST_G_GROSS_AMT->G_GROSS_AMT->NET_AMT;
                    $colunaJ = floatval((string)$row->TOTAL_NET) - floatval((string)$row->LIST_G_TAX_GROUP->G_TAX_GROUP->LIST_G_GROSS_AMT->G_GROSS_AMT->NET_AMT);
                    $colunaK = 0;
                    $colunaL = (string)$row->TOTAL_NET;
                    $colunaM = 0;
                    $colunaN = 0;
                    $colunaO = 0;

                    // Adicione os dados a um array associativo
                    $dados_taxbase[] = [
                        'rps_num' => $colunaA,
                        'situacao' => $colunaB,
                        'data_emissao' => $colunaC,
                        'guest_name' => $colunaD,
                        'guest_empresa' => $colunaE,
                        'documento_num' => $colunaF,
                        'room_number' => $colunaG,
                        'valor_nf' => $colunaH,
                        'valor_base_iss' => $colunaI,
                        'valor_iss' => $colunaJ,
                        'valor_iss_retido' => $colunaK,
                        'valor_servico' => $colunaL,
                        'valor_aeb' => $colunaM,
                        'valor_diversos' => $colunaN,
                        'valor_evento' => $colunaO
                    ];
                }

                $_SESSION['dados_taxbase'] = $dados_taxbase;
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

$arquivos_selecionados = $arquivo_gerencial_1 + $arquivo_salesanalyze + $arquivo_inhouse + $arquivo_noshow + $arquivo_freestay_1 + $arquivo_taxbase;

if($arquivos_selecionados == 6){

$room_numbers = []; // Array auxiliar para rastrear 'room_number' duplicadas

foreach ($dados_presentlist as $index => $dados) {
    $room_number = $dados['room_number'];

    // Verifica se 'room_number' já foi encontrado
    if (in_array($room_number, $room_numbers)) {
        // Remove o item duplicado
        unset($dados_presentlist[$index]);
    } else {
        // Adiciona 'room_number' ao array auxiliar
        $room_numbers[] = $room_number;
    }
}

// Reindexa o array após a remoção dos itens duplicados
$_SESSION['dados_presentlist'] = array_values($dados_presentlist);

$_SESSION['status_auditoria'] = 'Em Andamento Pos';

$_SESSION['comentario_gerencial'] = '';
$_SESSION['comentario_bac'] = '';
$_SESSION['comentario_garantias'] = '';
$_SESSION['comentario_taxbase'] = '';

$_SESSION['freestay'] = 0;
$_SESSION['gerencial'] = 0;
$_SESSION['taxbase'] = 0;
$_SESSION['controlebac'] = 0;
$_SESSION['Garantias'] = 0;

$_SESSION['dados_gerencial'] = $dados_gerencial1;

echo "<script>
    window.location.replace('auditoria.php')
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