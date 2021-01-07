<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("../dados_conexao.php");

if (isset($_FILES['acao'])) {
    $acao = $_FILES['acao'];
} else {
    $acao = filter_var($_POST['acao'], FILTER_SANITIZE_STRING);
}

switch ($acao) {

    case 'consultaCampanhas':
        $dados = $_POST['dados'];
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);


        $sql = "SELECT c.*, l.nome AS nome_lista, a.nome As nome_audio, a.id_audio AS id_audio_db FROM tbl_mf_listas l, tbl_velip_campanhas c LEFT JOIN tbl_audios a ON c.id_audio = a.id_fornecedor WHERE c.id_lista = l.id_velip AND c.id_empresa = $idEmpresa ORDER BY c.id_campanha DESC";
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayCampanhaBd = [];
        while ($campanha = mysqli_fetch_array($query)) {

            $retornoVelip = statusCampanha($campanha['id_campanha_velip'], '');

            if ($retornoVelip != 0) {
                $cp_active = $retornoVelip['campaing']['cp_active']; //0 para inativa e 1 para ativa
                $cp_ontime = $retornoVelip['campaing']['cp_ontime']; //0 para fora do horário programado e 1 para dentro do horário programado.
                $cp_lig_min = $retornoVelip['campaing']['cp_lig_min']; //número de ligações por minuto programado
                $cp_pas = $retornoVelip['campaing']['cp_pas']; //número de PAs programadas para atendimento para tipo com transferência.
                $cp_made = $retornoVelip['campaing']['cp_made']; //ligações realizados
                $cp_answered = $retornoVelip['campaing']['cp_answered']; //ligações atendidas
                $cp_transfered = $retornoVelip['campaing']['cp_transfered']; //ligações transferidas para call center ( campanhas de atendente virtual)
                $cp_destinations = $retornoVelip['campaing']['cp_destinations']; //quantidade de destinos programados

                array_push($arrayCampanhaBd, array(
                    'id_campanha' => $campanha['id_campanha'],
                    'id_campanha_fornecedor' => $campanha['id_campanha_velip'],
                    'nome' => $campanha['nome'],
                    'id_empresa' => $campanha['id_empresa'],
                    'id_lista' => $campanha['id_lista'],
                    'id_audio' => $campanha['id_audio'],
                    'inicio' => $campanha['inicio'],
                    'fim' => $campanha['fim'],
                    'status' => $campanha['status'],
                    'create_at' => $campanha['create_at'],
                    'nome_lista' => $campanha['nome_lista'],
                    'nome_audio' => $campanha['nome_audio'],
                    'id_audio_db' => $campanha['id_audio_db'],
                    'cp_active' => $cp_active,
                    'cp_ontime' => $cp_ontime,
                    'cp_lig_min' => $cp_lig_min,
                    'cp_pas' => $cp_pas,
                    'cp_made' => $cp_made,
                    'cp_answered' => $cp_answered,
                    'cp_transfered' => $cp_transfered,
                    'cp_destinations' => $cp_destinations

                ));
            }
        }

        echo json_encode($arrayCampanhaBd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'consultaTotalLista':
        $dados = $_POST['dados'];
        $idLista = filter_var($dados['idLista'], FILTER_SANITIZE_STRING);

        $messageflow = curl_init();
        $url = "http://api.messageflow.com.br/api/v2/list/" . $idLista . "/";
        curl_setopt($messageflow, CURLOPT_URL, $url);
        curl_setopt($messageflow, CURLOPT_RETURNTRANSFER, 1);

        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = "Authorization: Token c3adcffb-122f-4a71-9fae-b872bb0a6b67";
        $headers[] = 'Content-Type: application/json';
        curl_setopt($messageflow, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($messageflow);

        $result = json_decode($result, true);

        curl_close($messageflow);

        echo $result['total_contacts'];

        /* $arrayTotalContatos = [];
        array_push($arrayTotalContatos, array(
            'total' => $result['total_contacts']
        )); */

        //echo json_encode($arrayTotalContatos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'consultaContatos':
        $dados = $_POST['dados'];
        $idMessageFlow = filter_var($dados['idMessageFlow'], FILTER_SANITIZE_STRING);


        $messageflow = curl_init();
        $url = "http://api.messageflow.com.br/api/v2/campaign/" . $idMessageFlow . "/status/detail/";
        curl_setopt($messageflow, CURLOPT_URL, $url);
        curl_setopt($messageflow, CURLOPT_RETURNTRANSFER, 1);

        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = "Authorization: Token c3adcffb-122f-4a71-9fae-b872bb0a6b67";
        $headers[] = 'Content-Type: application/json';
        curl_setopt($messageflow, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($messageflow);

        curl_close($messageflow);

        echo $result;

        break;

    case 'consultaContas':
        $dados = $_POST['dados'];
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);

        $sql = "SELECT * FROM tbl_contas WHERE id_empresa = $idEmpresa";
        $query = mysqli_query($conn['link'], $sql);

        $arrayContas = [];
        while ($conta = mysqli_fetch_array($query)) {
            array_push($arrayContas, array(
                'idConta' => $conta['id_conta'],
                'nome' => $conta['nome']
            ));
        }

        echo json_encode($arrayContas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'salvarCampanha':

        $idEmpresa = filter_var($_POST['idEmpresa'], FILTER_SANITIZE_STRING);
        $idUsuario = filter_var($_POST['idUsuario'], FILTER_SANITIZE_STRING);
        $nomeCampanha = filter_var($_POST['nomeCampanha'], FILTER_SANITIZE_STRING);
        $lista = $_POST['lista'];
        $jsonLista = str_replace("'", '"', $lista);
        $arrayLista = json_decode($jsonLista, true);
        $id_lista = $arrayLista['id'];
        $num_lista = $arrayLista['num'];
        $audio = filter_var($_POST['audio'], FILTER_SANITIZE_STRING);
        $inicio = filter_var($_POST['inicio'], FILTER_SANITIZE_STRING);
        $fim = filter_var($_POST['fim'], FILTER_SANITIZE_STRING);


        $sql = "SELECT c.status, SUM(c.valor) AS creditos FROM tbl_creditos c, tbl_empresas e WHERE c.id_empresa = e.id_empresa AND c.id_empresa = $idEmpresa GROUP BY c.status ORDER BY SUM(c.valor) ASC";
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $saldoReal = 0;
        $saldoPendente = 0;
        while ($saldo = mysqli_fetch_array($query)) {
            if ($saldo['status'] == 1) {
                $saldoReal = $saldo['creditos'];
            }
            if ($saldo['status'] == 2) {
                $saldoPendente = $saldo['creditos'];
            }
        }
        $saldoDisponivel = $saldoReal + $saldoPendente;




        //* Consulta no BD para identificar qual é o custo de cada crédito
        $sql = "SELECT * FROM tbl_conf_creditos";
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        while ($custo = mysqli_fetch_array($query)) {
            if ($custo['tipo'] == 'voz_simples') {
                $voz_simples = $custo['valor'];
            }
        }
        $totalCreditos = $num_lista * $voz_simples; //coloquei aqui para que só seja preciso ter créditos


        if ($totalCreditos > $saldoDisponivel) {

            $arrayResult = [];
            array_push($arrayResult, array(
                'result' => 2,
                'saldoDisponivel' => $saldoDisponivel,
                'saldoReal' => $saldoReal,
                'saldoPendente' => $saldoPendente,
                'totalContatos' => $totalCreditos
            ));

            echo json_encode($arrayResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
        } else {
            $dataInicio = substr($inicio, 0, 10);
            $horaInicio = substr($inicio, -5) . ':00';
            $dataFim = substr($fim, 0, 10);
            $horaFim = substr($fim, -5) . ':00';

            salvarCampanha($idEmpresa, $idUsuario, $nomeCampanha, $id_lista, $num_lista, $totalCreditos, $audio, $dataInicio, $horaInicio, $dataFim, $horaFim);
        }

        break;

        //* Alterar status da Campanha
    case 'alterarStatusCampanha':
        $dados = $_POST['dados'];
        $idCampanha = filter_var($dados['idCampanha'], FILTER_SANITIZE_STRING);
        $idCampanhaFornecedor = filter_var($dados['idCampanhaFornecedor'], FILTER_SANITIZE_STRING);
        $acaoStatus = filter_var($dados['acaoStatus'], FILTER_SANITIZE_STRING);

        if ($acaoStatus == 'ativar') {
            $status = 1;
        } else {
            $status = 0;
        }

        $retornoVelip = statusCampanha($idCampanhaFornecedor, $status);

        if ($retornoVelip['campaing']['cp_name'] != '') {
            $sql = "UPDATE tbl_velip_campanhas SET status = $status WHERE id_campanha = '$idCampanha'";

            $query = mysqli_query($conn['link'], $sql);
            $linhasAfetadas = mysqli_affected_rows($conn['link']);

            if ($query != true && $linhasAfetadas == 0) {
                echo 0;
                logSis('ERR', 'Alterou status no Fornecedor mas não alterou no BD . Resultado: ' . $idCampanha);
            } else {
                echo 1;
            }
        } else {
            echo 0;
            logSis('ERR', 'Não conseguiu alterar o status no fornecedor. Resultado: ' . $idCampanha);
        }

        break;
}


function salvarCampanha($idEmpresa, $idUsuario, $nomeCampanha, $lista, $num_lista, $totalCreditos, $audio, $dataInicio, $horaInicio, $dataFim, $horaFim)
{
    include("../dados_conexao.php");

    //( Salvando a campanha na VELIP 
    $target_url = "https://vox.velip.com.br/pop/torpedo/CreateCampaign.php";
    $post = array("user" => "resolv", "password" => "velocidade", 'name' => $nomeCampanha, 'cdlc_id' => $lista, 'content' => $audio, 'date_start' => $dataInicio, 'time_start' => $horaInicio, 'date_end' => $dataFim, 'time_end' => $horaFim);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $target_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 100);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $resultVelip = curl_exec($ch);


    if ($resultVelip === FALSE) {
        curl_close($ch);
        logSis('ERR', 'Não conseguiu salvar a campanha na VELIP. Resultado: ' . $resultVelip);
    } else {
        curl_close($ch);

        $xml = simplexml_load_string($resultVelip);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);

        $status = $array['status'];

        if ($status != 'OK') {
            //print_r($array);
            $cod_erro = $array['cod_erro'];
            $erro = $array['erro'];
            logSis('ERR', 'Não conseguiu salvar a lista na VELIP. Erro: ' . $cod_erro . ' - ' . $erro);

            $arrayResult = [];
            array_push($arrayResult, array(
                'result' => 0,
                'mensagem' => 'Não foi possível gerar a campanha.',
                'resultadoAPI' => $erro
            ));
            echo json_encode($arrayResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
        } else {

            $idVelip = $array['cp_id'];

            if ($idVelip != 0) {
                $inicio = $dataInicio . ' ' . $horaInicio;
                $fim = $dataFim . ' ' . $horaFim;
                $sql = "INSERT INTO tbl_velip_campanhas(id_campanha_velip, nome, id_empresa, id_lista, id_audio, inicio, fim, status, create_at) VALUES ($idVelip, '$nomeCampanha', $idEmpresa, '$lista', '$audio', '$inicio', '$fim', 1, NOW())";

                //@ Separar o salvamento de arquivo em uma função e o salvamento em banco de dados em outra função.
                $query = mysqli_query($conn['link'], $sql);
                if ($query == true) {

                    $arrayResult = [];
                    array_push($arrayResult, array(
                        'result' => 1
                    ));
                    echo json_encode($arrayResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
                    salvarCreditos($idEmpresa, 2, $idVelip, $totalCreditos);
                } else {
                    file_put_contents('log/log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Conseguiu gerar a campanha mas não salvar ' . $jsonResult . PHP_EOL, FILE_APPEND);

                    $arrayResult = [];
                    array_push($arrayResult, array(
                        'result' => 0,
                        'mensagem' => 'Conseguiu gerar a campanha mas não salvar.'
                    ));
                    echo json_encode($arrayResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
                    //echo $sql;
                }
            } else {

                file_put_contents('log/log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Não Conseguiu gerar a campanha ' . $jsonResult . PHP_EOL, FILE_APPEND);
                //var_dump($result);

                $arrayResult = [];
                array_push($arrayResult, array(
                    'result' => 0,
                    'mensagem' => 'Não Conseguiu gerar a campanha.',
                    'resultadoAPI' => $result
                ));
                echo json_encode($arrayResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
            }
        }
    }
}

//* Consulta e edição de status da campanha junto ao Fornecedor 
function statusCampanha($idCampanha, $status)
{

    $target_url = "https://vox.velip.com.br/pop/torpedo/ChangeCampaign.php";
    $post = array("user" => "resolv", "password" => "velocidade", 'cp_id' => $idCampanha);

    if ($status == 0 || $status == 1) {
        $post += array('cp_active' => $status);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $target_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: multipart/form-data'));
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
    curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 100);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $resultVelip = curl_exec($ch);

    if ($resultVelip === FALSE) {
        curl_close($ch);
        logSis('ERR', 'Não conseguiu consultar a campanha . Resultado: ' . $idCampanha);

        return 0;
    } else {
        curl_close($ch);

        $xml = simplexml_load_string($resultVelip);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);

        return $array;
    }
}

function salvarCreditos($idEmpresa, $tipo, $id, $totalCreditos)
{
    include("../dados_conexao.php");

    $contatos = $totalCreditos;
    $contatos = -abs($contatos);

    $sql = "INSERT INTO tbl_creditos(tipo, id_empresa, referencia, valor, id_usuario, status, create_at) VALUES ($tipo, $idEmpresa, $id, $contatos, 0, 2, NOW())";

    //@ Separar o salvamento de arquivo em uma função e o salvamento em banco de dados em outra função.
    $query = mysqli_query($conn['link'], $sql);
    if ($query == true) {
        file_put_contents('log/log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Salvou o crédito ' . $id . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents('log/log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Não salvou o crédito ' . $id . PHP_EOL, FILE_APPEND);
    }
}


//* Função de LOG
function logSis($tipo, $texto)
{
    file_put_contents('log.txt', "> " . $tipo . " " . date('d/m/Y h:i:s') . $texto . PHP_EOL, FILE_APPEND);
}
