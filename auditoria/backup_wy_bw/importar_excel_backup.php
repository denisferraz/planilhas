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

// Chave de criptografia
$chave = $_SESSION['hotel'].'Accor@123';
$metodo = 'AES-256-CBC';
$iv = '8246508246508246';

$arquivo_backup = 0;

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

                if (strpos($file_name, "Backup - Auditoria Digital [".ucfirst($dir)."]") !== false) {
                    $arquivo_backup = 1;
                }else{
                    echo "<script>
                    alert('Arquivo Selecionado Invalido')
                    window.location.replace('importar.php')
                    </script>";
                    exit();
                }
    
                //Importar Backup
                if (strpos($file_name, "Backup - Auditoria Digital [".ucfirst($dir)."]") !== false) {
                    $dados_ratecheck = [];

                    // Process each row in the CSV file
                    while (($data = fgetcsv($file_handle, 10000, ";")) !== FALSE) {
                        if ($skip_first_line) {
                            $skip_first_line = false;
                            continue;
                        }

                        // Para descriptografar os dados
                        $dados = base64_decode($data[0]);
                        $dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

                        $dados_array = explode(';', $dados_decifrados);
                        
                        //Atualizar Present List
                        if($dados_array[0] == 'presentlist' && isset($_POST['presentlist'])){

                            $id = $dados_array[1];

                            foreach ($_SESSION['dados_presentlist'] as &$item) {
                                if ($item['id'] == $id) {

                                    $item['room_number'] = $dados_array[2];
                                    $item['guest_name'] = $dados_array[3];
                                    $item['checkin'] = $dados_array[4];
                                    $item['checkout'] = $dados_array[5];
                                    $item['room_rate'] = $dados_array[6];
                                    $item['comentario_checkins'] = $dados_array[7];
                                    $item['auditoria_diarias'] = $dados_array[8];
                                    $item['auditoria_garantia'] = $dados_array[9];
                                    break;

                                }
                            }

                        }else
                        
                        //Atualizar Free Stay
                        if($dados_array[0] == 'freestay' && isset($_POST['freestay'])){

                            $id = $dados_array[1];

                            foreach ($_SESSION['dados_presentlist'] as &$item) {
                                if ($item['id'] == $id) {

                                    $item['guest_name'] = $dados_array[2];
                                    $item['reserva'] = $dados_array[3];
                                    $item['checkin'] = $dados_array[4];
                                    $item['checkout'] = $dados_array[5];
                                    $item['room_number'] = $dados_array[6];
                                    $item['comentario_freestay'] = $dados_array[7];
                                    break;

                                }
                            }

                        }else
                        
                        //Atualizar Caixa
                        if($dados_array[0] == 'caixa' && isset($_POST['caixa'])){

                            $id = $dados_array[1];

                            foreach ($_SESSION['dados_caixa'] as &$item) {
                                if ($item['id'] == $id) {

                                    $item['reserva'] = $dados_array[2];
                                    $item['guest_name'] = $dados_array[3];
                                    $item['data_lancamento'] = $dados_array[4];
                                    $item['pgto_forma'] = $dados_array[5];
                                    $item['pgto_valor'] = $dados_array[6];
                                    $item['documento'] = $dados_array[7];
                                    $item['auditoria_forma'] = $dados_array[8];
                                    $item['auditoria_conferido'] = $dados_array[9];
                                    break;

                                }
                            }

                        }else

                        //Atualizar Gerencial
                        if($dados_array[0] == 'gerencial' && isset($_POST['gerencial'])){

                            $linha = $dados_array[1];

                            if($linha == 'linha1'){
                                $_SESSION['quartos_bloqueados_dia'] = $dados_array[2];
                                $_SESSION['quartos_bloqueados_mes'] = $dados_array[3];
                                $_SESSION['quartos_ocupados_dia'] = $dados_array[4];
                                $_SESSION['quartos_ocupados_mes'] = $dados_array[5];
                                $_SESSION['quartos_cortesia_dia'] = $dados_array[6];
                                $_SESSION['quartos_cortesia_mes'] = $dados_array[7];
                                $_SESSION['quartos_houseuse_dia'] = $dados_array[8];
                                $_SESSION['quartos_houseuse_mes'] = $dados_array[9];
                            }else if($linha == 'linha2'){
                                $_SESSION['adultos_dia'] = $dados_array[2];
                                $_SESSION['adultos_mes'] = $dados_array[3];
                                $_SESSION['criancas_dia'] = $dados_array[4];
                                $_SESSION['criancas_mes'] = $dados_array[5];
                                $_SESSION['noshow_dia'] = $dados_array[6];
                                $_SESSION['noshow_mes'] = $dados_array[7];
                            }else if($linha == 'linha3'){
                                $_SESSION['forecast_1'] = $dados_array[2];
                                $_SESSION['forecast_2'] = $dados_array[3];
                                $_SESSION['forecast_3'] = $dados_array[4];
                                $_SESSION['comentario_gerencial'] = $dados_array[5];
                                $_SESSION['forecast_pax_1'] = $dados_array[6];
                                $_SESSION['forecast_pax_2'] = $dados_array[7];
                                $_SESSION['forecast_pax_3'] = $dados_array[8];
                                $_SESSION['forecast_dm_1'] = $dados_array[9];
                                $_SESSION['forecast_dm_2'] = $dados_array[10];
                                $_SESSION['forecast_dm_3'] = $dados_array[11];
                            }

                        }
                        
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
    window.location.replace('importar.php')
    </script>";
    exit();
}

if($arquivo_backup == 1){

$_SESSION['ratecheck'] = 0;
$_SESSION['creditlimit'] = 0;
$_SESSION['freestay'] = 0;
$_SESSION['controlebac'] = 0;
$_SESSION['Garantias'] = 0;

echo "<script>
    alert('Back Importado com Sucesso!')
    top.location.replace('auditoria.php')
    </script>";
    exit();

}else{

    echo "<script>
    alert('Selecione o Arquivo Backup certo')
    window.location.replace('importar.php')
    </script>";
    exit();

}

?>