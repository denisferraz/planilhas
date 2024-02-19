<?php

session_start();

require('../../conexao.php');
require('../../verifica_login.php');

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

$id_job = mysqli_real_escape_string($conn_mysqli, $_POST['id_job']);
if (isset($_POST['id_acao'])) {
$id_acao = mysqli_real_escape_string($conn_mysqli, $_POST['id_acao']);
}

$data_plano = date('d/m/Y', $_SESSION['id']);

if ($id_job == 'imprimir_plano') {
    require('../../fpdf/fpdf.php');

    $dados_roomstatus_pre = $_SESSION['dados_roomstatus'];
    $checkbox_camareiras = $_POST['checkbox_camareiras'];

    foreach ($checkbox_camareiras as $camareira) {

        $dados_roomstatus = array_filter($dados_roomstatus_pre, function($item) use ($camareira) {
            return $item['id_camareira'] == $camareira;
        });

        // Crie um arquivo PDF para cada página
        $pdf = new FPDF('L');
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);

        // Título do PDF
        $pdf->MultiCell(0, 7, "COVID 19 - ACAO TOMADAS DE FORMA INDIVIDUAL EM CADA APARTAMENTO/ PROCESSO HIGIENIZACAO(LIMPEZA E DESINFECCAO) - PEROXIDO POR 03 MIN E ACAO MECANICA(MANUAL)", 1, 'C');

        // Inicio
        $pdf->Cell(50, 7, 'Data: '.$data_plano, 1, 0, 'C');
        $pdf->Cell(105, 7, $_SESSION['hotel_name'], 1, 0, 'C');
        $pdf->Cell(80, 7, 'Arrumador(a): '.$_SESSION['camareira_'.$camareira], 1, 0, 'C');
        $pdf->Cell(42, 7, 'Chave:', 1, 'L');
        $pdf->Ln();
        $pdf->Cell(61, 7, 'Total: '.count($dados_roomstatus), 1, 0, 'C');
        $pdf->Cell(164, 7, 'Quarto', 1, 0, 'C');
        $pdf->Cell(52, 7, 'Banheiro', 1, 0, 'C');
        $pdf->Ln();
        
        $pdf->SetFont('Arial', 'B', 10);

        // Cabeçalho da tabela
        $pdf->Cell(10, 7, 'Qtd', 1, 0, 'C');
        $pdf->Cell(13, 7, 'Uh', 1, 0, 'C');
        $pdf->Cell(28, 7, 'Status PMS', 1, 0, 'C');
        $pdf->Cell(20, 7, 'Hora', 1, 0, 'C');
        $pdf->Cell(20, 7, 'Status', 1, 0, 'C');
        $pdf->Cell(15, 7, 'Lencol', 1, 0, 'C');
        $pdf->Cell(15, 7, 'Fronha', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Ln();

        $pdf->SetFont('Arial', '', 8);

        $qtd = 0;
        foreach ($dados_roomstatus as $select) {
            $room_number = $select['room_number'];
            $room_stay_status = $select['room_stay_status'];
            $room_status_1 = $select['room_status_1'];
            $room_status_2 = $select['room_status_2'];

            if($room_stay_status == 'Prevista'){
                $stay_status = 'Ocupado/Prevista';
            }else if($room_status_1 == 'Bloqueado'){
                $stay_status = 'Bloqueado';
            }else{
                $stay_status = $room_status_1.'/'.$room_status_2;
            }

        $qtd++;
        // Conteúdo da tabela (você precisará inserir os dados da tabela manualmente aqui)
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(10, 7, $qtd, 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(13, 7, $room_number, 1, 0, 'C');
        $pdf->Cell(28, 7, $stay_status, 1, 0, 'L');
        $pdf->Cell(20, 7, '', 1, 0, 'C');
        $pdf->Cell(20, 7, '', 1, 0, 'C');
        $pdf->Cell(15, 7, '', 1, 0, 'C');
        $pdf->Cell(15, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Ln();
        }

        for($branco = $qtd + 1; $branco < 10; $branco++){
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(10, 7, $branco, 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(28, 7, '', 1, 0, 'L');
        $pdf->Cell(20, 7, '', 1, 0, 'C');
        $pdf->Cell(20, 7, '', 1, 0, 'C');
        $pdf->Cell(15, 7, '', 1, 0, 'C');
        $pdf->Cell(15, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Cell(13, 7, '', 1, 0, 'C');
        $pdf->Ln();
        }

        $pdf->Ln();
        //Footer
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(15, 5, 'Status', 1, 0, 'C');
        $pdf->Cell(262, 5, 'Descricao', 1, 0, 'C');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(15, 5, 'SL', 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(262, 5, '(Saida Limpa) - Limpeza de um quarto sujo e sem bagagem', 1, 0, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(15, 5, 'SS', 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(262, 5, '(Saida Suja) Quarto nao limpo no dia em funcao da falta de tempo, que amanheceu ocupado por um hospede que ja deu check-out', 1, 0, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(15, 5, 'OL', 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(262, 5, '(Ocupado Limpo) Limpeza de um quarto cujo cliente continua hospedado no hotel ou apartamento VL que estiver fiscamente ocupado, mesmo que nao realizar limpeza. Anotar informacao na OBS', 1, 0, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(15, 5, 'BM', 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(262, 5, '(Bloqueado Manutencao) Quarto em manutencao (ex.: troca do carpete, pintura da parede,...), sem venda respectiva na Recepcao , e sem limpeza governanca. So e verificado fisicamente e anotado no plano', 1, 0, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(15, 5, 'BL', 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(262, 5, '(Bloqueado Limpo) Quarto limpo devido manutencao', 1, 0, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(15, 5, 'VL', 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(262, 5, '(Vago Limpo) Quarto nao feito limpeza no dia. A limpeza ja foi efetuada em dias anteriores. Quando a arrumadeira entra no apartamento, o mesmo esta pronto para venda, sem utilizacao', 1, 0, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(15, 5, 'Geral', 1, 0, 'C');
        $pdf->Cell(262, 5, 'Descricao', 1, 0, 'C');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(15, 5, 'NP', 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(262, 5, '(Nao Perturbe) Quarto nao limpo por solicitacao de hospede que continua hospedado no hotel, que deixa placa para nao ser perturbado.', 1, 0, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(15, 5, 'DF', 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(262, 5, 'DORMIU FORA', 1, 0, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(15, 5, 'LC', 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(262, 5, '(Lencol de Casal)', 1, 0, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(15, 5, 'LS', 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(262, 5, '(Lencol de Solteiro)', 1, 0, 'L');
        $pdf->Ln();
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(15, 5, 'TP', 1, 0, 'C');
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(262, 5, '(Toalha Pendurada) Assinalar caso a toalha esteja pendurada', 1, 0, 'L');

        // Abra o diálogo de impressão automaticamente
        $pdfFileName = "pagina_$camareira.pdf";
        $pdf->Output($pdfFileName, 'F');

        // Use JavaScript para abrir a página PDF automaticamente em uma nova janela/aba
        echo "<script>window.open('$pdfFileName', '_blank');</script>";
    }

    echo   "<script>
    top.location.replace('plano.php')
        </script>";
        exit();
}


?>
