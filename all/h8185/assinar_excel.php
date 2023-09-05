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

$hotel = 'Novotel Salvador Hangar Aeroporto';
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

if (isset($_FILES["excelFile"]["tmp_name"]) && !empty($_FILES["excelFile"]["tmp_name"])) {
    $uploadedFile = $_FILES["excelFile"]["tmp_name"];
    $skip_first_line = true;

        $spreadsheet = IOFactory::load($uploadedFile);

        $palavraProcurada = "Assinatura Digital N + 1";
        $data_quando = date('d/m/Y - H:i:s');

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

//Pegar Linha da Pontuações Hotel Link
$spreadsheet->setActiveSheetIndexByName('Pontuações Hotel Link');
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

    $linha_excel = ($linhaEncontrada - 4);
    $worksheet->getCell('J' . $linha_excel)->setValue($_SESSION['name']);
    $worksheet->getCell('M' . $linha_excel)->setValue($data_quando);

    //Aplicar Condição
    $range = 'L7:L'.($linhaEncontrada - 8);
    $worksheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_GREEN);
    $worksheet->getStyle($range)->setConditionalStyles([$conditionRed]);

    $range = 'M7:M'.($linhaEncontrada - 8);
    $worksheet->getStyle($range)->setConditionalStyles([$conditionGrey]);

    $worksheet->setSelectedCell('A1');

}

//Pegar Linha da Pontuações Pendentes
$spreadsheet->setActiveSheetIndexByName('Pontuações Pendentes');
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

    $linha_excel = ($linhaEncontrada - 4);
    $worksheet->getCell('G' . $linha_excel)->setValue($_SESSION['name']);
    $worksheet->getCell('I' . $linha_excel)->setValue($data_quando);

    $worksheet->setSelectedCell('A1');

}

$spreadsheet->setActiveSheetIndexByName('Pontuações Hotel Link');

// Create a temporary file for download
$filename = 'Conciliação ALL - '.ucfirst($dir).' (Conferido).xls';
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