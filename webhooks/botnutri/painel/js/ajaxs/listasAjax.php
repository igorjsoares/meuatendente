<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("../dados_conexao.php");

if (isset($_FILES['acao'])) {
    $acao = $_FILES['acao'];
} else {
    $acao = filter_var($_POST['acao'], FILTER_SANITIZE_STRING);
}

switch ($acao) {

    case 'consultaListas':
        $dados = $_POST['dados'];
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);

        //@ Aqui colocar pra ele buscar o status (caso necessário de cada um dos serviços)
        $sql = "SELECT * FROM tbl_mf_listas WHERE id_empresa = $idEmpresa AND status = 1 ORDER BY id_lista DESC";
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayListasBd = [];
        while ($lista = mysqli_fetch_array($query)) {

            //Retorna todas as listas, caso queira o retorno de apenas uma lista basta passar o id no final da url
            //ex: $url = "http://api.messageflow.com.br/api/v3/list/34234";
            $chatpro = curl_init();
            $url = "http://api.messageflow.com.br/api/v2/list/" . $lista['id_messageflow'] . "/";
            curl_setopt($chatpro, CURLOPT_URL, $url);
            curl_setopt($chatpro, CURLOPT_RETURNTRANSFER, 1);

            $headers = array();
            $headers[] = 'Accept: application/json';
            $headers[] = "Authorization: Token c3adcffb-122f-4a71-9fae-b872bb0a6b67";
            $headers[] = 'Content-Type: application/json';
            curl_setopt($chatpro, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($chatpro);
            $result = json_decode($result, true);

            if (isset($result['detail']) && $result['detail'] == "Não encontrado.") {
            } else {
                array_push($arrayListasBd, array(
                    'id_lista' => $lista['id_lista'],
                    'id_messageflow' => $lista['id_messageflow'],
                    'total_contacts' => $result['total_contacts'],
                    'processing_status' => $result['processing_status'],
                    'upload_date' => $result['upload_date'],
                    'nome' => $lista['nome']
                ));
            }
        }

        echo json_encode($arrayListasBd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'consultaListasVoz':
        $dados = $_POST['dados'];
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);

        //@ Aqui colocar pra ele buscar o status (caso necessário de cada um dos serviços)
        $sql = "SELECT * FROM tbl_mf_listas WHERE id_empresa = $idEmpresa AND status = 1 ORDER BY id_lista DESC";
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayListasBd = [];
        while ($lista = mysqli_fetch_array($query)) {


            array_push($arrayListasBd, array(
                'id_lista' => $lista['id_lista'],
                'id_velip' => $lista['id_velip'],
                'num_velip' => $lista['num_velip'],
                'nome' => $lista['nome']
            ));
        }

        echo json_encode($arrayListasBd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'salvarLista':
        $idEmpresa = filter_var($_POST['idEmpresa'], FILTER_SANITIZE_STRING);
        $idUsuario = filter_var($_POST['idUsuario'], FILTER_SANITIZE_STRING);
        $nomeLista = filter_var($_POST['nomeLista'], FILTER_SANITIZE_STRING);
        $areaContatos = filter_var(@$_POST['areaContatos'], FILTER_SANITIZE_STRING);
        file_put_contents('log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Entrou no salvar lista ' . PHP_EOL, FILE_APPEND);


        $extensoes = array('csv', 'txt'); // extensões válidas
        $pasta = '../assets/empresas/'; // upload pasta
        $nome = $idEmpresa . '_' . rand(1000, 1000000);

        if ($_FILES['file']) {

            $file = $_FILES['file']['name'];
            $tmp = $_FILES['file']['tmp_name'];
            $nomereal = $nome . '.csv'; //.strtolower(pathinfo($file, PATHINFO_EXTENSION));

            // obter a extensão do arquivo carregado
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            if (in_array($ext, $extensoes)) {

                $pasta = $pasta . strtolower($nomereal);
                if (move_uploaded_file($tmp, $pasta)) {
                    file_put_contents('log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Salvou o arquivo ' . PHP_EOL, FILE_APPEND);

                    $resultado = getCsv($nomereal, $nomeLista);

                    $jsonContatos = str_replace(array("\\r", "\\n", "\\t"), "", json_encode($resultado, JSON_PRETTY_PRINT));
                    $jsonContatos = preg_replace('#(?<!\\\\)(\\$|\\\\)#', "", $jsonContatos);

                    $jsonContatosVelip = str_replace(array("\\r", "\\n", "\\t"), "", json_encode($resultado, JSON_PRETTY_PRINT));
                    $jsonContatosVelip = preg_replace('#(?<!\\\\)(\\$|\\\\)#', "", $jsonContatosVelip);

                    $arrayContatosVelip = json_decode($jsonContatosVelip, TRUE);
                    $arrayContatosVelipPhones = $arrayContatosVelip['recipients'];

                    //( Percorre o array de contatos para criar o array sem a key Phone. 
                    $arrayTemp = [];
                    foreach ($arrayContatosVelipPhones as $numero) {
                        array_push($arrayTemp, $numero['phone']);
                    }
                    $jsonContatosVelipTemp = json_encode($arrayTemp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

                    salvarLista($jsonContatos, $jsonContatosVelipTemp, $nomeLista, $idEmpresa, $idUsuario);
                } else {
                    echo '<h7 style="color: red">Não foi possível fazer o upload do arquivo.</h7>';
                }
            } else {
                echo '<h7 style="color: red">Extensão inválida</h7>';
            }
        }
        break;
}

function getCsv($nomeArquivo, $nomeLista)
{

    file_put_contents('log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Entrou no getCSV ' . PHP_EOL, FILE_APPEND);

    $file = fopen("../assets/empresas/" . $nomeArquivo, "r");
    $result = array("name" => $nomeLista, "recipients" => []);
    $i = 0;

    while (!feof($file)) {
        $linha = explode(';', fgets($file));
        if ($linha == ';;;;;;;;') {
            $i++;
        } else {
            $telefone = $linha[0];
            if (isset($linha[1])) {
                $nome = $linha[1];

                array_push($result['recipients'], array(
                    'phone' => $telefone,
                    'name' => $nome
                ));
            } else {
                $nome = '';

                array_push($result['recipients'], array(
                    'phone' => $telefone
                ));
            }
        }
    }

    file_put_contents('log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Passou pelo arquivo ' . PHP_EOL, FILE_APPEND);

    fclose($file);

    return $result;
}

//* Salvar lista 
function salvarLista($jsonContatos, $jsonContatosVelip, $nomeLista, $idEmpresa, $idUsuario)
{
    include("../dados_conexao.php");

    //( Salvando lista no MessageFlow
    $messageflow = curl_init();
    $url = "http://api.messageflow.com.br/api/v2/list/";
    curl_setopt($messageflow, CURLOPT_URL, $url);
    curl_setopt($messageflow, CURLOPT_RETURNTRANSFER, 1);

    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = "Authorization: Token c3adcffb-122f-4a71-9fae-b872bb0a6b67";
    $headers[] = 'Content-Type: application/json';
    curl_setopt($messageflow, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($messageflow, CURLOPT_POSTFIELDS, $jsonContatos);
    curl_setopt($messageflow, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($messageflow);
    $jsonResult = curl_exec($messageflow);

    $result = json_decode($result, true);

    if (isset($result['list_id'])) {
        $idMessageFlow = $result['list_id'];
    } else {
        $idMessageFlow = 0;
    }

    //( Salvando a lista na VELIP 
    $target_url = "https://vox.velip.com.br/pop/torpedo/CreateDestinationBase.php";
    $fname = $caminhoFinal;
    $cfile = file_get_contents(realpath($fname), null);
    $post = array("user" => "resolv", "password" => "velocidade", 'datajson' => $jsonContatosVelip, 'cdlc_nome' => $nomeLista);
    //$post = array("user" => "resolv", 'datafile' => $cfile, 'cdlc_nome' => $nomeLista);
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
        logSis('ERR', 'Não conseguiu salvar a lista na VELIP. Resultado: ' . $resultVelip);
    } else {
        curl_close($ch);

        $xml = simplexml_load_string($resultVelip);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);

        $status = $array['status'];

        if ($status == 'OK') {
            $idVelip = $array['cdlc_id'];
            $numVelip = $array['num_dest'];
        } else {
            $cod_erro = $array['cod_erro'];
            $erro = $array['erro'];
            logSis('ERR', 'Não conseguiu salvar a lista na VELIP. Erro: ' . $cod_erro . ' - ' . $erro);
        }
    }

    /* 
        status : OK para base de destinos criada com sucesso ou NO para erro na criação
        cdlc_id: ID para conta gerada pelo sistema ou null se houver erro na criação
        num_dest: Número de destinos na base ou null para erro
        cod_erro: Código de erro em caso do status ser NO, 0 (zero) se não houver erro
        erro: Descritivo do erro ou null se não houver erro 
        */

    //( Salvando a lista no BD
    if ($idMessageFlow != 0 || $idVelip != 0) {

        $sql = "INSERT INTO tbl_mf_listas(id_messageflow, id_velip, num_velip, nome, id_empresa, id_usuario, status, create_at) VALUES ('$idMessageFlow', '$idVelip', $numVelip, '$nomeLista', $idEmpresa, $idUsuario, 1, NOW())";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {

            $idLista = mysqli_insert_id($conn['link']);
            logSis('SUC', 'Conseguiu gerar a lista e salvar. idLista: ' . $idLista);

            echo 1;
        } else {
            logSis('ERR', 'Conseguiu gerar a lista mas não salvar. MF: ' . $idMessageFlow . ' Velip: ' . $idVelip);

            echo 0;
        }
    } else {
        logSis('ERR', 'Não Conseguiu gerar a lista em nenhum dos serviços. Usuário: ' . $idUsuario);

        echo 0;
    }
}

//* Função de LOG
function logSis($tipo, $texto)
{
    file_put_contents('log.txt', "> " . $tipo . " " . date('d/m/Y h:i:s') . " " . $texto . PHP_EOL, FILE_APPEND);
}
