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

$_SESSION['data_plano'] = mysqli_real_escape_string($conn_mysqli, $_POST['data_plano']);
$_SESSION['camareiras'] = mysqli_real_escape_string($conn_mysqli, $_POST['camareiras']);

$arquivos_selecionados = 0;

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

// Check if the CSV files were uploaded successfully
if (!empty($_FILES["csvFile"]["name"]) && count($_FILES["csvFile"]["name"]) == 1) {
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

                if (strpos($file_name, ucfirst($dir)."_RoomState_S_") !== false) {
                    $arquivos_selecionados = 1;
                }else{
                    echo "<script>
                    alert('Arquivo Selecionado Invalido')
                    window.location.replace('index.php')
                    </script>";
                    exit();
                }
    
                //Importar Rate Check
                if (strpos($file_name, ucfirst($dir)."_RoomState_S_") !== false) {
                    $dados_roomstatus = [];
                    $id = 0;

                    // Process each row in the CSV file
                    while (($data = fgetcsv($file_handle, 1000, ";")) !== FALSE) {
                        if ($skip_first_line) {
                            $skip_first_line = false;
                            continue;
                        }
                        
                        $colunaA = $data[1]; //Room Number
                        $colunaB = $data[4].' '.$data[3]; //Guest Name
                        $colunaC = $data[11]; //Room Stay Status
                        $colunaD = $data[16]; //Room Status 1
                        $colunaE = $data[24]; //Room Status 2
                        $colunaF = $data[15]; //Room Type
                    
                        $id++;

                        // Adicione os dados a um array associativo
                        $dados_roomstatus[] = [
                            'id' => $id,
                            'id_camareira' => 0,
                            'room_number' => $colunaA,
                            'guest_name' => $colunaB,
                            'room_stay_status' => $colunaC,
                            'room_status_1' => $colunaD,
                            'room_status_2' => $colunaE,
                            'room_type' => $colunaF
                        ];
                    }

                    // Deleta duplicidades
                    $indicesParaExcluir = [];

                    // Itere sobre os dados_ratecheck para encontrar índices de linhas a serem excluídas
                    for ($k = 0; $k < count($dados_roomstatus); $k++) {
                        for ($j = $k + 1; $j < count($dados_roomstatus); $j++) {
                            if (
                                $dados_roomstatus[$k]['room_number'] === $dados_roomstatus[$j]['room_number'] &&
                                $dados_roomstatus[$k]['room_type'] === $dados_roomstatus[$j]['room_type']
                            ) {
                                // Adicione o índice da linha a ser excluída ao array
                                $indicesParaExcluir[] = $j;
                            }
                        }
                    }

                    // Remova as linhas duplicadas com base nos índices coletados
                    foreach ($indicesParaExcluir as $indice) {
                        unset($dados_roomstatus[$indice]);
                    }
                    
                $dados_roomstatus = array_values($dados_roomstatus);
                $_SESSION['dados_roomstatus'] = $dados_roomstatus;
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

if($arquivos_selecionados == 1){

$_SESSION['status_plano'] = 'Preenchimento';

for($id_camareiras = 1; $id_camareiras <= $_SESSION['camareiras']; $id_camareiras++){

    $formattedCamareiras = $id_camareiras < 10 ? '0' . $id_camareiras : $id_camareiras;

    $_SESSION['camareira_'.$id_camareiras] = 'Camareira ('.$formattedCamareiras.')';
    $_SESSION['id_camareira_'.$id_camareiras] = $id_camareiras;
}

echo "<script>
    window.location.replace('plano.php')
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