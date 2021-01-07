<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("../dados_conexao.php");

if (isset($_FILES['acao'])) {
    $acao = $_FILES['acao'];
} else {
    $acao = filter_var($_POST['acao'], FILTER_SANITIZE_STRING);
}

switch ($acao) {

    case 'salvarConta':
        $idEmpresa = filter_var($_POST['idEmpresa'], FILTER_SANITIZE_STRING);
        $idUsuario = filter_var($_POST['idUsuario'], FILTER_SANITIZE_STRING);
        $nome = filter_var($_POST['nome'], FILTER_SANITIZE_STRING);

        $arquivo = $_FILES['file']['name'];
        $tmp = $_FILES['file']['tmp_name'];




        // obter a extensão do arquivo carregado
        $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));

        $extensoes = array('jpg');

        if (in_array($ext, $extensoes)) {

            //( Entende e converte o tamanho do arquivo profile para o tamanho exigido no message flow 
            $altura = "192";
            $largura = "192";
            $imagem_temporaria = imagecreatefromjpeg($tmp);
            $largura_original = imagesx($imagem_temporaria);
            $altura_original = imagesy($imagem_temporaria);
            $nova_largura = $largura ? $largura : floor(($largura_original / $altura_original) * $altura);
            $nova_altura = $altura ? $altura : floor(($altura_original / $largura_original) * $largura);
            $imgPerfilRedimensionada = imagecreatetruecolor($nova_largura, $nova_altura);
            imagecopyresampled($imgPerfilRedimensionada, $imagem_temporaria, 0, 0, 0, 0, $nova_largura, $nova_altura, $largura_original, $altura_original);
            //imagejpeg($imgPerfilRedimensionada, 'arquivo/' . $_FILES['arquivo']['name']);


            $sql = "INSERT INTO tbl_contas(id_empresa, nome, id_usuario, create_at) VALUES ($idEmpresa, '$nome', $idUsuario, NOW())";

            $query = mysqli_query($conn['link'], $sql);
            $idConta = mysqli_insert_id($conn['link']);
            $nomereal = $idConta . '.jpg'; //.strtolower(pathinfo($img, PATHINFO_EXTENSION));


            if ($query == true) {
                
                $pasta = '../assets/contas/'; // upload pasta
                $pasta = $pasta . strtolower($nomereal);
                imagejpeg($imgPerfilRedimensionada, $pasta);

                //if (move_uploaded_file($tmp, $pasta)) {
                if (file_exists($pasta)) {
                    logSis('SUC', ' Salvou arquivo da conta: ' . $idConta);

                    $arrayResult = [];
                    array_push($arrayResult, array(
                        'result' => 1
                    ));
                    echo json_encode($arrayResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
                } else {
                    logSis('ERR', ' Não foi possível fazer o upload do arquivo. Conta: ' . $idConta);

                    $sql = "DELETE FROM tbl_contas WHERE id_conta = $idConta";
                    $query = mysqli_query($conn['link'], $sql);

                    $arrayResult = [];
                    array_push($arrayResult, array(
                        'result' => 0
                    ));
                    echo json_encode($arrayResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
                }
            } else {
                logSis('ERR', ' Não conseguiu salvar conta. Usuario: ' . $idUsuario);
                $arrayResult = [];
                array_push($arrayResult, array(
                    'result' => 0
                ));
                echo json_encode($arrayResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
            }
        } else {
            logSis('ERR', ' A extensão escolhida não é válida. Usuario: ' . $idUsuario);

            $arrayResult = [];
            array_push($arrayResult, array(
                'result' => 0
            ));
            echo json_encode($arrayResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
        }

        break;

    case 'excluirConta':
        $dados = $_POST['dados'];
        $idConta = filter_var($dados['idConta'], FILTER_SANITIZE_STRING);

        $sql = "DELETE FROM tbl_contas WHERE id_conta = $idConta";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo 1;
        } else {
            echo 0;
        }
        break;
}

function logSis($tipo, $texto)
{
    file_put_contents('log.txt', "> " . $tipo . " " . date('d/m/Y h:i:s') . $texto . PHP_EOL, FILE_APPEND);
}
