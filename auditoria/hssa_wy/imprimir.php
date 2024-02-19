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

$data_auditoria = $_SESSION['data_auditoria'];

$query_status = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_auditorias WHERE id > 0 AND data_auditoria = '{$data_auditoria}'");
$query_status->execute();
while($select_status = $query_status->fetch(PDO::FETCH_ASSOC)){
    $status_auditoria = $select_status['auditoria_status'];
    $data_finalizada = $select_status['data_finalizada'];
    $colaborador = $select_status['colaborador'];
    $comentario_gerencial = $select_status['comentario_gerencial'];
    $comentario_garantias = $select_status['comentario_garantias'];
}

$hotel = $_SESSION['hotel_name'];

//Valida se toda a auditoria foi preenchida
if($status_auditoria != 'Finalizada'){
    echo "<script>
    alert('Auditoria não foi Finalizada!')
    window.location.replace('auditoria.php')
    </script>";
    exit();
}


$hotel = 'Auditoria Digital - '.$hotel.' ['.date('d/m/Y', strtotime("$data_auditoria")).']';
$data_quando = date('d/m/Y - H:i:s', strtotime("$data_finalizada"));

$chave = $_SESSION['hotel'].$chave;

//$_SESSION['dados_presentlist']
$query = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria WHERE data_auditoria = '{$data_auditoria}'");
$query->execute();

$dados_presentlist = [];
$dados_caixa = [];
$dados_noshow = [];
$dados_rds = [];
while($select = $query->fetch(PDO::FETCH_ASSOC)){
    $dados_auditoria = $select['dados_auditoria'];
    $id = $select['id'];

// Para descriptografar os dados
$dados = base64_decode($dados_auditoria);
$dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);

$dados_array = explode(';', $dados_decifrados);

if($dados_array[0] == 'inhouse'){
$dados_presentlist[] = [
  'id' => $id,
  'reserva' => $dados_array[2],
  'room_number' => $dados_array[3],
  'guest_name' => $dados_array[4],
  'checkin' => $dados_array[5],
  'checkout' => $dados_array[6],
  'room_rate' => $dados_array[7],
  'comentario_checkins' => $dados_array[8],
  'comentario_freestay' => $dados_array[9],
  'auditoria_diarias' => $dados_array[10],
  'auditoria_garantia' => $dados_array[11]
];
}else if($dados_array[0] == 'caixa'){
$dados_caixa[] = [
  'id' => $id,
  'reserva' => $dados_array[2],
  'guest_name' => $dados_array[3],
  'data_lancamento' => $dados_array[4],
  'pgto_forma' => $dados_array[5],
  'pgto_valor' => $dados_array[6],
  'room_number' => $dados_array[7],
  'documento' => $dados_array[8],
  'auditoria_forma' => $dados_array[9],
  'auditoria_conferido' => $dados_array[10]
];
}else if($dados_array[0] == 'noshow'){
  $dados_noshow[] = [
    'id' => $id,
    'reserva' => $dados_array[2],
    'guest_name' => $dados_array[3],
    'checkin' => $dados_array[4],
    'checkout' => $dados_array[5],
    'room_rate' => $dados_array[6],
    'cobrado' => $dados_array[7]
  ];
}else if($dados_array[0] == 'rds'){
    $dados_gerencial = $dados_array[1].';'.$dados_array[2].';'.$dados_array[3].';'.$dados_array[4].';'.$dados_array[5].';'.$dados_array[6].';'.$dados_array[7].';'.$dados_array[8].';'.$dados_array[9].';'.$dados_array[10].';'.$dados_array[11].';'.$dados_array[12];
    $dados_gerencial_occ = $dados_array[13];
    $quartos_construidos_dia = $dados_array[14];
    $quartos_construidos_mes = $dados_array[15];
    $quartos_bloqueados_dia = $dados_array[16];
    $quartos_bloqueados_mes = $dados_array[17];
    $quartos_ocupados_dia = $dados_array[18];
    $quartos_ocupados_mes = $dados_array[19];
    $quartos_cortesia_dia = $dados_array[20];
    $quartos_cortesia_mes = $dados_array[21];
    $quartos_houseuse_dia = $dados_array[22];
    $quartos_houseuse_mes = $dados_array[23];
    $adultos_dia = $dados_array[24];
    $adultos_mes = $dados_array[25];
    $criancas_dia = $dados_array[26];
    $criancas_mes = $dados_array[27];
    $noshow_dia = $dados_array[28];
    $noshow_mes = $dados_array[29];
    $forecast_1 = $dados_array[30];
    $forecast_2 = $dados_array[31];
    $forecast_3 = $dados_array[32];
  }else if($dados_array[0] == 'forecast'){
    $id_rds = $id;
    $forecast_pax_1 = $dados_array[1];
    $forecast_pax_2 = $dados_array[2];
    $forecast_pax_3 = $dados_array[3];
    $forecast_dm_1 = $dados_array[4];
    $forecast_dm_2 = $dados_array[5];
    $forecast_dm_3 = $dados_array[6];
  }
}

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

$styleArray_branco = [
    'font' => [
        'bold' => true,
        'size' => 9,
        'name' => 'Verdana',
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'FFFF', // Branco
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$styleArray_branco_10 = [
    'font' => [
        'bold' => true,
        'size' => 10,
        'name' => 'Verdana',
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'FFFF', // Branco
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

$styleArray_green = [
    'font' => [
        'bold' => true,
        'size' => 9,
        'name' => 'Verdana',
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => '0ddb9c', // Green
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$styleArray_blue = [
    'font' => [
        'bold' => true,
        'size' => 9,
        'name' => 'Verdana',
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'ADD8E6', // Blue
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$styleArray_verdana_left = [
    'font' => [
        'bold' => false,
        'size' => 9,
        'name' => 'Verdana',
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

$styleArray_verdana_left_bold = [
    'font' => [
        'bold' => true,
        'size' => 9,
        'name' => 'Verdana',
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_LEFT,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
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

$styleArray_outside_borders_fina = [
    'borders' => [
        'outline' => [
            'borderStyle' => 'thin',
            'color' => ['rgb' => '000000'],
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'font' => [
        'bold' => true,
        'size' => 11,
        'name' => 'Calibri'
    ]
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

$styleArray_padrao = [
    'font' => [
        'bold' => false,
        'size' => 9,
        'name' => 'Verdana',
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'FFFF', // Branco
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];

//Aba Gerencial

//Gerencial - Receitas
$linhas_gerenciais = explode(';', $dados_gerencial);

$receita_hospedagem_dia = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[0])) /100;
$receita_hospedagem_mes = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[1])) /100;
$receita_aeb_dia = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[2])) /100;
$receita_aeb_mes = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[3])) /100;
$receita_lavanderia_dia = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[4])) /100;
$receita_lavanderia_mes = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[5])) /100;
$receita_taxaiss_dia = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[6])) /100;
$receita_taxaiss_mes = str_replace(',', '.', str_replace('.', '', $linhas_gerenciais[7])) /100;
$receita_total_dia = $receita_hospedagem_dia + $receita_lavanderia_dia + $receita_taxaiss_dia;
$receita_total_mes = $receita_hospedagem_mes + $receita_lavanderia_mes + $receita_taxaiss_mes;

$walkins_dia = 0;
$walkins_mes = 0;
$fator_occ_dia = number_format((floatval($adultos_dia) + floatval($criancas_dia)) / (floatval($quartos_ocupados_dia) + floatval($quartos_cortesia_dia)), 2, ',', '.');
$fator_occ_mes = number_format((floatval($adultos_mes) + floatval($criancas_mes)) / (floatval($quartos_ocupados_mes) + floatval($quartos_cortesia_mes)), 2, ',', '.');
$receita_noshow_dia = 0;
$receita_noshow_mes = 0;
$receita_outros_dia = 0;
$receita_outros_mes = 0;
$receita_eventos_dia = 0;
$receita_eventos_mes = 0;

if($forecast_1 == ''){
    $forecast_occ_1 = 0;
}else{
    $forecast_occ_1 = floatval($forecast_1) / floatval($quartos_construidos_dia) * 100;
}
if($forecast_2 == ''){
    $forecast_occ_2 = 0;
}else{
    $forecast_occ_2 = floatval($forecast_2) / floatval($quartos_construidos_dia) * 100;
}
if($forecast_3 == ''){
    $forecast_occ_3 = 0;
}else{
    $forecast_occ_3 = floatval($forecast_3) / floatval($quartos_construidos_dia) * 100;
}

$forecast_occ_1 = number_format($forecast_occ_1, 2, ',', '.').'%';
$forecast_occ_2 = number_format($forecast_occ_2, 2, ',', '.').'%';
$forecast_occ_3 = number_format($forecast_occ_3, 2, ',', '.').'%';

//Ocupação
if($quartos_ocupados_dia == ''){
    $quartos_occ_dia = 0;
    $dm_dia = 0;
}else{
    $quartos_occ_dia = number_format(floatval($quartos_ocupados_dia) / floatval($quartos_construidos_dia) * 100, 2, ',', '.');
    $dm_dia = number_format(floatval($receita_hospedagem_dia) / floatval($quartos_ocupados_dia), 2, ',', '.');
}

if($quartos_ocupados_mes == ''){
    $quartos_occ_mes = 0;
    $dm_mes = 0;
}else{
    $quartos_occ_mes = number_format(floatval($quartos_ocupados_mes) / floatval($quartos_construidos_mes) * 100, 2, ',', '.');
    $dm_mes = number_format(floatval($receita_hospedagem_mes) / floatval($quartos_ocupados_mes), 2, ',', '.');
}

$revpar_dia = number_format(floatval($receita_hospedagem_dia) / floatval($quartos_construidos_dia), 2, ',', '.');
$revpar_mes = number_format(floatval($receita_hospedagem_mes) / floatval($quartos_construidos_mes), 2, ',', '.');

$revpar_dia_total = number_format(floatval($receita_total_dia) / floatval($quartos_construidos_dia), 2, ',', '.');
$revpar_mes_total = number_format(floatval($receita_total_mes) / floatval($quartos_construidos_mes), 2, ',', '.');

//Planilha
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
$activeWorksheet->mergeCells('C3:I3');
$activeWorksheet->setCellValue('C5', 'Relatorio Gerencial');
$activeWorksheet->mergeCells('C5:E5');
$activeWorksheet->setCellValue('C6', 'Linha');
$activeWorksheet->setCellValue('D6', 'Dia');
$activeWorksheet->setCellValue('E6', 'Acumulado');
$activeWorksheet->setCellValue('C7', 'Quartos Bloqueados');
$activeWorksheet->setCellValue('D7', $quartos_bloqueados_dia);
$activeWorksheet->setCellValue('E7', $quartos_bloqueados_mes);
$activeWorksheet->setCellValue('C8', 'Quartos Ocupados');
$activeWorksheet->setCellValue('D8', $quartos_ocupados_dia);
$activeWorksheet->setCellValue('E8', $quartos_ocupados_mes);
$activeWorksheet->setCellValue('C9', 'Quartos Cortesia');
$activeWorksheet->setCellValue('D9', $quartos_cortesia_dia);
$activeWorksheet->setCellValue('E9', $quartos_cortesia_mes);
$activeWorksheet->setCellValue('C10', 'Quartos House Use');
$activeWorksheet->setCellValue('D10', $quartos_houseuse_dia);
$activeWorksheet->setCellValue('E10', $quartos_houseuse_mes);
$activeWorksheet->setCellValue('G11', 'Justificativas:');
$activeWorksheet->setCellValue('G12', $comentario_gerencial);
$activeWorksheet->mergeCells('G12:I16');
$activeWorksheet->setCellValue('C11', 'Ocupação');
$activeWorksheet->setCellValue('D11', $quartos_occ_dia.'%');
$activeWorksheet->setCellValue('E11', $quartos_occ_mes.'%');
$activeWorksheet->setCellValue('C12', 'Diária Média');
$activeWorksheet->setCellValue('D12', 'R$'.$dm_dia);
$activeWorksheet->setCellValue('E12', 'R$'.$dm_mes);
$activeWorksheet->setCellValue('C13', 'Rev Par');
$activeWorksheet->setCellValue('D13', 'R$'.$revpar_dia);
$activeWorksheet->setCellValue('E13', 'R$'.$revpar_mes);
$activeWorksheet->setCellValue('C14', 'Adultos');
$activeWorksheet->setCellValue('D14', $adultos_dia);
$activeWorksheet->setCellValue('E14', $adultos_mes);
$activeWorksheet->setCellValue('C15', 'Crianças');
$activeWorksheet->setCellValue('D15', $criancas_dia);
$activeWorksheet->setCellValue('E15', $criancas_mes);
$activeWorksheet->setCellValue('C16', 'No Show');
$activeWorksheet->setCellValue('D16', $noshow_dia);
$activeWorksheet->setCellValue('E16', $noshow_mes);
//Receitas
$activeWorksheet->setCellValue('C18', 'Receitas');
$activeWorksheet->mergeCells('C18:E18');
$activeWorksheet->setCellValue('C19', 'Linha');
$activeWorksheet->setCellValue('D19', 'Dia');
$activeWorksheet->setCellValue('E19', 'Acumulado');
$activeWorksheet->setCellValue('C20', 'Receita Hospedagem');
$activeWorksheet->setCellValue('D20', $receita_hospedagem_dia);
$activeWorksheet->setCellValue('E20', $receita_hospedagem_mes);
$activeWorksheet->setCellValue('C21', 'Receita Taxa Iss');
$activeWorksheet->setCellValue('D21', $receita_taxaiss_dia);
$activeWorksheet->setCellValue('E21', $receita_taxaiss_mes);
$activeWorksheet->setCellValue('C22', 'Receita A&B');
$activeWorksheet->setCellValue('D22', $receita_aeb_dia);
$activeWorksheet->setCellValue('E22', $receita_aeb_mes);
$activeWorksheet->setCellValue('C23', 'Receita Lavanderia');
$activeWorksheet->setCellValue('D23', $receita_lavanderia_dia);
$activeWorksheet->setCellValue('E23', $receita_lavanderia_mes);
$activeWorksheet->setCellValue('C24', 'Receita Total');
$activeWorksheet->setCellValue('D24', $receita_total_dia);
$activeWorksheet->setCellValue('E24', $receita_total_mes);
//Forecast
$activeWorksheet->setCellValue('G18', 'Forecast');
$activeWorksheet->mergeCells('G18:I18');
$activeWorksheet->setCellValue('G19', date('d/m/Y', strtotime("$data_auditoria +1 day")));
$activeWorksheet->setCellValue('G20', 'Uhs '.$forecast_1);
$activeWorksheet->setCellValue('G21', 'Occ. '.$forecast_occ_1);
$activeWorksheet->setCellValue('H19', date('d/m/Y', strtotime("$data_auditoria +2 day")));
$activeWorksheet->setCellValue('H20', 'Uhs '.$forecast_2);
$activeWorksheet->setCellValue('H21', 'Occ. '.$forecast_occ_2);
$activeWorksheet->setCellValue('I19', date('d/m/Y', strtotime("$data_auditoria +3 day")));
$activeWorksheet->setCellValue('I20', 'Uhs '.$forecast_3);
$activeWorksheet->setCellValue('I21', 'Occ. '.$forecast_occ_3);
$activeWorksheet->setCellValue('G22', 'DM R$'.number_format(floatval($forecast_dm_1), 2, ',', '.'));
$activeWorksheet->setCellValue('H22', 'DM R$'.number_format(floatval($forecast_dm_2), 2, ',', '.'));
$activeWorksheet->setCellValue('I22', 'DM R$'.number_format(floatval($forecast_dm_3), 2, ',', '.'));
$activeWorksheet->setCellValue('G23', 'Pax '.$forecast_pax_1);
$activeWorksheet->setCellValue('H23', 'Pax '.$forecast_pax_2);
$activeWorksheet->setCellValue('I23', 'Pax '.$forecast_pax_3);

//Assinatura
$activeWorksheet->setCellValue('C27', $colaborador.' | '.$data_quando.'h');
$activeWorksheet->mergeCells('C27:J27');
$activeWorksheet->setCellValue('C28', 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('C28:J28');


$activeWorksheet->setShowGridlines(false);
$activeWorksheet->setShowRowColHeaders(false);
$activeWorksheet->getRowDimension(1)->setRowHeight(10);
$activeWorksheet->getColumnDimension('A')->setWidth(2);
$activeWorksheet->getColumnDimension('B')->setWidth(2);
$activeWorksheet->getColumnDimension('C')->setWidth(24);
$activeWorksheet->getColumnDimension('D')->setWidth(15);
$activeWorksheet->getColumnDimension('E')->setWidth(15);
$activeWorksheet->getColumnDimension('F')->setWidth(2);
$activeWorksheet->getColumnDimension('G')->setWidth(15);
$activeWorksheet->getColumnDimension('H')->setWidth(15);
$activeWorksheet->getColumnDimension('I')->setWidth(15);
$activeWorksheet->getColumnDimension('J')->setWidth(2);
$activeWorksheet->getColumnDimension('K')->setWidth(2);
$activeWorksheet->getColumnDimension('L')->setWidth(2);

// Inserir uma imagem
$imagePath = '../imagem/hcc.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('HCC');
$objDrawing->setDescription('HCC');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('G5'); // Posição onde a imagem será inserida
$objDrawing->setWorksheet($activeWorksheet);

$spreadsheet->getActiveSheet()->getStyle('C8:E8')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('C10:E10')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('C12:E12')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('C14:E14')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('C16:E16')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('C21:E21')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('G21:I21')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('G23:I23')->applyFromArray($styleArray_separacao);
$spreadsheet->getActiveSheet()->getStyle('C23:E23')->applyFromArray($styleArray_separacao);

$spreadsheet->getActiveSheet()->getStyle('C3:I3')->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('B26:K29')->applyFromArray($styleArray_cinza);

$spreadsheet->getActiveSheet()->getStyle('C5:E5')->applyFromArray($styleArray_laranja);
$spreadsheet->getActiveSheet()->getStyle('C18:E18')->applyFromArray($styleArray_laranja);
$spreadsheet->getActiveSheet()->getStyle('G18:I18')->applyFromArray($styleArray_laranja);

$spreadsheet->getActiveSheet()->getStyle('C6:E6')->applyFromArray($styleArray_amarelo);
$spreadsheet->getActiveSheet()->getStyle('C19:E19')->applyFromArray($styleArray_amarelo);
$spreadsheet->getActiveSheet()->getStyle('G19:I19')->applyFromArray($styleArray_amarelo);

$spreadsheet->getActiveSheet()->getStyle('C27:E27')->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('G27:J27')->applyFromArray($styleArray_branco5);

$spreadsheet->getActiveSheet()->getStyle('C28:E28')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('G28:J28')->applyFromArray($styleArray_preto);

$activeWorksheet->getStyle('C5:E16')->applyFromArray($styleArray_inside_borders);
$activeWorksheet->getStyle('C19:E24')->applyFromArray($styleArray_inside_borders);
$activeWorksheet->getStyle('G18:I23')->applyFromArray($styleArray_inside_borders);

$activeWorksheet->getStyle('C3:I3')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C5:E16')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C18:E24')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('G18:I23')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B26:K29')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C27:J28')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B2:K29')->applyFromArray($styleArray_outside_borders);

$activeWorksheet->getStyle('G11')->applyFromArray($styleArray_bold);
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
$activeWorksheet->getStyle('G12:I16')->applyFromArray($styleArray);

$styleArray = [
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];
$activeWorksheet->getStyle('D5:E24')->applyFromArray($styleArray);
$activeWorksheet->getStyle('G18:I23')->applyFromArray($styleArray);

$activeWorksheet->getStyle('D12:E13')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('D20:E24')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('G22:I22')->getNumberFormat()->setFormatCode('R$ #,##0.00');

$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setTitle('Gerencial');
$worksheet->setSelectedCell('A1');

//Segunda Aba (Controle de Garantias)
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
$activeWorksheet->mergeCells('C3:M3');
$activeWorksheet->setCellValue('C5', 'Controle de Garantias');
$activeWorksheet->mergeCells('C5:K5');
$activeWorksheet->mergeCells('C7:K7');
$activeWorksheet->setCellValue('C9', 'Qtd.');
$activeWorksheet->setCellValue('D9', 'Apto.');
$activeWorksheet->setCellValue('E9', 'Hospede');
$activeWorksheet->setCellValue('F9', 'Checkin');
$activeWorksheet->setCellValue('G9', 'Checkout');
$activeWorksheet->setCellValue('H9', 'Diária');
$activeWorksheet->setCellValue('I9', 'Conferido');
$activeWorksheet->setCellValue('J9', 'Garantia');
$activeWorksheet->setCellValue('K9', 'Valor');

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
    $room_rate = $select['room_rate'];
    $comentario_checkins = $select['comentario_checkins'];
    $auditoria_diarias = $select['auditoria_diarias'];
    $auditoria_garantia = $select['auditoria_garantia'];

    if($comentario_checkins ==''){
        $comentario = 'Não';
    }else{
        $comentario = 'Sim';
    }

    $linha_excel++;
    $qtd++;

    if($linha_excel % 2 != 0){
    $spreadsheet->getActiveSheet()->getStyle('C'.$linha_excel.':K'.$linha_excel)->applyFromArray($styleArray_separacao); 
    }

$activeWorksheet->setCellValue('C'.$linha_excel, $qtd);
$activeWorksheet->setCellValue('D'.$linha_excel, $room_number);
$activeWorksheet->setCellValue('E'.$linha_excel, $guest_name);
$activeWorksheet->setCellValue('F'.$linha_excel, date('d/m/Y', strtotime("$checkin")));
$activeWorksheet->setCellValue('G'.$linha_excel, date('d/m/Y', strtotime("$checkout")));
$activeWorksheet->setCellValue('H'.$linha_excel, $room_rate);
$activeWorksheet->setCellValue('I'.$linha_excel, $comentario);
$activeWorksheet->setCellValue('J'.$linha_excel, $auditoria_garantia);
$activeWorksheet->setCellValue('K'.$linha_excel, $auditoria_diarias);

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
}
}

$activeWorksheet->setCellValue('C7', 'Chegadas do Dia: '.$quantidade_dados);
$activeWorksheet->setCellValue('C'.($linha_excel + 2), 'Comentarios');
$activeWorksheet->setCellValue('C'.($linha_excel + 3), $comentario_garantias);

$activeWorksheet->getStyle('H10:H'.$linha_excel)->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('K10:K'.$linha_excel)->getNumberFormat()->setFormatCode('R$ #,##0.00');

//Assinatura Digital
$activeWorksheet->setCellValue('D'.($linha_excel + 10), $colaborador.' | '.$data_quando.'h');
$activeWorksheet->mergeCells('D'.($linha_excel + 10).':I'.($linha_excel + 10));
$activeWorksheet->setCellValue('D'.($linha_excel + 11), 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('D'.($linha_excel + 11).':I'.($linha_excel + 11));

// Inserir uma imagem
$imagePath = '../imagem/hcc.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('HCC');
$objDrawing->setDescription('HCC');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('M5'); // Posição onde a imagem será inserida
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
$activeWorksheet->getColumnDimension('I')->setWidth(16);
$activeWorksheet->getColumnDimension('J')->setWidth(22);
$activeWorksheet->getColumnDimension('K')->setWidth(12);
$activeWorksheet->getColumnDimension('L')->setWidth(2);
$activeWorksheet->getColumnDimension('M')->setWidth(22);
$activeWorksheet->getColumnDimension('N')->setWidth(2);

//Colocar as bordas
$activeWorksheet->getStyle('C9:K'.$linha_excel)->applyFromArray($styleArray_inside_borders);

$activeWorksheet->getStyle('C3:M3')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C5:K'.$linha_excel)->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('D'.($linha_excel + 10).':I'.($linha_excel + 11))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B'.($linha_excel + 9).':N'.($linha_excel + 12))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C'.($linha_excel + 3).':K'.($linha_excel + 6))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B2:N'.($linha_excel + 7))->applyFromArray($styleArray_outside_borders);

$spreadsheet->getActiveSheet()->getStyle('C3:M3')->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('C'.($linha_excel + 3).':K'.($linha_excel + 6))->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('B'.($linha_excel + 9).':N'.($linha_excel + 12))->applyFromArray($styleArray_cinza);

$spreadsheet->getActiveSheet()->getStyle('C5:K5')->applyFromArray($styleArray_laranja);

$spreadsheet->getActiveSheet()->getStyle('C9:K9')->applyFromArray($styleArray_amarelo);
$spreadsheet->getActiveSheet()->getStyle('C10:C'.$linha_excel)->applyFromArray($styleArray_amarelo);

$spreadsheet->getActiveSheet()->getStyle('C7:K7')->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 10).':E'.($linha_excel + 10))->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 10).':I'.($linha_excel + 10))->applyFromArray($styleArray_branco5);

$spreadsheet->getActiveSheet()->getStyle('C6:K6')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('C8:K8')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 11).':E'.($linha_excel + 11))->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 11).':I'.($linha_excel + 11))->applyFromArray($styleArray_preto);

$spreadsheet->getActiveSheet()->getStyle('C7:K7')->applyFromArray($styleArray_bold);
$spreadsheet->getActiveSheet()->getStyle('C'.($linha_excel + 2))->applyFromArray($styleArray_bold);

$worksheet->setSelectedCell('A1');

//Terceira Aba (Free Stay)
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
$activeWorksheet->mergeCells('C3:J3');
$activeWorksheet->setCellValue('C5', 'Free Stay');
$activeWorksheet->mergeCells('C5:H5');
$activeWorksheet->mergeCells('C7:H7');
$activeWorksheet->setCellValue('C9', 'Qtd.');
$activeWorksheet->setCellValue('D9', 'Apto');
$activeWorksheet->setCellValue('E9', '[Reserva] - Hospede');
$activeWorksheet->setCellValue('F9', 'Checkin');
$activeWorksheet->setCellValue('G9', 'Checkout');
$activeWorksheet->setCellValue('H9', 'Comentario');

$dados_filtrados = array_filter($dados_presentlist, function($item) {
    return $item['room_rate'] == 0;
});

// Ordenar o array por 'room_number'
usort($dados_filtrados, function($a, $b) {
    return $a['room_number'] <=> $b['room_number'];
});

$quantidade_dados = count($dados_filtrados);

$linha_excel = 9;
$qtd = 0;

foreach ($dados_filtrados as $select) {
    $guest_name = $select['guest_name'];
    $reserva = $select['reserva'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $room_number = $select['room_number'];
    $comentario = $select['comentario_freestay'];

    $linha_excel++;
    $qtd++;

    if($linha_excel % 2 != 0){
    $spreadsheet->getActiveSheet()->getStyle('C'.$linha_excel.':H'.$linha_excel)->applyFromArray($styleArray_separacao); 
    }

$activeWorksheet->setCellValue('C'.$linha_excel, $qtd);
$activeWorksheet->setCellValue('D'.$linha_excel, $room_number);
$activeWorksheet->setCellValue('E'.$linha_excel, '['.$reserva.']'.' - '.$guest_name);
$activeWorksheet->setCellValue('F'.$linha_excel, date('d/m/Y', strtotime("$checkin")));
$activeWorksheet->setCellValue('G'.$linha_excel, date('d/m/Y', strtotime("$checkout")));
$activeWorksheet->setCellValue('H'.$linha_excel, $comentario);

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

$activeWorksheet->setCellValue('C7', 'Free Stays: '.$quantidade_dados);


//Assinatura Digital
$activeWorksheet->setCellValue('D'.($linha_excel + 4), $colaborador.' | '.$data_quando.'h');
$activeWorksheet->mergeCells('D'.($linha_excel + 4).':H'.($linha_excel + 4));
$activeWorksheet->setCellValue('D'.($linha_excel + 5), 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('D'.($linha_excel + 5).':H'.($linha_excel + 5));

// Inserir uma imagem
$imagePath = '../imagem/hcc.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('HCC');
$objDrawing->setDescription('HCC');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('J5'); // Posição onde a imagem será inserida
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
$activeWorksheet->getColumnDimension('I')->setWidth(2);
$activeWorksheet->getColumnDimension('J')->setWidth(22);
$activeWorksheet->getColumnDimension('K')->setWidth(2);

//Colocar as bordas
$activeWorksheet->getStyle('C9:H'.$linha_excel)->applyFromArray($styleArray_inside_borders);

$activeWorksheet->getStyle('C3:J3')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C5:H'.$linha_excel)->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('D'.($linha_excel + 4).':H'.($linha_excel + 5))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B'.($linha_excel + 3).':K'.($linha_excel + 6))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B2:K'.($linha_excel + 1))->applyFromArray($styleArray_outside_borders);

$spreadsheet->getActiveSheet()->getStyle('C3:J3')->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('B'.($linha_excel + 3).':K'.($linha_excel + 6))->applyFromArray($styleArray_cinza);

$spreadsheet->getActiveSheet()->getStyle('C5:H5')->applyFromArray($styleArray_laranja);

$spreadsheet->getActiveSheet()->getStyle('C9:H9')->applyFromArray($styleArray_amarelo);
$spreadsheet->getActiveSheet()->getStyle('C10:C'.$linha_excel)->applyFromArray($styleArray_amarelo);

$spreadsheet->getActiveSheet()->getStyle('C7:H7')->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 4).':E'.($linha_excel + 4))->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 4).':H'.($linha_excel + 4))->applyFromArray($styleArray_branco5);

$spreadsheet->getActiveSheet()->getStyle('C6:H6')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('C8:H8')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 5).':E'.($linha_excel + 5))->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 5).':H'.($linha_excel + 5))->applyFromArray($styleArray_preto);

$spreadsheet->getActiveSheet()->getStyle('C7:H7')->applyFromArray($styleArray_bold);

$worksheet->setSelectedCell('A1');

//Quarta Aba (Caixa)
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
$worksheet->setTitle('Caixa');
$worksheet->setSelectedCell('A1');

$activeWorksheet->setCellValue('C3', $hotel);
$activeWorksheet->mergeCells('C3:J3');
$activeWorksheet->setCellValue('C5', 'Caixa');
$activeWorksheet->mergeCells('C5:H5');
$activeWorksheet->mergeCells('C7:H7');
$activeWorksheet->setCellValue('C9', 'Data');
$activeWorksheet->setCellValue('D9', 'Apto.');
$activeWorksheet->setCellValue('E9', 'Hospede');
$activeWorksheet->setCellValue('F9', 'Forma');
$activeWorksheet->setCellValue('G9', 'Valor');
$activeWorksheet->setCellValue('H9', 'Documento');

$dados_filtrados = array_filter($dados_caixa, function($item) {
    return $item['auditoria_conferido'] != 'Sim' && $item['pgto_forma'] != 'A Faturar' && $item['pgto_forma'] != 'Dinheiro' && $item['pgto_forma'] != 'Deposito';
});


// Ordenar o array por 'room_number'
usort($dados_filtrados, function($a, $b) {
    return $a['pgto_forma'] <=> $b['pgto_forma'];
});

$quantidade_dados = count($dados_filtrados);

$mastercard = 0;
$maestro = 0;
$visa = 0;
$visaelectron = 0;
$elodebito = 0;
$elocredito = 0;
$amex = 0;
$pix = 0;

foreach ($dados_filtrados as $dados) {
    if ($dados['pgto_forma'] === 'Redecard - Mastercard') {
        $mastercard += $dados['pgto_valor'];
    }
    if ($dados['pgto_forma'] === 'Redecard - Maestro') {
        $maestro += $dados['pgto_valor'];
    }
    if ($dados['pgto_forma'] === 'Redecard - Visa') {
        $visa += $dados['pgto_valor'];
    }
    if ($dados['pgto_forma'] === 'Redecard - Visa Electron') {
        $visaelectron += $dados['pgto_valor'];
    }
    if ($dados['pgto_forma'] === 'Redecard - Elo Débito') {
        $elodebito += $dados['pgto_valor'];
    }
    if ($dados['pgto_forma'] === 'Redecard - Elo Crédito') {
        $elocredito += $dados['pgto_valor'];
    }
    if ($dados['pgto_forma'] === 'Redecard - American Express') {
        $amex += $dados['pgto_valor'];
    }
    if ($dados['pgto_forma'] === 'PIX - Redecard') {
        $pix += $dados['pgto_valor'];
    }
}

$mastercard = number_format($mastercard, 2, ',', '.');
$maestro = number_format($maestro, 2, ',', '.');
$visa = number_format($visa, 2, ',', '.');
$visaelectron = number_format($visaelectron, 2, ',', '.');
$elodebito = number_format($elodebito, 2, ',', '.');
$elocredito = number_format($elocredito, 2, ',', '.');
$amex = number_format($amex, 2, ',', '.');
$pix = number_format($pix, 2, ',', '.');

$linha_excel = 9;
$qtd = 0;

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

    $linha_excel++;
    $qtd++;

    if($linha_excel % 2 != 0){
    $spreadsheet->getActiveSheet()->getStyle('C'.$linha_excel.':H'.$linha_excel)->applyFromArray($styleArray_separacao); 
    }

$activeWorksheet->setCellValue('C'.$linha_excel, date('d/m/Y', strtotime("$data_lancamento")));
$activeWorksheet->setCellValue('D'.$linha_excel, $room_number);
$activeWorksheet->setCellValue('E'.$linha_excel, $guest_name);
$activeWorksheet->setCellValue('F'.$linha_excel, $pgto_forma);
$activeWorksheet->setCellValue('G'.$linha_excel, 'R$'.number_format($pgto_valor, 2, ',', '.'));
$activeWorksheet->setCellValue('H'.$linha_excel, $documento);

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

$activeWorksheet->setCellValue('C7', 'Pendentes de Conferência: '.$quantidade_dados);


//Assinatura Digital
$activeWorksheet->setCellValue('D'.($linha_excel + 4), $colaborador.' | '.$data_quando.'h');
$activeWorksheet->mergeCells('D'.($linha_excel + 4).':H'.($linha_excel + 4));
$activeWorksheet->setCellValue('D'.($linha_excel + 5), 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('D'.($linha_excel + 5).':H'.($linha_excel + 5));

// Inserir uma imagem
$imagePath = '../imagem/hcc.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('HCC');
$objDrawing->setDescription('HCC');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('J5'); // Posição onde a imagem será inserida
$objDrawing->setWorksheet($activeWorksheet);

//Definir Tamanhos
$activeWorksheet->setShowGridlines(false);
$activeWorksheet->setShowRowColHeaders(false);
$activeWorksheet->getRowDimension(1)->setRowHeight(10);
$activeWorksheet->getColumnDimension('A')->setWidth(2);
$activeWorksheet->getColumnDimension('B')->setWidth(2);
$activeWorksheet->getColumnDimension('C')->setWidth(11);
$activeWorksheet->getColumnDimension('D')->setWidth(8);
$activeWorksheet->getColumnDimension('E')->setWidth(48);
$activeWorksheet->getColumnDimension('F')->setWidth(26);
$activeWorksheet->getColumnDimension('G')->setWidth(16);
$activeWorksheet->getColumnDimension('H')->setWidth(11);
$activeWorksheet->getColumnDimension('I')->setWidth(2);
$activeWorksheet->getColumnDimension('J')->setWidth(22);
$activeWorksheet->getColumnDimension('K')->setWidth(2);

//Colocar as bordas
$activeWorksheet->getStyle('C9:H'.$linha_excel)->applyFromArray($styleArray_inside_borders);

$activeWorksheet->getStyle('C3:J3')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C5:H'.$linha_excel)->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('D'.($linha_excel + 4).':H'.($linha_excel + 5))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B'.($linha_excel + 3).':K'.($linha_excel + 6))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B2:K'.($linha_excel + 1))->applyFromArray($styleArray_outside_borders);

$spreadsheet->getActiveSheet()->getStyle('C3:J3')->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('B'.($linha_excel + 3).':K'.($linha_excel + 6))->applyFromArray($styleArray_cinza);

$spreadsheet->getActiveSheet()->getStyle('C5:H5')->applyFromArray($styleArray_laranja);

$spreadsheet->getActiveSheet()->getStyle('C9:H9')->applyFromArray($styleArray_amarelo);

$spreadsheet->getActiveSheet()->getStyle('C7:H7')->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 4).':E'.($linha_excel + 4))->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 4).':H'.($linha_excel + 4))->applyFromArray($styleArray_branco5);

$spreadsheet->getActiveSheet()->getStyle('C6:H6')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('C8:H8')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 5).':E'.($linha_excel + 5))->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 5).':H'.($linha_excel + 5))->applyFromArray($styleArray_preto);

$spreadsheet->getActiveSheet()->getStyle('C7:H7')->applyFromArray($styleArray_bold);

$worksheet->setSelectedCell('A1');

//Quinta Aba (No Show)
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
$activeWorksheet->setCellValue('D9', '[Reserva] - Hospede');
$activeWorksheet->setCellValue('E9', 'Checkin');
$activeWorksheet->setCellValue('F9', 'Checkout');
$activeWorksheet->setCellValue('G9', 'Diária');
$activeWorksheet->setCellValue('H9', 'Cobrado');

$quantidade_dados = count($dados_noshow);

$linha_excel = 9;
$qtd = 0;

foreach ($dados_noshow as $select) {
    $id = $select['id'];
    $guest_name = $select['guest_name'];
    $reserva = $select['reserva'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $room_rate = $select['room_rate'];
    $cobrado = $select['cobrado'];

    $linha_excel++;
    $qtd++;

    if($linha_excel % 2 != 0){
    $spreadsheet->getActiveSheet()->getStyle('C'.$linha_excel.':H'.$linha_excel)->applyFromArray($styleArray_separacao); 
    }

$activeWorksheet->setCellValue('C'.$linha_excel, $qtd);
$activeWorksheet->setCellValue('D'.$linha_excel, '['.$reserva.']'.' - '.$guest_name);
$activeWorksheet->setCellValue('E'.$linha_excel, date('d/m/Y', strtotime("$checkin")));
$activeWorksheet->setCellValue('F'.$linha_excel, date('d/m/Y', strtotime("$checkout")));
$activeWorksheet->setCellValue('G'.$linha_excel,  'R$'.number_format($room_rate, 2, ',', '.'));
$activeWorksheet->setCellValue('H'.$linha_excel,  'R$'.number_format($cobrado, 2, ',', '.'));

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

$activeWorksheet->setCellValue('C7', 'No Show: '.$quantidade_dados);


//Assinatura Digital
$activeWorksheet->setCellValue('D'.($linha_excel + 4), $colaborador.' | '.$data_quando.'h');
$activeWorksheet->mergeCells('D'.($linha_excel + 4).':H'.($linha_excel + 4));
$activeWorksheet->setCellValue('D'.($linha_excel + 5), 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('D'.($linha_excel + 5).':H'.($linha_excel + 5));

// Inserir uma imagem
$imagePath = '../imagem/hcc.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('HCC');
$objDrawing->setDescription('HCC');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('J5'); // Posição onde a imagem será inserida
$objDrawing->setWorksheet($activeWorksheet);

//Definir Tamanhos
$activeWorksheet->setShowGridlines(false);
$activeWorksheet->setShowRowColHeaders(false);
$activeWorksheet->getRowDimension(1)->setRowHeight(10);
$activeWorksheet->getColumnDimension('A')->setWidth(2);
$activeWorksheet->getColumnDimension('B')->setWidth(2);
$activeWorksheet->getColumnDimension('D')->setWidth(48);
$activeWorksheet->getColumnDimension('E')->setWidth(11);
$activeWorksheet->getColumnDimension('F')->setWidth(11);
$activeWorksheet->getColumnDimension('G')->setWidth(14);
$activeWorksheet->getColumnDimension('H')->setWidth(14);
$activeWorksheet->getColumnDimension('I')->setWidth(2);
$activeWorksheet->getColumnDimension('J')->setWidth(22);
$activeWorksheet->getColumnDimension('K')->setWidth(2);

//Colocar as bordas
$activeWorksheet->getStyle('C9:H'.$linha_excel)->applyFromArray($styleArray_inside_borders);

$activeWorksheet->getStyle('C3:J3')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('C5:H'.$linha_excel)->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('D'.($linha_excel + 4).':H'.($linha_excel + 5))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B'.($linha_excel + 3).':K'.($linha_excel + 6))->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B2:K'.($linha_excel + 1))->applyFromArray($styleArray_outside_borders);

$spreadsheet->getActiveSheet()->getStyle('C3:J3')->applyFromArray($styleArray_cinza);
$spreadsheet->getActiveSheet()->getStyle('B'.($linha_excel + 3).':K'.($linha_excel + 6))->applyFromArray($styleArray_cinza);

$spreadsheet->getActiveSheet()->getStyle('C5:H5')->applyFromArray($styleArray_laranja);

$spreadsheet->getActiveSheet()->getStyle('C9:H9')->applyFromArray($styleArray_amarelo);
$spreadsheet->getActiveSheet()->getStyle('C10:C'.$linha_excel)->applyFromArray($styleArray_amarelo);

$spreadsheet->getActiveSheet()->getStyle('C7:H7')->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 4).':E'.($linha_excel + 4))->applyFromArray($styleArray_branco5);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 4).':H'.($linha_excel + 4))->applyFromArray($styleArray_branco5);

$spreadsheet->getActiveSheet()->getStyle('C6:H6')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('C8:H8')->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('D'.($linha_excel + 5).':E'.($linha_excel + 5))->applyFromArray($styleArray_preto);
$spreadsheet->getActiveSheet()->getStyle('G'.($linha_excel + 5).':H'.($linha_excel + 5))->applyFromArray($styleArray_preto);

$spreadsheet->getActiveSheet()->getStyle('C7:H7')->applyFromArray($styleArray_bold);

$worksheet->setSelectedCell('A1');

//Sexta Aba (Receitas Email)

//Orçado
$ano_atual = date('Y', strtotime("$data_auditoria"));
$mes_atual = strtolower(date('M', strtotime("$data_auditoria")));
$dia_atual = date('d', strtotime("$data_auditoria"));
$dia_ultimo = date('d', strtotime(date('Y-m-t', strtotime($data_auditoria))));

$meses = [
    'jan', 'feb', 'mar', 'apr', 'mai', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dec'
];

$poa_array = [];

    $query = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_poa WHERE data_poa = :data_poa");
    $query->execute(array('data_poa' => $ano_atual));
    $query_qtd = $query->rowCount();

    if($query_qtd > 0){


//POA
$query_poa = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_poa WHERE data_poa = :ano");
$query_poa->bindParam(':ano', $ano_atual);
$query_poa->execute();

while ($select = $query_poa->fetch(PDO::FETCH_ASSOC)) {
    $dados_poa = $select['dados_poa'];
}

// Chave de criptografia
$dados = base64_decode($dados_poa);
$dados_decifrados = openssl_decrypt($dados, $metodo, $chave, 0, $iv);
$dados_array = explode(';', $dados_decifrados);

foreach ($meses as $mes) {
    ${"total_uhs_$mes"} = 0;
    ${"uhs_ocupadas_$mes"} = 0;
    ${"dm_$mes"} = 0.00;
    ${"total_hospedagem_$mes"} = 0.00;

    $mesIndex = array_search($mes, $meses);
    $startIndex = $mesIndex * 4; // Cada mês tem 4 valores

        $poa_array[$mes][] = [
            'total_uhs' => $dados_array[$startIndex],
            'uhs_ocupadas' => $dados_array[$startIndex + 1],
            'dm' => $dados_array[$startIndex + 2],
            'receita' => $dados_array[$startIndex + 3]
        ];

        // Atualiza as variáveis mensais
        ${"total_uhs_$mes"} += $poa_array[$mes][0]['total_uhs'];
        ${"uhs_ocupadas_$mes"} += $poa_array[$mes][0]['uhs_ocupadas'];
        ${"dm_$mes"} += $poa_array[$mes][0]['dm'];
        ${"total_hospedagem_$mes"} += $poa_array[$mes][0]['receita'];

}

    }else{

        foreach ($meses as $mes) {
            ${"total_uhs_$mes"} = 0;
            ${"uhs_ocupadas_$mes"} = 0;
            ${"dm_$mes"} = 0.00;
            ${"total_hospedagem_$mes"} = 0.00;
        }

    }

$activeWorksheet = $spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndexByName('Worksheet');
$spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
$protection = $spreadsheet->getActiveSheet()->getProtection();
$protection->setPassword("$worksheet_password");
$protection->setSheet(true);
$protection->setSort(false);
$protection->setInsertRows(false);
$protection->setFormatCells(false);

//$spreadsheet->getActiveSheet()->freezePane('E9');

$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setTitle('Receitas');
$worksheet->setSelectedCell('A1');

$dia_da_semana = date('N', strtotime("$data_auditoria"));
$nomes_dias_da_semana = [
    1 => 'Segunda-feira',
    2 => 'Terça-feira',
    3 => 'Quarta-feira',
    4 => 'Quinta-feira',
    5 => 'Sexta-feira',
    6 => 'Sábado',
    7 => 'Domingo',
];
$data_relatorio = date('F j Y', strtotime("$data_auditoria"));
$activeWorksheet->setCellValue('B3', $_SESSION['hotel_name']);
$activeWorksheet->mergeCells('B3:K3');
$activeWorksheet->setCellValue('B6', $data_relatorio);
$activeWorksheet->mergeCells('B6:D6');
$activeWorksheet->setCellValue('E6', $nomes_dias_da_semana[$dia_da_semana]);
$activeWorksheet->setCellValue('I6', 'Proporcional Mês');
$activeWorksheet->mergeCells('I6:K6');
$activeWorksheet->setCellValue('B8', 'ESTATÍSTICAS');
$activeWorksheet->mergeCells('B8:D8');
$activeWorksheet->setCellValue('E8', 'HOJE');
$activeWorksheet->setCellValue('G8', 'ACUMULADO');
$activeWorksheet->setCellValue('I8', 'ORÇAMENTO');
$activeWorksheet->setCellValue('K8', 'ANO ANTERIOR');
$activeWorksheet->setCellValue('B10', 'Total de UH');
$activeWorksheet->mergeCells('B10:D10');
$activeWorksheet->setCellValue('E10', $quartos_construidos_dia);
$activeWorksheet->setCellValue('G10', $quartos_construidos_mes);
$activeWorksheet->setCellValue('I10', ${"total_uhs_$mes_atual"} * $dia_atual);
$activeWorksheet->setCellValue('K10', '-');
$activeWorksheet->setCellValue('B11', 'UHs em Manutenção');
$activeWorksheet->mergeCells('B11:D11');
$activeWorksheet->setCellValue('E11', $quartos_bloqueados_dia);
$activeWorksheet->setCellValue('G11', $quartos_bloqueados_mes);
$activeWorksheet->setCellValue('I11', '-');
$activeWorksheet->setCellValue('K11', '-');
$activeWorksheet->setCellValue('B12', 'UHs Uso da Casa');
$activeWorksheet->mergeCells('B12:D12');
$activeWorksheet->setCellValue('E12', $quartos_houseuse_dia);
$activeWorksheet->setCellValue('G12', $quartos_houseuse_mes);
$activeWorksheet->setCellValue('I12', '-');
$activeWorksheet->setCellValue('K12', '-');
$activeWorksheet->setCellValue('B13', 'UHs Disponíveis');
$activeWorksheet->mergeCells('B13:D13');
$activeWorksheet->setCellValue('E13', $quartos_construidos_dia - $quartos_bloqueados_dia - $quartos_houseuse_dia - $quartos_ocupados_dia);
$activeWorksheet->setCellValue('G13', $quartos_construidos_mes - $quartos_bloqueados_mes - $quartos_houseuse_mes - $quartos_ocupados_mes);
$activeWorksheet->setCellValue('I13', '-');
$activeWorksheet->setCellValue('K13', '-');
$activeWorksheet->setCellValue('B14', 'UHs Ocupadas');
$activeWorksheet->mergeCells('B14:D14');
$activeWorksheet->setCellValue('E14', $quartos_ocupados_dia);
$activeWorksheet->setCellValue('G14', $quartos_ocupados_mes);
$activeWorksheet->setCellValue('I14',  intval(${"uhs_ocupadas_$mes_atual"} * $dia_atual));
$activeWorksheet->setCellValue('K14', '-');
$activeWorksheet->setCellValue('B15', 'UHs No Show');
$activeWorksheet->mergeCells('B15:D15');
$activeWorksheet->setCellValue('E15', $noshow_dia);
$activeWorksheet->setCellValue('G15', $noshow_mes);
$activeWorksheet->setCellValue('I15', '-');
$activeWorksheet->setCellValue('K15', '-');
$activeWorksheet->setCellValue('B16', 'UHs Cortesia');
$activeWorksheet->mergeCells('B16:D16');
$activeWorksheet->setCellValue('E16', $quartos_cortesia_dia);
$activeWorksheet->setCellValue('G16', $quartos_cortesia_dia);
$activeWorksheet->setCellValue('I16', '-');
$activeWorksheet->setCellValue('K16', '-');
$activeWorksheet->setCellValue('B17', 'Total de UHs Ocupadas');
$activeWorksheet->mergeCells('B17:D17');
$activeWorksheet->setCellValue('E17', $quartos_ocupados_dia + $quartos_cortesia_dia);
$activeWorksheet->setCellValue('G17', $quartos_ocupados_mes + $quartos_cortesia_dia);
$activeWorksheet->setCellValue('I17',  intval(${"uhs_ocupadas_$mes_atual"} * $dia_atual));
$activeWorksheet->setCellValue('K17', '-');
$activeWorksheet->setCellValue('B18', 'Taxa de Ocupação');
$activeWorksheet->mergeCells('B18:D18');
$activeWorksheet->setCellValue('E18', $quartos_occ_dia.'%');
$activeWorksheet->setCellValue('G18', $quartos_occ_mes.'%');
$activeWorksheet->setCellValue('I18', number_format(${"uhs_ocupadas_$mes_atual"} / ${"total_uhs_$mes_atual"} * 100, 2, ',', '.').'%');
$activeWorksheet->setCellValue('K18', '-');
$activeWorksheet->setCellValue('B19', 'Diária Média Bruta');
$activeWorksheet->mergeCells('B19:D19');
$activeWorksheet->setCellValue('E19', 'R$'.$dm_dia);
$activeWorksheet->setCellValue('G19', 'R$'.$dm_mes);
$activeWorksheet->setCellValue('I19', 'R$'.number_format(${"dm_$mes_atual"}, 2, ',', '.'));
$activeWorksheet->setCellValue('K19', '-');
$activeWorksheet->setCellValue('B20', 'RevPar de Hospedagem');
$activeWorksheet->mergeCells('B20:D20');
$activeWorksheet->setCellValue('E20', 'R$'.$revpar_dia);
$activeWorksheet->setCellValue('G20', 'R$'.$revpar_mes);
$activeWorksheet->setCellValue('I20', 'R$'.number_format(${"dm_$mes_atual"} * ${"uhs_ocupadas_$mes_atual"} / ${"total_uhs_$mes_atual"}, 2, ',', '.'));
$activeWorksheet->setCellValue('K20', '-');
$activeWorksheet->setCellValue('B21', 'RevPar Total');
$activeWorksheet->mergeCells('B21:D21');
$activeWorksheet->setCellValue('E21', 'R$'.$revpar_dia_total);
$activeWorksheet->setCellValue('G21', 'R$'.$revpar_mes_total);
$activeWorksheet->setCellValue('I21', 'R$'.number_format(${"dm_$mes_atual"} * ${"uhs_ocupadas_$mes_atual"} / ${"total_uhs_$mes_atual"} * 1.05, 2, ',', '.'));
$activeWorksheet->setCellValue('K21', '-');
$activeWorksheet->setCellValue('B22', 'Walk-ins');
$activeWorksheet->mergeCells('B22:D22');
$activeWorksheet->setCellValue('E22', $walkins_dia);
$activeWorksheet->setCellValue('G22', $walkins_mes);
$activeWorksheet->setCellValue('I22', '-');
$activeWorksheet->setCellValue('K22', '-');
$activeWorksheet->setCellValue('B23', 'Número de Adultos');
$activeWorksheet->mergeCells('B23:D23');
$activeWorksheet->setCellValue('E23', $adultos_dia);
$activeWorksheet->setCellValue('G23', $adultos_mes);
$activeWorksheet->setCellValue('I23', '-');
$activeWorksheet->setCellValue('K23', '-');
$activeWorksheet->setCellValue('B24', 'Número de Crianças');
$activeWorksheet->mergeCells('B24:D24');
$activeWorksheet->setCellValue('E24', $criancas_dia);
$activeWorksheet->setCellValue('G24', $criancas_mes);
$activeWorksheet->setCellValue('I24', '-');
$activeWorksheet->setCellValue('K24', '-');
$activeWorksheet->setCellValue('B25', 'Fator de Ocupação');
$activeWorksheet->mergeCells('B25:D25');
$activeWorksheet->setCellValue('E25', $fator_occ_dia);
$activeWorksheet->setCellValue('G25', $fator_occ_mes);
$activeWorksheet->setCellValue('I25', '-');
$activeWorksheet->setCellValue('K25', '-');
$activeWorksheet->setCellValue('B26', 'Receita Bruta');
$activeWorksheet->mergeCells('B26:D26');
$activeWorksheet->setCellValue('B27', 'Hospedagem');
$activeWorksheet->mergeCells('B27:D27');
$activeWorksheet->setCellValue('E27', $receita_hospedagem_dia);
$activeWorksheet->setCellValue('G27', $receita_hospedagem_mes);
$activeWorksheet->setCellValue('I27', 'R$'.number_format(${"total_hospedagem_$mes_atual"} / $dia_ultimo * $dia_atual, 2, ',', '.'));
$activeWorksheet->setCellValue('K27', '-');
$activeWorksheet->setCellValue('B28', 'No Show');
$activeWorksheet->mergeCells('B28:D28');
$activeWorksheet->setCellValue('E28', $receita_noshow_dia);
$activeWorksheet->setCellValue('G28', $receita_noshow_mes);
$activeWorksheet->setCellValue('I28', '-');
$activeWorksheet->setCellValue('K28', '-');
$activeWorksheet->setCellValue('B29', 'Outras Receitas - Hospedagem');
$activeWorksheet->mergeCells('B29:D29');
$activeWorksheet->setCellValue('E29', $receita_outros_dia);
$activeWorksheet->setCellValue('G29', $receita_outros_mes);
$activeWorksheet->setCellValue('I29', '-');
$activeWorksheet->setCellValue('K29', '-');
$activeWorksheet->setCellValue('B30', 'Alimentos e Bebidas');
$activeWorksheet->mergeCells('B30:D30');
$activeWorksheet->setCellValue('E30', '0');
$activeWorksheet->setCellValue('G30', '0');
$activeWorksheet->setCellValue('I30', '-');
$activeWorksheet->setCellValue('K30', '-');
$activeWorksheet->setCellValue('B31', 'Aluguel de Salas e Equipamentos');
$activeWorksheet->mergeCells('B31:D31');
$activeWorksheet->setCellValue('E31', $receita_eventos_dia);
$activeWorksheet->setCellValue('G31', $receita_eventos_mes);
$activeWorksheet->setCellValue('I31', '-');
$activeWorksheet->setCellValue('K31', '-');
$activeWorksheet->setCellValue('B32', 'Estac/Spa/Frigobar/ Lav/Div');
$activeWorksheet->mergeCells('B32:D32');
$activeWorksheet->setCellValue('E32', $receita_lavanderia_dia);
$activeWorksheet->setCellValue('G32', $receita_lavanderia_mes);
$activeWorksheet->setCellValue('I32', '-');
$activeWorksheet->setCellValue('K32', '-');
$activeWorksheet->setCellValue('B33', 'Taxa de ISS');
$activeWorksheet->mergeCells('B33:D33');
$activeWorksheet->setCellValue('E33', $receita_taxaiss_dia);
$activeWorksheet->setCellValue('G33', $receita_taxaiss_mes);
$activeWorksheet->setCellValue('I33', 'R$'.number_format(${"total_hospedagem_$mes_atual"} / $dia_ultimo * $dia_atual * 0.05, 2, ',', '.'));
$activeWorksheet->setCellValue('K33', '-');
$activeWorksheet->setCellValue('B34', 'TOTAL DE RECEITAS');
$activeWorksheet->mergeCells('B34:D34');
$activeWorksheet->setCellValue('E34', $receita_total_dia);
$activeWorksheet->setCellValue('G34', $receita_total_mes);
$activeWorksheet->setCellValue('I34', 'R$'.number_format(${"total_hospedagem_$mes_atual"} / $dia_ultimo * $dia_atual * 1.05, 2, ',', '.'));
$activeWorksheet->setCellValue('K34', '-');
$activeWorksheet->setCellValue('B35', 'Previsão Total do Mês >>>>');
$activeWorksheet->mergeCells('B35:G35');
$activeWorksheet->setCellValue('I35', '-');
$activeWorksheet->setCellValue('K35', '-');
$activeWorksheet->setCellValue('B36', 'Proporcional do mês até a data>>>>');
$activeWorksheet->mergeCells('B36:G36');
$activeWorksheet->setCellValue('I36', '-');
$activeWorksheet->setCellValue('K36', '-');
$activeWorksheet->setCellValue('B37', 'Percentual do mês realizado>>>>');
$activeWorksheet->mergeCells('B37:G37');
$activeWorksheet->setCellValue('I37', '-');
$activeWorksheet->setCellValue('K37', '-');
$activeWorksheet->setCellValue('B39', 'Previsão para os Próximos dias');
$activeWorksheet->mergeCells('B39:D39');
$activeWorksheet->setCellValue('E39', date('d/m/Y', strtotime("$data_auditoria +1 day")));
$activeWorksheet->mergeCells('E39:F39');
$activeWorksheet->setCellValue('G39', date('d/m/Y', strtotime("$data_auditoria +2 day")));
$activeWorksheet->mergeCells('G39:H39');
$activeWorksheet->setCellValue('I39', date('d/m/Y', strtotime("$data_auditoria +3 day")));
$activeWorksheet->mergeCells('I39:J39');
$activeWorksheet->setCellValue('B40', 'UHs Disponíveis');
$activeWorksheet->mergeCells('B40:D40');
$activeWorksheet->setCellValue('E40', $quartos_construidos_dia);
$activeWorksheet->mergeCells('E40:F40');
$activeWorksheet->setCellValue('G40', $quartos_construidos_dia);
$activeWorksheet->mergeCells('G40:H40');
$activeWorksheet->setCellValue('I40', $quartos_construidos_dia);
$activeWorksheet->mergeCells('I40:J40');
$activeWorksheet->setCellValue('B41', 'Previsão UHs Ocupadas');
$activeWorksheet->mergeCells('B41:D41');
$activeWorksheet->setCellValue('E41', $forecast_1);
$activeWorksheet->mergeCells('E41:F41');
$activeWorksheet->setCellValue('G41', $forecast_2);
$activeWorksheet->mergeCells('G41:H41');
$activeWorksheet->setCellValue('I41', $forecast_3);
$activeWorksheet->mergeCells('I41:J41');
$activeWorksheet->setCellValue('B42', '% Ocupação');
$activeWorksheet->mergeCells('B42:D42');
$activeWorksheet->setCellValue('E42', $forecast_occ_1);
$activeWorksheet->mergeCells('E42:F42');
$activeWorksheet->setCellValue('G42', $forecast_occ_2);
$activeWorksheet->mergeCells('G42:H42');
$activeWorksheet->setCellValue('I42', $forecast_occ_3);
$activeWorksheet->mergeCells('I42:J42');
$activeWorksheet->setCellValue('B43', 'Diária Média');
$activeWorksheet->mergeCells('B43:D43');
$activeWorksheet->setCellValue('E43', number_format(floatval($forecast_dm_1), 2, ',', '.'));
$activeWorksheet->mergeCells('E43:F43');
$activeWorksheet->setCellValue('G43', number_format(floatval($forecast_dm_2), 2, ',', '.'));
$activeWorksheet->mergeCells('G43:H43');
$activeWorksheet->setCellValue('I43', number_format(floatval($forecast_dm_3), 2, ',', '.'));
$activeWorksheet->mergeCells('I43:J43');
$activeWorksheet->setCellValue('B44', 'Número de Hóspedes');
$activeWorksheet->mergeCells('B44:D44');
$activeWorksheet->setCellValue('E44', $forecast_pax_1);
$activeWorksheet->mergeCells('E44:F44');
$activeWorksheet->setCellValue('G44', $forecast_pax_2);
$activeWorksheet->mergeCells('G44:H44');
$activeWorksheet->setCellValue('I44', $forecast_pax_3);
$activeWorksheet->mergeCells('I44:J44');

$activeWorksheet->setShowGridlines(false);
$activeWorksheet->setShowRowColHeaders(false);
$activeWorksheet->getColumnDimension('A')->setWidth(3);
$activeWorksheet->getColumnDimension('B')->setWidth(19);
$activeWorksheet->getColumnDimension('C')->setWidth(3);
$activeWorksheet->getColumnDimension('D')->setWidth(7);
$activeWorksheet->getColumnDimension('E')->setWidth(19);
$activeWorksheet->getColumnDimension('F')->setWidth(3);
$activeWorksheet->getColumnDimension('G')->setWidth(19);
$activeWorksheet->getColumnDimension('H')->setWidth(3);
$activeWorksheet->getColumnDimension('I')->setWidth(19);
$activeWorksheet->getColumnDimension('J')->setWidth(3);
$activeWorksheet->getColumnDimension('K')->setWidth(19);
$activeWorksheet->getColumnDimension('L')->setWidth(3);

$activeWorksheet->getStyle('B5:K44')->applyFromArray($styleArray_padrao);

$activeWorksheet->getStyle('B34:K34')->applyFromArray($styleArray_outside_borders_fina);

$activeWorksheet->getStyle('A5:K37')->applyFromArray($styleArray_outside_borders);
$activeWorksheet->getStyle('B39:J44')->applyFromArray($styleArray_outside_borders);

$activeWorksheet->getStyle('B3:K3')->applyFromArray($styleArray_branco_10);
$activeWorksheet->getStyle('I6:K6')->applyFromArray($styleArray_branco);
$activeWorksheet->getStyle('B6:D6')->applyFromArray($styleArray_branco);
$activeWorksheet->getStyle('E8:K8')->applyFromArray($styleArray_branco);
$activeWorksheet->getStyle('B34:D37')->applyFromArray($styleArray_branco);

$activeWorksheet->getStyle('E11:E12')->applyFromArray($styleArray_blue);
$activeWorksheet->getStyle('E14:E16')->applyFromArray($styleArray_blue);
$activeWorksheet->getStyle('E22:E24')->applyFromArray($styleArray_blue);
$activeWorksheet->getStyle('E27:E33')->applyFromArray($styleArray_blue);
$activeWorksheet->getStyle('I37')->applyFromArray($styleArray_blue);
$activeWorksheet->getStyle('G11:G12')->applyFromArray($styleArray_blue);
$activeWorksheet->getStyle('G14:G16')->applyFromArray($styleArray_blue);
$activeWorksheet->getStyle('G22:G24')->applyFromArray($styleArray_blue);
$activeWorksheet->getStyle('G27:G33')->applyFromArray($styleArray_blue);

$activeWorksheet->getStyle('E6')->applyFromArray($styleArray_blue);
$activeWorksheet->getStyle('E41:I41')->applyFromArray($styleArray_blue);
$activeWorksheet->getStyle('E43:I44')->applyFromArray($styleArray_blue);

$activeWorksheet->getStyle('B11:B44')->applyFromArray($styleArray_verdana_left);

$activeWorksheet->getStyle('B8:B10')->applyFromArray($styleArray_verdana_left_bold);
$activeWorksheet->getStyle('B13')->applyFromArray($styleArray_verdana_left_bold);
$activeWorksheet->getStyle('B17:B19')->applyFromArray($styleArray_verdana_left_bold);
$activeWorksheet->getStyle('B21')->applyFromArray($styleArray_verdana_left_bold);
$activeWorksheet->getStyle('B26')->applyFromArray($styleArray_verdana_left_bold);
$activeWorksheet->getStyle('B35:B37')->applyFromArray($styleArray_verdana_left_bold);

$activeWorksheet->getStyle('E27:K34')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('E43:J43')->getNumberFormat()->setFormatCode('R$ #,##0.00');

// Inserir uma imagem
$imagePath = '../imagem/hcc_pequena.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('HCC');
$objDrawing->setDescription('HCC');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('B1'); // Posição onde a imagem será inserida
$objDrawing->setWorksheet($activeWorksheet);

$worksheet->setSelectedCell('A1');

//Fim Abas
$spreadsheet->setActiveSheetIndexByName('Gerencial');

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