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

$data_plano = mysqli_real_escape_string($conn_mysqli, $_POST['data_plano']);
$qtd_camareira = mysqli_real_escape_string($conn_mysqli, $_POST['camareiras']);

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
                    $tabela_excel = "$dir"."_excel_plano_quartos";
                    $tabela_excel_2 = "$dir"."_excel_plano_camareiras";
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

                $sql = "INSERT INTO $tabela_excel (data_plano, qtd_camareira, id_camareira, room_number, guest_name, room_stay_status, room_status_1, room_status_2, room_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->bind_param("sssssssss", $data_plano, $qtd_camareira, $id_camareira, $colunaA, $colunaB, $colunaC, $colunaD, $colunaE, $colunaF);

                    // Process each row in the CSV file
                    while (($data = fgetcsv($file_handle, 1000, ";")) !== FALSE) {
                        if ($skip_first_line) {
                            $skip_first_line = false;
                            continue;
                        }
                        
                        if($data[16] == 'Out of order'){
                            $id_camareira = '-1';
                        }else if($data[16] == 'Vacant' && $data[24] == 'Clean'){
                            $id_camareira = '-2';
                        }else{
                            $id_camareira = '0';
                        }

                        $colunaA = $data[1]; //Room Number
                        $colunaB = $data[4].' '.$data[3]; //Guest Name
                        $colunaC = $data[11]; //Room Stay Status
                        $colunaD = $data[16]; //Room Status 1
                        $colunaE = $data[24]; //Room Status 2
                        $colunaF = $data[15]; //Room Type
                    
                        $stmt->execute();
                    }

                    // Deleta duplicidades
                $sql = "DELETE t1 FROM $tabela_excel t1
                INNER JOIN $tabela_excel t2
                WHERE t1.id < t2.id
                  AND t1.room_number = t2.room_number
                  AND t1.data_plano = t2.data_plano";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->execute();
    
                // Close the file handle and statement
                fclose($file_handle);
                $stmt->close();

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

    $query_camareiras = $conexao->prepare("SELECT * FROM $dir"."_excel_plano_camareiras WHERE data_plano = '{$data_plano}'");
    $query_camareiras->execute();

    if($query_camareiras->rowCount() >= 0){
        $query_2 = $conexao->prepare("DELETE FROM $dir"."_excel_plano_camareiras WHERE data_plano = '{$data_plano}'");
        $query_2->execute();
    }

    $sql = "INSERT INTO $tabela_excel_2 (data_plano, id_camareira, camareira) VALUES (?, ?, ?)";
    $stmt = $conn_mysqli->prepare($sql);
    $stmt->bind_param("sss", $data_plano, $id_camareiras, $camareira);

for($id_camareiras = 1; $id_camareiras <= $qtd_camareira; $id_camareiras++){

    $formattedCamareiras = $id_camareiras < 10 ? '0' . $id_camareiras : $id_camareiras;
    $camareira = 'Camareira ('.$formattedCamareiras.')';
    $stmt->execute();
}

    $stmt->close();

    //Cria Camareiras BM e VL
    $sql = "INSERT INTO $tabela_excel_2 (data_plano, id_camareira, camareira) VALUES (?, ?, ?)";
    $stmt = $conn_mysqli->prepare($sql);
    $stmt->bind_param("sss", $data_plano, $id_camareiras, $camareira);

    $camareira = 'Bloqueado';
    $id_camareiras = -1;
    $stmt->execute();

    $camareira = 'Vago Limpo';
    $id_camareiras = -2;
    $stmt->execute();

    $sql = "DELETE t1 FROM $tabela_excel_2 t1
    INNER JOIN $tabela_excel_2 t2
    WHERE t1.id < t2.id
    AND t1.id_camareira = t2.id_camareira
    AND t1.data_plano = t2.data_plano";
    $stmt = $conn_mysqli->prepare($sql);
    $stmt->execute();
    $stmt->close();


$data_plano = strtotime("$data_plano");

echo "<script>
    window.location.replace('index.php?id=$data_plano')
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