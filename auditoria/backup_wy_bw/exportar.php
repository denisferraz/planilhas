<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

$worksheet_password = 'h8185@Accor';

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

//Controle de Garantias
$data_auditoria = $_SESSION['data_auditoria'];
$dados_presentlist = $_SESSION['dados_presentlist'];

$dados_filtrados = array_filter($dados_presentlist, function($item) use ($data_auditoria) {
    return $item['checkin'] == $data_auditoria;
});

foreach ($dados_filtrados as $select) {
    $id = $select['id'];
    $room_number = $select['room_number'];
    $guest_name = $select['guest_name'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $room_rate = $select['room_rate'];
    $comentario = $select['comentario_checkins'];
    $auditoria_diarias = $select['auditoria_diarias'];
    $auditoria_garantia = $select['auditoria_garantia'];

    $dados_presentlist_cript = 'presentlist;'.$id.';'.$room_number.';'.$guest_name.';'.$checkin.';'.$checkout.';'.$room_rate.';'.$comentario.';'.$auditoria_diarias.';'.$auditoria_garantia;

    $dados_criptografados = openssl_encrypt($dados_presentlist_cript, $metodo, $chave, 0, $iv);
    $dados_final = base64_encode($dados_criptografados);

    $activeWorksheet->setCellValue('A'.$linha_excel, $dados_final);

    $linha_excel++;
}

//Free Stay

$dados_presentlist = $_SESSION['dados_presentlist'];

$dados_filtrados = array_filter($dados_presentlist, function($item) {
    return $item['room_rate'] == 0;
});

foreach ($dados_filtrados as $select) {
    $id = $select['id'];
    $guest_name = $select['guest_name'];
    $reserva = $select['reserva'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $room_number = $select['room_number'];
    $comentario = $select['comentario_freestay'];

    $dados_freestay_cript = 'freestay;'.$id.';'.$guest_name.';'.$reserva.';'.$checkin.';'.$checkout.';'.$room_number.';'.$comentario;

    $dados_criptografados = openssl_encrypt($dados_freestay_cript, $metodo, $chave, 0, $iv);
    $dados_final = base64_encode($dados_criptografados);

    $activeWorksheet->setCellValue('A'.$linha_excel, $dados_final);

$linha_excel++;

}

//Caixa

$dados_caixa = $_SESSION['dados_caixa'];

$dados_filtrados = array_filter($dados_caixa, function($item) {
    return $item['auditoria_conferido'] == 'Não' && $item['pgto_forma'] != 'A Faturar' && $item['pgto_forma'] != 'Dinheiro' && $item['pgto_forma'] != 'Deposito';
});

foreach ($dados_filtrados as $select) {
    $id = $select['id'];
    $reserva = $select['reserva'];
    $guest_name = $select['guest_name'];
    $data_lancamento = $select['data_lancamento'];
    $pgto_forma = $select['pgto_forma'];
    $pgto_valor = $select['pgto_valor'];
    $room_number = $select['room_number'];
    $documento = $select['documento'];
    $auditoria_forma = $select['auditoria_forma'];
    $auditoria_conferido = $select['auditoria_conferido'];

    $dados_caixa_cript = 'caixa;'.$id.';'.$reserva.';'.$guest_name.';'.$data_lancamento.';'.$pgto_forma.';'.$pgto_valor.';'.$documento.';'.$auditoria_forma.';'.$auditoria_conferido;

    $dados_criptografados = openssl_encrypt($dados_caixa_cript, $metodo, $chave, 0, $iv);
    $dados_final = base64_encode($dados_criptografados);

    $activeWorksheet->setCellValue('A'.$linha_excel, $dados_final);

$linha_excel++;

}

//Gerencial

//Linha 1
$quartos_bloqueados_dia = $_SESSION['quartos_bloqueados_dia'];
$quartos_bloqueados_mes = $_SESSION['quartos_bloqueados_mes'];
$quartos_ocupados_dia = $_SESSION['quartos_ocupados_dia'];
$quartos_ocupados_mes = $_SESSION['quartos_ocupados_mes'];
$quartos_cortesia_dia = $_SESSION['quartos_cortesia_dia'];
$quartos_cortesia_mes = $_SESSION['quartos_cortesia_mes'];
$quartos_houseuse_dia = $_SESSION['quartos_houseuse_dia'];
$quartos_houseuse_mes = $_SESSION['quartos_houseuse_mes'];
$dados_gerencial_cript = 'gerencial;linha1;'.$quartos_bloqueados_dia.';'.$quartos_bloqueados_mes.';'.$quartos_ocupados_dia.';'.$quartos_ocupados_mes.';'.$quartos_cortesia_dia.';'.$quartos_cortesia_mes.';'.$quartos_houseuse_dia.';'.$quartos_houseuse_mes;
$dados_criptografados = openssl_encrypt($dados_gerencial_cript, $metodo, $chave, 0, $iv);
$dados_final = base64_encode($dados_criptografados);
$activeWorksheet->setCellValue('A'.$linha_excel, $dados_final);
$linha_excel++;
//Linha 2
$adultos_dia = $_SESSION['adultos_dia'];
$adultos_mes = $_SESSION['adultos_mes'];
$criancas_dia = $_SESSION['criancas_dia'];
$criancas_mes = $_SESSION['criancas_mes'];
$noshow_dia = $_SESSION['noshow_dia'];
$noshow_mes = $_SESSION['noshow_mes'];
$dados_gerencial_cript = 'gerencial;linha2;'.$adultos_dia.';'.$adultos_mes.';'.$criancas_dia.';'.$criancas_mes.';'.$noshow_dia.';'.$noshow_mes;
$dados_criptografados = openssl_encrypt($dados_gerencial_cript, $metodo, $chave, 0, $iv);
$dados_final = base64_encode($dados_criptografados);
$activeWorksheet->setCellValue('A'.$linha_excel, $dados_final);
$linha_excel++;
//Linha 3
$forecast_1 = $_SESSION['forecast_1'];
$forecast_2 = $_SESSION['forecast_2'];
$forecast_3 = $_SESSION['forecast_3'];
$forecast_pax_1 = $_SESSION['forecast_pax_1'];
$forecast_pax_2 = $_SESSION['forecast_pax_2'];
$forecast_pax_3 = $_SESSION['forecast_pax_3'];
$forecast_dm_1 = $_SESSION['forecast_dm_1'];
$forecast_dm_2 = $_SESSION['forecast_dm_2'];
$forecast_dm_3 = $_SESSION['forecast_dm_3'];
$comentario_gerencial = $_SESSION['comentario_gerencial'];
$dados_gerencial_cript = 'gerencial;linha3;'.$forecast_1.';'.$forecast_2.';'.$forecast_3.';'.$comentario_gerencial.';'.$forecast_pax_1.';'.$forecast_pax_2.';'.$forecast_pax_3.';'.$forecast_dm_1.';'.$forecast_dm_2.';'.$forecast_dm_3;
$dados_criptografados = openssl_encrypt($dados_gerencial_cript, $metodo, $chave, 0, $iv);
$dados_final = base64_encode($dados_criptografados);
$activeWorksheet->setCellValue('A'.$linha_excel, $dados_final);

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