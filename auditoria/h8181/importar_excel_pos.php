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
$arquivo_gerencial_2 = 0;
$arquivo_salesanalyze = 0;
$arquivo_inhouse = 0;
$arquivo_noshow = 0;
$arquivo_freestay_1 = 0;
$arquivo_freestay_2 = 0;
$arquivo_taxbase = 0;

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

// Check if the CSV files were uploaded successfully
if (!empty($_FILES["csvFile"]["name"]) && count($_FILES["csvFile"]["name"]) == 8) {
    $file_names = $_FILES["csvFile"]["name"];
    $file_tmp_names = $_FILES["csvFile"]["tmp_name"];

    for ($i = 0; $i < count($file_names); $i++) {
        $file_name = $file_names[$i];
        $tmp_name = $file_tmp_names[$i];

        // Check if the uploaded file is a CSV or XML
        if (strtolower(pathinfo($file_name, PATHINFO_EXTENSION)) == "csv") {
            // Read the CSV file
            $file_handle = fopen($tmp_name, "r");
            if ($file_handle !== FALSE) {
                $skip_first_line = true;

                if (strpos($file_name, ucfirst($dir)."_ManagerReport_") !== false) {
                    $arquivo_gerencial_1 = 1;
                    $arquivo_gerencial_2 = 1;
                }else if (strpos($file_name, ucfirst($dir)."_DaySheetSalesAnalyze_") !== false) {
                    $arquivo_salesanalyze = 1;
                }else if (strpos($file_name, ucfirst($dir)."_PresentList_") !== false) {
                    $arquivo_inhouse = 1;
                }else if (strpos($file_name, ucfirst($dir)."_NoShowControl_") !== false) {
                    $arquivo_noshow = 1;
                }else if (strpos($file_name, ucfirst($dir)."_FreeStay_") !== false) {
                    $arquivo_freestay_1 = 1;
                    $arquivo_freestay_2 = 1;
                }else if (strpos($file_name, ucfirst($dir)."_TaxCalculation_") !== false) {
                    $arquivo_taxbase = 1;
                }else{
                    echo "<script>
                    alert('Arquivo Selecionado Invalido')
                    window.location.replace('index.php')
                    </script>";
                    exit();
                }
    
                //Importar Manager Report 1
                if (strpos($file_name, ucfirst($dir)."_ManagerReport_") !== false && strlen($file_name) == 32) {
                $id = 0;
                $dados_gerencial1 = [];
    
                // Process each row in the CSV file
                while (($data = fgetcsv($file_handle, 10000, ";")) !== FALSE) {
                    if ($skip_first_line) {
                        $skip_first_line = false;
                        continue;
                    }
                    $id++;
                    $colunaA = str_replace('/', '-', substr($data[2], 0, 10));
                    $colunaB = $data[5];
                    $colunaC = $data[8];
                    $colunaD = $data[9];
                    $colunaE = $data[10];

                    $colunaA_partes = explode("-", $colunaA);
                    $colunaA_formatada = $colunaA_partes[2] . "-" . $colunaA_partes[1] . "-" . $colunaA_partes[0];
    
                    // Adicione os dados a um array associativo
                    $dados_gerencial1[] = [
                        'id' => $id,
                        'data_importacao' => $colunaA_formatada,
                        'item_nome' => $colunaB,
                        'valor_dia' => $colunaC,
                        'valor_mes' => $colunaD,
                        'valor_ano' => $colunaE
                    ];
                }

                fclose($file_handle);

            }else

            //Importar Manager Report 2
            if (strpos($file_name, ucfirst($dir)."_ManagerReport_") !== false && strlen($file_name) == 34) {
                $dados_gerencial2 = [];
                $id = 22;
    
                // Process each row in the CSV file
                while (($data = fgetcsv($file_handle, 10000, ";")) !== FALSE) {
                    if ($skip_first_line) {
                        $skip_first_line = false;
                        continue;
                    }
                    $id++;
                    $colunaA = str_replace('/', '-', substr($data[2], 0, 10));
                    $colunaB = $data[5];
                    $colunaC = $data[10];
                    $colunaD = $data[11];
                    $colunaE = $data[12];

                    $colunaA_partes = explode("-", $colunaA);
                    $colunaA_formatada = $colunaA_partes[2] . "-" . $colunaA_partes[1] . "-" . $colunaA_partes[0];
    
                    // Adicione os dados a um array associativo
                    $dados_gerencial2[] = [
                        'id' => $id,
                        'data_importacao' => $colunaA_formatada,
                        'item_nome' => $colunaB,
                        'valor_dia' => $colunaC,
                        'valor_mes' => $colunaD,
                        'valor_ano' => $colunaE
                    ];
                }

                fclose($file_handle);

            }else
            
            //Importar Sales Analyze
            if (strpos($file_name, ucfirst($dir)."_DaySheetSalesAnalyze_") !== false) {
                $dados_salesanalyze = [];
    
                // Process each row in the CSV file
                while (($data = fgetcsv($file_handle, 10000, ";")) !== FALSE) {
                    if ($skip_first_line) {
                        $skip_first_line = false;
                        continue;
                    }
                    $colunaA = str_replace('/', '-', substr($data[1], 0, 10));
                    $colunaB = $data[4];
                    $colunaC = str_replace(',','.', $data[9]);
                    $colunaD = str_replace(',','.', $data[12]);
                    $colunaE = str_replace(',','.', $data[15]);

                    $colunaA_partes = explode("-", $colunaA);
                    $colunaA_formatada = $colunaA_partes[2] . "-" . $colunaA_partes[1] . "-" . $colunaA_partes[0];
    
                    // Adicione os dados a um array associativo
                    $dados_salesanalyze[] = [
                        'data_importacao' => $colunaA_formatada,
                        'item_nome' => $colunaB,
                        'valor_dia' => $colunaC,
                        'valor_mes' => $colunaD,
                        'valor_ano' => $colunaE
                    ];
                }

                $_SESSION['dados_salesanalyze'] = $dados_salesanalyze;
                fclose($file_handle);

            }else
            
            //Importar List of In House
            if (strpos($file_name, ucfirst($dir)."_PresentList_") !== false) {
                $dados_presentlist = [];

                $id = 0;
    
                // Process each row in the CSV file
                while (($data = fgetcsv($file_handle, 10000, ";")) !== FALSE) {
                    if ($skip_first_line) {
                        $skip_first_line = false;
                        continue;
                    }
                    $colunaA = $data[1];
                    $colunaB = str_replace('/', '-', substr($data[3], 0, 10));
                    $colunaC = str_replace('/', '-', substr($data[4], 0, 10));
                    $colunaD = $data[6];
                    $colunaE = $data[7];
                    $colunaF = $data[14] * (-1);
                    $colunaG = $data[21];

                    $colunaB_partes = explode("-", $colunaB);
                    $colunaB_formatada = $colunaB_partes[2] . "-" . $colunaB_partes[1] . "-" . $colunaB_partes[0];
                    $colunaC_partes = explode("-", $colunaC);
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[1] . "-" . $colunaC_partes[0];
    
                    $id++;
                    // Adicione os dados a um array associativo
                    $dados_presentlist[] = [
                        'id' => $id,
                        'guest_name' => $colunaA,
                        'checkin' => $colunaB_formatada,
                        'checkout' => $colunaC_formatada,
                        'adultos' => $colunaD,
                        'criancas' => $colunaE,
                        'room_balance' => $colunaF,
                        'room_number' => $colunaG,
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

                $_SESSION['dados_presentlist'] = $dados_presentlist;
                fclose($file_handle);

            }else
            
            //Importar No Show
            if (strpos($file_name, ucfirst($dir)."_NoShowControl_") !== false) {
                $dados_noshow = [];
    
                // Process each row in the CSV file
                while (($data = fgetcsv($file_handle, 10000, ";")) !== FALSE) {
                    if ($skip_first_line) {
                        $skip_first_line = false;
                        continue;
                    }
                    $colunaA = $data[1];
                    $colunaB = $data[2];
                    $colunaC = str_replace('/', '-', substr($data[6], 0, 10));
                    $colunaD = str_replace('/', '-', substr($data[7], 0, 10));
                    $colunaE = $data[8];

                    $colunaC_partes = explode("-", $colunaC);
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[1] . "-" . $colunaC_partes[0];
                    $colunaD_partes = explode("-", $colunaD);
                    $colunaD_formatada = $colunaD_partes[2] . "-" . $colunaD_partes[1] . "-" . $colunaD_partes[0];

                    // Adicione os dados a um array associativo
                    $dados_noshow[] = [
                        'guest_name' => $colunaA,
                        'reserva' => $colunaB,
                        'checkin' => $colunaC_formatada,
                        'checkout' => $colunaD_formatada,
                        'room_balance' => $colunaE
                    ];
                }
    
                $_SESSION['dados_noshow'] = $dados_noshow;
                fclose($file_handle);

            }else
            
            //Importar Free Stay 1 e 2
            if (strpos($file_name, ucfirst($dir)."_FreeStay_") !== false && strlen($file_name) == 27) {
                $dados_freestay = [];
                $id = 0;
    
                // Process each row in the CSV file
                while (($data = fgetcsv($file_handle, 10000, ";")) !== FALSE) {
                    if ($skip_first_line) {
                        $skip_first_line = false;
                        continue;
                    }
                    $colunaA = $data[1];
                    $colunaB = $data[8];
                    $colunaC = str_replace('/', '-', substr($data[9], 0, 10));
                    $colunaD = str_replace('/', '-', substr($data[10], 0, 10));
                    $colunaE = $data[11];
                    $colunaF = $data[16];

                    $colunaC_partes = explode("-", $colunaC);
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[1] . "-" . $colunaC_partes[0];
                    $colunaD_partes = explode("-", $colunaD);
                    $colunaD_formatada = $colunaD_partes[2] . "-" . $colunaD_partes[1] . "-" . $colunaD_partes[0];

                    $id++;

                    // Adicione os dados a um array associativo
                    $dados_freestay[] = [
                        'id' => $id,
                        'guest_name' => $colunaA,
                        'reserva' => $colunaB,
                        'checkin' => $colunaC_formatada,
                        'checkout' => $colunaD_formatada,
                        'room_number' => $colunaE,
                        'tipo_cortesia' => $colunaF,
                        'comentario' => ''
                    ];
                }

                $_SESSION['dados_freestay'] = $dados_freestay;
                fclose($file_handle);

            }else
            
            //Importar Tax Base
            if (strpos($file_name, ucfirst($dir)."_TaxCalculation_") !== false) {
                $dados_taxbase = [];
    
                // Process each row in the CSV file
                while (($data = fgetcsv($file_handle, 10000, ";")) !== FALSE) {
                    if ($skip_first_line) {
                        $skip_first_line = false;
                        continue;
                    }
                    $colunaA = $data[1];
                    $colunaB = $data[2];
                    $colunaC = str_replace('/', '-', substr($data[0], 0, 10));
                    $colunaD = $data[3];
                    $colunaE = '';
                    $colunaF = $data[5];
                    $colunaG = $data[6];
                    $colunaH = $data[7];
                    $colunaI = $data[14];
                    $colunaJ = $data[9];
                    $colunaK = $data[10];
                    $colunaL = $data[12];
                    $colunaM = $data[11];
                    $colunaN = $data[17];
                    $colunaO = $data[13];

                    $colunaC_partes = explode("-", $colunaC);
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[1] . "-" . $colunaC_partes[0];

                    // Adicione os dados a um array associativo
                    $dados_taxbase[] = [
                        'rps_num' => $colunaA,
                        'situacao' => $colunaB,
                        'data_emissao' => $colunaC_formatada,
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

$arquivos_selecionados = $arquivo_gerencial_1 + $arquivo_gerencial_2 + $arquivo_salesanalyze + $arquivo_inhouse + $arquivo_noshow + $arquivo_freestay_1 + $arquivo_freestay_2 + $arquivo_taxbase;

if($arquivos_selecionados == 8){

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

$_SESSION['dados_gerencial'] = array_merge($dados_gerencial1, $dados_gerencial2);

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