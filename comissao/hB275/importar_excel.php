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
if (!empty($_FILES["csvFile"]["name"]) && count($_FILES["csvFile"]["name"]) > 0) {
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
    
                //Importar Journal
                if (strpos($file_name, ucfirst($dir)."_FactureJournal_") !== false) {

                    $excel_journal_data = [];

                    // Process each row in the CSV file
                    while (($data = fgetcsv($file_handle, 10000, ";")) !== FALSE) {
                        if ($skip_first_line) {
                            $skip_first_line = false;
                            continue;
                        }
                        $colunaA = $data[2];
                        $colunaB = $data[9];
                        $colunaC = $data[1];
                        $colunaD = str_replace('/', '-', substr($data[7], 0, 10));
                        $colunaE = str_replace('/', '-', substr($data[8], 0, 10));
                        $colunaF = $data[3];
                    
                        $colunaE_partes = explode("-", $colunaE);
                        $colunaE_formatada = $colunaE_partes[2] . "-" . $colunaE_partes[1] . "-" . $colunaE_partes[0];
                        $colunaD_partes = explode("-", $colunaD);
                        $colunaD_formatada = $colunaD_partes[2] . "-" . $colunaD_partes[1] . "-" . $colunaD_partes[0];
                    
                        // Create an associative array for the data
                        $excel_journal_data[] = [
                            'rps_num' => $colunaA,
                            'rps_valor' => $colunaB,
                            'id_guest' => $colunaC,
                            'folio_open' => $colunaD_formatada,
                            'folio_close' => $colunaE_formatada,
                            'folio_type' => $colunaF
                        ];
                    }
            }
            //Importar Cancellations
            else if (strpos($file_name, ucfirst($dir)."_CancellationsList_") !== false) {
                // Prepare the SQL statement for inserting data into the database
                $excel_cancellations_data = [];

                // Process each row in the CSV file
                while (($data = fgetcsv($file_handle, 1000, ";")) !== FALSE) {
                    if ($skip_first_line) {
                        $skip_first_line = false;
                        continue;
                    }
                    $colunaA = $data[2];
                
                    // Armazene os valores em um array
                    $excel_cancellations_data[] = [
                        'reserva_id' => $colunaA,
                    ];
                }
            }
            //Importar Arrivals
            else if (strpos($file_name, ucfirst($dir)."_ArrivalDepartureList_") !== false) {

                $excel_arrivals_data = [];

            // Process each row in the CSV file
            while (($data = fgetcsv($file_handle, 10000, ";")) !== FALSE) {
                if ($skip_first_line || empty($data[14])) {
                    $skip_first_line = false;
                    continue;
                }

                $colunaA = $data[0];
                $colunaB = $data[3];
                $colunaC = $data[5];
                $colunaD = $data[14];
                $colunaE = $data[15];
                $colunaF = $data[16];

                $colunaB_formatada = date('Y-m-d', strtotime($colunaB));
                $colunaC_formatada = date('Y-m-d', strtotime($colunaC));

                // Armazene os dados em um array associativo
                $excel_arrivals_data[] = [
                    'guest_name' => $colunaA,
                    'checkin' => $colunaB_formatada,
                    'checkout' => $colunaC_formatada,
                    'ratecode' => $colunaD,
                    'reserva_id' => $colunaE,
                    'id_guest' => $colunaF,
                ];
            }

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

    $excel_comissoes_data = [];

        // Carrega o arquivo Excel
        $spreadsheet = IOFactory::load($uploadedFile);

        // Seleciona a primeira planilha (índice 0)
        $worksheet = $spreadsheet->getActiveSheet();

        // Obtém todas as células da planilha como uma matriz
        $data = $worksheet->toArray();
        $id = 0;
        foreach ($data as $row) {

            if(empty($row[0])){
                continue;
            }

            if ($skip_first_line) {
                $skip_first_line = false;
                continue;
            }

            // Armazene os dados em um array associativo
            $id++;
            if($row[0] != $id){ //Planilhas Novas

                $checkin_partes = explode("/", $row[10]);
                $checkin = $checkin_partes[2] . "-" . $checkin_partes[0] . "-" . $checkin_partes[1];
                $checkout_partes = explode("/", $row[11]);
                $checkout = $checkout_partes[2] . "-" . $checkout_partes[0] . "-" . $checkout_partes[1];

                $excel_comissoes_data[] = [
                    'rid' => $row[0],
                    'hotel' => $row[1],
                    'cnpj' => $row[2],
                    'iata' => $row[3],
                    'agencia' => $row[4],
                    'reserva' => $row[5],
                    'reserva_num' => $row[6],
                    'reserva_id' => $row[5] . '-' . $row[6],
                    'confirmacao' => $row[7],
                    'pnr' => $row[8],
                    'hospede' => $row[9],
                    'checkin' => $checkin,
                    'checkout' => $checkout,
                    'ratecode' => $row[12],
                    'diarias' => $row[13],
                    'valor_diaria' => $row[14],
                    'valor_total_diaria' => $row[15],
                    'valor_comissao' => $row[16],
                    'rps' => $row[17],
                    'reserva_rps' => $row[6],
                    'comentarios' => 'A Preencher',
                    'valor_rps' => $row[20],
                    'valor_fatura' => $row[21],
                    'sistema_entrada' => $row[22],
                    'tipo_empresa_agencia' => $row[23],
                    'base_periodo' => $row[24],
                ];
            }else{ //Planilhas Antigas

                $checkin_partes = explode("/", $row[12]);
                $checkin = $checkin_partes[2] . "-" . $checkin_partes[0] . "-" . $checkin_partes[1];
                $checkout_partes = explode("/", $row[13]);
                $checkout = $checkout_partes[2] . "-" . $checkout_partes[0] . "-" . $checkout_partes[1];

                $excel_comissoes_data[] = [
                    'rid' => $row[1],
                    'hotel' => $row[2],
                    'cnpj' => $row[3],
                    'iata' => $row[4],
                    'agencia' => $row[5],
                    'reserva' => $row[6],
                    'reserva_num' => $row[7],
                    'reserva_id' => $row[6] . '-' . $row[7],
                    'confirmacao' => $row[8],
                    'pnr' => $row[9],
                    'hospede' => $row[10].' '.$row[11],
                    'checkin' => $checkin,
                    'checkout' => $checkout,
                    'ratecode' => $row[14],
                    'diarias' => $row[15],
                    'valor_diaria' => $row[16],
                    'valor_total_diaria' => $row[17],
                    'valor_comissao' => $row[18],
                    'rps' => $row[19],
                    'reserva_rps' => $row[20],
                    'comentarios' => 'A Preencher',
                    'valor_rps' => $row[22],
                    'valor_fatura' => $row[23],
                    'sistema_entrada' => $row[24],
                    'tipo_empresa_agencia' => $row[25],
                    'base_periodo' => '',
                ];
            }
        }


//Array de Ratecodes e Sistema de Entrada
$ratecode_comissionado = ['RB1', 'RB4 ', 'RB4', 'RB4S', 'RA1', 'RA4', 'RA4S'];
$ratecode_particular = ['ACO', 'ACOFAM', 'PCP', 'PEX', 'SPL', 'WKE', 'WKERO'];
$sistema_entrada_array = ['Accorhotels.com', 'Brands.com', 'Call Center HP'];
$ratecodes_comissionados_mais = ['RB1BAN', 'RA1BAN', 'RB4BAN', 'RA4BAN'];

foreach ($excel_comissoes_data as &$comissao) {

    $reserva_id = $comissao['reserva_id'];

    // Itera pelo array de chegadas (arrivals) em busca do reserva_id correspondente
    foreach ($excel_arrivals_data as $arrival) {
        if ($arrival['reserva_id'] == $reserva_id) {
            $id_guest = $arrival['id_guest'];
            $ratecode = $arrival['ratecode'];
            $checkin = $arrival['checkin'];
            $checkout = $arrival['checkout'];

            //Atualizar a Planilha de Acordo ao Arrivals
            $comissao['checkin'] = $checkin;
            $comissao['checkout'] = $checkout;
            $comissao['diarias'] = (strtotime($checkout) - strtotime($checkin)) / 86400;
            $comissao['ratecode'] = $ratecode;

            // Itera pelo array de registros do journal em busca do id_guest e datas correspondentes
            foreach ($excel_journal_data as $journal) {
                if ($journal['id_guest'] == $id_guest && $journal['folio_open'] == $checkin && $journal['folio_close'] == $checkout && $journal['folio_type'] == 'Invoice' && $journal['rps_valor'] >= $comissao['valor_rps']) {
                    
                    $rps_num = $journal['rps_num'];
                    $rps_valor = floatval($journal['rps_valor']);

                $diarias = (strtotime($checkout) - strtotime($checkin)) / 86400;
                $valor_comissao = $rps_valor / 1.05 * 0.1;
                //$valor_comissao = $diarias * 10;

                if (in_array($ratecode, $ratecodes_comissionados_mais)) {
                    $valor_comissao *= 1.5;
                }

                $comissao['diarias'] = $diarias;
                $comissao['valor_comissao'] = $valor_comissao;
                $comissao['rps'] = $rps_num;
                $comissao['valor_rps'] = $rps_valor;
                $comissao['valor_fatura'] = $rps_valor;
                }
            }
        }
    }
}

foreach ($excel_comissoes_data as &$comissao_pos) {
    $reserva_id = $comissao_pos['reserva_id'];

    // Verifica se a reserva está na lista de cancelamentos
    foreach ($excel_cancellations_data as $cancellation) {
        if ($cancellation['reserva_id'] == substr($reserva_id, 0, 8)) {
            $comissao_pos['valor_comissao'] = 0;
            $comissao_pos['comentarios'] = 'Reserva Cancelada';
            $comissao_pos['valor_rps'] = 0;
            $comissao_pos['valor_fatura'] = 0;
        }
    }

    // Verifica se a reserva tem ratecode que não está na lista de ratecodes comissionados e não é particular
    if (!in_array($comissao_pos['ratecode'], array_merge($ratecode_comissionado, $ratecodes_comissionados_mais, $ratecode_particular))) {
        $comissao_pos['valor_comissao'] = 0;
        $comissao_pos['comentarios'] = 'Tarifa Net';
    }

    // Verifica se a reserva tem RPS e ratecode comissionado
    if ($comissao_pos['rps'] > 0 && $comissao_pos['agencia'] != 'agência ou particular?? Se agência inserir nome aqui' && in_array($comissao_pos['ratecode'], array_merge($ratecode_comissionado, $ratecodes_comissionados_mais))) {
        $comissao_pos['comentarios'] = 'Comissão Confirmada';
    }

    // Verifica se a reserva tem RPS e ratecode particular ou atende aos critérios do sistema de entrada
    if ( (in_array($comissao_pos['ratecode'], $ratecode_particular)) || (in_array($comissao_pos['sistema_entrada'], $sistema_entrada_array) && $comissao_pos['pnr'] == 'Rerserva Offline (Outros Canais)' && $comissao_pos['comentarios'] == 'A Preencher' && in_array($comissao_pos['ratecode'], $ratecode_comissionado))) {
        $comissao_pos['comentarios'] = 'Particular';
        $comissao_pos['valor_comissao'] = 0;
    }
}

//Criar Planilha Excel

$spreadsheet = new Spreadsheet();
$activeWorksheet = $spreadsheet->getActiveSheet();
$activeWorksheet->setCellValue('A1', 'Id Hotel');
$activeWorksheet->setCellValue('B1', 'Hotel');
$activeWorksheet->setCellValue('C1', 'CNPJ Hotel');
$activeWorksheet->setCellValue('D1', 'IATA Agência');
$activeWorksheet->setCellValue('E1', 'Nome Agência');
$activeWorksheet->setCellValue('F1', 'Código Reserva');
$activeWorksheet->setCellValue('G1', 'Nº Reserva Tars');
$activeWorksheet->setCellValue('H1', 'Nº Confirmação');
$activeWorksheet->setCellValue('I1', 'PNR Localizador');
$activeWorksheet->setCellValue('J1', 'Nome Hospede');
$activeWorksheet->setCellValue('K1', 'Check-in');
$activeWorksheet->setCellValue('L1', 'Check-Out');
$activeWorksheet->setCellValue('M1', 'Código Tarifa');
$activeWorksheet->setCellValue('N1', 'Quantidade Diária');
$activeWorksheet->setCellValue('O1', 'Valor Diária');
$activeWorksheet->setCellValue('P1', 'Valor Total Diárias');
$activeWorksheet->setCellValue('Q1', 'Valor de Comissão');
$activeWorksheet->setCellValue('R1', 'Nº RPS');
$activeWorksheet->setCellValue('S1', 'Nº Reserva RPS');
$activeWorksheet->setCellValue('T1', 'Status | Observações');
$activeWorksheet->setCellValue('U1', 'Total RPS ou Nota');
$activeWorksheet->setCellValue('V1', 'Total Fatura | Boleto');
$activeWorksheet->setCellValue('W1', 'Sistema de Entrada Reserva');
$activeWorksheet->setCellValue('X1', 'Agência / Empresa');
$activeWorksheet->setCellValue('Y1', 'Base Período');

$styleArray_header = [
    'font' => [
        'bold' => true,
        'size' => 8,
        'name' => 'Calibri',
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'FFADD8E6', // Solid blue color (you can use the desired RGB color)
        ],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
];
$spreadsheet->getActiveSheet()->getStyle('A1:Y1')->applyFromArray($styleArray_header);

$spreadsheet->getProperties()
    ->setCreator("Denis Ferraz")
    ->setLastModifiedBy("Denis Ferraz")
    ->setTitle("Controle de Comssões Automatizado by Denis Ferraz");

$activeWorksheet->calculateColumnWidths();
foreach ($activeWorksheet->getColumnIterator() as $column) {
    $activeWorksheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
}

//Total Comissões
$linha_excel = 1;

foreach ($excel_comissoes_data as $select) {
    $rid = $select['rid'];
    $hotel = $select['hotel'];
    $cnpj = $select['cnpj'];
    $iata = $select['iata'];
    $agencia = $select['agencia'];
    $reserva = $select['reserva'];
    $reserva_num = $select['reserva_num'];
    $reserva_id = $select['reserva_id'];
    $confirmacao = $select['confirmacao'];
    $pnr = $select['pnr'];
    $hospede = $select['hospede'];
    $checkin = $select['checkin'];
    $checkout = $select['checkout'];
    $ratecode = $select['ratecode'];
    $diarias = $select['diarias'];
    $valor_diaria = $select['valor_diaria'];
    $valor_total_diaria = $select['valor_total_diaria'];
    $valor_comissao = $select['valor_comissao'];
    $rps = $select['rps'];
    $reserva_rps = $select['reserva_rps'];
    $comentarios = $select['comentarios'];
    $valor_rps = $select['valor_rps'];
    $valor_fatura = $select['valor_fatura'];
    $sistema_entrada = $select['sistema_entrada'];
    $tipo_empresa_agencia = $select['tipo_empresa_agencia'];
    $base_periodo = $select['base_periodo'];

    $linha_excel++;

    $checkin = date('d/m/Y', strtotime("$checkin"));
    $checkout = date('d/m/Y', strtotime("$checkout"));

    $activeWorksheet->setCellValue('A'.$linha_excel, $rid);
    $activeWorksheet->setCellValue('B'.$linha_excel, $hotel);
    $activeWorksheet->setCellValue('C'.$linha_excel, $cnpj);
    $activeWorksheet->setCellValue('D'.$linha_excel, $iata);
    $activeWorksheet->setCellValue('E'.$linha_excel, $agencia);
    $activeWorksheet->setCellValue('F'.$linha_excel, $reserva);
    $activeWorksheet->setCellValue('G'.$linha_excel, $reserva_num);
    $activeWorksheet->setCellValue('H'.$linha_excel, $confirmacao);
    $activeWorksheet->setCellValue('I'.$linha_excel, $pnr);
    $activeWorksheet->setCellValue('J'.$linha_excel, $hospede);
    $activeWorksheet->setCellValue('K'.$linha_excel, $checkin);
    $activeWorksheet->setCellValue('L'.$linha_excel, $checkout);
    $activeWorksheet->setCellValue('M'.$linha_excel, $ratecode);
    $activeWorksheet->setCellValue('N'.$linha_excel, $diarias);
    $activeWorksheet->setCellValue('O'.$linha_excel, $valor_diaria);
    $activeWorksheet->setCellValue('P'.$linha_excel, $valor_total_diaria);
    $activeWorksheet->setCellValue('Q'.$linha_excel, $valor_comissao);
    $activeWorksheet->setCellValue('R'.$linha_excel, $rps);
    $activeWorksheet->setCellValue('S'.$linha_excel, $reserva_rps);
    $activeWorksheet->setCellValue('T'.$linha_excel, $comentarios);
    $activeWorksheet->setCellValue('U'.$linha_excel, $valor_rps);
    $activeWorksheet->setCellValue('V'.$linha_excel, $valor_fatura);
    $activeWorksheet->setCellValue('W'.$linha_excel, $sistema_entrada);
    $activeWorksheet->setCellValue('X'.$linha_excel, $tipo_empresa_agencia);
    $activeWorksheet->setCellValue('Y'.$linha_excel, $base_periodo);

}

// Add borders to the entire data range
$lastColumn = $activeWorksheet->getHighestColumn();
$lastRow = $activeWorksheet->getHighestRow();
$range = "A1:$lastColumn$lastRow";
$styleArray = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => 'thin',
            'color' => ['rgb' => '000000'],
        ],
    ],
];
$activeWorksheet->getStyle($range)->applyFromArray($styleArray);

$activeWorksheet->getStyle('O')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('P')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('Q')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('U')->getNumberFormat()->setFormatCode('R$ #,##0.00');
$activeWorksheet->getStyle('V')->getNumberFormat()->setFormatCode('R$ #,##0.00');

$styleArray_down = [
    'font' => [
        'bold' => false,
        'size' => 8,
        'name' => 'Calibri',
    ],
];
$spreadsheet->getActiveSheet()->getStyle('A2:Y'.$linha_excel)->applyFromArray($styleArray_down);

$worksheet = $spreadsheet->getActiveSheet();
$worksheet->setTitle('Comissão - '.ucfirst($rid));


$styleArray_footer = [
    'font' => [
        'bold' => true,
        'size' => 8,
        'name' => 'Calibri',
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'A0A0A0', // Solid blue color (you can use the desired RGB color)
        ],
    ],
];
$spreadsheet->getActiveSheet()->getStyle('A'.($linha_excel + 1).':Y'.($linha_excel + 1))->applyFromArray($styleArray_footer);

// Create a temporary file for download
$filename = 'Comissões - '.ucfirst($rid).' '.$hotel.'.xls';
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

echo "<script>window.location.replace('index.php')</script>";
    exit();

}else {
    echo "Favor selecionar todos os aquivos.";
}

?>