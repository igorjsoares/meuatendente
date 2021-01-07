<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("../dados_conexao.php");

$acao = filter_var($_POST['acao'], FILTER_SANITIZE_STRING);


switch ($acao) {
    case 'consultaEnvios':
        $sql = "SELECT * FROM tbl_envios";
        //echo $sql;
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayEnvios = [];
        while ($envios = mysqli_fetch_array($query)) {
            array_push($arrayEnvios, array(
                'id_envio' => $envios['id_envio'],
                'nome' => $envios['nome'],
                'telefone' => $envios['telefone'],
                'status' => $envios['status']
            ));
        }

        echo json_encode($arrayEnvios, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'envio':
        $dados = $_POST['dados'];

        $tentativa = filter_var($dados['tentativa'], FILTER_SANITIZE_STRING);
        $mensagem = filter_var($dados['mensagem'], FILTER_SANITIZE_STRING);
        $numero = filter_var($dados['numero'], FILTER_SANITIZE_STRING);

        $sql = "UPDATE tbl_envios SET status = $tentativa WHERE telefone = $numero AND status = 1";

        $query = mysqli_query($conn['link'], $sql);
        $linhasAfetadas = mysqli_affected_rows($conn['link']);

        if ($query != true || $linhasAfetadas == 0) {
            echo "0";
        } else {

            $segundos_intervalos = 10;
            $intervalo = (rand(1, $segundos_intervalos) + ((rand(0, 9) * 0.1) + (rand(0, 9) * 0.01)));
            usleep($intervalo * 1000000);

            $data = array('number' => $numero, 'menssage' => $mensagem);

            $url = 'https://' . 'v4.chatpro.com.br/chatpro-mmcqgsvkwz' . '/api/v1/' . 'send_message';
            if (is_array($data)) {
                $data = json_encode($data);
            }

            $options = stream_context_create(['http' => [
                'method'  => 'POST',
                'header'  => "Content-type: application/json\r\nAuthorization: 27eb763a592ef0c2ef276476ffe50755e3fbc9bb\r\n",
                'content' => $data
            ]]);

            $response = file_get_contents($url, false, $options);

            /* $curl = curl_init();

            $url = 'https://' . 'v4.chatpro.com.br/chatpro-mmcqgsvkwz' . '/api/v1/' . $method;
            $token = '27eb763a592ef0c2ef276476ffe50755e3fbc9bb';

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "{\r\n  \"menssage\": \"$mensagem\",\r\n  \"number\": \"$numero\"\r\n}",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: $token",
                    "cache-control: no-cache"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                //echo "cURL Error #:" . $err;
                file_put_contents('log/log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Erro no Curl: ' . $response . PHP_EOL, FILE_APPEND);
                echo 0;
            } */

            file_put_contents('log/log.txt', "> REQ " . date('d/m/Y h:i:s') . ' Resp Requisição: ' . $response . PHP_EOL, FILE_APPEND);

            //return $response;

            $resposta = json_decode($response, true);
            $statusEnvio = $resposta['message'];
            if ($statusEnvio == "Mensagem enviada com sucesso" || $statusEnvio == "Mensagem Enviada") {
                $id_resposta = $resposta['requestMenssage']['id'];

                $sql = "UPDATE tbl_envios SET status = 5 WHERE telefone = $numero";

                $query = mysqli_query($conn['link'], $sql);
                $linhasAfetadas = mysqli_affected_rows($conn['link']);

                if ($query != true && $linhasAfetadas == 0) {
                    file_put_contents('log/log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Atualização do status para 5. Número: ' . $numero . PHP_EOL, FILE_APPEND);
                    echo 1;
                } else {
                    file_put_contents('log/log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Atualização do status para 5. Número: ' . $numero . PHP_EOL, FILE_APPEND);
                    echo 1;
                }
            } else {
                if ($statusEnvio ==  "Not exist number in whatsapp") {
                    $sql = "UPDATE tbl_envios SET status = 4 WHERE telefone = $numero";

                    $query = mysqli_query($conn['link'], $sql);
                    $linhasAfetadas = mysqli_affected_rows($conn['link']);

                    if ($query != true && $linhasAfetadas == 0) {
                        file_put_contents('log/log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Atualização do status para 4. Número: ' . $numero . PHP_EOL, FILE_APPEND);
                        echo 2;
                    } else {
                        file_put_contents('log/log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Atualização do status para 4. Número: ' . $numero . PHP_EOL, FILE_APPEND);
                        echo 2;
                    }
                } else {
                    file_put_contents('log/log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Problema no envio. Numero. ' . $numero . ' Mensagem: ' . $statusEnvio . PHP_EOL, FILE_APPEND);
                }
            }
        }

        break;

    case 'pendentesTodos':
        $dados = $_POST['dados'];

        $sql = "UPDATE tbl_envios SET status = 1";

        $query = mysqli_query($conn['link'], $sql);
        $linhasAfetadas = mysqli_affected_rows($conn['link']);

        if ($query == true && $linhasAfetadas != 0) {
            echo "1";
        } else {
            echo "0";
        }

        break;
}

//* P R E P A R O  E N V I O
//Prepara para envio da mensagem de texto
function sendMessage($remoteJID, $text)
{
    $data = array('number' => $remoteJID, 'menssage' => $text);
    sendRequest($remoteJID, 'send_message', $data);
}

//* E N V I O
//Envia a requisição
function sendRequest($numero, $method, $data)
{
    include("../dados_conexao.php");

    $url = 'https://' . 'v4.chatpro.com.br/chatpro-mmcqgsvkwz' . '/api/v1/' . $method;
    if (is_array($data)) {
        $data = json_encode($data);
    }

    $options = stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => "Content-type: application/json\r\nAuthorization: 27eb763a592ef0c2ef276476ffe50755e3fbc9bb\r\n",
        'content' => $data
    ]]);

    $response = file_get_contents($url, false, $options);

    file_put_contents('log/log.txt', "> REQ " . date('d/m/Y h:i:s') . ' Resp Requisição: ' . $response . PHP_EOL, FILE_APPEND);

    //return $response;

    $resposta = json_decode($response, true);
    $statusEnvio = $resposta['message'];
    if ($statusEnvio == "Mensagem enviada com sucesso" || $statusEnvio == "Mensagem Enviada") {
        $id_resposta = $resposta['requestMenssage']['id'];

        $sql = "UPDATE tbl_envios SET status = 5 WHERE telefone = $numero";

        $query = mysqli_query($conn['link'], $sql);
        $linhasAfetadas = mysqli_affected_rows($conn['link']);

        if ($query != true && $linhasAfetadas == 0) {
            file_put_contents('log/log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Atualização do status para 5. Número: ' . $numero . PHP_EOL, FILE_APPEND);
            return 1;
        } else {
            file_put_contents('log/log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Atualização do status para 5. Número: ' . $numero . PHP_EOL, FILE_APPEND);
            return 1;
        }
    } else {
        if ($statusEnvio ==  "Not exist number in whatsapp") {
            $sql = "UPDATE tbl_envios SET status = 4 WHERE telefone = $numero";

            $query = mysqli_query($conn['link'], $sql);
            $linhasAfetadas = mysqli_affected_rows($conn['link']);

            if ($query != true && $linhasAfetadas == 0) {
                file_put_contents('log/log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Atualização do status para 4. Número: ' . $numero . PHP_EOL, FILE_APPEND);
                return 2;
            } else {
                file_put_contents('log/log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Atualização do status para 4. Número: ' . $numero . PHP_EOL, FILE_APPEND);
                return 2;
            }
        } else {
            file_put_contents('log/log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Problema no envio. Numero. ' . $numero . ' Mensagem: ' . $statusEnvio . PHP_EOL, FILE_APPEND);
        }
    }
}
