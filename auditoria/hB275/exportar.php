<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

$worksheet_password = 'h8185@Accor';

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

$hotel = 'Backup Auditoria Digital - '.$_SESSION['hotel_name'];
$data_quando = date('d/m/Y - H:i:s');

// Chave de criptografia
$chave = $_SESSION['hotel'].'Accor@123';
$metodo = 'AES-256-CBC';
//$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($metodo));
$iv = '8246508246508246';

//Criar Planilha Excel
$spreadsheet = new Spreadsheet();

$spreadsheet->getProperties()
->setCreator("Denis Ferraz")
->setLastModifiedBy("Denis Ferraz")
->setTitle("Auditoria Digital by Denis Ferraz");

//Inicio do Excel

$activeWorksheet = $spreadsheet->getActiveSheet();
$activeWorksheet->setTitle('Backup Auditoria Digital');
$activeWorksheet->setSelectedCell('A1');

$activeWorksheet->setCellValue('A1', $hotel);

$linha_excel = 2;


//Conferencia de Diarias

$dados_ratecheck = $_SESSION['dados_ratecheck'];

foreach ($dados_ratecheck as $select) {
    $id = $select['id'];
    $comentario = $select['comentario'];

    $dados_ratecheck_cript = 'ratecheck;'.$id.';'.$comentario;

    $dados_criptografados = openssl_encrypt($dados_ratecheck_cript, $metodo, $chave, 0, $iv);
    $dados_final = base64_encode($dados_criptografados);

    $activeWorksheet->setCellValue('A'.$linha_excel, $dados_final);

$linha_excel++;

}

//Saldo Elevado

$dados_creditlimit = $_SESSION['dados_creditlimit'];

foreach ($dados_creditlimit as $select) {
    $id = $select['id'];
    $comentario = $select['comentario'];

    $dados_creditlimit_cript = 'creditlimit;'.$id.';'.$comentario;

    $dados_criptografados = openssl_encrypt($dados_creditlimit_cript, $metodo, $chave, 0, $iv);
    $dados_final = base64_encode($dados_criptografados);

    $activeWorksheet->setCellValue('A'.$linha_excel, $dados_final);

    $linha_excel++;

}

//Controle du Bac + Controle de Garantias
$dados_presentlist = $_SESSION['dados_presentlist'];

foreach ($dados_presentlist as $select) {
    $id = $select['id'];
    $auditoria_diarias = $select['auditoria_diarias'];
    $auditoria_extras = $select['auditoria_extras'];
    $auditoria_garantia = $select['auditoria_garantia'];
    $auditoria_valor = $select['auditoria_valor'];
    $auditoria_pasta_limpa = $select['auditoria_pasta_limpa'];
    $auditoria_pasta_pdv = $select['auditoria_pasta_pdv'];
    $auditoria_pasta_pasta = $select['auditoria_pasta_pasta'];
    $auditoria_pasta_ass = $select['auditoria_pasta_ass'];
    $auditoria_fnrh = $select['auditoria_fnrh'];
    $auditoria_doc = $select['auditoria_doc'];

    $dados_presentlist_cript = 'presentlist;'.$id.';'.$auditoria_diarias.';'.$auditoria_extras.';'.$auditoria_garantia.';'.$auditoria_valor.';'.$auditoria_pasta_limpa.';'.$auditoria_pasta_pdv.';'.$auditoria_pasta_pasta.';'.$auditoria_pasta_ass.';'.$auditoria_fnrh.';'.$auditoria_doc;

    $dados_criptografados = openssl_encrypt($dados_presentlist_cript, $metodo, $chave, 0, $iv);
    $dados_final = base64_encode($dados_criptografados);

    $activeWorksheet->setCellValue('A'.$linha_excel, $dados_final);

    $linha_excel++;
}

//Free Stay

$dados_freestay = $_SESSION['dados_freestay'] ;

foreach ($dados_freestay as $select) {
    $id = $select['id'];
    $comentario = $select['comentario'];

    $dados_freestay_cript = 'freestay;'.$id.';'.$comentario;

    $dados_criptografados = openssl_encrypt($dados_freestay_cript, $metodo, $chave, 0, $iv);
    $dados_final = base64_encode($dados_criptografados);

    $activeWorksheet->setCellValue('A'.$linha_excel, $dados_final);

$linha_excel++;

}

//Fim Abas

$activeWorksheet->setSelectedCell('A1');

// Create a temporary file for download
$filename = 'Backup - Auditoria Digital ['.ucfirst($dir).'].csv';
$tempFile = tempnam(sys_get_temp_dir(), $filename);
$writer = new Csv($spreadsheet);
$writer->save($tempFile);

// Send headers to force download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Output the file to the browser
readfile($tempFile);

// Delete the temporary file
unlink($tempFile);

$conn_mysqli->close();

?>