<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');
require '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Color;

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

$limite_credito = 1500;

if (isset($_FILES["excelFile"]["tmp_name"]) && !empty($_FILES["excelFile"]["tmp_name"])) {
    $uploadedFile = $_FILES["excelFile"]["tmp_name"];
    $skip_first_line = true;

        $spreadsheet = IOFactory::load($uploadedFile);

        $palavraProcurada = "Assinatura Digital N + 1";
        $data_quando = date('d/m/Y - H:i:s\h');

        $conditionRed = new Conditional();
        $conditionRed->setConditionType(Conditional::CONDITION_CELLIS)
            ->setOperatorType(Conditional::OPERATOR_EQUAL)
            ->addCondition('X')
            ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_DARKRED);
        
        $conditionIssRetido = new Conditional();
        $conditionIssRetido->setConditionType(Conditional::CONDITION_CELLIS)
            ->setOperatorType(Conditional::OPERATOR_NOTEQUAL)
            ->addCondition('0')
            ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_MAGENTA);

        $conditionRedBalance = new Conditional();
        $conditionRedBalance->setConditionType(Conditional::CONDITION_CELLIS)
            ->setOperatorType(Conditional::OPERATOR_GREATERTHAN)
            ->addCondition($limite_credito)
            ->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_RED);

//Pegar Linha da Aba Gerencial
$spreadsheet->setActiveSheetIndexByName('Gerencial');
$worksheet = $spreadsheet->getActiveSheet();

$linhaEncontrada = null;

foreach ($worksheet->getRowIterator() as $row) {
    foreach ($row->getCellIterator() as $cell) {
        $conteudo = $cell->getValue();
        if ($conteudo !== null && stripos($conteudo, $palavraProcurada) !== false) {
            $linhaEncontrada = $row->getRowIndex();
            break 2;
        }
    }
}

if ($linhaEncontrada !== null) {

    $linha_excel = ($linhaEncontrada - 1);
    $worksheet->getCell('H' . $linha_excel)->setValue($_SESSION['name'].' | '.$data_quando);

    //Aplicar Condição
    $range = 'J20:N24';
    $conditionalStyles = $worksheet->getStyle($range)->getConditionalStyles();
    $conditionalStyles[] = $conditionRed;
    $worksheet->getStyle($range)->setConditionalStyles($conditionalStyles);

    $worksheet->setSelectedCell('A1');

}

//Pegar Linha da Aba Conferencia de Diárias
$spreadsheet->setActiveSheetIndexByName('Conferencia de Diárias');
$worksheet = $spreadsheet->getActiveSheet();
$linhaEncontrada = null;

foreach ($worksheet->getRowIterator() as $row) {
    foreach ($row->getCellIterator() as $cell) {
        $conteudo = $cell->getValue();
        if ($conteudo !== null && stripos($conteudo, $palavraProcurada) !== false) {
            $linhaEncontrada = $row->getRowIndex();
            break 2;
        }
    }
}

if ($linhaEncontrada !== null) {

    $linha_excel = ($linhaEncontrada - 1);
    $worksheet->getCell('G' . $linha_excel)->setValue($_SESSION['name'].' | '.$data_quando);

    $worksheet->setSelectedCell('A1');

}

//Pegar Linha da Aba Saldo Elevado
$spreadsheet->setActiveSheetIndexByName('Saldo Elevado');
$worksheet = $spreadsheet->getActiveSheet();
$linhaEncontrada = null;

foreach ($worksheet->getRowIterator() as $row) {
    foreach ($row->getCellIterator() as $cell) {
        $conteudo = $cell->getValue();
        if ($conteudo !== null && stripos($conteudo, $palavraProcurada) !== false) {
            $linhaEncontrada = $row->getRowIndex();
            break 2;
        }
    }
}

if ($linhaEncontrada !== null) {

    $linha_excel = ($linhaEncontrada - 1);
    $worksheet->getCell('G' . $linha_excel)->setValue($_SESSION['name'].' | '.$data_quando);

    $worksheet->setSelectedCell('A1');

}

//Pegar Linha da Aba Controle du Bac
$spreadsheet->setActiveSheetIndexByName('Controle du Bac');
$worksheet = $spreadsheet->getActiveSheet();
$linhaEncontrada = null;

foreach ($worksheet->getRowIterator() as $row) {
    foreach ($row->getCellIterator() as $cell) {
        $conteudo = $cell->getValue();
        if ($conteudo !== null && stripos($conteudo, $palavraProcurada) !== false) {
            $linhaEncontrada = $row->getRowIndex();
            break 2;
        }
    }
}

if ($linhaEncontrada !== null) {

    $linha_excel = ($linhaEncontrada - 1);
    $worksheet->getCell('M' . $linha_excel)->setValue($_SESSION['name'].' | '.$data_quando);

    //Condição
    $range = 'H11:H'.($linhaEncontrada - 11);
    $worksheet->getStyle($range)->setConditionalStyles([$conditionRedBalance]);

    $worksheet->setSelectedCell('A1');

}

//Pegar Linha da Aba Controle de Garantias
$spreadsheet->setActiveSheetIndexByName('Controle de Garantias');
$worksheet = $spreadsheet->getActiveSheet();
$linhaEncontrada = null;

foreach ($worksheet->getRowIterator() as $row) {
    foreach ($row->getCellIterator() as $cell) {
        $conteudo = $cell->getValue();
        if ($conteudo !== null && stripos($conteudo, $palavraProcurada) !== false) {
            $linhaEncontrada = $row->getRowIndex();
            break 2;
        }
    }
}

if ($linhaEncontrada !== null) {

    $linha_excel = ($linhaEncontrada - 1);
    $worksheet->getCell('G' . $linha_excel)->setValue($_SESSION['name'].' | '.$data_quando);

    $worksheet->setSelectedCell('A1');

}

//Pegar Linha da Aba No Show
$spreadsheet->setActiveSheetIndexByName('No Show');
$worksheet = $spreadsheet->getActiveSheet();
$linhaEncontrada = null;

foreach ($worksheet->getRowIterator() as $row) {
    foreach ($row->getCellIterator() as $cell) {
        $conteudo = $cell->getValue();
        if ($conteudo !== null && stripos($conteudo, $palavraProcurada) !== false) {
            $linhaEncontrada = $row->getRowIndex();
            break 2;
        }
    }
}

if ($linhaEncontrada !== null) {

    $linha_excel = ($linhaEncontrada - 1);
    $worksheet->getCell('G' . $linha_excel)->setValue($_SESSION['name'].' | '.$data_quando);

    $worksheet->setSelectedCell('A1');

}

//Pegar Linha da Aba Free Stay
$spreadsheet->setActiveSheetIndexByName('Free Stay');
$worksheet = $spreadsheet->getActiveSheet();
$linhaEncontrada = null;

foreach ($worksheet->getRowIterator() as $row) {
    foreach ($row->getCellIterator() as $cell) {
        $conteudo = $cell->getValue();
        if ($conteudo !== null && stripos($conteudo, $palavraProcurada) !== false) {
            $linhaEncontrada = $row->getRowIndex();
            break 2;
        }
    }
}

if ($linhaEncontrada !== null) {

    $linha_excel = ($linhaEncontrada - 1);
    $worksheet->getCell('G' . $linha_excel)->setValue($_SESSION['name'].' | '.$data_quando);

    $worksheet->setSelectedCell('A1');

}

//Pegar Linha da Aba Tax Base
$spreadsheet->setActiveSheetIndexByName('Tax Base');
$worksheet = $spreadsheet->getActiveSheet();
$linhaEncontrada = null;

foreach ($worksheet->getRowIterator() as $row) {
    foreach ($row->getCellIterator() as $cell) {
        $conteudo = $cell->getValue();
        if ($conteudo !== null && stripos($conteudo, $palavraProcurada) !== false) {
            $linhaEncontrada = $row->getRowIndex();
            break 2;
        }
    }
}

if ($linhaEncontrada !== null) {

    $linha_excel = ($linhaEncontrada - 1);
    $worksheet->getCell('K' . $linha_excel)->setValue($_SESSION['name'].' | '.$data_quando);

    //Aplicar Condição
    $range = 'O10:O'.($linhaEncontrada - 11);
    $worksheet->getStyle($range)->setConditionalStyles([$conditionIssRetido]);

    $worksheet->setSelectedCell('A1');

}

$spreadsheet->setActiveSheetIndexByName('Gerencial');

// Create a temporary file for download
$filename = 'Auditoria Digital - '.ucfirst($dir).' (Conferida).xls';
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

}else {
    echo "Favor selecionar todos os aquivos.";
}

?>