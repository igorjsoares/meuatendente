<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("../dados_conexao.php");

if (isset($_FILES['acao'])) {
    $acao = $_FILES['acao'];
} else {
    $acao = filter_var($_POST['acao'], FILTER_SANITIZE_STRING);
}

switch ($acao) {

        //* Consultando Áudios 
    case 'consultaAudios':
        $dados = $_POST['dados'];
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);

        //@ Aqui colocar pra ele buscar o status (caso necessário de cada um dos serviços)
        $sql = "SELECT * FROM tbl_audios WHERE id_empresa = $idEmpresa AND status = 1 ORDER BY id_audio DESC";
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayAudiosBd = [];
        while ($audio = mysqli_fetch_array($query)) {

            array_push($arrayAudiosBd, array(
                'id_audio' => $audio['id_audio'],
                'id_fornecedor' => $audio['id_fornecedor'],
                'id_empresa' => $audio['id_empresa'],
                'nome' => $audio['nome']
            ));
        }

        echo json_encode($arrayAudiosBd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

        //* CASE Salvar o Áudio
    case 'salvarAudio':
        $idEmpresa = filter_var($_POST['idEmpresa'], FILTER_SANITIZE_STRING);
        $idUsuario = filter_var($_POST['idUsuario'], FILTER_SANITIZE_STRING);
        $nome = filter_var($_POST['nome'], FILTER_SANITIZE_STRING);
        $tipoAudio = filter_var($_POST['tipoAudio'], FILTER_SANITIZE_STRING);

        if ($tipoAudio == 'upload') {
            $arquivo = $_FILES['file']['name'];
            $tmp = $_FILES['file']['tmp_name'];
            // obter a extensão do arquivo carregado
            $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
        } else {
            
            $baseAudio = $_POST['file'];
            $decoded = base64_decode($baseAudio);
            $ext = 'mp3';
        }


        //Lista de extensoes válidas
        $extensoes = array('mp3', 'wav', 'ogg');

        if (in_array($ext, $extensoes)) {

            $sql = "INSERT INTO tbl_audios(id_fornecedor, id_empresa, nome, id_usuario, status, create_at) VALUES (0, $idEmpresa, '$nome', $idUsuario, 1, NOW())";
            //echo $sql;

            $query = mysqli_query($conn['link'], $sql);
            $idAudio = mysqli_insert_id($conn['link']);
            $nomeReal = $idAudio . '.mp3'; //.strtolower(pathinfo($img, PATHINFO_EXTENSION));


            if ($query == true) {
                $pasta = '../assets/audios/'; // upload pasta
                $caminhoFinal = $pasta . strtolower($nomeReal);

                if ($tipoAudio == 'upload') {
                    $resultado = move_uploaded_file($tmp, $caminhoFinal);
                } else {
                    $resultado = file_put_contents($caminhoFinal, $decoded);
                }
                if ($resultado) { //( Sucesso, conseguiu salvar o arquivo no servidor 
                    logSis('SUC', ' Salvou arquivo de áudio: ' . $idAudio);

                    $resultado = salvarAudioVelip($nome, $caminhoFinal, $nomeReal, $idAudio);

                    echo $resultado;
                } else {

                    if (deleteAudioBD($idAudio) == true) {
                        logSis('ERR', ' Não foi possível fazer o upload do arquivo. Audio: ' . $idAudio);
                    } else {
                        logSis('ERR', ' Não foi possível fazer o upload do arquivo. E não conseguiu deletar o registro no BD. Audio: ' . $idAudio);
                    }
                    echo alimentaArrayResult(0, 'Não foi possível fazer o upload do arquivo.');
                }
            } else {
                logSis('ERR', ' Não conseguiu salvar áudio no DB. Usuario: ' . $idUsuario);

                echo alimentaArrayResult(0, 'Não conseguiu salvar áudio no banco de dados.');
            }
        } else {
            logSis('ERR', ' A extensão escolhida não é válida. Usuario: ' . $idUsuario);
            echo alimentaArrayResult(0, 'A extensão escolhida não é válida. Extensão: ' . $ext);
        }

        break;

    case 'excluirAudio':
        $dados = $_POST['dados'];
        $idAudio = filter_var($dados['idAudio'], FILTER_SANITIZE_STRING);

        $sql = "UPDATE tbl_audios SET status = 0 WHERE id_audio = '$idAudio'";

        $query = mysqli_query($conn['link'], $sql);
        $linhasAfetadas = mysqli_affected_rows($conn['link']);

        if ($query != true && $linhasAfetadas == 0) {
            echo 0;
        } else {
            echo 1;
        }
        break;
}

//* Deletar o áudio no BD 
function deleteAudioBD($idAudio)
{
    include("../dados_conexao.php");

    $sql = "DELETE FROM tbl_audios WHERE id_audio = $idAudio";
    $query = mysqli_query($conn['link'], $sql);

    return $query;
}

//* Salvar o áudio na plataforma VELIP 
function salvarAudioVelip($nomeUpload, $caminhoFinal, $nomeReal, $idAudioBd)
{
    $target_url = "https://vox.velip.com.br/pop/torpedo/GetAudioFile.php";
    $fname = $caminhoFinal;
    $cfile = file_get_contents(realpath($fname), null);
    $expires = date("Y/m/d");
    $expires = date('Y:m:d', strtotime("+60 days", strtotime($expires)));
    $post = array("user" => "igorjsoares", 'audio' => $cfile, 'expires' => $expires, 'name' => $nomeUpload, 'type' => 'upload', 'nome_up' => $nomeReal);
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
    $result = curl_exec($ch);


    if ($result === FALSE) {
        curl_close($ch);
        return alimentaArrayResult(0, "Error sending" . $fname . " " . curl_error($ch));
    } else {
        curl_close($ch);

        $xml = simplexml_load_string($result);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);

        $id_fornecedor = $array['cdw_url'];

        if (isset($id_fornecedor) && $id_fornecedor != '') {
            $respostaAtualizacaoBd = updateAudioBd($idAudioBd, 'id_fornecedor', $id_fornecedor);

            if ($respostaAtualizacaoBd == 0) {
                return alimentaArrayResult(0, "Error UPDATE BD: " . $idAudioBd);

                if (deleteAudioBD($idAudioBd) == true) {
                    logSis('ERR', ' Não foi possível fazer a atualização no BD. Audio: ' . $idAudioBd);
                } else {
                    logSis('ERR', ' Não foi possível fazer a atualização no BD. E não conseguiu deletar o registro no BD. Audio: ' . $idAudioBd);
                }
            } else {
                logSis('SUCC', ' Segundo IF, foi salvo: ' . $id_fornecedor);

                return alimentaArrayResult(1, "Result: " . realpath($fname) . $xml);
            }
        } else {
            logSis('ERR', ' Não foi possível receber o ID da VELIP. Audio: ' . $idAudioBd);

            return alimentaArrayResult(0, "Error sending" . $fname . " " . curl_error($ch));
        }
    }
}

//* Atualizar o áudio no BD com ID VELIP 
function updateAudioBd($idAudio, $campo, $valor)
{
    include("../dados_conexao.php");

    $sql = "UPDATE tbl_audios SET $campo = '$valor' WHERE id_audio = '$idAudio'";

    $query = mysqli_query($conn['link'], $sql);
    $linhasAfetadas = mysqli_affected_rows($conn['link']);

    if ($query != true && $linhasAfetadas == 0) {
        logSis('ERR', ' Salvamento áudio - Erro ao salvar no BD ' . $idUsuario);
        return 0;
    } else {
        return 1;
    }
}

//* Alimenta array result 
function alimentaArrayResult($status, $mensagem)
{
    $arrayResult = [];
    array_push($arrayResult, array(
        'result' => $status,
        'mensagem' => $mensagem
    ));

    return json_encode($arrayResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
}

//* Função de LOG
function logSis($tipo, $texto)
{
    file_put_contents('log.txt', "> " . $tipo . " " . date('d/m/Y h:i:s') . $texto . PHP_EOL, FILE_APPEND);
}
