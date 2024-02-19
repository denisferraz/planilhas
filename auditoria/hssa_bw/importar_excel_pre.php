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

use PhpOffice\PhpSpreadsheet\IOFactory;

$data_auditoria = mysqli_real_escape_string($conn_mysqli, $_POST['data_auditoria']);
$_SESSION['data_auditoria'] = $data_auditoria;

$query_status = $conexao->prepare("SELECT * FROM $dir"."_excel_auditoria_auditorias WHERE id > 0 AND data_auditoria = '{$data_auditoria}'");
$query_status->execute();
while($select_status = $query_status->fetch(PDO::FETCH_ASSOC)){
    $status_auditoria = $select_status['auditoria_status'];
}

if($status_auditoria != 'Pendente'){
    echo "<script>
    window.location.replace('auditoria.php')
    </script>";
    exit();
}

$arquivo_inhouse = 0;
$arquivo_caixa = 0;
$dados_caixa = [];

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
error_reporting(0);

// Check if the CSV files were uploaded successfully
if (!empty($_FILES["xlsFile"]["name"]) && (count($_FILES["xlsFile"]["name"]) == 2 || count($_FILES["xlsFile"]["name"]) == 1)) {
    $file_names = $_FILES["xlsFile"]["name"];
    $file_tmp_names = $_FILES["xlsFile"]["tmp_name"];

    $chave = $_SESSION['hotel'].$chave;

    for ($i = 0; $i < count($file_names); $i++) {
        $file_name = $file_names[$i];
        $tmp_name = $file_tmp_names[$i];

        // Check if the uploaded file is a CSV or XML
        if (strtolower(pathinfo($file_name, PATHINFO_EXTENSION)) == "xls") {
            // Read the CSV file
            $file_handle = fopen($tmp_name, "r");
            if ($file_handle !== FALSE) {

                if (strpos($file_name, "inhouse") !== false) {
                    $arquivo_inhouse = 1;
                }else if (strpos($file_name, "caixa") !== false) {
                    $arquivo_caixa = 1;
                }else{
                    echo "<script>
                    alert('Arquivo Selecionado Invalido')
                    window.location.replace('index.php')
                    </script>";
                    exit();
                }
            
            //Importar List of In House
            if (strpos($file_name, "inhouse") !== false) {

                $dados_presentlist = [];

                $id = 0;
    
                    // Carrega o arquivo Excel
                    $spreadsheet = IOFactory::load($tmp_name);

                    // Seleciona a primeira planilha (índice 0)
                    $worksheet = $spreadsheet->getActiveSheet();

                    // Obtém todas as células da planilha como uma matriz
                    $datas = $worksheet->toArray();

                    foreach ($datas as $data) {

                    if (!is_numeric($data[6])) {
                        continue;
                    }

                    if($data[0] == '*'){
                        $colunaA = $data[1];
                    }else{
                        $colunaA = $data[0];
                    }
                    $colunaB = str_replace('/', '-', $data[15]);
                    $colunaC = str_replace('/', '-', $data[16]);
                    $colunaE = $data[4];
                    $colunaF = str_replace(',', '', $data[14]);
                    $colunaG = $data[6];

                    $colunaB_partes = explode("-", $colunaB);
                    if($colunaB_partes[1] >= 13){
                    $colunaB_formatada = $colunaB_partes[2] . "-" . $colunaB_partes[0] . "-" . $colunaB_partes[1];
                    }else{
                    $colunaB_formatada = $colunaB_partes[2] . "-" . $colunaB_partes[1] . "-" . $colunaB_partes[0];  
                    }
                    $colunaC_partes = explode("-", $colunaC);
                    if($colunaC_partes[1] >= 13){
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[0] . "-" . $colunaC_partes[1];
                    }else{
                    $colunaC_formatada = $colunaC_partes[2] . "-" . $colunaC_partes[1] . "-" . $colunaC_partes[0]; 
                    }
    
                    $id++;
                    // Adicione os dados a um array associativo
                    $dados_presentlist[] = [
                        'id' => $id,
                        'reserva' => $colunaE,
                        'guest_name' => $colunaA,
                        'checkin' => $colunaB_formatada,
                        'checkout' => $colunaC_formatada,
                        'room_rate' => $colunaF,
                        'room_number' => $colunaG,
                        'comentario_checkins' => '',
                        'comentario_freestay' => '',
                        'auditoria_garantia' => '',
                        'auditoria_diarias' => ''
                    ];

                }

                // Ordenar o array por 'room_number'
                usort($dados_presentlist, function($a, $b) {
                    return $a['room_number'] <=> $b['room_number'];
                });

                // Deleta duplicidades
                $indicesParaExcluir = [];

                // Itere sobre os dados_presentlist para encontrar índices de linhas a serem excluídas
                for ($k = 0; $k < count($dados_presentlist); $k++) {
                    for ($j = $k + 1; $j < count($dados_presentlist); $j++) {
                        if (
                            $dados_presentlist[$k]['room_number'] === $dados_presentlist[$j]['room_number']
                        ) {
                            // Adicione o índice da linha a ser excluída ao array
                            $indicesParaExcluir[] = $j;
                            $dados_presentlist[$k]['room_rate'] += $dados_presentlist[$j]['room_rate'];
                        }
                    }
                }

                // Remova as linhas duplicadas com base nos índices coletados
                foreach ($indicesParaExcluir as $indice) {
                    unset($dados_presentlist[$indice]);
                }
                
                $dados_presentlist = array_values($dados_presentlist);
                fclose($file_handle);

            }else

            //Importar Recebimntos e Pagamentos
            if (strpos($file_name, "caixa") !== false) {

                $id = 0;
    
                    // Carrega o arquivo Excel
                    $spreadsheet = IOFactory::load($tmp_name);

                    // Seleciona a primeira planilha (índice 0)
                    $worksheet = $spreadsheet->getActiveSheet();

                    // Obtém todas as células da planilha como uma matriz
                    $datas = $worksheet->toArray();

                    foreach ($datas as $data) {

                    if (!is_numeric($data[16])) {
                        continue;
                    }

                    if (substr($data[19], 0, 8) === 'Redecard' && $data[19] != 'Redecard - Cabal Débito') {
                        $colunaC = 'Pgto Direto - Cartão';
                    }else if ($data[19] == 'A Faturar') {
                        $colunaC = 'Faturado';
                    }else if ($data[19] == 'Dinheiro') {
                        $colunaC = 'Pgto Direto - Cash';
                    }else if ($data[19] == 'PIX - Redecard') {
                        $colunaC = 'PIX';
                    }else if ($data[19] == 'PIX - Redecard') {
                        $colunaC = 'PIX';
                    }else if ($data[19] == 'Redecard - Cabal Débito' || $data[19] == 'Depósito Antecipado') {
                        $colunaC = 'Transferencia Bancaria';
                    }else{
                        $colunaC = 'Sem Garantia';
                    }

                    if ($data[19] == 'Redecard - Cabal Débito' || $data[19] == 'Depósito Antecipado') {
                        $colunaD = 'Deposito';
                    }else{
                        $colunaD = $colunaD = $data[19];
                    }

                    $colunaA = $data[0];
                    $colunaB = str_replace('/', '-', $data[22]);
                    $colunaE = $data[6];
                    $colunaF = str_replace(',', '', $data[11]) * (-1);
                    $colunaG = $data[4];
                    $colunaH = preg_replace('/[^0-9]/', '', $data[17]);

                    $colunaB_partes = explode("-", $colunaB);
                    if($colunaB_partes[1] >= 13){
                    $colunaB_formatada = $colunaB_partes[2] . "-" . $colunaB_partes[0] . "-" . $colunaB_partes[1];
                    }else{
                    $colunaB_formatada = $colunaB_partes[2] . "-" . $colunaB_partes[1] . "-" . $colunaB_partes[0];  
                    }
    
                    $id++;
                    // Adicione os dados a um array associativo
                    $dados_caixa[] = [
                        'id' => $id,
                        'reserva' => $colunaE,
                        'guest_name' => $colunaA,
                        'data_lancamento' => $colunaB_formatada,
                        'pgto_forma' => $colunaD,
                        'pgto_valor' => $colunaF,
                        'room_number' => $colunaG,
                        'documento' => $colunaH,
                        'auditoria_forma' => $colunaC,
                        'auditoria_conferido' => 'Não'
                    ];
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
    window.location.replace('index.php')
    </script>";
    exit();
}

$arquivos_selecionados = $arquivo_inhouse + $arquivo_caixa;

$dados_caixa = array_values($dados_caixa);

if($arquivos_selecionados == 2 || $arquivos_selecionados == 1){

    foreach ($dados_presentlist as &$item) {

        foreach ($dados_caixa as $select) {
            $reserva = $select['reserva'];
            $auditoria_forma = $select['auditoria_forma'];
            $pgto_valor = $select['pgto_valor'];

            if ($item['reserva'] == $reserva) {
                $item['auditoria_garantia'] = $auditoria_forma;
                $item['auditoria_diarias'] = $pgto_valor;
                break;
            }

        }
    }

 //Insere Inhouse no Database
 $sql = "INSERT INTO $dir"."_excel_auditoria (data_auditoria, dados_auditoria) VALUES (?, ?)";
 $stmt = $conn_mysqli->prepare($sql);
 $stmt->bind_param("ss", $data_auditoria, $dados_final);
 foreach($dados_presentlist as $select) {
     $id = $select['id'];
     $reserva = $select['reserva'];
     $room_number = $select['room_number'];
     $guest_name = $select['guest_name'];
     $checkin = $select['checkin'];
     $checkout = $select['checkout'];
     $room_rate = $select['room_rate'];
     $comentario_checkins = $select['comentario_checkins'];
     $comentario_freestay = $select['comentario_freestay'];
     $auditoria_diarias = $select['auditoria_diarias'];
     $auditoria_garantia = $select['auditoria_garantia'];

     $dados_auditoria = 'inhouse;'.$id.';'.$reserva.';'.$room_number.';'.$guest_name.';'.$checkin.';'.$checkout.';'.$room_rate.';'.$comentario_checkins.';'.$comentario_freestay.';'.$auditoria_diarias.';'.$auditoria_garantia;
     $dados_criptografados = openssl_encrypt($dados_auditoria, $metodo, $chave, 0, $iv);
     $dados_final = base64_encode($dados_criptografados);
     $stmt->execute();
 }
     $stmt->close();

//Insere Caixa no Database
$sql = "INSERT INTO $dir"."_excel_auditoria (data_auditoria, dados_auditoria) VALUES (?, ?)";
$stmt = $conn_mysqli->prepare($sql);
$stmt->bind_param("ss", $data_auditoria, $dados_final);
foreach($dados_caixa as $select) {
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

    $dados_auditoria = 'caixa;'.$id.';'.$reserva.';'.$guest_name.';'.$data_lancamento.';'.$pgto_forma.';'.$pgto_valor.';'.$room_number.';'.$documento.';'.$auditoria_forma.';'.$auditoria_conferido;
    $dados_criptografados = openssl_encrypt($dados_auditoria, $metodo, $chave, 0, $iv);
    $dados_final = base64_encode($dados_criptografados);
    $stmt->execute();
}
    $stmt->close();

$_SESSION['freestay'] = 0;
$_SESSION['Garantias'] = 0;
$_SESSION['caixa'] = 0;

$query = $conexao->prepare("UPDATE $dir"."_excel_auditoria_auditorias SET auditoria_status = 'Em Andamento Pre' WHERE data_auditoria = '{$data_auditoria}'");
$query->execute();

echo "<script>
    top.location.replace('auditoria.php')
    </script>";
    exit();

}else{

    echo "<script>
    alert('Selecione todos os Arquivos')
    window.location.replace('index.php')
    </script>";
    exit();

}

?>