<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Color;

$hotel = $_SESSION['hotel_name'];
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

// Check if the CSV files were uploaded successfully
if (!empty($_FILES["csvFile"]["name"]) && count($_FILES["csvFile"]["name"]) > 0) {
    $file_names = $_FILES["csvFile"]["name"];
    $file_tmp_names = $_FILES["csvFile"]["tmp_name"];

    //Importar Loyalts Reports
    $excel_all_pms = [];
    
    for ($i = 0; $i < count($file_names); $i++) {
        $file_name = $file_names[$i];
        $tmp_name = $file_tmp_names[$i];

        // Check if the uploaded file is a CSV or XML
        if (strtolower(pathinfo($file_name, PATHINFO_EXTENSION)) == "csv") {
            // Read the CSV file
            $file_handle = fopen($tmp_name, "r");
            if ($file_handle !== FALSE) {
                $skip_first_line = true;
    
                // Process each row in the CSV file
                while (($data = fgetcsv($file_handle, 10000, ";")) !== FALSE) {
                    if ($skip_first_line) {
                        $skip_first_line = false;
                        continue;
                    }
                    $colunaA = substr($data[1], 7, -1);
                    $colunaB = $data[17] . ' ' . $data[2];
                    $colunaC = str_replace('/', '-', substr($data[3], 0, 10));
                    $colunaD = str_replace('/', '-', substr($data[4], 0, 10));
                    $colunaE = $data[15];
                    $colunaF = $data[6];
                    $colunaG = $data[8];
                    $colunaH = $data[10];
                    $colunaI = $data[12];

                    $colunaC_partes = explode("-", $colunaC);
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[1] . "-" . $colunaC_partes[0];
                    $colunaD_partes = explode("-", $colunaD);
                    $colunaD_formatada = $colunaD_partes[2] . "-" . $colunaD_partes[1] . "-" . $colunaD_partes[0];

                    // Create an associative array for the data
                    $excel_all_pms[] = [
                        'pmid' => $colunaA,
                        'guest_name' => $colunaB,
                        'checkin' => $colunaC_formatada,
                        'checkout' => $colunaD_formatada,
                        'ratecode' => $colunaE,
                        'pontuacao_total' => $colunaF,
                        'pontuacao_room' => $colunaG,
                        'pontuacao_aeb' => $colunaH,
                        'pontuacao_outros' => $colunaI,
                        'status_pontos' => 'Pendente',
                    ];
                }
            
            } else {
                echo "Erro ao importar o arquivo $file_name.";
            }
        } else {
            echo "Invalid file format. Only CSV files are allowed.";
        }
    }

}else {
    echo "Favor selecionar todos os aquivos.";
}

if (isset($_FILES["excelFile"]["tmp_name"]) && !empty($_FILES["excelFile"]["tmp_name"])) {
    $uploadedFile = $_FILES["excelFile"]["tmp_name"];
    $skip_first_line = true;

    $excel_all_hotellink = [];
    $excel_all_planilha = [];

        // Carrega o arquivo Excel
        $spreadsheet = IOFactory::load($uploadedFile);

        // Seleciona a primeira planilha (índice 0)
        $worksheet = $spreadsheet->getActiveSheet();

        // Obtém todas as células da planilha como uma matriz
        $datas = $worksheet->toArray();
        $qtd = 0;

        foreach ($datas as $data_row) {

            $qtd++;

            if ($qtd <= 4) {
                if($qtd == 3){
                    $data_hl = explode(" ",$data_row[0]);
                }
                continue;
            }

            if($data_row[24] == ''){
            $data_rows = str_replace('"', '', $data_row[0].$data_row[1].' '.$data_row[2].' '.$data_row[3].' '.$data_row[4].$data_row[5]);
            $data_row = explode(';', $data_rows);
            }

            $pmid = explode(" ", $data_row[0]);

            if($data_row[7] == 'COURTESY' || $data_row[7] == 'Earn Restaurant'){
                $data_row[2] = $data_row[5];
                $data_row[3] = $data_row[5];
                $data_row[11] = 'N/A';
                $data_row[18] = $data_row[7];
            }

            if($data_row[7] == 'COURTESY'){
                $data_row[15] = $data_row[12];
            }

            $checkin_partes = substr($data_row[2], 0, 10);
            $checkout_partes = substr($data_row[3], 0, 10);
            $data_pontuacao_partes = substr($data_row[5], 0, 10);

            $colunaA_partes = explode("-", $checkin_partes);
            $checkin = $colunaA_partes[2] . "-" . $colunaA_partes[1] . "-" . $colunaA_partes[0];
            $colunaB_partes = explode("-", $checkout_partes);
            $checkout = $colunaB_partes[2] . "-" . $colunaB_partes[1] . "-" . $colunaB_partes[0];
            $colunaC_partes = explode("-", $data_pontuacao_partes);
            $data_pontuacao = $colunaC_partes[2] . "-" . $colunaC_partes[1] . "-" . $colunaC_partes[0];

            if($data_row[18] == ''){
            $reason =  'Cancelamento para Correção';
            }else  if($data_row[21] == '' && $data_row[24] != 'null'){
            $reason = $data_row[24];
            }else{
            $reason = $data_row[21];  
            }

            $excel_all_hotellink[] = [
                'pmid' => $pmid[2],
                'card_name' => $data_row[4],
                'data_pontuacao' => $data_pontuacao,
                'checkin' => $checkin,
                'checkout' => $checkout,
                'ratecode' => $data_row[11],
                'earnmidia' => $data_row[18],
                'usuario' => $data_row[20],
                'reserva' => $data_row[24],
                'pontuacao_real' => $data_row[15],
                'pontuacao_euro' => $data_row[14],
                'pontuacao_burn' => $data_row[17],
                'reason' => $reason,
            ];
        
            $excel_all_planilha[] = [
                'pmid' => $pmid[2],
                'hospede_hl' => $data_row[4],
                'hospede_pms' => '',
                'checkin' => $checkin,
                'checkout' => $checkout,
                'data_pontuacao' => $data_pontuacao,
                'ratecode' => $data_row[11],
                'pontuacao_hl' => $data_row[15],
                'pontuacao_pms' => 0,
                'burn' => $data_row[17],
                'earnmidia' => $data_row[18],
                'usuario' => $data_row[20],
                'reason' => $reason,
            ];

        }

        //Data do Relatorio Hotel Link
        $data_hotellink_pt1 = explode("-", $data_hl[2]);
        $data_hotellink_pt2 = explode("-", $data_hl[4]);
        $data_hotellink = strtoupper($_SESSION['hotel']).' - Conferencia ALL '.$data_hotellink_pt1[2].'-'.$data_hotellink_pt1[1].' a '.$data_hotellink_pt2[2].'-'.$data_hotellink_pt2[1];

}else {
    echo "Favor selecionar todos os aquivos.";
}

//Total ALL
foreach ($excel_all_hotellink as $hotelLinkData) {
    $pmid = $hotelLinkData['pmid'];
    $checkin = $hotelLinkData['checkin'];
    $checkout = $hotelLinkData['checkout'];

    // Find matching data in $excel_all_pms
    $matchingPMSData = array_filter($excel_all_pms, function ($pmsData) use ($pmid, $checkin, $checkout) {
        return $pmsData['pmid'] == $pmid && $pmsData['checkin'] == $checkin && $pmsData['checkout'] == $checkout;
    });

    if (!empty($matchingPMSData)) {
        foreach ($matchingPMSData as $pmsData) {
            $guest_name = $pmsData['guest_name'];
            $ratecode = $pmsData['ratecode'];
            $pontuacao_total = $pmsData['pontuacao_total'];
            $pontuacao_room = $pmsData['pontuacao_room'];

            // Perform your calculations here
            $pontuacao_total /= 1.05;

            // Update status_pontos in $excel_all_planilha
            foreach ($excel_all_pms as &$pmsData) {
                if (
                    $pmsData['pmid'] == $pmid &&
                    $pmsData['checkin'] == $checkin &&
                    $pmsData['checkout'] == $checkout
                ) {
                    // Update the 'status_pontos' field to 'Pontuado'
                    $pmsData['status_pontos'] = 'Pontuado';
            
                    // Calculate the 'pontuacao_total' field
                    $pmsData['pontuacao_total'] /= 1.05;
                }
            }

        // Update the $excel_all_planilha array directly
        foreach ($excel_all_planilha as &$planilhaData) {
            if (
                $planilhaData['pmid'] == $pmid &&
                $planilhaData['checkin'] == $checkin &&
                $planilhaData['checkout'] == $checkout
            ) {
                $planilhaData['hospede_pms'] = $guest_name;
                $planilhaData['pontuacao_pms'] = $pontuacao_total;
                $planilhaData['ratecode'] = $ratecode;
            }
        }
        
        }
    }
}


//Criar Planilha Excel
$spreadsheet = new Spreadsheet();

$conditionGreen = new Conditional();
$conditionGreen->setConditionType(Conditional::CONDITION_CELLIS)
    ->setOperatorType(Conditional::OPERATOR_EQUAL)
    ->addCondition('0')
    ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_GREEN);

$conditionRed = new Conditional();
$conditionRed->setConditionType(Conditional::CONDITION_CELLIS)
    ->setOperatorType(Conditional::OPERATOR_NOTEQUAL)
    ->addCondition('0')
    ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_RED);

$conditionGrey = new Conditional();
$conditionGrey->setConditionType(Conditional::CONDITION_CELLIS)
    ->setOperatorType(Conditional::OPERATOR_NOTEQUAL)
    ->addCondition('0')
    ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_MAGENTA);

//Primeira Aba
$spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
$security = $spreadsheet->getSecurity();
$security->setLockWindows(true);
$security->setLockStructure(true);
$security->setWorkbookPassword($worksheet_password);

$activeWorksheet = $spreadsheet->getActiveSheet();
$activeWorksheet->setCellValue('C3', $hotel);
$activeWorksheet->mergeCells('C3:S3');
$activeWorksheet->setCellValue('C5', 'Conferencia de Pontos entre Hotel Link vs PMS');
$activeWorksheet->mergeCells('C5:P5');
$activeWorksheet->setCellValue('C6', 'PMID');
$activeWorksheet->setCellValue('D6', 'Hospede HL');
$activeWorksheet->setCellValue('E6', 'Hospede PMS');
$activeWorksheet->setCellValue('F6', 'Checkin');
$activeWorksheet->setCellValue('G6', 'Checkout');
$activeWorksheet->setCellValue('H6', 'Data Pontuação');
$activeWorksheet->setCellValue('I6', 'Rate Code');
$activeWorksheet->setCellValue('J6', 'Pontuação HL');
$activeWorksheet->setCellValue('K6', 'Pontuação PMS');
$activeWorksheet->setCellValue('L6', 'Diferença');
$activeWorksheet->setCellValue('M6', 'Burn');
$activeWorksheet->setCellValue('N6', 'Earn Midia');
$activeWorksheet->setCellValue('O6', 'Login');
$activeWorksheet->setCellValue('P6', 'Motivo');

$activeWorksheet->setShowGridlines(false);
$activeWorksheet->setShowRowColHeaders(false);
$activeWorksheet->getRowDimension(1)->setRowHeight(10);
$activeWorksheet->getColumnDimension('A')->setWidth(2);
$activeWorksheet->getColumnDimension('B')->setWidth(2);
$activeWorksheet->getColumnDimension('Q')->setWidth(2);
$activeWorksheet->getColumnDimension('T')->setWidth(2);

// Inserir uma imagem
$imagePath = '../imagem/logo_all.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('ALL');
$objDrawing->setDescription('ALL');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('R6'); // Posição onde a imagem será inserida
$objDrawing->setWorksheet($activeWorksheet);

$styleArray_header = [
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
$spreadsheet->getActiveSheet()->getStyle('C5:P5')->applyFromArray($styleArray_header);

$styleArray_header = [
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
$spreadsheet->getActiveSheet()->getStyle('C6:P6')->applyFromArray($styleArray_header);

$styleArray_header = [
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
$spreadsheet->getActiveSheet()->getStyle('C3:P3')->applyFromArray($styleArray_header);

$spreadsheet->getProperties()
    ->setCreator("Denis Ferraz")
    ->setLastModifiedBy("Denis Ferraz")
    ->setTitle("Conciliação ALL Automatizado by Denis Ferraz");

// Definir a largura automática das colunas C até P
$activeWorksheet->calculateColumnWidths();
for ($col = 'C'; $col <= 'P'; $col++) {
    $activeWorksheet->getColumnDimension($col)->setAutoSize(true);
}

$linha_excel = 6;

//Total Comissões
foreach ($excel_all_planilha as $select) {
    $pmid = $select['pmid'];
    $hospede_hl = $select['hospede_hl'];
    $hospede_pms = $select['hospede_pms'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $data_pontuacao = $select['data_pontuacao'];
    $ratecode = $select['ratecode'];
    $pontuacao_hl = floatval($select['pontuacao_hl']);
    $pontuacao_pms = floatval($select['pontuacao_pms']);
    $burn = $select['burn'];
    $earnmidia = $select['earnmidia'];
    $usuario = $select['usuario'];
    $reason = $select['reason'];

    $linha_excel++;

    $checkin = date('d/m/Y', strtotime("$checkin"));
    $checkout = date('d/m/Y', strtotime("$checkout"));
    $data_pontuacao = date('d/m/Y', strtotime("$data_pontuacao"));

    $pontuacao = round($pontuacao_hl - $pontuacao_pms, 0);

    if($pontuacao == 0){
    $reason = '';   
    }else{
    $reason = $reason;
    }

    $activeWorksheet->setCellValue('C'.$linha_excel, $pmid);
    $activeWorksheet->setCellValue('D'.$linha_excel, $hospede_hl);
    $activeWorksheet->setCellValue('E'.$linha_excel, $hospede_pms);
    $activeWorksheet->setCellValue('F'.$linha_excel, $checkin);
    $activeWorksheet->setCellValue('G'.$linha_excel, $checkout);
    $activeWorksheet->setCellValue('H'.$linha_excel, $data_pontuacao);
    $activeWorksheet->setCellValue('I'.$linha_excel, $ratecode);
    $activeWorksheet->setCellValue('J'.$linha_excel, $pontuacao_hl);
    $activeWorksheet->setCellValue('K'.$linha_excel, $pontuacao_pms);
    $activeWorksheet->setCellValue('L'.$linha_excel, '=ROUND(K'.$linha_excel.' - J'.$linha_excel.', 0)');
    $activeWorksheet->setCellValue('M'.$linha_excel, $burn);
    $activeWorksheet->setCellValue('N'.$linha_excel, $earnmidia);
    $activeWorksheet->setCellValue('O'.$linha_excel, $usuario);
    $activeWorksheet->setCellValue('P'.$linha_excel, $reason);

}

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
    $activeWorksheet->setCellValue('J'.$linha_excel, '');
    $activeWorksheet->setCellValue('K'.$linha_excel, '');
    $activeWorksheet->setCellValue('L'.$linha_excel, '');
    $activeWorksheet->setCellValue('M'.$linha_excel, '');
    $activeWorksheet->setCellValue('N'.$linha_excel, '');
    $activeWorksheet->setCellValue('O'.$linha_excel, '');
    $activeWorksheet->setCellValue('P'.$linha_excel, '');
}
}

$linha_excel += 4;
$linha_signature = $linha_excel;

$data_quando = date('d/m/Y - H:i:s');

$activeWorksheet->setCellValue('D'.$linha_excel, $_SESSION['name']);
$activeWorksheet->setCellValue('E'.$linha_excel, $data_quando);
$activeWorksheet->setCellValue('J'.$linha_excel, '');
$activeWorksheet->mergeCells('J'.$linha_excel.':L'.$linha_excel);
$activeWorksheet->setCellValue('M'.$linha_excel, '');
$activeWorksheet->mergeCells('M'.$linha_excel.':O'.$linha_excel);

$spreadsheet->getActiveSheet()->getStyle('B'.($linha_excel - 1).':T'.($linha_excel + 2))->applyFromArray($styleArray_header);

$linha_excel += 1;

$activeWorksheet->setCellValue('D'.$linha_excel, 'Responsavel');
$activeWorksheet->setCellValue('E'.$linha_excel, 'Hora');
$activeWorksheet->setCellValue('J'.$linha_excel, 'Responsavel');
$activeWorksheet->mergeCells('J'.$linha_excel.':L'.$linha_excel);
$activeWorksheet->setCellValue('M'.$linha_excel, 'Hora');
$activeWorksheet->mergeCells('M'.$linha_excel.':O'.$linha_excel);

$linha_excel += 3;

$activeWorksheet->setCellValue('D'.$linha_excel, 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('D'.$linha_excel.':E'.$linha_excel);
$activeWorksheet->setCellValue('J'.$linha_excel, 'Assinatura Digital N + 1');
$activeWorksheet->mergeCells('J'.$linha_excel.':O'.$linha_excel);

$styleArray_header = [
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
    'borders' => [
        'top' => [
            'borderStyle' => Border::BORDER_THICK,
            'color' => ['rgb' => '000000'],
        ],
        'bottom' => [
            'borderStyle' => Border::BORDER_DOUBLE,
            'color' => ['rgb' => '000000'],
        ],
    ],
];

$spreadsheet->getActiveSheet()->getStyle('D'.$linha_excel.':E'.$linha_excel)->applyFromArray($styleArray_header);
$spreadsheet->getActiveSheet()->getStyle('J'.$linha_excel.':O'.$linha_excel)->applyFromArray($styleArray_header);

$styleArray_header = [
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
    'borders' => [
        'bottom' => [
            'borderStyle' => Border::BORDER_THICK,
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$spreadsheet->getActiveSheet()->getStyle('D'.$linha_signature.':E'.$linha_signature)->applyFromArray($styleArray_header);
$spreadsheet->getActiveSheet()->getStyle('J'.$linha_signature.':O'.$linha_signature)->applyFromArray($styleArray_header);

// Inside Borders
$lastColumn = 'P';
$lastRow = $linha_excel - 8;
$range = "C5:$lastColumn$lastRow";
$styleArray = [
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
$activeWorksheet->getStyle($range)->applyFromArray($styleArray);

// Destravar a coluna Pontuação PMS e Motivo
$range = 'K7:K'.($linha_excel - 8);
$activeWorksheet->getStyle($range)
    ->getProtection()
    ->setLocked(Protection::PROTECTION_UNPROTECTED);

$range = 'P7:P'.($linha_excel - 8);
$activeWorksheet->getStyle($range)
    ->getProtection()
    ->setLocked(Protection::PROTECTION_UNPROTECTED);

//Aplicar Condição
$range = 'L7:L'.($linha_excel - 8);
$conditionalStyles = $activeWorksheet->getStyle($range)->getConditionalStyles();
$conditionalStyles[] = $conditionGreen;
$conditionalStyles[] = $conditionRed;
$activeWorksheet->getStyle($range)->setConditionalStyles($conditionalStyles);

$range = 'M7:M'.($linha_excel - 8);
$conditionalStyles = $activeWorksheet->getStyle($range)->getConditionalStyles();
$conditionalStyles[] = $conditionGrey;
$activeWorksheet->getStyle($range)->setConditionalStyles($conditionalStyles);

// Outside Borders
$lastColumn = 'T';
$lastRow = $linha_excel - 7;
$range = "B2:$lastColumn$lastRow";
$styleArray = [
    'borders' => [
        'outline' => [
            'borderStyle' => 'thick',
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$activeWorksheet->getStyle($range)->applyFromArray($styleArray);

// Signature Borders
$linha_signature = $linha_excel - 5;
$lastColumn = 'T';
$lastRow = $linha_excel - 2;
$range = "B$linha_signature:$lastColumn$lastRow";
$styleArray = [
    'borders' => [
        'outline' => [
            'borderStyle' => 'thick',
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$activeWorksheet->getStyle($range)->applyFromArray($styleArray);

// Hotel Nome
$range = "C3:S3";
$styleArray = [
    'borders' => [
        'outline' => [
            'borderStyle' => 'thick',
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$activeWorksheet->getStyle($range)->applyFromArray($styleArray);

$activeWorksheet->getStyle('J')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('K')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('L')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('M')->getNumberFormat()->setFormatCode('R$ #,##0.00');

$styleArray_down = [
    'font' => [
        'bold' => false,
        'size' => 8,
        'name' => 'Calibri',
    ],
];
$spreadsheet->getActiveSheet()->getStyle('C7:N'.($linha_excel - 7))->applyFromArray($styleArray_down);

$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setTitle('Pontuações Hotel Link');
$activeWorksheet->setSelectedCell('A1');

//Segunda Aba

//Criar Aba
$activeWorksheet = $spreadsheet->createSheet();
$spreadsheet->setActiveSheetIndexByName('Worksheet');
$spreadsheet->getActiveSheet()->getProtection()->setSheet(true);
$security = $spreadsheet->getSecurity();
$security->setLockWindows(true);
$security->setLockStructure(true);
$security->setWorkbookPassword($worksheet_password);

$activeWorksheet = $spreadsheet->getActiveSheet();
$activeWorksheet->setCellValue('C3', $hotel);
$activeWorksheet->mergeCells('C3:L3');
$activeWorksheet->setCellValue('C5', 'Conferencia de Pontos Nâo Lançados no Hotel Link');
$activeWorksheet->mergeCells('C5:I5');
$activeWorksheet->setCellValue('C6', 'PMID');
$activeWorksheet->setCellValue('D6', 'Hospede PMS');
$activeWorksheet->setCellValue('E6', 'Checkin');
$activeWorksheet->setCellValue('F6', 'Checkout');
$activeWorksheet->setCellValue('G6', 'Rate Code');
$activeWorksheet->setCellValue('H6', 'Pontuação PMS');
$activeWorksheet->setCellValue('I6', 'Motivo');

$activeWorksheet->setShowGridlines(false);
$activeWorksheet->setShowRowColHeaders(false);
$activeWorksheet->getRowDimension(1)->setRowHeight(10);
$activeWorksheet->getColumnDimension('A')->setWidth(2);
$activeWorksheet->getColumnDimension('B')->setWidth(2);
$activeWorksheet->getColumnDimension('I')->setWidth(40);
$activeWorksheet->getColumnDimension('J')->setWidth(2);
$activeWorksheet->getColumnDimension('M')->setWidth(2);

// Inserir uma imagem
$imagePath = '../imagem/logo_all.jpg';
$objDrawing = new Drawing();
$objDrawing->setName('ALL');
$objDrawing->setDescription('ALL');
$objDrawing->setPath($imagePath);
$objDrawing->setCoordinates('K6'); // Posição onde a imagem será inserida
$objDrawing->setWorksheet($activeWorksheet);

$styleArray_header = [
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
$spreadsheet->getActiveSheet()->getStyle('C5:I5')->applyFromArray($styleArray_header);

$styleArray_header = [
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
$spreadsheet->getActiveSheet()->getStyle('C6:I6')->applyFromArray($styleArray_header);

$styleArray_header = [
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
$spreadsheet->getActiveSheet()->getStyle('C3:L3')->applyFromArray($styleArray_header);

// Definir a largura automática das colunas C até I
$activeWorksheet->calculateColumnWidths();
for ($col = 'C'; $col <= 'H'; $col++) {
    $activeWorksheet->getColumnDimension($col)->setAutoSize(true);
}

//Filtrar por apenas não pontuados
$filtered_pms_data = array_filter($excel_all_pms, function ($pmsData) {
    return $pmsData['status_pontos'] === 'Pendente';
});

$linha_excel = 6;

foreach ($filtered_pms_data as $select) {
    $pmid = $select['pmid'];
    $guest_name = $select['guest_name'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $ratecode = $select['ratecode'];
    $pontuacao_total = $select['pontuacao_total'];

    $linha_excel++;

    $checkin = date('d/m/Y', strtotime("$checkin"));
    $checkout = date('d/m/Y', strtotime("$checkout"));
    $data_pontuacao = date('d/m/Y', strtotime("$data_pontuacao"));

    $activeWorksheet->setCellValue('C'.$linha_excel, $pmid);
    $activeWorksheet->setCellValue('D'.$linha_excel, $guest_name);
    $activeWorksheet->setCellValue('E'.$linha_excel, $checkin);
    $activeWorksheet->setCellValue('F'.$linha_excel, $checkout);
    $activeWorksheet->setCellValue('G'.$linha_excel, $ratecode);
    $activeWorksheet->setCellValue('H'.$linha_excel, $pontuacao_total);
    $activeWorksheet->setCellValue('I'.$linha_excel, '');

}

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

$linha_excel += 4;
$linha_signature = $linha_excel;

$data_quando = date('d/m/Y - H:i:s');

$activeWorksheet->setCellValue('C'.$linha_excel, $_SESSION['name']);
$activeWorksheet->setCellValue('D'.$linha_excel, $data_quando);
$activeWorksheet->mergeCells('D'.$linha_excel.':E'.$linha_excel);
$activeWorksheet->setCellValue('G'.$linha_excel, '');
$activeWorksheet->mergeCells('G'.$linha_excel.':H'.$linha_excel);
$activeWorksheet->setCellValue('I'.$linha_excel, '');
$activeWorksheet->mergeCells('I'.$linha_excel.':K'.$linha_excel);

$spreadsheet->getActiveSheet()->getStyle('B'.($linha_excel - 1).':M'.($linha_excel + 2))->applyFromArray($styleArray_header);

$linha_excel += 1;

$activeWorksheet->setCellValue('C'.$linha_excel, 'Responsavel');
$activeWorksheet->setCellValue('D'.$linha_excel, 'Hora');
$activeWorksheet->mergeCells('D'.$linha_excel.':E'.$linha_excel);
$activeWorksheet->setCellValue('G'.$linha_excel, 'Responsavel');
$activeWorksheet->mergeCells('G'.$linha_excel.':H'.$linha_excel);
$activeWorksheet->setCellValue('I'.$linha_excel, 'Hora');
$activeWorksheet->mergeCells('I'.$linha_excel.':K'.$linha_excel);

$linha_excel += 3;

$activeWorksheet->setCellValue('C'.$linha_excel, 'Assinatura Digital Auditor');
$activeWorksheet->mergeCells('C'.$linha_excel.':E'.$linha_excel);
$activeWorksheet->setCellValue('G'.$linha_excel, 'Assinatura Digital N + 1');
$activeWorksheet->mergeCells('G'.$linha_excel.':K'.$linha_excel);

$styleArray_header = [
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
    'borders' => [
        'top' => [
            'borderStyle' => Border::BORDER_THICK,
            'color' => ['rgb' => '000000'],
        ],
        'bottom' => [
            'borderStyle' => Border::BORDER_DOUBLE,
            'color' => ['rgb' => '000000'],
        ],
    ],
];

$spreadsheet->getActiveSheet()->getStyle('C'.$linha_excel.':E'.$linha_excel)->applyFromArray($styleArray_header);
$spreadsheet->getActiveSheet()->getStyle('G'.$linha_excel.':K'.$linha_excel)->applyFromArray($styleArray_header);

$styleArray_header = [
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
    'borders' => [
        'bottom' => [
            'borderStyle' => Border::BORDER_THICK,
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$spreadsheet->getActiveSheet()->getStyle('C'.$linha_signature.':E'.$linha_signature)->applyFromArray($styleArray_header);
$spreadsheet->getActiveSheet()->getStyle('G'.$linha_signature.':K'.$linha_signature)->applyFromArray($styleArray_header);

// Inside Borders
$lastColumn = 'I';
$lastRow = $linha_excel - 8;
$range = "C5:$lastColumn$lastRow";
$styleArray = [
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
$activeWorksheet->getStyle($range)->applyFromArray($styleArray);

// Destravar a coluna Motivo
$range = 'I7:I'.($linha_excel - 8);
$activeWorksheet->getStyle($range)
    ->getProtection()
    ->setLocked(Protection::PROTECTION_UNPROTECTED);

// Outside Borders
$lastColumn = 'M';
$lastRow = $linha_excel - 7;
$range = "B2:$lastColumn$lastRow";
$styleArray = [
    'borders' => [
        'outline' => [
            'borderStyle' => 'thick',
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$activeWorksheet->getStyle($range)->applyFromArray($styleArray);

// Signature Borders
$linha_signature = $linha_excel - 5;
$lastColumn = 'M';
$lastRow = $linha_excel - 2;
$range = "B$linha_signature:$lastColumn$lastRow";
$styleArray = [
    'borders' => [
        'outline' => [
            'borderStyle' => 'thick',
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$activeWorksheet->getStyle($range)->applyFromArray($styleArray);

// Hotel Nome
$range = "C3:L3";
$styleArray = [
    'borders' => [
        'outline' => [
            'borderStyle' => 'thick',
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$activeWorksheet->getStyle($range)->applyFromArray($styleArray);

$activeWorksheet->getStyle('H')->getNumberFormat()->setFormatCode('R$ #,##0.00');

$styleArray_down = [
    'font' => [
        'bold' => false,
        'size' => 8,
        'name' => 'Calibri',
    ],
];
$spreadsheet->getActiveSheet()->getStyle('C7:I'.($linha_excel - 7))->applyFromArray($styleArray_down);

$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setTitle('Pontuações Pendentes');
$activeWorksheet->setSelectedCell('A1');

//Fim Abas
$spreadsheet->setActiveSheetIndexByName('Pontuações Hotel Link');

// Create a temporary file for download
$filename = $data_hotellink.'.xls';
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