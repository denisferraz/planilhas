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

// Check if the CSV files were uploaded successfully
if (!empty($_FILES["csvFile"]["name"]) && count($_FILES["csvFile"]["name"]) == 3) {
    $file_names = $_FILES["csvFile"]["name"];
    $file_tmp_names = $_FILES["csvFile"]["tmp_name"];

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

        // Check if the uploaded file is a CSV
        if (strtolower(pathinfo($file_name, PATHINFO_EXTENSION)) == "csv") {
            // Read the CSV file
            $file_handle = fopen($tmp_name, "r");
            if ($file_handle !== FALSE) {
                $skip_first_line = true;

                if (strpos($file_name, ucfirst($dir)."_RoomState_S_") !== false) {
                    $tabela_excel = "$dir"."_excel_gestaorecepcao_roomstatus";
                }else if (strpos($file_name, ucfirst($dir)."_ArrivalList_") !== false) {
                    $tabela_excel = "$dir"."_excel_gestaorecepcao_arrivals";
                }else if (strpos($file_name, ucfirst($dir)."_PresentList_") !== false) {
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
                if (strpos($file_name, ucfirst($dir)."_RoomState_S_") !== false) {
                // Prepare the SQL statement for inserting data into the database
                $sql = "INSERT INTO $tabela_excel (room_number, room_status, room_type) VALUES (?, ?, ?)";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->bind_param("sss", $colunaA, $colunaB, $colunaC);
    
                // Process each row in the CSV file
                while (($data = fgetcsv($file_handle, 1000, ";")) !== FALSE) {
                    if ($skip_first_line) {
                        $skip_first_line = false;
                        continue;
                    }
                    $colunaA = $data[1];
                    $colunaB = $data[13];
                    $colunaC = $data[15];
    
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
                fclose($file_handle);
                $stmt->close();
            }
            //Importar Arrivals
            else if (strpos($file_name, ucfirst($dir)."_ArrivalList_") !== false) {
                // Prepare the SQL statement for inserting data into the database
                $sql = "INSERT INTO $tabela_excel (dados_arrivals) VALUES (?)";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->bind_param("s", $dados_final);
    
                // Process each row in the CSV file
                while (($data = fgetcsv($file_handle, 1000, ";")) !== FALSE) {
                    if ($skip_first_line) {
                        $skip_first_line = false;
                        continue;
                    }
                    $colunaA = $data[0]; //guest name
                    $colunaB = $data[1]; //noites
                    $colunaC = $data[2]; //adultos
                    $colunaD = $data[3]; //crianças
                    $colunaE = $data[7]; //room type
                    $colunaF = $data[9]; //rate code
                    $colunaG = strip_tags($data[23]); //comentarios
                    $colunaH = $data[25]; //room number
                    $colunaI = 'Pendente'; //alteração

                    $dados_arrivalslist = $colunaA.';'.$colunaB.';'.$colunaC.';'.$colunaD.';'.$colunaE.';'.$colunaF.';'.$colunaG.';'.$colunaH.';'.$colunaI;
                    $dados_criptografados = openssl_encrypt($dados_arrivalslist, $metodo, $chave, 0, $iv);
                    $dados_final = base64_encode($dados_criptografados);
    
                    // Execute the SQL statement
                    $stmt->execute();
                }
    
                // Close the file handle and statement
                fclose($file_handle);
                $stmt->close();
            }
            //Importar In House
            else if (strpos($file_name, ucfirst($dir)."_PresentList_") !== false) {
                // Prepare the SQL statement for inserting data into the database
                $sql = "INSERT INTO $tabela_excel (dados_presentlist) VALUES (?)";
                $stmt = $conn_mysqli->prepare($sql);
                $stmt->bind_param("s", $dados_final);
    
                // Process each row in the CSV file
                while (($data = fgetcsv($file_handle, 1000, ";")) !== FALSE) {
                    if ($skip_first_line) {
                        $skip_first_line = false;
                        continue;
                    }
                    $colunaA = $data[1]; //guest_name
                    $colunaB = str_replace('/', '-', substr($data[3], 0, 10)); //checkin
                    $colunaC = str_replace('/', '-', substr($data[4], 0, 10)); //checkout
                    $colunaD = $data[5]; //noites
                    $colunaE = $data[6]; //adultos
                    $colunaF = $data[7]; //crianças
                    $colunaG = $data[12]; //ratecode
                    $colunaH = $data[15] * (-1); //balance
                    $colunaI = $data[21]; //room number
                    $colunaJ = strip_tags($data[23]); //room msg
                    $colunaK = $data[17].' | '.$data[28]; //room company/travel
                    $colunaL = 'Pendente'; //alteração

                    $colunaB_partes = explode("-", $colunaB);
                    $colunaB_formatada = $colunaB_partes[2] . "-" . $colunaB_partes[1] . "-" . $colunaB_partes[0];
                    $colunaC_partes = explode("-", $colunaC);
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[1] . "-" . $colunaC_partes[0];

                    $dados_presentlist = $colunaA.';'.$colunaB_formatada.';'.$colunaC_formatada.';'.$colunaD.';'.$colunaE.';'.$colunaF.';'.$colunaG.';'.$colunaH.';'.$colunaI.';'.$colunaJ.';'.$colunaK.';'.$colunaL;
                    $dados_criptografados = openssl_encrypt($dados_presentlist, $metodo, $chave, 0, $iv);
                    $dados_final = base64_encode($dados_criptografados);
    

                    if (empty($colunaI)) {
                        continue; // Pular para a próxima iteração
                    }
    
                    // Execute the SQL statement
                    $stmt->execute();
                }
    
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
        window.location.replace('gestao.php')
        </script>";
    exit();
}

// Close the database connection
$conn_mysqli->close();
?>
