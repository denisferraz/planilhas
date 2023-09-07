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

                        //Atualizar Ratecheck
                        if($dados_array[0] == 'ratecheck' && isset($_POST['ratecheck'])){

                            $id = $dados_array[1];

                            foreach ($_SESSION['dados_ratecheck'] as &$item) {
                                if ($item['id'] == $id) {

                                    $item['comentario'] = $dados_array[2];

                                    break;
                                }
                            }

                        }else
                        
                        //Atualizar Credit Limit
                        if($dados_array[0] == 'creditlimit' && isset($_POST['creditlimit'])){

                            $id = $dados_array[1];

                            foreach ($_SESSION['dados_creditlimit'] as &$item) {
                                if ($item['id'] == $id) {

                                    $item['comentario'] = $dados_array[2];
                                    break;
                                }
                            }

                        }else
                        
                        //Atualizar Present List
                        if($dados_array[0] == 'presentlist' && isset($_POST['presentlist'])){

                            $id = $dados_array[1];

                            foreach ($_SESSION['dados_presentlist'] as &$item) {
                                if ($item['id'] == $id) {

                                    $item['auditoria_diarias'] = $dados_array[2];
                                    $item['auditoria_extras'] = $dados_array[3];
                                    $item['auditoria_garantia'] = $dados_array[4];
                                    $item['auditoria_valor'] = $dados_array[5];
                                    $item['auditoria_pasta_limpa'] = $dados_array[6];
                                    $item['auditoria_pasta_pdv'] = $dados_array[7];
                                    $item['auditoria_pasta_pasta'] = $dados_array[8];
                                    $item['auditoria_pasta_ass'] = $dados_array[9];
                                    $item['auditoria_fnrh'] = $dados_array[10];
                                    $item['auditoria_doc'] = $dados_array[11];
                                    break;

                                }
                            }

                        }else
                        
                        //Atualizar Free Stay
                        if($dados_array[0] == 'freestay' && isset($_POST['freestay'])){

                            $id = $dados_array[1];

                            foreach ($_SESSION['dados_freestay'] as &$item) {
                                if ($item['id'] == $id) {

                                    $item['comentario'] = $dados_array[2];
                                    break;

                                }
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