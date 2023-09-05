<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Color;

$worksheet_password = 'h8185@Accor';

$dir = substr(__DIR__, -5);

if($dir != $_SESSION['hotel']){
    echo "<script>
    alert('Você não tem permissão para acessar esta pagina!')
    window.location.replace('../../index.html')
    </script>";
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//error_reporting(0);

    $data_auditoria = $_SESSION['data_auditoria'];
    $hotel = $_SESSION['hotel_name'];
    $limite_credito = $_SESSION['limite_credito'];
    $comentario_gerencial = $_SESSION['comentario_gerencial'];
    $comentario_bac = $_SESSION['comentario_bac'];
    $comentario_garantias = $_SESSION['comentario_garantias'];
    $comentario_taxbase = $_SESSION['comentario_taxbase'];


$hotel = 'Auditoria Digital - '.$hotel.' ['.date('d/m/Y', strtotime("$data_auditoria")).']';
$data_quando = date('d/m/Y - H:i:s');

//Criar Planilha Excel
$spreadsheet = new Spreadsheet();

$conditionRed = new Conditional();
$conditionRed->setConditionType(Conditional::CONDITION_CELLIS)
    ->setOperatorType(Conditional::OPERATOR_EQUAL)
    ->addCondition('X')
    ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_DARKRED);

$conditionGrey = new Conditional();
$conditionGrey->setConditionType(Conditional::CONDITION_CELLIS)
    ->setOperatorType(Conditional::OPERATOR_NOTEQUAL)
    ->addCondition('0')
    ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_MAGENTA);

$conditionRedBalance = new Conditional();
$conditionRedBalance->setConditionType(Conditional::CONDITION_CELLIS)
    ->setOperatorType(Conditional::OPERATOR_GREATERTHAN)
    ->addCondition($limite_credito)
    ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_RED);

$spreadsheet->getProperties()
->setCreator("Denis Ferraz")
->setLastModifiedBy("Denis Ferraz")
->setTitle("Auditoria Digital by Denis Ferraz");

$security = $spreadsheet->getSecurity();
$security->setLockWindows(true);
$security->setLockStructure(true);
$security->setWorkbookPassword("$worksheet_password");

//Linhas de Configurações e Cores das Linhas/COlunas
$styleArray_separacao = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'FFC0C0C0', // Cinza
        ],
    ]
];

$styleArray_cinza = [
    'font' => [
        'bold' => true,
        'size' => 11,
        'name' => 'Calibri',
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'FFC0C0C0', // Cinza
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$styleArray_laranja = [
    'font' => [
        'bold' => true,
        'size' => 11,
        'name' => 'Calibri',
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'FFA500', //  Laranja
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$styleArray_amarelo = [
    'font' => [
        'bold' => true,
        'size' => 10,
        'name' => 'Calibri',
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'FFFF00', //  Amarelo
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$styleArray_branco5 = [
    'font' => [
        'bold' => false,
        'size' => 10,
        'name' => 'Calibri',
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'F0F0F0', // Branco 5%
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$styleArray_preto = [
    'font' => [
        'bold' => false,
        'size' => 10,
        'name' => 'Calibri',
        'color' => ['rgb' => 'FFFFFF']
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'black', // Preto
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$styleArray_verde = [
    'font' => [
        'bold' => false,
        'size' => 10,
        'name' => 'Calibri',
        'color' => ['rgb' => '00FF00']
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => '00FF00', // Verde
        ],
    ]
];

// Inside Borders
$styleArray_inside_borders = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => 'thin',
            'color' => ['rgb' => '000000'],
        ],
        'outline' => [
            'borderStyle' => 'thick',
            'color' => ['rgb' => '000000'],
        ],
    ],
];

$styleArray_outside_borders = [
    'borders' => [
        'outline' => [
            'borderStyle' => 'thick',
            'color' => ['rgb' => '000000'],
        ],
    ],
];

$styleArray_bold = [
    'font' => [
        'bold' => true,
        'size' => 11,
        'name' => 'Calibri'
    ]
];

$styleArray_creditlimit = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'FF0000', // Vermelho
        ],
    ]
];

$styleArray_difiss = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'FF0000', // Vermelho
        ],
    ]
];

$styleArray_difissok = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => '00FF00', // Verde
        ],
    ]
];

//Aba Gerencial

//Gerencial
$linhas_gerenciais = array();

foreach ($_SESSION['dados_gerencial']  as $select) {
    $id = $select['id'];
    $item_nome = $select['item_nome'];
    $valor_dia = floatval($select['valor_dia']);
    $valor_mes = floatval($select['valor_mes']);
    $valor_ano = floatval($select['valor_ano']);

    $valor_dia = number_format($valor_dia, 2, ',', '.');
    $valor_mes = number_format($valor_mes, 2, ',', '.');
    $valor_ano = number_format($valor_ano, 2, ',', '.');

    $linha_gerencial = "$item_nome;$valor_dia;$valor_mes;$valor_ano";
    $linhas_gerenciais[] = $linha_gerencial;

}
$quartos_construidos = explode(';', $linhas_gerenciais[0]);
$quartos_bloqueados = explode(';', $linhas_gerenciais[1]);
$quartos_ocupados = explode(';', $linhas_gerenciais[3]);
$quartos_cortesia = explode(';', $linhas_gerenciais[4]);
$quartos_dayuse = explode(';', $linhas_gerenciais[5]);
$quartos_houseuse = explode(';', $linhas_gerenciais[6]);
$quartos_occ = (intval(str_replace(array(',', '.'), '', $quartos_ocupados[1]) / 100) - intval(str_replace(array(',', '.'), '', $quartos_houseuse[1]) / 100) + intval(str_replace(array(',', '.'), '', $quartos_dayuse[1]) / 100))/ intval(str_replace(array(',', '.'), '', $quartos_construidos[1]) / 100)*100;
$hospedes = explode(';', $linhas_gerenciais[19]);
$noshow = explode(';', $linhas_gerenciais[21]);
$cafe_incluso = explode(';', $linhas_gerenciais[25]);
$cafe_passante = explode(';', $linhas_gerenciais[26]);
$receita_hospedagem = explode(';', $linhas_gerenciais[22]);
$receita_aeb = explode(';', $linhas_gerenciais[28]);
$receita_eventos = explode(';', $linhas_gerenciais[29]);
$receita_diversos = explode(';', $linhas_gerenciais[30]);
$receita_total = explode(';', $linhas_gerenciais[31]);

//Sales Analyze
$dados_salesanalyze = $_SESSION['dados_salesanalyze'];

//Hospedagem
$sales_hospedagem_dia = 0;
$sales_hospedagem_mes = 0;
$sales_hospedagem_ano = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ($salesanalyze['item_nome'] === '15' || $salesanalyze['item_nome'] === '21') {
        $sales_hospedagem_dia += floatval($salesanalyze['valor_dia']);
        $sales_hospedagem_mes += floatval($salesanalyze['valor_mes']);
        $sales_hospedagem_ano += floatval($salesanalyze['valor_ano']);
    }
}
$sales_hospedagem_dia = number_format($sales_hospedagem_dia, 2, ",", ".");
$sales_hospedagem_mes = number_format($sales_hospedagem_mes, 2, ",", ".");
$sales_hospedagem_ano = number_format($sales_hospedagem_ano, 2, ",", ".");


//AeB
$sales_aeb_dia = 0;
$sales_aeb_mes = 0;
$sales_aeb_ano = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ($salesanalyze['item_nome'] === '17') {
        $sales_aeb_dia += floatval($salesanalyze['valor_dia']);
        $sales_aeb_mes += floatval($salesanalyze['valor_mes']);
        $sales_aeb_ano += floatval($salesanalyze['valor_ano']);
    }
}
$sales_aeb_dia = number_format($sales_aeb_dia, 2, ",", ".");
$sales_aeb_mes = number_format($sales_aeb_mes, 2, ",", ".");
$sales_aeb_ano = number_format($sales_aeb_ano, 2, ",", ".");


//Eventos
$sales_eventos_dia = 0;
$sales_eventos_mes = 0;
$sales_eventos_ano = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ($salesanalyze['item_nome'] === '51') {
        $sales_eventos_dia += floatval($salesanalyze['valor_dia']);
        $sales_eventos_mes += floatval($salesanalyze['valor_mes']);
        $sales_eventos_ano += floatval($salesanalyze['valor_ano']);
    }
}
$sales_eventos_dia = number_format($sales_eventos_dia, 2, ",", ".");
$sales_eventos_mes = number_format($sales_eventos_mes, 2, ",", ".");
$sales_eventos_ano = number_format($sales_eventos_ano, 2, ",", ".");


//Outros
$sales_outros_dia = 0;
$sales_outros_mes = 0;
$sales_outros_ano = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    if ($salesanalyze['item_nome'] === '25' || $salesanalyze['item_nome'] === '3' || $salesanalyze['item_nome'] === '29') {
        $sales_outros_dia += floatval($salesanalyze['valor_dia']);
        $sales_outros_mes += floatval($salesanalyze['valor_mes']);
        $sales_outros_ano += floatval($salesanalyze['valor_ano']);
    }
}
$sales_outros_dia = number_format($sales_outros_dia, 2, ",", ".");
$sales_outros_mes = number_format($sales_outros_mes, 2, ",", ".");
$sales_outros_ano = number_format($sales_outros_ano, 2, ",", ".");


//Total
$sales_total_dia = 0;
$sales_total_mes = 0;
$sales_total_ano = 0;

foreach ($dados_salesanalyze as $salesanalyze) {
    $sales_total_dia += floatval($salesanalyze['valor_dia']);
    $sales_total_mes += floatval($salesanalyze['valor_mes']);
    $sales_total_ano += floatval($salesanalyze['valor_ano']);
}
$sales_total_dia = number_format($sales_total_dia, 2, ",", ".");
$sales_total_mes = number_format($sales_total_mes, 2, ",", ".");
$sales_total_ano = number_format($sales_total_ano, 2, ",", ".");


if($sales_hospedagem_dia == number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[1]) / 100), 2, ',', '.')){
$dif_receita_hospedagem_dia = 'X'; }else{  $dif_receita_hospedagem_dia = ''; }
if($sales_hospedagem_mes == number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[2]) / 100), 2, ',', '.')){
$dif_receita_hospedagem_mes = 'X'; }else{  $dif_receita_hospedagem_mes = ''; }
if($sales_hospedagem_ano == number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[3]) / 100), 2, ',', '.')){
$dif_receita_hospedagem_ano = 'X'; }else{  $dif_receita_hospedagem_ano = ''; }

if($sales_aeb_dia == number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[1]) / 100), 2, ',', '.')){
$dif_receita_aeb_dia = 'X'; }else{  $dif_receita_aeb_dia = ''; }
if($sales_aeb_mes == number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[2]) / 100), 2, ',', '.')){
$dif_receita_aeb_mes = 'X'; }else{  $dif_receita_aeb_mes = ''; }
if($sales_aeb_ano == number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[3]) / 100), 2, ',', '.')){
$dif_receita_aeb_ano = 'X'; }else{  $dif_receita_aeb_ano = ''; }

if($sales_eventos_dia == number_format(floatval(str_replace(array(',', '.'), '', $receita_eventos[1]) / 100), 2, ',', '.')){
$dif_receita_eventos_dia = 'X'; }else{  $dif_receita_eventos_dia = ''; }
if($sales_eventos_mes == number_format(floatval(str_replace(array(',', '.'), '', $receita_eventos[2]) / 100), 2, ',', '.')){
$dif_receita_eventos_mes = 'X'; }else{  $dif_receita_eventos_mes = ''; }
if($sales_eventos_ano == number_format(floatval(str_replace(array(',', '.'), '', $receita_eventos[3]) / 100), 2, ',', '.')){
$dif_receita_eventos_ano = 'X'; }else{  $dif_receita_eventos_ano = ''; }

if($sales_outros_dia == number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[1]) / 100), 2, ',', '.')){
$dif_receita_diversos_dia = 'X'; }else{  $dif_receita_diversos_dia = ''; }
if($sales_outros_mes == number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[2]) / 100), 2, ',', '.')){
$dif_receita_diversos_mes = 'X'; }else{  $dif_receita_diversos_mes = ''; }
if($sales_outros_ano == number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[3]) / 100), 2, ',', '.')){
$dif_receita_diversos_ano = 'X'; }else{  $dif_receita_diversos_ano = ''; }

if($sales_total_dia == number_format(floatval(str_replace(array(',', '.'), '', $receita_total[1]) / 100), 2, ',', '.')){
$dif_receita_total_dia = 'X'; }else{  $dif_receita_total_dia = ''; }
if($sales_total_mes == number_format(floatval(str_replace(array(',', '.'), '', $receita_total[2]) / 100), 2, ',', '.')){
$dif_receita_total_mes = 'X'; }else{  $dif_receita_total_mes = ''; }
if($sales_total_ano == number_format(floatval(str_replace(array(',', '.'), '', $receita_total[3]) / 100), 2, ',', '.')){
$dif_receita_total_ano = 'X'; }else{  $dif_receita_total_ano = ''; }

$spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
$protection = $spreadsheet->getActiveSheet()->getProtection();
$protection->setPassword("$worksheet_password");
$protection->setSheet(true);
$protection->setSort(false);
$protection->setInsertRows(false);
$protection->setFormatCells(false);

$security = $spreadsheet->getSecurity();
$security->setLockWindows(true);
$security->setLockStructure(true);
$security->setWorkbookPassword("$worksheet_password");

$activeWorksheet = $spreadsheet->getActiveSheet();
$activeWorksheet->setCellValue('C3', $hotel);
$activeWorksheet->mergeCells('C3:N3');
$activeWorksheet->setCellValue('C5', 'Relatorio Gerencial');
$activeWorksheet->mergeCells('C5:F5');
$activeWorksheet->setCellValue('C6', 'Linha');
$activeWorksheet->setCellValue('D6', 'Dia');
$activeWorksheet->setCellValue('E6', 'Mês');
$activeWorksheet->setCellValue('F6', 'Ano');
$activeWorksheet->setCellValue('C7', 'Quartos Bloqueados');
$activeWorksheet->setCellValue('D7', $quartos_bloqueados[1]);
$activeWorksheet->setCellValue('E7', $quartos_bloqueados[2]);
$activeWorksheet->setCellValue('F7', $quartos_bloqueados[3]);
$activeWorksheet->setCellValue('C8', 'Quartos Ocupados');
$activeWorksheet->setCellValue('D8', $quartos_ocupados[1]);
$activeWorksheet->setCellValue('E8', $quartos_ocupados[2]);
$activeWorksheet->setCellValue('F8', $quartos_ocupados[3]);
$activeWorksheet->setCellValue('C9', 'Quartos Cortesia');
$activeWorksheet->setCellValue('D9', $quartos_cortesia[1]);
$activeWorksheet->setCellValue('E9', $quartos_cortesia[2]);
$activeWorksheet->setCellValue('F9', $quartos_cortesia[3]);
$activeWorksheet->setCellValue('C10', 'Quartos House Use');
$activeWorksheet->setCellValue('D10', $quartos_houseuse[1]);
$activeWorksheet->setCellValue('E10', $quartos_houseuse[2]);
$activeWorksheet->setCellValue('F10', $quartos_houseuse[3]);
$activeWorksheet->setCellValue('H10', 'Justificativas:');
$activeWorksheet->setCellValue('H11', $comentario_gerencial);
$activeWorksheet->mergeCells('H11:N16');
$activeWorksheet->setCellValue('C11', 'Ocupação');
$activeWorksheet->setCellValue('D11', number_format((floatval(str_replace(array(',', '.'), '', $quartos_ocupados[1]) / 100) - intval(str_replace(array(',', '.'), '', $quartos_houseuse[1]) / 100) + intval(str_replace(array(',', '.'), '', $quartos_dayuse[1]) / 100))/ intval(str_replace(array(',', '.'), '', $quartos_construidos[1]) / 100) * 100, 2, '.').'%');
$activeWorksheet->setCellValue('E11', number_format((floatval(str_replace(array(',', '.'), '', $quartos_ocupados[2]) / 100) - intval(str_replace(array(',', '.'), '', $quartos_houseuse[2]) / 100) + intval(str_replace(array(',', '.'), '', $quartos_dayuse[2]) / 100))/ intval(str_replace(array(',', '.'), '', $quartos_construidos[2]) / 100) * 100, 2, '.').'%');
$activeWorksheet->setCellValue('F11', number_format((floatval(str_replace(array(',', '.'), '', $quartos_ocupados[3]) / 100) - intval(str_replace(array(',', '.'), '', $quartos_houseuse[3]) / 100) + intval(str_replace(array(',', '.'), '', $quartos_dayuse[3]) / 100))/ intval(str_replace(array(',', '.'), '', $quartos_construidos[3]) / 100) * 100, 2, '.').'%');
$activeWorksheet->setCellValue('C12', 'Diária Média');
$activeWorksheet->setCellValue('D12', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[1]) / 100) / intval(str_replace(array(',', '.'), '', $quartos_ocupados[1]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('E12', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[2]) / 100) / intval(str_replace(array(',', '.'), '', $quartos_ocupados[2]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('F12', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[3]) / 100) / intval(str_replace(array(',', '.'), '', $quartos_ocupados[3]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('C13', 'Rev Par');
$activeWorksheet->setCellValue('D13', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[1]) / 100) / intval(str_replace(array(',', '.'), '', $quartos_construidos[1]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('E13', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[2]) / 100) / intval(str_replace(array(',', '.'), '', $quartos_construidos[2]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('F13', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[3]) / 100) / intval(str_replace(array(',', '.'), '', $quartos_construidos[3]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('C14', 'Total Hospedes');
$activeWorksheet->setCellValue('D14', $hospedes[1]);
$activeWorksheet->setCellValue('E14', $hospedes[2]);
$activeWorksheet->setCellValue('F14', $hospedes[3]);
$activeWorksheet->setCellValue('C15', 'No Show');
$activeWorksheet->setCellValue('D15', $noshow[1]);
$activeWorksheet->setCellValue('E15', $noshow[2]);
$activeWorksheet->setCellValue('F15', $noshow[3]);
$activeWorksheet->setCellValue('C16', 'Linha');
$activeWorksheet->setCellValue('D16', 'Dia');
$activeWorksheet->setCellValue('E16', 'Mês');
$activeWorksheet->setCellValue('F16', 'Ano');
$activeWorksheet->setCellValue('C17', 'Café Incluso');
$activeWorksheet->setCellValue('D17', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $cafe_incluso[1]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('E17', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $cafe_incluso[2]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('F17', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $cafe_incluso[3]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('C18', 'Café Passante');
$activeWorksheet->setCellValue('D18', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $cafe_passante[1]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('E18', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $cafe_passante[2]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('F18', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $cafe_passante[3]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('H18', 'Sales Analyze by Product');
$activeWorksheet->mergeCells('H18:N18');
$activeWorksheet->setCellValue('C19', 'Linha');
$activeWorksheet->setCellValue('D19', 'Dia');
$activeWorksheet->setCellValue('E19', 'Mês');
$activeWorksheet->setCellValue('F19', 'Ano');
$activeWorksheet->setCellValue('H19', 'Linha');
$activeWorksheet->setCellValue('I19', 'Dia');
$activeWorksheet->mergeCells('I19:J19');
$activeWorksheet->setCellValue('K19', 'Mês');
$activeWorksheet->mergeCells('K19:L19');
$activeWorksheet->setCellValue('M19', 'Ano');
$activeWorksheet->mergeCells('M19:N19');
$activeWorksheet->setCellValue('C20', 'Receita Hospedagem');
$activeWorksheet->setCellValue('D20', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[1]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('E20', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[2]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('F20', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_hospedagem[3]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('H20', 'Receita Hospedagem');
$activeWorksheet->setCellValue('I20', 'R$'.$sales_hospedagem_dia);
$activeWorksheet->setCellValue('J20', $dif_receita_hospedagem_dia);
$activeWorksheet->setCellValue('K20', 'R$'.$sales_hospedagem_mes);
$activeWorksheet->setCellValue('L20', $dif_receita_hospedagem_mes);
$activeWorksheet->setCellValue('M20', 'R$'.$sales_hospedagem_ano);
$activeWorksheet->setCellValue('N20', $dif_receita_hospedagem_ano);
$activeWorksheet->setCellValue('C21', 'Receita A&B');
$activeWorksheet->setCellValue('D21', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[1]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('E21', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[2]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('F21', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_aeb[3]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('H21', 'Receita A&B');
$activeWorksheet->setCellValue('I21', 'R$'.$sales_aeb_dia);
$activeWorksheet->setCellValue('J21', $dif_receita_aeb_dia);
$activeWorksheet->setCellValue('K21', 'R$'.$sales_aeb_mes);
$activeWorksheet->setCellValue('L21', $dif_receita_aeb_mes);
$activeWorksheet->setCellValue('M21', 'R$'.$sales_aeb_ano);
$activeWorksheet->setCellValue('N21', $dif_receita_aeb_ano);
$activeWorksheet->setCellValue('C22', 'Receita Eventos');
$activeWorksheet->setCellValue('D22', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_eventos[1]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('E22', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_eventos[2]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('F22', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_eventos[3]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('H22', 'Receita Eventos');
$activeWorksheet->setCellValue('I22', 'R$'.$sales_eventos_dia);
$activeWorksheet->setCellValue('J22', $dif_receita_eventos_dia);
$activeWorksheet->setCellValue('K22', 'R$'.$sales_eventos_mes);
$activeWorksheet->setCellValue('L22', $dif_receita_eventos_mes);
$activeWorksheet->setCellValue('M22', 'R$'.$sales_eventos_ano);
$activeWorksheet->setCellValue('N22', $dif_receita_eventos_ano);
$activeWorksheet->setCellValue('C23', 'Receita Diversos');
$activeWorksheet->setCellValue('D23', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[1]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('E23', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[2]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('F23', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_diversos[3]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('H23', 'Receita Diversos');
$activeWorksheet->setCellValue('I23', 'R$'.$sales_outros_dia);
$activeWorksheet->setCellValue('J23', $dif_receita_diversos_dia);
$activeWorksheet->setCellValue('K23', 'R$'.$sales_outros_mes);
$activeWorksheet->setCellValue('L23', $dif_receita_diversos_mes);
$activeWorksheet->setCellValue('M23', 'R$'.$sales_outros_ano);
$activeWorksheet->setCellValue('N23', $dif_receita_diversos_ano);
$activeWorksheet->setCellValue('C24', 'Receita Total');
$activeWorksheet->setCellValue('D24', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_total[1]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('E24', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_total[2]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('F24', 'R$'.number_format(floatval(str_replace(array(',', '.'), '', $receita_total[3]) / 100), 2, ',', '.'));
$activeWorksheet->setCellValue('H24', 'Receita Total');
$activeWorksheet->setCellValue('I24', 'R$'.$sales_total_dia);
$activeWorksheet->setCellValue('J24', $dif_receita_total_dia);
$activeWorksheet->setCellValue('K24', 'R$'.$sales_total_mes);
$activeWorksheet->setCellValue('L24', $dif_receita_total_mes);
$activeWorksheet->setCellValue('M24', 'R$'.$sales_total_ano);
$activeWorksheet->setCellValue('N24', $dif_receita_total_ano);
$activeWorksheet->setCellValue('C28', $_SESSION['name'].' | '.$data_quando.'h');
$activeWorksheet->mergeCells('C28:F28');
$activeWorksheet->setCellValue('C29', 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('C29:F29');
$activeWorksheet->setCellValue('H28', '');
$activeWorksheet->mergeCells('H28:N28');
$activeWorksheet->setCellValue('H29', 'Assinatura Digital N + 1');
$activeWorksheet->mergeCells('H29:N29');


$activeWorksheet->setShowGridlines(false);
$activeWorksheet->setShowRowColHeaders(false);
$activeWorksheet->getRowDimension(1)->setRowHeight(10);
$activeWorksheet->getColumnDimension('A')->setWidth(2);
$activeWorksheet->getColumnDimension('B')->setWidth(2);
$activeWorksheet->getColumnDimension('C')->setWidth(24);
$activeWorksheet->getColumnDimension('D')->setWidth(15);
$activeWorksheet->getColumnDimension('E')->setWidth(15);
$activeWorksheet->getColumnDimension('F')->setWidth(15);
$activeWorksheet->getColumnDimension('G')->setWidth(2);
$activeWorksheet->getColumnDimension('H')->setWidth(24);
$activeWorksheet->getColumnDimension('I')->setWidth(15);
$activeWorksheet->getColumnDimension('K')->setWidth(15);
$activeWorksheet->getColumnDimension('M')->setWidth(15);
$activeWorksheet->getColumnDimension('J')->setWidth(2);
$activeWorksheet->getColumnDimension('L')->setWidth(2);
$activeWorksheet->getColumnDimension('N')->setWidth(2);
$activeWorksheet->getColumnDimension('O')->setWidth(2);
$activeWorksheet->getColumnDimension('P')->setWidth(2);

// Inserir uma imagem
$imagePath = '../imagem/logo_all.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('ALL');
$objDrawing->setDescription('ALL');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('I5'); // Posição onde a imagem será inserida
$objDrawing->setWorksheet($activeWorksheet);

$spreadsheet->getActiveSheet()->getStyle('C8:F8')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('C10:F10')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('C12:F12')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('C14:F14')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('C18:F18')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('C21:F21')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('H21:N21')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('C23:F23')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('H23:N23')->applyFromArray($styleArray_separacao);

$spreadsheet->getActiveSheet()->getStyle('C3:N3')->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('B27:P30')->applyFromArray($styleArray_cinza);

$spreadsheet->getActiveSheet()->getStyle('C5:F5')->applyFromArray($styleArray_laranja);
$spreadsheet->getActiveSheet()->getStyle('H18:N18')->applyFromArray($styleArray_laranja);

$spreadsheet->getActiveSheet()->getStyle('C6:F6')->applyFromArray($styleArray_amarelo);
$spreadsheet->getActiveSheet()->getStyle('C16:F16')->applyFromArray($styleArray_amarelo);
$spreadsheet->getActiveSheet()->getStyle('C19:F19')->applyFromArray($styleArray_amarelo);
$spreadsheet->getActiveSheet()->getStyle('H19:N19')->applyFromArray($styleArray_amarelo);

$spreadsheet->getActiveSheet()->getStyle('C28:F28')->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('H28:N28')->applyFromArray($styleArray_branco5);

$spreadsheet->getActiveSheet()->getStyle('C29:F29')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('H29:N29')->applyFromArray($styleArray_preto);

$spreadsheet->getActiveSheet()->getStyle('J20:J24')->applyFromArray($styleArray_verde);
$spreadsheet->getActiveSheet()->getStyle('L20:L24')->applyFromArray($styleArray_verde);
$spreadsheet->getActiveSheet()->getStyle('N20:N24')->applyFromArray($styleArray_verde);

$activeWorksheet->getStyle('C5:F24')->applyFromArray($styleArray_inside_borders);
$activeWorksheet->getStyle('H18:N24')->applyFromArray($styleArray_inside_borders);

//Aplicar Condição
$range = 'J20:N24';
$conditionalStyles = $activeWorksheet->getStyle($range)->getConditionalStyles();
$conditionalStyles[] = $conditionRed;
$activeWorksheet->getStyle($range)->setConditionalStyles($conditionalStyles);

$activeWorksheet->getStyle('C3:N3')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C5:F24')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('H18:N24')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B27:P30')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C28:F29')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('H28:N29')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B2:P30')->applyFromArray($styleArray_outside_borders);

$activeWorksheet->getStyle('H10')->applyFromArray($styleArray_bold);
$styleArray = [
    'borders' => [
        'outline' => [
            'borderStyle' => 'thick',
            'color' => ['rgb' => '000000'],
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_TOP,
    ],
];
$activeWorksheet->getStyle('H11:N16')->applyFromArray($styleArray);

$activeWorksheet->getStyle('D12:F13')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('D17:F18')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('D20:F24')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('I20:I24')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('K20:K24')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('M20:M24')->getNumberFormat()->setFormatCode('R$ #,##0.00');

$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setTitle('Gerencial');
$worksheet->setSelectedCell('A1');

//Segunda Aba (Conferencia de Diarias)
$activeWorksheet = $spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndexByName('Worksheet');
$spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
$protection = $spreadsheet->getActiveSheet()->getProtection();
$protection->setPassword("$worksheet_password");
$protection->setSheet(true);
$protection->setSort(false);
$protection->setInsertRows(false);
$protection->setFormatCells(false);

$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setTitle('Conferencia de Diárias');
$worksheet->setSelectedCell('A1');

$activeWorksheet->setCellValue('C3', $hotel);
$activeWorksheet->mergeCells('C3:K3');
$activeWorksheet->setCellValue('C5', 'Conferencia de Diárias');
$activeWorksheet->mergeCells('C5:I5');
$activeWorksheet->mergeCells('C7:I7');
$activeWorksheet->setCellValue('C9', 'Apto');
$activeWorksheet->setCellValue('D9', 'Hospede');
$activeWorksheet->setCellValue('E9', 'Checkin');
$activeWorksheet->setCellValue('F9', 'Checkout');
$activeWorksheet->setCellValue('G9', 'Rate Code');
$activeWorksheet->setCellValue('H9', 'Diária');
$activeWorksheet->setCellValue('I9', 'Comentário');

//Conferencia Diárias
$quantidade_dados = count($_SESSION['dados_ratecheck']);

$linha_excel = 9;

$dados_ratecheck = $_SESSION['dados_ratecheck'];


function compararPorDataAuditoriaEApartamento($a, $b) {
    global $data_auditoria;

    if ($a['checkin'] == $data_auditoria && $b['checkin'] != $data_auditoria) {
        return -1;
    } elseif ($a['checkin'] != $data_auditoria && $b['checkin'] == $data_auditoria) {
        return 1;
    } else {
        return strcmp($a['room_number'], $b['room_number']);
    }
}

usort($dados_ratecheck, 'compararPorDataAuditoriaEApartamento');

foreach ($dados_ratecheck as $select) {
    $room_number = $select['room_number'];
    $guest_name = $select['guest_name'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $ratecode = $select['ratecode'];
    $room_rate = $select['room_rate'];
    $comentario = $select['comentario'];

    $linha_excel++;

    if($linha_excel % 2 != 0){
    $spreadsheet->getActiveSheet()->getStyle('C'.$linha_excel.':I'.$linha_excel)->applyFromArray($styleArray_separacao); 
    }

$activeWorksheet->setCellValue('C'.$linha_excel, $room_number);
$activeWorksheet->setCellValue('D'.$linha_excel, $guest_name);
$activeWorksheet->setCellValue('E'.$linha_excel, date('d/m/Y', strtotime("$checkin")));
$activeWorksheet->setCellValue('F'.$linha_excel, date('d/m/Y', strtotime("$checkout")));
$activeWorksheet->setCellValue('G'.$linha_excel, $ratecode);
$activeWorksheet->setCellValue('H'.$linha_excel, $room_rate);
$activeWorksheet->setCellValue('I'.$linha_excel, $comentario);

}

$activeWorksheet->setCellValue('C7', 'Apartamentos Ocupados: '.$quantidade_dados);

//Manter no Minimo 3 linhas
if($linha_excel < 10){
$linhas = $linha_excel;
for($i = $linhas ; $i < 10 ; $i++){
$linha_excel++;
$activeWorksheet->setCellValue('C'.$linha_excel, '');
$activeWorksheet->setCellValue('D'.$linha_excel, '');
$activeWorksheet->setCellValue('E'.$linha_excel, '');
$activeWorksheet->setCellValue('F'.$linha_excel, '');
$activeWorksheet->setCellValue('G'.$linha_excel, '');
$activeWorksheet->setCellValue('H'.$linha_excel, '');
$activeWorksheet->setCellValue('I'.$linha_excel, '');
}
}

$activeWorksheet->getStyle('H7:H'.$linha_excel)->getNumberFormat()->setFormatCode('R$ #,##0.00');

//Assinatura Digital
$activeWorksheet->setCellValue('D'.($linha_excel + 4), $_SESSION['name'].' | '.$data_quando.'h');
$activeWorksheet->mergeCells('D'.($linha_excel + 4).':E'.($linha_excel + 4));
$activeWorksheet->setCellValue('D'.($linha_excel + 5), 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('D'.($linha_excel + 5).':E'.($linha_excel + 5));
$activeWorksheet->setCellValue('G'.($linha_excel + 4), '');
$activeWorksheet->mergeCells('G'.($linha_excel + 4).':J'.($linha_excel + 4));
$activeWorksheet->setCellValue('G'.($linha_excel + 5), 'Assinatura Digital N + 1');
$activeWorksheet->mergeCells('G'.($linha_excel + 5).':J'.($linha_excel + 5));

// Inserir uma imagem
$imagePath = '../imagem/logo_all.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('ALL');
$objDrawing->setDescription('ALL');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('K5'); // Posição onde a imagem será inserida
$objDrawing->setWorksheet($activeWorksheet);

//Definir Tamanhos
$activeWorksheet->setShowGridlines(false);
$activeWorksheet->setShowRowColHeaders(false);
$activeWorksheet->getRowDimension(1)->setRowHeight(10);
$activeWorksheet->getColumnDimension('A')->setWidth(2);
$activeWorksheet->getColumnDimension('B')->setWidth(2);
$activeWorksheet->getColumnDimension('D')->setWidth(45);
$activeWorksheet->getColumnDimension('E')->setWidth(11);
$activeWorksheet->getColumnDimension('F')->setWidth(11);
$activeWorksheet->getColumnDimension('H')->setWidth(12);
$activeWorksheet->getColumnDimension('I')->setWidth(26);
$activeWorksheet->getColumnDimension('J')->setWidth(2);
$activeWorksheet->getColumnDimension('K')->setWidth(21);
$activeWorksheet->getColumnDimension('L')->setWidth(2);

//Colocar as bordas
$activeWorksheet->getStyle('C9:I'.$linha_excel)->applyFromArray($styleArray_inside_borders);

$activeWorksheet->getStyle('C3:K3')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C5:I'.$linha_excel)->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('D'.($linha_excel + 4).':E'.($linha_excel + 5))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('G'.($linha_excel + 4).':J'.($linha_excel + 5))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B'.($linha_excel + 3).':L'.($linha_excel + 6))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B2:L'.($linha_excel + 1))->applyFromArray($styleArray_outside_borders);

$spreadsheet->getActiveSheet()->getStyle('C3:K3')->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('B'.($linha_excel + 3).':L'.($linha_excel + 6))->applyFromArray($styleArray_cinza);

$spreadsheet->getActiveSheet()->getStyle('C5:I5')->applyFromArray($styleArray_laranja);

$spreadsheet->getActiveSheet()->getStyle('C9:I9')->applyFromArray($styleArray_amarelo);

$spreadsheet->getActiveSheet()->getStyle('C7:I7')->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 4).':E'.($linha_excel + 4))->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 4).':J'.($linha_excel + 4))->applyFromArray($styleArray_branco5);

$spreadsheet->getActiveSheet()->getStyle('C6:I6')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('C8:I8')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 5).':E'.($linha_excel + 5))->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 5).':J'.($linha_excel + 5))->applyFromArray($styleArray_preto);

$spreadsheet->getActiveSheet()->getStyle('C7:I7')->applyFromArray($styleArray_bold);

$worksheet->setSelectedCell('A1');


//Terceira Aba (Saldo Elevado)
$activeWorksheet = $spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndexByName('Worksheet');
$spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
$protection = $spreadsheet->getActiveSheet()->getProtection();
$protection->setPassword("$worksheet_password");
$protection->setSheet(true);
$protection->setSort(false);
$protection->setInsertRows(false);
$protection->setFormatCells(false);

$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setTitle('Saldo Elevado');
$worksheet->setSelectedCell('A1');

$activeWorksheet->setCellValue('C3', $hotel);
$activeWorksheet->mergeCells('C3:J3');
$activeWorksheet->setCellValue('C5', 'Saldo Elevado');
$activeWorksheet->mergeCells('C5:H5');
$activeWorksheet->mergeCells('C7:H7');
$activeWorksheet->setCellValue('C9', 'Apto');
$activeWorksheet->setCellValue('D9', 'Hospede');
$activeWorksheet->setCellValue('E9', 'Checkin');
$activeWorksheet->setCellValue('F9', 'Checkout');
$activeWorksheet->setCellValue('G9', 'Balance');
$activeWorksheet->setCellValue('H9', 'Comentário');

//Saldo Elevado
$linha_excel = 9;

$dados_creditlimit = array_filter($_SESSION['dados_creditlimit'], function($select) use ($limite_credito) {
    return $select['balance'] > $limite_credito && $select['room_number'] > 0;
});

foreach ($dados_creditlimit as $select) {
    $room_number = $select['room_number'];
    $guest_name = $select['guest_name'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $balance = $select['balance'];
    $comentario = $select['comentario'];

    $linha_excel++;

    if($linha_excel % 2 != 0){
    $spreadsheet->getActiveSheet()->getStyle('C'.$linha_excel.':H'.$linha_excel)->applyFromArray($styleArray_separacao); 
    }

$activeWorksheet->setCellValue('C'.$linha_excel, $room_number);
$activeWorksheet->setCellValue('D'.$linha_excel, $guest_name);
$activeWorksheet->setCellValue('E'.$linha_excel, date('d/m/Y', strtotime("$checkin")));
$activeWorksheet->setCellValue('F'.$linha_excel, date('d/m/Y', strtotime("$checkout")));
$activeWorksheet->setCellValue('G'.$linha_excel, $balance);
$activeWorksheet->setCellValue('H'.$linha_excel, $comentario);

}

$activeWorksheet->setCellValue('C7', 'Limite de Crédito: R$'.number_format($limite_credito, 2, ',', '.'));

//Manter no Minimo 3 linhas
if($linha_excel < 10){
$linhas = $linha_excel;
for($i = $linhas ; $i < 10 ; $i++){
$linha_excel++;
$activeWorksheet->setCellValue('C'.$linha_excel, '');
$activeWorksheet->setCellValue('D'.$linha_excel, '');
$activeWorksheet->setCellValue('E'.$linha_excel, '');
$activeWorksheet->setCellValue('F'.$linha_excel, '');
$activeWorksheet->setCellValue('G'.$linha_excel, '');
$activeWorksheet->setCellValue('H'.$linha_excel, '');
}
}

$activeWorksheet->getStyle('G7:G'.$linha_excel)->getNumberFormat()->setFormatCode('R$ #,##0.00');

//Assinatura Digital
$activeWorksheet->setCellValue('D'.($linha_excel + 4), $_SESSION['name'].' | '.$data_quando.'h');
$activeWorksheet->mergeCells('D'.($linha_excel + 4).':E'.($linha_excel + 4));
$activeWorksheet->setCellValue('D'.($linha_excel + 5), 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('D'.($linha_excel + 5).':E'.($linha_excel + 5));
$activeWorksheet->setCellValue('G'.($linha_excel + 4), '');
$activeWorksheet->mergeCells('G'.($linha_excel + 4).':I'.($linha_excel + 4));
$activeWorksheet->setCellValue('G'.($linha_excel + 5), 'Assinatura Digital N + 1');
$activeWorksheet->mergeCells('G'.($linha_excel + 5).':I'.($linha_excel + 5));

// Inserir uma imagem
$imagePath = '../imagem/logo_all.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('ALL');
$objDrawing->setDescription('ALL');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('J5'); // Posição onde a imagem será inserida
$objDrawing->setWorksheet($activeWorksheet);

//Definir Tamanhos
$activeWorksheet->setShowGridlines(false);
$activeWorksheet->setShowRowColHeaders(false);
$activeWorksheet->getRowDimension(1)->setRowHeight(10);
$activeWorksheet->getColumnDimension('A')->setWidth(2);
$activeWorksheet->getColumnDimension('B')->setWidth(2);
$activeWorksheet->getColumnDimension('D')->setWidth(45);
$activeWorksheet->getColumnDimension('E')->setWidth(11);
$activeWorksheet->getColumnDimension('F')->setWidth(11);
$activeWorksheet->getColumnDimension('G')->setWidth(15);
$activeWorksheet->getColumnDimension('H')->setWidth(35);
$activeWorksheet->getColumnDimension('I')->setWidth(2);
$activeWorksheet->getColumnDimension('J')->setWidth(21);
$activeWorksheet->getColumnDimension('K')->setWidth(2);

//Colocar as bordas
$activeWorksheet->getStyle('C9:H'.$linha_excel)->applyFromArray($styleArray_inside_borders);

$activeWorksheet->getStyle('C3:J3')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C5:H'.$linha_excel)->applyFromArray($styleArray);
$activeWorksheet->getStyle('D'.($linha_excel + 4).':E'.($linha_excel + 5))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('G'.($linha_excel + 4).':I'.($linha_excel + 5))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B'.($linha_excel + 3).':K'.($linha_excel + 6))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B2:K'.($linha_excel + 1))->applyFromArray($styleArray_outside_borders);

$spreadsheet->getActiveSheet()->getStyle('C3:J3')->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('B'.($linha_excel + 3).':K'.($linha_excel + 6))->applyFromArray($styleArray_cinza);

$spreadsheet->getActiveSheet()->getStyle('C5:H5')->applyFromArray($styleArray_laranja);

$spreadsheet->getActiveSheet()->getStyle('C9:H9')->applyFromArray($styleArray_amarelo);

$spreadsheet->getActiveSheet()->getStyle('C7:H7')->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 4).':E'.($linha_excel + 4))->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 4).':I'.($linha_excel + 4))->applyFromArray($styleArray_branco5);

$spreadsheet->getActiveSheet()->getStyle('C6:H6')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('C8:H8')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 5).':E'.($linha_excel + 5))->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 5).':I'.($linha_excel + 5))->applyFromArray($styleArray_preto);

$spreadsheet->getActiveSheet()->getStyle('C7:H7')->applyFromArray($styleArray_bold);

$worksheet->setSelectedCell('A1');

//Quarta Aba (Controle du Bac)
$activeWorksheet = $spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndexByName('Worksheet');
$spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
$protection = $spreadsheet->getActiveSheet()->getProtection();
$protection->setPassword("$worksheet_password");
$protection->setSheet(true);
$protection->setSort(false);
$protection->setInsertRows(false);
$protection->setFormatCells(false);

$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setTitle('Controle du Bac');
$worksheet->setSelectedCell('A1');

$activeWorksheet->setCellValue('C3', $hotel);
$activeWorksheet->mergeCells('C3:W3');
$activeWorksheet->setCellValue('C5', 'Controle du Bac');
$activeWorksheet->mergeCells('C5:U5');
$activeWorksheet->mergeCells('C7:U7');
$activeWorksheet->mergeCells('C9:G9');
$activeWorksheet->setCellValue('H9', 'Diária + Extra');
$activeWorksheet->mergeCells('I9:L9');
$activeWorksheet->setCellValue('I9', 'Garantias ($)');
$activeWorksheet->setCellValue('M9', 'Crédito');
$activeWorksheet->mergeCells('N9:Q9');
$activeWorksheet->setCellValue('N9', 'Pasta');
$activeWorksheet->mergeCells('R9:U9');
$activeWorksheet->setCellValue('R9', 'FNRH');
$activeWorksheet->setCellValue('C10', 'Qtd.');
$activeWorksheet->setCellValue('D10', 'Apto.');
$activeWorksheet->setCellValue('E10', 'Hospede');
$activeWorksheet->setCellValue('F10', 'Checkin');
$activeWorksheet->setCellValue('G10', 'Checkout');
$activeWorksheet->setCellValue('H10', 'Balance');
$activeWorksheet->setCellValue('I10', 'Diárias');
$activeWorksheet->setCellValue('J10', 'Extras');
$activeWorksheet->setCellValue('K10', 'Garantia');
$activeWorksheet->setCellValue('L10', 'Valor Diária');
$activeWorksheet->setCellValue('M10', 'Limite');
$activeWorksheet->setCellValue('N10', 'Pasta Limpa');
$activeWorksheet->setCellValue('O10', 'PDV');
$activeWorksheet->setCellValue('P10', 'Pasta');
$activeWorksheet->setCellValue('Q10', 'Assinado');
$activeWorksheet->setCellValue('R10', 'Adt.');
$activeWorksheet->setCellValue('S10', 'FNRH');
$activeWorksheet->setCellValue('T10', 'Chd.');
$activeWorksheet->setCellValue('U10', 'Doc');

if (date('d', strtotime($data_auditoria)) % 2 == 0) {
    $ordem_query = 'ASC';
} else {
    $ordem_query = 'DESC';
}

$dados_presentlist = $_SESSION['dados_presentlist'];
$quantidade_dados = count($dados_presentlist);

$limite = ceil($quantidade_dados * 0.3);

// Ordenar o array por 'room_number' em ordem ascendente ou descendente
usort($dados_presentlist, function($a, $b) use ($ordem_query) {
    if ($ordem_query == 'ASC') {
        return $a['room_number'] <=> $b['room_number'];
    } else {
        return $b['room_number'] <=> $a['room_number'];
    }
});

// Limitar o array aos primeiros $limite elementos
$dados_filtrados_bac = array_slice($dados_presentlist, 0, $limite);

$query_qtd = count($dados_filtrados_bac);

$linha_excel = 10;
$qtd = 0;

foreach ($dados_filtrados_bac as $select) {
    $guest_name = $select['guest_name'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $adultos = $select['adultos'];
    $criancas = $select['criancas'];
    $room_balance = $select['room_balance'];
    $room_number = $select['room_number'];
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

    $linha_excel++;
    $qtd++;

    if($linha_excel % 2 == 0){
    $spreadsheet->getActiveSheet()->getStyle('D'.$linha_excel.':U'.$linha_excel)->applyFromArray($styleArray_separacao); 
    }

    if($room_balance > $limite_credito){
    $spreadsheet->getActiveSheet()->getStyle('M'.$linha_excel)->applyFromArray($styleArray_creditlimit);
    $limite_sim_nao = 'Não';
    }else{
    $limite_sim_nao = 'Sim';
    }

$activeWorksheet->setCellValue('C'.$linha_excel, $qtd);
$activeWorksheet->setCellValue('D'.$linha_excel, $room_number);
$activeWorksheet->setCellValue('E'.$linha_excel, $guest_name);
$activeWorksheet->setCellValue('F'.$linha_excel, date('d/m/Y', strtotime("$checkin")));
$activeWorksheet->setCellValue('G'.$linha_excel, date('d/m/Y', strtotime("$checkout")));
$activeWorksheet->setCellValue('H'.$linha_excel, $room_balance);
$activeWorksheet->setCellValue('I'.$linha_excel, $auditoria_diarias);
$activeWorksheet->setCellValue('J'.$linha_excel, $auditoria_extras);
$activeWorksheet->setCellValue('K'.$linha_excel, $auditoria_garantia);
$activeWorksheet->setCellValue('L'.$linha_excel, $auditoria_valor);
$activeWorksheet->setCellValue('M'.$linha_excel, $limite_sim_nao);
$activeWorksheet->setCellValue('N'.$linha_excel, $auditoria_pasta_limpa);
$activeWorksheet->setCellValue('O'.$linha_excel, $auditoria_pasta_pdv);
$activeWorksheet->setCellValue('P'.$linha_excel, $auditoria_pasta_pasta);
$activeWorksheet->setCellValue('Q'.$linha_excel, $auditoria_pasta_ass);
$activeWorksheet->setCellValue('R'.$linha_excel, $adultos);
$activeWorksheet->setCellValue('S'.$linha_excel, $auditoria_fnrh);
$activeWorksheet->setCellValue('T'.$linha_excel, $criancas);
$activeWorksheet->setCellValue('U'.$linha_excel, $auditoria_doc);

}

//Manter no Minimo 3 linhas
if($linha_excel < 14){
$linhas = $linha_excel;
for($i = $linhas ; $i < 14 ; $i++){
$linha_excel++;
$activeWorksheet->setCellValue('C'.$linha_excel, '');
$activeWorksheet->setCellValue('D'.$linha_excel, '');
$activeWorksheet->setCellValue('E'.$linha_excel, '');
$activeWorksheet->setCellValue('F'.$linha_excel, '');
$activeWorksheet->setCellValue('G'.$linha_excel, '');
$activeWorksheet->setCellValue('H'.$linha_excel, '');
$activeWorksheet->setCellValue('I'.$linha_excel, '');
$activeWorksheet->setCellValue('J'.$linha_excel, '');
$activeWorksheet->setCellValue('K'.$linha_excel, '');
$activeWorksheet->setCellValue('L'.$linha_excel, '');
$activeWorksheet->setCellValue('M'.$linha_excel, '');
$activeWorksheet->setCellValue('N'.$linha_excel, '');
$activeWorksheet->setCellValue('O'.$linha_excel, '');
$activeWorksheet->setCellValue('P'.$linha_excel, '');
$activeWorksheet->setCellValue('Q'.$linha_excel, '');
$activeWorksheet->setCellValue('R'.$linha_excel, '');
$activeWorksheet->setCellValue('S'.$linha_excel, '');
$activeWorksheet->setCellValue('T'.$linha_excel, '');
$activeWorksheet->setCellValue('U'.$linha_excel, '');
}
}

//Condição
$range = 'H11:H'.$linha_excel;
$worksheet->getStyle($range)->setConditionalStyles([$conditionRedBalance]);

$activeWorksheet->setCellValue('C7', 'Total Uhs Ocupadas: '.$quantidade_dados.' | 30% a Serem Conferidas: '.$query_qtd);
$activeWorksheet->setCellValue('C'.($linha_excel + 2), 'Comentarios');
$activeWorksheet->setCellValue('C'.($linha_excel + 3), $comentario_bac);

$activeWorksheet->getStyle('G11:J'.$linha_excel)->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('L11:L'.$linha_excel)->getNumberFormat()->setFormatCode('R$ #,##0.00');

//Assinatura Digital
$activeWorksheet->setCellValue('E'.($linha_excel + 10), $_SESSION['name'].' | '.$data_quando.'h');
$activeWorksheet->mergeCells('E'.($linha_excel + 10).':H'.($linha_excel + 10));
$activeWorksheet->setCellValue('E'.($linha_excel + 11), 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('E'.($linha_excel + 11).':H'.($linha_excel + 11));
$activeWorksheet->setCellValue('M'.($linha_excel + 10), '');
$activeWorksheet->mergeCells('M'.($linha_excel + 10).':S'.($linha_excel + 10));
$activeWorksheet->setCellValue('M'.($linha_excel + 11), 'Assinatura Digital N + 1');
$activeWorksheet->mergeCells('M'.($linha_excel + 11).':S'.($linha_excel + 11));

// Inserir uma imagem
$imagePath = '../imagem/logo_all.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('ALL');
$objDrawing->setDescription('ALL');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('W5'); // Posição onde a imagem será inserida
$objDrawing->setWorksheet($activeWorksheet);

//Definir Tamanhos
$activeWorksheet->setShowGridlines(false);
$activeWorksheet->setShowRowColHeaders(false);
$activeWorksheet->getRowDimension(1)->setRowHeight(10);
$activeWorksheet->getColumnDimension('A')->setWidth(2);
$activeWorksheet->getColumnDimension('B')->setWidth(2);
$activeWorksheet->getColumnDimension('E')->setWidth(45);
$activeWorksheet->getColumnDimension('F')->setWidth(11);
$activeWorksheet->getColumnDimension('G')->setWidth(11);
$activeWorksheet->getColumnDimension('H')->setWidth(15);
$activeWorksheet->getColumnDimension('I')->setWidth(15);
$activeWorksheet->getColumnDimension('J')->setWidth(15);
$activeWorksheet->getColumnDimension('K')->setWidth(20);
$activeWorksheet->getColumnDimension('L')->setWidth(15);
$activeWorksheet->getColumnDimension('N')->setWidth(11);
$activeWorksheet->getColumnDimension('V')->setWidth(2);
$activeWorksheet->getColumnDimension('W')->setWidth(22);
$activeWorksheet->getColumnDimension('X')->setWidth(2);

//Colocar as bordas
$activeWorksheet->getStyle('C9:U'.$linha_excel)->applyFromArray($styleArray_inside_borders);

$activeWorksheet->getStyle('C3:W3')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C5:U'.$linha_excel)->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('E'.($linha_excel + 10).':H'.($linha_excel + 11))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('M'.($linha_excel + 10).':S'.($linha_excel + 11))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('D'.($linha_excel + 9).':T'.($linha_excel + 12))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C'.($linha_excel + 3).':U'.($linha_excel + 6))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B2:X'.($linha_excel + 7))->applyFromArray($styleArray_outside_borders);

$spreadsheet->getActiveSheet()->getStyle('C3:W3')->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('C'.($linha_excel + 3).':U'.($linha_excel + 6))->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 9).':T'.($linha_excel + 12))->applyFromArray($styleArray_cinza);

$spreadsheet->getActiveSheet()->getStyle('C5:U5')->applyFromArray($styleArray_laranja);

$spreadsheet->getActiveSheet()->getStyle('C9:U10')->applyFromArray($styleArray_amarelo);
$spreadsheet->getActiveSheet()->getStyle('C11:C'.$linha_excel)->applyFromArray($styleArray_amarelo);

$spreadsheet->getActiveSheet()->getStyle('C7:U7')->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('E'.($linha_excel + 10).':H'.($linha_excel + 10))->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('M'.($linha_excel + 10).':S'.($linha_excel + 10))->applyFromArray($styleArray_branco5);

$spreadsheet->getActiveSheet()->getStyle('C6:U6')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('C8:U8')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('E'.($linha_excel + 11).':H'.($linha_excel + 11))->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('M'.($linha_excel + 11).':S'.($linha_excel + 11))->applyFromArray($styleArray_preto);

$spreadsheet->getActiveSheet()->getStyle('C7:U7')->applyFromArray($styleArray_bold);
$spreadsheet->getActiveSheet()->getStyle('C'.($linha_excel + 2))->applyFromArray($styleArray_bold);

$worksheet->setSelectedCell('A1');

//Quinta Aba (Controle de Garantias)
$activeWorksheet = $spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndexByName('Worksheet');
$spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
$protection = $spreadsheet->getActiveSheet()->getProtection();
$protection->setPassword("$worksheet_password");
$protection->setSheet(true);
$protection->setSort(false);
$protection->setInsertRows(false);
$protection->setFormatCells(false);

$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setTitle('Controle de Garantias');
$worksheet->setSelectedCell('A1');

$activeWorksheet->setCellValue('C3', $hotel);
$activeWorksheet->mergeCells('C3:K3');
$activeWorksheet->setCellValue('C5', 'Controle de Garantias');
$activeWorksheet->mergeCells('C5:I5');
$activeWorksheet->mergeCells('C7:I7');
$activeWorksheet->setCellValue('C9', 'Qtd.');
$activeWorksheet->setCellValue('D9', 'Apto.');
$activeWorksheet->setCellValue('E9', 'Hospede');
$activeWorksheet->setCellValue('F9', 'Checkin');
$activeWorksheet->setCellValue('G9', 'Checkout');
$activeWorksheet->setCellValue('H9', 'Diária');
$activeWorksheet->setCellValue('I9', 'Garantia');

  
$dados_presentlist = $_SESSION['dados_presentlist'];

$dados_filtrados_garantias = array_filter($dados_presentlist, function($item) use ($data_auditoria) {
    return $item['checkin'] == $data_auditoria;
});

// Ordenar o array por 'room_number'
usort($dados_filtrados_garantias, function($a, $b) {
    return $a['room_number'] <=> $b['room_number'];
});

$quantidade_dados = count($dados_filtrados_garantias);

$linha_excel = 9;
$qtd = 0;

foreach ($dados_filtrados_garantias as $select) {
    $room_number = $select['room_number'];
    $guest_name = $select['guest_name'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $auditoria_valor = $select['auditoria_valor'];
    $auditoria_garantia = $select['auditoria_garantia'];

    $linha_excel++;
    $qtd++;

    if($linha_excel % 2 != 0){
    $spreadsheet->getActiveSheet()->getStyle('C'.$linha_excel.':I'.$linha_excel)->applyFromArray($styleArray_separacao); 
    }

$activeWorksheet->setCellValue('C'.$linha_excel, $qtd);
$activeWorksheet->setCellValue('D'.$linha_excel, $room_number);
$activeWorksheet->setCellValue('E'.$linha_excel, $guest_name);
$activeWorksheet->setCellValue('F'.$linha_excel, date('d/m/Y', strtotime("$checkin")));
$activeWorksheet->setCellValue('G'.$linha_excel, date('d/m/Y', strtotime("$checkout")));
$activeWorksheet->setCellValue('H'.$linha_excel, $auditoria_valor);
$activeWorksheet->setCellValue('I'.$linha_excel, $auditoria_garantia);

}

//Manter no Minimo 3 linhas
if($linha_excel < 13){
$linhas = $linha_excel;
for($i = $linhas ; $i < 13 ; $i++){
$linha_excel++;
$activeWorksheet->setCellValue('C'.$linha_excel, '');
$activeWorksheet->setCellValue('D'.$linha_excel, '');
$activeWorksheet->setCellValue('E'.$linha_excel, '');
$activeWorksheet->setCellValue('F'.$linha_excel, '');
$activeWorksheet->setCellValue('G'.$linha_excel, '');
$activeWorksheet->setCellValue('H'.$linha_excel, '');
$activeWorksheet->setCellValue('I'.$linha_excel, '');
}
}

$activeWorksheet->setCellValue('C7', 'Chegadas do Dia: '.$quantidade_dados);
$activeWorksheet->setCellValue('C'.($linha_excel + 2), 'Comentarios');
$activeWorksheet->setCellValue('C'.($linha_excel + 3), $comentario_garantias);

$activeWorksheet->getStyle('H10:H'.$linha_excel)->getNumberFormat()->setFormatCode('R$ #,##0.00');

//Assinatura Digital
$activeWorksheet->setCellValue('D'.($linha_excel + 10), $_SESSION['name'].' | '.$data_quando.'h');
$activeWorksheet->mergeCells('D'.($linha_excel + 10).':E'.($linha_excel + 10));
$activeWorksheet->setCellValue('D'.($linha_excel + 11), 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('D'.($linha_excel + 11).':E'.($linha_excel + 11));
$activeWorksheet->setCellValue('G'.($linha_excel + 10), '');
$activeWorksheet->mergeCells('G'.($linha_excel + 10).':J'.($linha_excel + 10));
$activeWorksheet->setCellValue('G'.($linha_excel + 11), 'Assinatura Digital N + 1');
$activeWorksheet->mergeCells('G'.($linha_excel + 11).':J'.($linha_excel + 11));

// Inserir uma imagem
$imagePath = '../imagem/logo_all.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('ALL');
$objDrawing->setDescription('ALL');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('K5'); // Posição onde a imagem será inserida
$objDrawing->setWorksheet($activeWorksheet);

//Definir Tamanhos
$activeWorksheet->setShowGridlines(false);
$activeWorksheet->setShowRowColHeaders(false);
$activeWorksheet->getRowDimension(1)->setRowHeight(10);
$activeWorksheet->getColumnDimension('A')->setWidth(2);
$activeWorksheet->getColumnDimension('B')->setWidth(2);
$activeWorksheet->getColumnDimension('E')->setWidth(45);
$activeWorksheet->getColumnDimension('F')->setWidth(11);
$activeWorksheet->getColumnDimension('G')->setWidth(11);
$activeWorksheet->getColumnDimension('H')->setWidth(12);
$activeWorksheet->getColumnDimension('I')->setWidth(30);
$activeWorksheet->getColumnDimension('J')->setWidth(2);
$activeWorksheet->getColumnDimension('K')->setWidth(22);
$activeWorksheet->getColumnDimension('L')->setWidth(2);

//Colocar as bordas
$activeWorksheet->getStyle('C9:I'.$linha_excel)->applyFromArray($styleArray_inside_borders);

$activeWorksheet->getStyle('C3:K3')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C5:I'.$linha_excel)->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('D'.($linha_excel + 10).':E'.($linha_excel + 11))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('G'.($linha_excel + 10).':J'.($linha_excel + 11))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B'.($linha_excel + 9).':L'.($linha_excel + 12))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C'.($linha_excel + 3).':K'.($linha_excel + 6))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B2:L'.($linha_excel + 7))->applyFromArray($styleArray_outside_borders);

$spreadsheet->getActiveSheet()->getStyle('C3:K3')->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('C'.($linha_excel + 3).':K'.($linha_excel + 6))->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('B'.($linha_excel + 9).':L'.($linha_excel + 12))->applyFromArray($styleArray_cinza);

$spreadsheet->getActiveSheet()->getStyle('C5:I5')->applyFromArray($styleArray_laranja);

$spreadsheet->getActiveSheet()->getStyle('C9:I9')->applyFromArray($styleArray_amarelo);
$spreadsheet->getActiveSheet()->getStyle('C10:C'.$linha_excel)->applyFromArray($styleArray_amarelo);

$spreadsheet->getActiveSheet()->getStyle('C7:I7')->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 10).':E'.($linha_excel + 10))->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 10).':J'.($linha_excel + 10))->applyFromArray($styleArray_branco5);

$spreadsheet->getActiveSheet()->getStyle('C6:I6')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('C8:I8')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 11).':E'.($linha_excel + 11))->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 11).':J'.($linha_excel + 11))->applyFromArray($styleArray_preto);

$spreadsheet->getActiveSheet()->getStyle('C7:I7')->applyFromArray($styleArray_bold);
$spreadsheet->getActiveSheet()->getStyle('C'.($linha_excel + 2))->applyFromArray($styleArray_bold);

$worksheet->setSelectedCell('A1');

//Sexta Aba (No Show)
$activeWorksheet = $spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndexByName('Worksheet');
$spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
$protection = $spreadsheet->getActiveSheet()->getProtection();
$protection->setPassword("$worksheet_password");
$protection->setSheet(true);
$protection->setSort(false);
$protection->setInsertRows(false);
$protection->setFormatCells(false);

$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setTitle('No Show');
$worksheet->setSelectedCell('A1');

$activeWorksheet->setCellValue('C3', $hotel);
$activeWorksheet->mergeCells('C3:J3');
$activeWorksheet->setCellValue('C5', 'No Show');
$activeWorksheet->mergeCells('C5:H5');
$activeWorksheet->mergeCells('C7:H7');
$activeWorksheet->setCellValue('C9', 'Qtd.');
$activeWorksheet->setCellValue('D9', 'Reserva');
$activeWorksheet->setCellValue('E9', 'Hospede');
$activeWorksheet->setCellValue('F9', 'Checkin');
$activeWorksheet->setCellValue('G9', 'Checkout');
$activeWorksheet->setCellValue('H9', 'Diária');

$quantidade_dados = count($_SESSION['dados_noshow']);

$linha_excel = 9;
$qtd = 0;

foreach ($_SESSION['dados_noshow'] as $select) {
    $guest_name = $select['guest_name'];
    $reserva = $select['reserva'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $room_balance = $select['room_balance'];

    $linha_excel++;
    $qtd++;

    if($linha_excel % 2 != 0){
    $spreadsheet->getActiveSheet()->getStyle('C'.$linha_excel.':H'.$linha_excel)->applyFromArray($styleArray_separacao); 
    }

$activeWorksheet->setCellValue('C'.$linha_excel, $qtd);
$activeWorksheet->setCellValue('D'.$linha_excel, $reserva);
$activeWorksheet->setCellValue('E'.$linha_excel, $guest_name);
$activeWorksheet->setCellValue('F'.$linha_excel, date('d/m/Y', strtotime("$checkin")));
$activeWorksheet->setCellValue('G'.$linha_excel, date('d/m/Y', strtotime("$checkout")));
$activeWorksheet->setCellValue('H'.$linha_excel, $room_balance);

}

//Manter no Minimo 3 linhas
if($linha_excel < 13){
$linhas = $linha_excel;
for($i = $linhas ; $i < 13 ; $i++){
$linha_excel++;
$activeWorksheet->setCellValue('C'.$linha_excel, '');
$activeWorksheet->setCellValue('D'.$linha_excel, '');
$activeWorksheet->setCellValue('E'.$linha_excel, '');
$activeWorksheet->setCellValue('F'.$linha_excel, '');
$activeWorksheet->setCellValue('G'.$linha_excel, '');
$activeWorksheet->setCellValue('H'.$linha_excel, '');
}
}

$activeWorksheet->setCellValue('C7', 'No Shows: '.$quantidade_dados);

$activeWorksheet->getStyle('H10:H'.$linha_excel)->getNumberFormat()->setFormatCode('R$ #,##0.00');

//Assinatura Digital
$activeWorksheet->setCellValue('D'.($linha_excel + 4), $_SESSION['name'].' | '.$data_quando.'h');
$activeWorksheet->mergeCells('D'.($linha_excel + 4).':E'.($linha_excel + 4));
$activeWorksheet->setCellValue('D'.($linha_excel + 5), 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('D'.($linha_excel + 5).':E'.($linha_excel + 5));
$activeWorksheet->setCellValue('G'.($linha_excel + 4), '');
$activeWorksheet->mergeCells('G'.($linha_excel + 4).':J'.($linha_excel + 4));
$activeWorksheet->setCellValue('G'.($linha_excel + 5), 'Assinatura Digital N + 1');
$activeWorksheet->mergeCells('G'.($linha_excel + 5).':J'.($linha_excel + 5));

// Inserir uma imagem
$imagePath = '../imagem/logo_all.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('ALL');
$objDrawing->setDescription('ALL');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('J5'); // Posição onde a imagem será inserida
$objDrawing->setWorksheet($activeWorksheet);

//Definir Tamanhos
$activeWorksheet->setShowGridlines(false);
$activeWorksheet->setShowRowColHeaders(false);
$activeWorksheet->getRowDimension(1)->setRowHeight(10);
$activeWorksheet->getColumnDimension('A')->setWidth(2);
$activeWorksheet->getColumnDimension('B')->setWidth(2);
$activeWorksheet->getColumnDimension('D')->setWidth(15);
$activeWorksheet->getColumnDimension('E')->setWidth(40);
$activeWorksheet->getColumnDimension('F')->setWidth(11);
$activeWorksheet->getColumnDimension('G')->setWidth(11);
$activeWorksheet->getColumnDimension('H')->setWidth(20);
$activeWorksheet->getColumnDimension('I')->setWidth(2);
$activeWorksheet->getColumnDimension('J')->setWidth(22);
$activeWorksheet->getColumnDimension('K')->setWidth(2);

//Colocar as bordas
$activeWorksheet->getStyle('C9:H'.$linha_excel)->applyFromArray($styleArray_inside_borders);

$activeWorksheet->getStyle('C3:J3')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C5:H'.$linha_excel)->applyFromArray($styleArray);
$activeWorksheet->getStyle('D'.($linha_excel + 4).':E'.($linha_excel + 5))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('G'.($linha_excel + 4).':J'.($linha_excel + 5))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B'.($linha_excel + 3).':K'.($linha_excel + 6))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B2:K'.($linha_excel + 1))->applyFromArray($styleArray_outside_borders);

$spreadsheet->getActiveSheet()->getStyle('C3:J3')->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('B'.($linha_excel + 3).':K'.($linha_excel + 6))->applyFromArray($styleArray_cinza);

$spreadsheet->getActiveSheet()->getStyle('C5:H5')->applyFromArray($styleArray_laranja);

$spreadsheet->getActiveSheet()->getStyle('C9:H9')->applyFromArray($styleArray_amarelo);
$spreadsheet->getActiveSheet()->getStyle('C10:C'.$linha_excel)->applyFromArray($styleArray_amarelo);

$spreadsheet->getActiveSheet()->getStyle('C7:H7')->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 4).':E'.($linha_excel + 4))->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 4).':J'.($linha_excel + 4))->applyFromArray($styleArray_branco5);

$spreadsheet->getActiveSheet()->getStyle('C6:H6')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('C8:H8')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 5).':E'.($linha_excel + 5))->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 5).':J'.($linha_excel + 5))->applyFromArray($styleArray_preto);

$spreadsheet->getActiveSheet()->getStyle('C7:H7')->applyFromArray($styleArray_bold);

$worksheet->setSelectedCell('A1');


//Setima Aba (Free Stay)
$activeWorksheet = $spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndexByName('Worksheet');
$spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
$protection = $spreadsheet->getActiveSheet()->getProtection();
$protection->setPassword("$worksheet_password");
$protection->setSheet(true);
$protection->setSort(false);
$protection->setInsertRows(false);
$protection->setFormatCells(false);

$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setTitle('Free Stay');
$worksheet->setSelectedCell('A1');

$activeWorksheet->setCellValue('C3', $hotel);
$activeWorksheet->mergeCells('C3:K3');
$activeWorksheet->setCellValue('C5', 'Free Stay');
$activeWorksheet->mergeCells('C5:I5');
$activeWorksheet->mergeCells('C7:I7');
$activeWorksheet->setCellValue('C9', 'Qtd.');
$activeWorksheet->setCellValue('D9', 'Apto');
$activeWorksheet->setCellValue('E9', '[Reserva] - Hospede');
$activeWorksheet->setCellValue('F9', 'Checkin');
$activeWorksheet->setCellValue('G9', 'Checkout');
$activeWorksheet->setCellValue('H9', 'Tipo Free Stay');
$activeWorksheet->setCellValue('I9', 'Tipo Free Stay');

  
$quantidade_dados = count($_SESSION['dados_freestay']);

$linha_excel = 9;
$qtd = 0;

foreach ($_SESSION['dados_freestay'] as $select) {
    $guest_name = $select['guest_name'];
    $reserva = $select['reserva'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $room_number = $select['room_number'];
    $tipo_cortesia = $select['tipo_cortesia'];
    $comentario = $select['comentario'];

    $linha_excel++;
    $qtd++;

    if($linha_excel % 2 != 0){
    $spreadsheet->getActiveSheet()->getStyle('C'.$linha_excel.':I'.$linha_excel)->applyFromArray($styleArray_separacao); 
    }

$activeWorksheet->setCellValue('C'.$linha_excel, $qtd);
$activeWorksheet->setCellValue('D'.$linha_excel, $room_number);
$activeWorksheet->setCellValue('E'.$linha_excel, '['.$reserva.']'.' - '.$guest_name);
$activeWorksheet->setCellValue('F'.$linha_excel, date('d/m/Y', strtotime("$checkin")));
$activeWorksheet->setCellValue('G'.$linha_excel, date('d/m/Y', strtotime("$checkout")));
$activeWorksheet->setCellValue('H'.$linha_excel, $tipo_cortesia);
$activeWorksheet->setCellValue('I'.$linha_excel, $comentario);

}

//Manter no Minimo 3 linhas
if($linha_excel < 13){
$linhas = $linha_excel;
for($i = $linhas ; $i < 13 ; $i++){
$linha_excel++;
$activeWorksheet->setCellValue('C'.$linha_excel, '');
$activeWorksheet->setCellValue('D'.$linha_excel, '');
$activeWorksheet->setCellValue('E'.$linha_excel, '');
$activeWorksheet->setCellValue('F'.$linha_excel, '');
$activeWorksheet->setCellValue('G'.$linha_excel, '');
$activeWorksheet->setCellValue('H'.$linha_excel, '');
$activeWorksheet->setCellValue('I'.$linha_excel, '');
}
}

$activeWorksheet->setCellValue('C7', 'Free Stays: '.$quantidade_dados);


//Assinatura Digital
$activeWorksheet->setCellValue('D'.($linha_excel + 4), $_SESSION['name'].' | '.$data_quando.'h');
$activeWorksheet->mergeCells('D'.($linha_excel + 4).':E'.($linha_excel + 4));
$activeWorksheet->setCellValue('D'.($linha_excel + 5), 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('D'.($linha_excel + 5).':E'.($linha_excel + 5));
$activeWorksheet->setCellValue('G'.($linha_excel + 4), '');
$activeWorksheet->mergeCells('G'.($linha_excel + 4).':H'.($linha_excel + 4));
$activeWorksheet->setCellValue('G'.($linha_excel + 5), 'Assinatura Digital N + 1');
$activeWorksheet->mergeCells('G'.($linha_excel + 5).':H'.($linha_excel + 5));

// Inserir uma imagem
$imagePath = '../imagem/logo_all.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('ALL');
$objDrawing->setDescription('ALL');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('K5'); // Posição onde a imagem será inserida
$objDrawing->setWorksheet($activeWorksheet);

//Definir Tamanhos
$activeWorksheet->setShowGridlines(false);
$activeWorksheet->setShowRowColHeaders(false);
$activeWorksheet->getRowDimension(1)->setRowHeight(10);
$activeWorksheet->getColumnDimension('A')->setWidth(2);
$activeWorksheet->getColumnDimension('B')->setWidth(2);
$activeWorksheet->getColumnDimension('E')->setWidth(45);
$activeWorksheet->getColumnDimension('F')->setWidth(11);
$activeWorksheet->getColumnDimension('G')->setWidth(11);
$activeWorksheet->getColumnDimension('H')->setWidth(40);
$activeWorksheet->getColumnDimension('I')->setWidth(40);
$activeWorksheet->getColumnDimension('J')->setWidth(2);
$activeWorksheet->getColumnDimension('K')->setWidth(22);
$activeWorksheet->getColumnDimension('L')->setWidth(2);

//Colocar as bordas
$activeWorksheet->getStyle('C9:I'.$linha_excel)->applyFromArray($styleArray_inside_borders);

$activeWorksheet->getStyle('C3:K3')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C5:I'.$linha_excel)->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('D'.($linha_excel + 4).':E'.($linha_excel + 5))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('G'.($linha_excel + 4).':H'.($linha_excel + 5))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B'.($linha_excel + 3).':L'.($linha_excel + 6))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B2:L'.($linha_excel + 1))->applyFromArray($styleArray_outside_borders);

$spreadsheet->getActiveSheet()->getStyle('C3:K3')->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('B'.($linha_excel + 3).':L'.($linha_excel + 6))->applyFromArray($styleArray_cinza);

$spreadsheet->getActiveSheet()->getStyle('C5:I5')->applyFromArray($styleArray_laranja);

$spreadsheet->getActiveSheet()->getStyle('C9:I9')->applyFromArray($styleArray_amarelo);
$spreadsheet->getActiveSheet()->getStyle('C10:C'.$linha_excel)->applyFromArray($styleArray_amarelo);

$spreadsheet->getActiveSheet()->getStyle('C7:I7')->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 4).':E'.($linha_excel + 4))->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 4).':H'.($linha_excel + 4))->applyFromArray($styleArray_branco5);

$spreadsheet->getActiveSheet()->getStyle('C6:I6')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('C8:I8')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 5).':E'.($linha_excel + 5))->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 5).':H'.($linha_excel + 5))->applyFromArray($styleArray_preto);

$spreadsheet->getActiveSheet()->getStyle('C7:I7')->applyFromArray($styleArray_bold);

$worksheet->setSelectedCell('A1');

//Oitava Aba (Tax Base)

$conditionIssRetido = new Conditional();
    $conditionIssRetido->setConditionType(Conditional::CONDITION_CELLIS)
        ->setOperatorType(Conditional::OPERATOR_NOTEQUAL)
        ->addCondition('0')
        ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_MAGENTA);

$activeWorksheet = $spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndexByName('Worksheet');
$spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
$protection = $spreadsheet->getActiveSheet()->getProtection();
$protection->setPassword("$worksheet_password");
$protection->setSheet(true);
$protection->setSort(false);
$protection->setInsertRows(false);
$protection->setFormatCells(false);

$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setTitle('Tax Base');
$worksheet->setSelectedCell('A1');

$activeWorksheet->setCellValue('C3', $hotel);
$activeWorksheet->mergeCells('C3:U3');
$activeWorksheet->setCellValue('C5', 'Tax Base');
$activeWorksheet->mergeCells('C5:S5');
$activeWorksheet->mergeCells('C7:S7');
$activeWorksheet->setCellValue('C9', 'Qtd.');
$activeWorksheet->setCellValue('D9', 'RPS');
$activeWorksheet->setCellValue('E9', 'Situação');
$activeWorksheet->setCellValue('F9', 'Data');
$activeWorksheet->setCellValue('G9', 'Hospede');
$activeWorksheet->setCellValue('H9', 'Empresa');
$activeWorksheet->setCellValue('I9', 'Quarto');
$activeWorksheet->setCellValue('J9', 'Total');
$activeWorksheet->setCellValue('K9', 'Base Iss');
$activeWorksheet->setCellValue('L9', 'Iss');
$activeWorksheet->setCellValue('M9', 'Iss Esperado');
$activeWorksheet->mergeCells('M9:N9');
$activeWorksheet->setCellValue('O9', 'Iss Retido');
$activeWorksheet->setCellValue('P9', 'Serviço');
$activeWorksheet->setCellValue('Q9', 'A&B');
$activeWorksheet->setCellValue('R9', 'Diversos');
$activeWorksheet->setCellValue('S9', 'Eventos');
    
$quantidade_dados = count($_SESSION['dados_taxbase']);

$sum_valor_base_iss = 0;
$sum_valor_iss = 0;
$sum_valor_iss_retido = 0;
$sum_valor_iss_esperado = 0;
$sum_diferenca = 0;

foreach ($_SESSION['dados_taxbase'] as $taxbase) {
    $sum_valor_base_iss += floatval($taxbase['valor_base_iss']);
    $sum_valor_iss += floatval($taxbase['valor_iss']);
    $sum_valor_iss_retido += floatval($taxbase['valor_iss_retido']);
}

$sum_valor_iss_esperado = $sum_valor_base_iss * 0.05;
$sum_diferenca = $sum_valor_iss_esperado - $sum_valor_iss;

$sum_valor_base_iss = number_format($sum_valor_base_iss, 2, ",", ".");
$sum_valor_iss = number_format($sum_valor_iss, 2, ",", ".");
$sum_valor_iss_retido = number_format($sum_valor_iss_retido, 2, ",", ".");
$sum_valor_iss_esperado = number_format($sum_valor_iss_esperado, 2, ",", ".");
$sum_diferenca = number_format($sum_diferenca, 2, ",", ".");

$linha_excel = 9;
$qtd = 0;

foreach ($_SESSION['dados_taxbase'] as $select) {
    $rps_num = $select['rps_num'];
    $situacao = $select['situacao'];
    $data_emissao = $select['data_emissao'];
    $guest_name = $select['guest_name'];
    $guest_empresa = $select['guest_empresa'];
    $room_number = $select['room_number'];
    $valor_nf = $select['valor_nf'];
    $valor_base_iss = round($select['valor_base_iss'], 2);
    $valor_iss = round($select['valor_iss'], 2);
    $valor_iss_retido = $select['valor_iss_retido'];
    $valor_servico = $select['valor_servico'];
    $valor_aeb = $select['valor_aeb'];
    $valor_diversos = $select['valor_diversos'];
    $valor_evento = $select['valor_evento'];

    $valor_iss_esperado = round($valor_base_iss * 0.05, 2);
    $diferenca_iss = round($valor_iss_esperado - $valor_iss, 2);

    $linha_excel++;
    $qtd++;

    if($linha_excel % 2 != 0){
    $spreadsheet->getActiveSheet()->getStyle('C'.$linha_excel.':S'.$linha_excel)->applyFromArray($styleArray_separacao); 
    }

    if($diferenca_iss != 0){
    $spreadsheet->getActiveSheet()->getStyle('N'.$linha_excel)->applyFromArray($styleArray_difiss); 
    }else{
    $spreadsheet->getActiveSheet()->getStyle('N'.$linha_excel)->applyFromArray($styleArray_difissok); 
    }

$activeWorksheet->setCellValue('C'.$linha_excel, $qtd);
$activeWorksheet->setCellValue('D'.$linha_excel, $rps_num);
$activeWorksheet->setCellValue('E'.$linha_excel, $situacao);
$activeWorksheet->setCellValue('F'.$linha_excel, date('d/m/Y', strtotime("$data_emissao")));
$activeWorksheet->setCellValue('G'.$linha_excel, $guest_name);
$activeWorksheet->setCellValue('H'.$linha_excel, $guest_empresa);
$activeWorksheet->setCellValue('I'.$linha_excel, $room_number);
$activeWorksheet->setCellValue('J'.$linha_excel, $valor_nf);
$activeWorksheet->setCellValue('K'.$linha_excel, $valor_base_iss);
$activeWorksheet->setCellValue('L'.$linha_excel, $valor_iss);
$activeWorksheet->setCellValue('M'.$linha_excel, $valor_iss_esperado);
$activeWorksheet->setCellValue('N'.$linha_excel, '');
$activeWorksheet->setCellValue('O'.$linha_excel, $valor_iss_retido);
$activeWorksheet->setCellValue('P'.$linha_excel, $valor_servico);
$activeWorksheet->setCellValue('Q'.$linha_excel, $valor_aeb);
$activeWorksheet->setCellValue('R'.$linha_excel, $valor_diversos);
$activeWorksheet->setCellValue('S'.$linha_excel, $valor_evento);

}

//Manter no Minimo 3 linhas
if($linha_excel < 13){
$linhas = $linha_excel;
for($i = $linhas ; $i < 13 ; $i++){
$linha_excel++;
$activeWorksheet->setCellValue('C'.$linha_excel, '');
$activeWorksheet->setCellValue('D'.$linha_excel, '');
$activeWorksheet->setCellValue('E'.$linha_excel, '');
$activeWorksheet->setCellValue('F'.$linha_excel, '');
$activeWorksheet->setCellValue('G'.$linha_excel, '');
$activeWorksheet->setCellValue('H'.$linha_excel, '');
$activeWorksheet->setCellValue('I'.$linha_excel, '');
$activeWorksheet->setCellValue('J'.$linha_excel, '');
$activeWorksheet->setCellValue('K'.$linha_excel, '');
$activeWorksheet->setCellValue('L'.$linha_excel, '');
$activeWorksheet->setCellValue('M'.$linha_excel, '');
$activeWorksheet->setCellValue('N'.$linha_excel, '');
$activeWorksheet->setCellValue('O'.$linha_excel, '');
$activeWorksheet->setCellValue('P'.$linha_excel, '');
$activeWorksheet->setCellValue('Q'.$linha_excel, '');
$activeWorksheet->setCellValue('R'.$linha_excel, '');
$activeWorksheet->setCellValue('S'.$linha_excel, '');
}
}

$range = 'O10:O'.$linha_excel;
$worksheet->getStyle($range)->setConditionalStyles([$conditionIssRetido]);

$activeWorksheet->setCellValue('C7', 'RPS Emitadas: '.$quantidade_dados.' | Valor Total Emitido: R$'.$sum_valor_base_iss.' | Valor Total Iss: R$'.$sum_valor_iss.' | Valor Total Iss Esperado: R$'.$sum_valor_iss_esperado.' | Diferença: R$'.$sum_diferenca.' | Valor Total Iss Retido: R$'.$sum_valor_iss_retido);
$activeWorksheet->setCellValue('C'.($linha_excel + 2), 'Comentarios');
$activeWorksheet->setCellValue('C'.($linha_excel + 3), $comentario_taxbase);

$activeWorksheet->getStyle('J10:M'.$linha_excel)->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('O10:S'.$linha_excel)->getNumberFormat()->setFormatCode('R$ #,##0.00');

//Assinatura Digital
$activeWorksheet->setCellValue('G'.($linha_excel + 10), $_SESSION['name'].' | '.$data_quando.'h');
$activeWorksheet->mergeCells('G'.($linha_excel + 10).':H'.($linha_excel + 10));
$activeWorksheet->setCellValue('G'.($linha_excel + 11), 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('G'.($linha_excel + 11).':H'.($linha_excel + 11));
$activeWorksheet->setCellValue('K'.($linha_excel + 10), '');
$activeWorksheet->mergeCells('K'.($linha_excel + 10).':P'.($linha_excel + 10));
$activeWorksheet->setCellValue('K'.($linha_excel + 11), 'Assinatura Digital N + 1');
$activeWorksheet->mergeCells('K'.($linha_excel + 11).':P'.($linha_excel + 11));

// Inserir uma imagem
$imagePath = '../imagem/logo_all.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('ALL');
$objDrawing->setDescription('ALL');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('U5'); // Posição onde a imagem será inserida
$objDrawing->setWorksheet($activeWorksheet);

//Definir Tamanhos
$activeWorksheet->setShowGridlines(false);
$activeWorksheet->setShowRowColHeaders(false);
$activeWorksheet->getRowDimension(1)->setRowHeight(10);
$activeWorksheet->getColumnDimension('A')->setWidth(2);
$activeWorksheet->getColumnDimension('B')->setWidth(2);
$activeWorksheet->getColumnDimension('F')->setWidth(12);
$activeWorksheet->getColumnDimension('G')->setWidth(40);
$activeWorksheet->getColumnDimension('H')->setWidth(10);
$activeWorksheet->getColumnDimension('J')->setWidth(15);
$activeWorksheet->getColumnDimension('K')->setWidth(15);
$activeWorksheet->getColumnDimension('L')->setWidth(15);
$activeWorksheet->getColumnDimension('M')->setWidth(15);
$activeWorksheet->getColumnDimension('N')->setWidth(2);
$activeWorksheet->getColumnDimension('O')->setWidth(15);
$activeWorksheet->getColumnDimension('P')->setWidth(15);
$activeWorksheet->getColumnDimension('Q')->setWidth(15);
$activeWorksheet->getColumnDimension('R')->setWidth(15);
$activeWorksheet->getColumnDimension('S')->setWidth(15);
$activeWorksheet->getColumnDimension('T')->setWidth(2);
$activeWorksheet->getColumnDimension('U')->setWidth(22);
$activeWorksheet->getColumnDimension('V')->setWidth(2);

//Colocar as bordas
$activeWorksheet->getStyle('C9:S'.$linha_excel)->applyFromArray($styleArray_inside_borders);

$activeWorksheet->getStyle('C3:U3')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C5:S'.$linha_excel)->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('G'.($linha_excel + 10).':H'.($linha_excel + 11))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('K'.($linha_excel + 10).':P'.($linha_excel + 11))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C'.($linha_excel + 9).':U'.($linha_excel + 12))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C'.($linha_excel + 3).':S'.($linha_excel + 6))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B2:V'.($linha_excel + 7))->applyFromArray($styleArray_outside_borders);

$spreadsheet->getActiveSheet()->getStyle('C3:U3')->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('C'.($linha_excel + 3).':S'.($linha_excel + 6))->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('C'.($linha_excel + 9).':U'.($linha_excel + 12))->applyFromArray($styleArray_cinza);

$spreadsheet->getActiveSheet()->getStyle('C5:S5')->applyFromArray($styleArray_laranja);

$spreadsheet->getActiveSheet()->getStyle('C9:S9')->applyFromArray($styleArray_amarelo);
$spreadsheet->getActiveSheet()->getStyle('C10:C'.$linha_excel)->applyFromArray($styleArray_amarelo);

$spreadsheet->getActiveSheet()->getStyle('C7:S7')->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 10).':H'.($linha_excel + 10))->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('K'.($linha_excel + 10).':P'.($linha_excel + 10))->applyFromArray($styleArray_branco5);

$spreadsheet->getActiveSheet()->getStyle('C6:S6')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('C8:S8')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 11).':H'.($linha_excel + 11))->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('K'.($linha_excel + 11).':P'.($linha_excel + 11))->applyFromArray($styleArray_preto);

$spreadsheet->getActiveSheet()->getStyle('C7:S7')->applyFromArray($styleArray_bold);
$spreadsheet->getActiveSheet()->getStyle('C'.($linha_excel + 2))->applyFromArray($styleArray_bold);

$worksheet->setSelectedCell('A1');

//Fim Abas
$spreadsheet->setActiveSheetIndexByName('Gerencial');


$_SESSION['status_auditoria'] = 'Concluida';

// Create a temporary file for download
$filename = ucfirst($dir).' - Auditoria Digital - '.date('d-m-Y', strtotime("$data_auditoria")).'.xls';
$tempFile = tempnam(sys_get_temp_dir(), $filename);
$writer = new Xls($spreadsheet);
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

header('Location: index.php');
    exit();

?>