<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("../dados_conexao.php");

if (isset($_FILES['acao'])) {
    $acao = $_FILES['acao'];
} else {
    $acao = filter_var($_POST['acao'], FILTER_SANITIZE_STRING);
}
$APIurl = "v4.chatpro.com.br/chatpro-9piq49nyf9" . '/api/v1/';
$token = "69fa9a02548516e0e7507d0265b1caf2e3fde824";

switch ($acao) {

    case 'consultaMenuAtendimento':
        $dados = $_POST['dados'];
        $ultimaRecebida = filter_var(@$dados['ultimaRecebida'], FILTER_SANITIZE_STRING);
        if ($ultimaRecebida != "") {
            $whereUltimaRecebida = "AND i.data_envio > '$ultimaRecebida' ";
            $sql = "SELECT c.id_contato, c.nome, c.numero, c.email, c.bloqueio_bot, c.created_at AS contato_criado, count(i.id_contato) AS quant, max(i.data_envio) AS ultima_recebida FROM tbl_contatos c, tbl_interacoes i WHERE c.id_contato = i.id_contato AND i.direcao = 0 AND i.status_chat = 0 $whereUltimaRecebida GROUP BY c.id_contato";
        } else {
            $sql = "SELECT c.id_contato, c.nome, c.numero, c.email, c.bloqueio_bot, c.created_at AS contato_criado, (count(i.id_contato)-sum(status_chat)) AS quant, max(i.data_envio) AS ultima_recebida FROM tbl_contatos c LEFT JOIN tbl_interacoes i ON c.id_contato = i.id_contato AND i.direcao = 0 GROUP BY c.id_contato ORDER BY max(i.data_envio) DESC";
        }

        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayMensagens = [];
        while ($campanha = mysqli_fetch_array($query)) {

            array_push($arrayMensagens, array(
                'idContato' => $campanha['id_contato'],
                'nome' => $campanha['nome'],
                'numero' => $campanha['numero'],
                'email' => $campanha['email'],
                'bloqueio_bot' => $campanha['bloqueio_bot'],
                'contatoCriado' => $campanha['contato_criado'],
                'quant' => $campanha['quant'],
                'ultimaRecebida' => $campanha['ultima_recebida']

            ));
        }

        echo json_encode($arrayMensagens, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'consultaConversaAtendimento':
        $dados = $_POST['dados'];
        $idContato = filter_var($dados['idContato'], FILTER_SANITIZE_STRING);
        $ultimaRecebida = filter_var(@$dados['ultimaRecebida'], FILTER_SANITIZE_STRING);

        if (isset($ultimaRecebida)) {
            $whereUltimaRecebida = "AND data_envio > '$ultimaRecebida' ";
        }

        $sql = "SELECT id_interacao, direcao, tipo, subtipo, id_mensagem, mensagem, status, status_chat, data_envio, DATE_FORMAT(data_envio, '%d/%m %H:%i') AS data_envio_formatada FROM tbl_interacoes WHERE id_contato = $idContato $whereUltimaRecebida ORDER BY data_envio ASC";
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayMensagens = [];
        while ($campanha = mysqli_fetch_array($query)) {

            array_push($arrayMensagens, array(
                'idInteracao' => $campanha['id_interacao'],
                'direcao' => $campanha['direcao'],
                'tipo' => $campanha['tipo'],
                'subtipo' => $campanha['subtipo'],
                'idMensagem' => $campanha['id_mensagem'],
                'mensagem' => $campanha['mensagem'],
                'status' => $campanha['status'],
                'status_chat' => $campanha['status_chat'],
                'dataEnvioPadrao' => $campanha['data_envio'],
                'dataEnvio' => $campanha['data_envio_formatada']

            ));
        }

        echo json_encode($arrayMensagens, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'consultaUltimaRecebida':
        $dados = $_POST['dados'];
        $idContato = filter_var($dados['idContato'], FILTER_SANITIZE_STRING);


        if ($idContato != "") {
            $sql = "SELECT MAX(data_envio) AS ultimo_envio FROM tbl_interacoes WHERE direcao = 0 AND id_contato = $idContato";
        } else {
            $sql = "SELECT MAX(data_envio) AS ultimo_envio FROM tbl_interacoes WHERE direcao = 0";
        }
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayMensagens = [];
        while ($campanha = mysqli_fetch_array($query)) {

            array_push($arrayMensagens, array(
                'ultimo_envio' => $campanha['ultimo_envio']

            ));
        }

        echo json_encode($arrayMensagens, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'atualizarStatusChat':
        $dados = $_POST['dados'];
        $idContato = filter_var($dados['idContato'], FILTER_SANITIZE_STRING);

        $sql = "UPDATE tbl_interacoes SET status_chat = 1 WHERE id_contato = $idContato";

        $query = mysqli_query($conn['link'], $sql);

        if ($query == true) {
            echo 1;
        } else {
            //echo $sql;
            echo 0;
        }
        break;

    case 'bloqueioBot':
        $dados = $_POST['dados'];
        $idContato = filter_var($dados['idContato'], FILTER_SANITIZE_STRING);
        $bloqueio = filter_var($dados['bloqueio'], FILTER_SANITIZE_STRING);
        if ($bloqueio == 1) {
            $bloqueio = 0;
        } else {
            $bloqueio = 1;
        }

        $sql = "UPDATE tbl_contatos SET bloqueio_bot = $bloqueio WHERE id_contato = $idContato";

        $query = mysqli_query($conn['link'], $sql);

        if ($query == true) {
            echo 1;
        } else {
            //echo $sql;
            echo 0;
        }
        break;

    case 'envioMensagem':
        $dados = $_POST['dados'];
        $numero = filter_var($dados['numero'], FILTER_SANITIZE_STRING);
        $mensagem = filter_var($dados['mensagem'], FILTER_SANITIZE_STRING);
        logSis('REQ', 'Requisição de envio Número: ' . $numero . ' Mensagem: ' . $mensagem);

        $method = 'send_message';
        $data = array('number' => $numero . '@s.whatsapp.net', 'menssage' => $mensagem);

        include("../dados_conexao.php");

        $url = 'https://' . $APIurl . $method;
        if (is_array($data)) {
            $data = json_encode($data);
        }

        $options = stream_context_create(['https' => [
            'method'  => 'POST',
            'header'  => "Content-type: application/json\r\nAuthorization: $token\r\n",
            'content' => $data
        ]]);

        $response = file_get_contents($url, false, $options);

        logSis('REQ', 'Resp Requisição: ' . $response);

        //return $response;

        $resposta = json_decode($response, true);
        $statusEnvio = $resposta['message'];
        if ($statusEnvio == "Mensagem enviada com sucesso" || $statusEnvio == "Mensagem Enviada") {
            //( Identifica se é uma função receptiva, aqui retorna a resposta da requisição
            if ($motivo == 'Receptivo') {
                return true;
                exit(0);
            }
            $id_resposta = $resposta['requestMenssage']['id'];
            if ($retorno == '') {
                $tipo = '';
                $subTipo = '';
                $idRetorno = '';
            } else {
                $tipo = $retorno['modo'];
                $subTipo = $retorno['subtipo'];
                $idRetorno = $retorno['id_retorno'];
            }
            if (isset($retorno['opcoes']) && $retorno['opcoes'] != '') {
                $opcoes = $retorno['opcoes'];
            } else {
                $opcoes = $retorno['opcoes'];
            }
            //logSis('REQ', 'Chegou aqui - Instância: ' . $idInstancia . ' IdContato: ' . $id_contato . ' Tipo: ' . $tipo . ' IdInteracaiCliente: ' . $id_interacao_cliente . ' IdResposta: ' . $id_resposta . ' Motivo: ' . $motivo);

            inserirInteracao($idInstancia, 1, $id_contato, $tipo, $subTipo, $opcoes, $ultimoRetorno, $idRetorno, $id_interacao_cliente, $id_resposta, $motivo, 1);
            return true;
        } else {
            if ($motivo == 'Receptivo') {
                return false;
                exit(0);
            }
            return false;
            logSis('ERR', 'Não teve resposta da requisição a tempo' . $resposta);
        }

        break;
}

//* Inserir interação 
function inserirInteracao($id_instancia, $direcao, $id_contato, $tipo, $subTipo, $opcoesVariaveis, $menuAnterior, $id_retorno, $resposta, $id_mensagem, $mensagem, $status)
{
    include("../dados_conexao.php");

    $sql = "INSERT INTO tbl_interacoes(id_instancia, direcao, id_contato, tipo, subtipo, opcoes_variaveis, menu_anterior, id_retorno, resposta, id_mensagem, mensagem, status, data_envio) VALUES ($id_instancia, $direcao, '$id_contato', '$tipo', '$subTipo', '$opcoesVariaveis', '$menuAnterior', '$id_retorno', '$resposta', '$id_mensagem', '$mensagem', $status, NOW())";
    logSis('DEB', 'SQL : ' . $sql);

    $resultado = mysqli_query($conn['link'], $sql);
    if (!$resultado) {
        logSis('ERR', "Mysql Connect Erro: " . mysqli_error($conn['link']));
        exit(0);
    }
    if ($direcao == 0) {
        $id_interacao_cliente = mysqli_insert_id($conn['link']);
    }
    $id_interacao = mysqli_insert_id($conn['link']);

    if ($resultado != '1') {
        logSis('ERR', 'Insert interação IN. Erro: ' . mysqli_error($conn['link']));
        logSis('DEB', 'SQL : ' . $sql);
    } else {
        return 1;
        logSis('SUC', 'Insert interação IN. ID_Interação: ' . $id_interacao);
    }
    mysqli_close($conn['link']);
}

//* Função de LOG
function logSis($tipo, $texto)
{
    file_put_contents('log.txt', "> " . $tipo . " " . date('d/m/Y h:i:s') . " " . $texto . PHP_EOL, FILE_APPEND);
}
