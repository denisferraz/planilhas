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

$_SESSION['data_auditoria'] = mysqli_real_escape_string($conn_mysqli, $_POST['data_auditoria']);
$_SESSION['limite_credito'] = mysqli_real_escape_string($conn_mysqli, $_POST['limite_credito']);

$arquivo_ratecheck = 0;
$arquivo_creditlimit = 0;

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

// Check if the CSV files were uploaded successfully
if (!empty($_FILES["csvFile"]["name"]) && count($_FILES["csvFile"]["name"]) == 2) {
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

                if (strpos($file_name, ucfirst($dir)."_RateCheck_") !== false) {
                    $arquivo_ratecheck = 1;
                }else if (strpos($file_name, ucfirst($dir)."_CreditLimitList_") !== false) {
                    $arquivo_creditlimit = 1;
                }else{
                    echo "<script>
                    alert('Arquivo Selecionado Invalido')
                    window.location.replace('index.php')
                    </script>";
                    exit();
                }
    
                //Importar Rate Check
                if (strpos($file_name, ucfirst($dir)."_RateCheck_") !== false) {
                    $dados_ratecheck = [];
                    $id = 0;

                    // Process each row in the CSV file
                    while (($data = fgetcsv($file_handle, 10000, ";")) !== FALSE) {
                        if ($skip_first_line) {
                            $skip_first_line = false;
                            continue;
                        }
                        
                        $colunaA = $data[1];
                        $colunaB = $data[2];
                        $colunaC = str_replace('/', '-', substr($data[3], 0, 10));
                        $colunaD = str_replace('/', '-', substr($data[4], 0, 10));
                        $colunaE = $data[10];
                        $colunaF = $data[12];
                    
                        $colunaC_partes = explode("-", $colunaC);
                        $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[1] . "-" . $colunaC_partes[0];
                        $colunaD_partes = explode("-", $colunaD);
                        $colunaD_formatada = $colunaD_partes[2] . "-" . $colunaD_partes[1] . "-" . $colunaD_partes[0];
                    
                        $id++;

                        // Adicione os dados a um array associativo
                        $dados_ratecheck[] = [
                            'id' => $id,
                            'room_number' => $colunaA,
                            'guest_name' => $colunaB,
                            'checkin' => $colunaC_formatada,
                            'checkout' => $colunaD_formatada,
                            'ratecode' => $colunaE,
                            'room_rate' => $colunaF,
                            'comentario' => ''
                        ];
                    }

                    // Deleta duplicidades
                    $indicesParaExcluir = [];

                    // Itere sobre os dados_ratecheck para encontrar índices de linhas a serem excluídas
                    for ($k = 0; $k < count($dados_ratecheck); $k++) {
                        for ($j = $k + 1; $j < count($dados_ratecheck); $j++) {
                            if (
                                $dados_ratecheck[$k]['room_number'] === $dados_ratecheck[$j]['room_number'] &&
                                $dados_ratecheck[$k]['guest_name'] === $dados_ratecheck[$j]['guest_name']
                            ) {
                                // Adicione o índice da linha a ser excluída ao array
                                $indicesParaExcluir[] = $j;
                            }
                        }
                    }

                    // Remova as linhas duplicadas com base nos índices coletados
                    foreach ($indicesParaExcluir as $indice) {
                        unset($dados_ratecheck[$indice]);
                    }
                    
                $dados_ratecheck = array_values($dados_ratecheck);
                $_SESSION['dados_ratecheck'] = $dados_ratecheck;
                fclose($file_handle);

            }else 
            
            //Importar Saldo Elevado
            if (strpos($file_name, ucfirst($dir)."_CreditLimitList_") !== false) {
                $dados_creditlimit = [];
                $id = 0;

                // Process each row in the CSV file
                while (($data = fgetcsv($file_handle, 10000, ";")) !== FALSE) {
                    if ($skip_first_line) {
                        $skip_first_line = false;
                        continue;
                    }
                    
                    $colunaA = $data[1];
                    $colunaB = $data[2];
                    $colunaC = str_replace('/', '-', substr($data[3], 0, 10));
                    $colunaD = str_replace('/', '-', substr($data[4], 0, 10));
                    $colunaE = round($data[10] / -1, 2);
                
                    $colunaC_partes = explode("-", $colunaC);
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[1] . "-" . $colunaC_partes[0];
                    $colunaD_partes = explode("-", $colunaD);
                    $colunaD_formatada = $colunaD_partes[2] . "-" . $colunaD_partes[1] . "-" . $colunaD_partes[0];
                
                    $id++;

                    // Adicione os dados a um array associativo
                    $dados_creditlimit[] = [
                        'id' => $id,
                        'room_number' => $colunaA,
                        'guest_name' => $colunaB,
                        'checkin' => $colunaC_formatada,
                        'checkout' => $colunaD_formatada,
                        'balance' => $colunaE,
                        'comentario' => ''
                    ];
                }

            // Deleta duplicidades
            $indicesParaExcluir = [];

            // Itere sobre os dados_creditlimit para encontrar índices de linhas a serem excluídas
            for ($k = 0; $k < count($dados_creditlimit); $k++) {
                for ($j = $k + 1; $j < count($dados_creditlimit); $j++) {
                    if (
                        $dados_creditlimit[$k]['room_number'] === $dados_creditlimit[$j]['room_number'] &&
                        $dados_creditlimit[$k]['guest_name'] === $dados_creditlimit[$j]['guest_name']
                    ) {
                        $indicesParaExcluir[] = $j;
                    }
                }
            }

            foreach ($indicesParaExcluir as $indice) {
                unset($dados_creditlimit[$indice]);
            }

            $dados_creditlimit = array_values($dados_creditlimit);
            $_SESSION['dados_creditlimit'] = $dados_creditlimit;
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

$arquivos_selecionados = $arquivo_ratecheck + $arquivo_creditlimit;

if($arquivos_selecionados == 2){

$_SESSION['status_auditoria'] = 'Em Andamento Pre';

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