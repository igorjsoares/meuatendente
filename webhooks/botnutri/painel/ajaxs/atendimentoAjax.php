<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("../dados_conexao.php");

if (isset($_FILES['acao'])) {
    $acao = $_FILES['acao'];
} else {
    $acao = filter_var($_POST['acao'], FILTER_SANITIZE_STRING);
}

switch ($acao) {

    case 'consultaMenuAtendimento':
        $dados = $_POST['dados'];


        $sql = "SELECT c.id_contato, c.nome, c.numero, c.email, c.created_at AS contato_criado, count(i.id_contato) AS quant FROM tbl_contatos c LEFT JOIN tbl_interacoes i ON c.id_contato = i.id_contato AND i.direcao = 0 AND i.status_chat = 0 GROUP BY c.id_contato";
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayMensagens = [];
        while ($campanha = mysqli_fetch_array($query)) {

            array_push($arrayMensagens, array(
                'idContato' => $campanha['id_contato'],
                'nome' => $campanha['nome'],
                'numero' => $campanha['numero'],
                'email' => $campanha['email'],
                'contatoCriado' => $campanha['contato_criado'],
                'quant' => $campanha['quant']

            ));
        }

        echo json_encode($arrayMensagens, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'consultaConversaAtendimento':
        $dados = $_POST['dados'];
        $idContato = filter_var($dados['idContato'], FILTER_SANITIZE_STRING);

        $sql = "SELECT id_interacao, direcao, tipo, subtipo, id_mensagem, mensagem, status, status_chat, DATE_FORMAT(data_envio, '%d/%m/%Y') AS data_envio FROM tbl_interacoes WHERE id_contato = $idContato ORDER BY data_envio ASC";
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
                'dataEnvio' => $campanha['data_envio']

            ));
        }

        echo json_encode($arrayMensagens, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

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
        $nomePerfil = filter_var($_POST['nomePerfil'], FILTER_SANITIZE_STRING);
        $caminhoImagemPerfil = filter_var($_POST['caminhoImagemPerfil'], FILTER_SANITIZE_STRING);
        $nomeEmpresa = filter_var($_POST['nomeEmpresa'], FILTER_SANITIZE_STRING);
        $idUsuario = filter_var($_POST['idUsuario'], FILTER_SANITIZE_STRING);
        $nomeCampanha = filter_var($_POST['nomeCampanha'], FILTER_SANITIZE_STRING);
        $totalContatos = filter_var($_POST['totalContatos'], FILTER_SANITIZE_STRING);
        $lista = filter_var($_POST['lista'], FILTER_SANITIZE_STRING);
        $inicio = filter_var($_POST['inicio'], FILTER_SANITIZE_STRING);
        $mensagem = filter_var($_POST['mensagem'], FILTER_SANITIZE_STRING);
        $tipo = filter_var($_POST['tipo'], FILTER_SANITIZE_STRING);
        $idConta = filter_var($_POST['idConta'], FILTER_SANITIZE_STRING);


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
            if ($custo['tipo'] == 'wp_texto') {
                $wp_texto = $custo['valor'];
            }
            if ($custo['tipo'] == 'wp_midia') {
                $wp_midia = $custo['valor'];
            }
            if ($custo['tipo'] == 'voz_simples') {
                $voz_simples = $custo['valor'];
            }
        }

        if ($tipo != 1) { //Envio de Whatsapp mídia
            $totalCreditos = $totalContatos * $wp_midia;
        } else { //Envio de Whatsapp texto
            $totalCreditos = $totalContatos * $wp_texto;
        }

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

            if ($mensagem == '') {
                $mensagem = '.';
            }


            if ($tipo == '2') { //Imagem
                $media_type = 'image';
                $extensoes = array('jpeg', 'jpg', 'png', 'gif', 'bmp'); // extensões válidas
                $limite = 490 * 1000;  //490kb

            } else if ($tipo == '3') { //Video
                $media_type = 'video';
                $extensoes = array('mp4'); // extensões válidas
                $limite = 3000 * 1000;  //3000kb(3Mb)

            } else if ($tipo == '4') { //Audio
                $media_type = 'audio';
                $extensoes = array('mp3'); // extensões válidas
                $limite = 1000 * 1000;  //1000kb(1Mb)

            } else if ($tipo == '5') { //Pdf
                $media_type = 'image';
                $extensoes = array('pdf'); // extensões válidas
                $limite = 490 * 1000;  //490kb
            }

            //Para o profile
            //$caminhoDoArquivo = "../assets/empresas/" . $idEmpresa . ".jpg";
            $caminhoDoArquivo = "../" . $caminhoImagemPerfil;
            $extensaoDoArquivo = "jpg";
            $imagemdePerfil = base64_encode(file_get_contents($caminhoDoArquivo));


            //@ Trocar isso aqui por algo melhor, onde só insere a chave media caso seja $tipo != 1
            if ($tipo == '1') {
                $jsonData = [
                    "method" => "zap",
                    "name" => $nomeCampanha,
                    "message" => $mensagem,

                    //DATA EM QUE VOCÊ DESEJA QUE A CAMPANHA SEJA ENVIADA
                    "send_date" => $inicio,

                    //POR UMA QUESTÃO DE BREVIDADE ESTAMOS UTILIZANDO APENAS O PARAMETRO `list_id`, MAS CONFORME JÁ DITO ANTERIORMENTE VOCÊ PODE PASSAR OS DESTINATARIOS ATRAVÉS DO PARAMETRO `recipients`
                    "list_id" => $lista,

                    //O CAMPO DE PERSONALIZAÇÃO DE PERFIL É OPCIONAL
                    "profile" => [
                        "name" => $nomePerfil,
                        "photo" => $imagemdePerfil
                    ],
                    "callback_url" => "https://newprospect.com.br/app/webhook_mf.php"
                ];
                salvarCampanha($jsonData, $nomeCampanha, $idEmpresa, $idConta, $idUsuario, $lista, $mensagem, $tipo, "", "", $totalContatos, $totalCreditos);
            } else {

                $arquivo = $_FILES['file']['name'];
                $tmp = $_FILES['file']['tmp_name'];

                $tamanho = filesize($tmp);

                if ($tamanho <= $limite) {
                    //Midia da mensagem
                    $mediaDaMensagem = base64_encode(file_get_contents($tmp));

                    //Midia da mensagem
                    //$caminhoDoArquivo = "../profile-icon.png";
                    //$extensaoDoArquivo = "jpg";
                    //$mediaDaMensagem = base64_encode(file_get_contents($caminhoDoArquivo));

                    $jsonData = [
                        "method" => "zap",
                        "name" => $nomeCampanha,
                        "message" => $mensagem,

                        //DATA EM QUE VOCÊ DESEJA QUE A CAMPANHA SEJA ENVIADA
                        "send_date" => $inicio,

                        //POR UMA QUESTÃO DE BREVIDADE ESTAMOS UTILIZANDO APENAS O PARAMETRO `list_id`, MAS CONFORME JÁ DITO ANTERIORMENTE VOCÊ PODE PASSAR OS DESTINATARIOS ATRAVÉS DO PARAMETRO `recipients`
                        "list_id" => $lista,

                        //O CAMPO DE PERSONALIZAÇÃO DE PERFIL É OPCIONAL
                        "profile" => [
                            "name" => $nomePerfil,
                            "photo" => $imagemdePerfil
                        ],

                        //O CAMPO DE MEDIA É OPCIONAL
                        "media" => [
                            "media_type" => $media_type,
                            "media_file" => $mediaDaMensagem
                        ],
                        "callback_url" => "https://newprospect.com.br/app/webhook_mf.php"
                    ];
                } else {
                    //echo "tamanho";
                    $arrayResult = [];
                    array_push($arrayResult, array(
                        'result' => 3
                    ));
                    echo json_encode($arrayResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
                }


                //echo $imagemdePerfil;
                salvarCampanha($jsonData, $nomeCampanha, $idEmpresa, $idConta, $idUsuario, $lista, $mensagem, $tipo, $arquivo, $tmp, $totalContatos, $totalCreditos);
            }
        }

        break;
}


function salvarCampanha($jsonData, $nomeCampanha, $idEmpresa, $idConta, $idUsuario, $idLista, $mensagem, $tipo, $arquivo, $tempArquivo, $totalContatos, $totalCreditos)
{
    include("../dados_conexao.php");

    //codifica os dados para envio no formato json
    $jsonData = json_encode($jsonData);

    //endpoint para criacao de campanhas
    $url = "http://api.messageflow.com.br/api/v2/campaign/create/";

    //inicia a sessão curl
    $messageflow = curl_init($url);

    //token de acesso a API
    $token = 'Token c3adcffb-122f-4a71-9fae-b872bb0a6b67';

    /**
     * Configura as opcoes de envio para a transferencia CURL,sendo:
     *
     * - CURLOPT_POSTFIELDS: Campos a serem enviados via POST
     * - CURLOPT_HTTPHEADER: Headers da requisição
     * - CURLOPT_RETURNTRANSFER: TRUE para retornar como string o resultado da requisicao
     */
    $headers = array();
    $headers[] = 'Content-Type:application/json';
    $headers[] = 'Authorization: ' . $token;

    curl_setopt($messageflow, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($messageflow, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($messageflow, CURLOPT_RETURNTRANSFER, true);

    //executa a transferencia
    $jsonResult = curl_exec($messageflow);

    //encerra a sessão curl
    curl_close($messageflow);

    $result = json_decode($jsonResult, true);


    if (isset($result['id_campaign'])) {
        $idMessageFlow = $result['id_campaign'];
        $sql = "INSERT INTO tbl_mf_campanhas(nome, id_messageflow, id_messageflow_lista, id_empresa, id_conta, id_usuario, tipo, mensagem, url, status, create_at) VALUES ('$nomeCampanha', $idMessageFlow, $idLista, $idEmpresa, $idConta, $idUsuario, $tipo, '$mensagem', '', 1, NOW())";

        //@ Separar o salvamento de arquivo em uma função e o salvamento em banco de dados em outra função.
        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {

            $pasta = '../assets/midias/';
            if ($tipo == 2) {
                $ext = 'jpg';
            } else {
                $ext = strtolower(pathinfo($arquivo, PATHINFO_EXTENSION));
            }
            $nomereal = $idMessageFlow . '.' . $ext; //.strtolower(pathinfo($img, PATHINFO_EXTENSION));

            $pasta = $pasta . strtolower($nomereal);
            if (move_uploaded_file($tempArquivo, $pasta)) {
                file_put_contents('log/log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Arquivo salvo com sucesso! ' . $jsonResult . PHP_EOL, FILE_APPEND);
            } else {
                file_put_contents('log/log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Não foi possível fazer o upload do arquivo. ' . $jsonResult . PHP_EOL, FILE_APPEND);
            }

            file_put_contents('log/log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Conseguiu gerar a campanha e salvar ' . $jsonResult . PHP_EOL, FILE_APPEND);

            $arrayResult = [];
            array_push($arrayResult, array(
                'result' => 1
            ));
            echo json_encode($arrayResult, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

            salvarCreditos($idEmpresa, $tipo, $idMessageFlow, $totalContatos, $totalCreditos);
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


    /* if($result['name'] == $nomeLista){
        echo '1';
    }else{
        echo '0';
    } */
}

function salvarCreditos($idEmpresa, $tipo, $id_mf_campanha, $totalContatos, $totalCreditos)
{
    include("../dados_conexao.php");

    $contatos = $totalCreditos;
    $contatos = -abs($contatos);

    $sql = "INSERT INTO tbl_creditos(tipo, id_empresa, referencia, valor, id_usuario, status, create_at) VALUES (1, $idEmpresa, $id_mf_campanha, $contatos, 0, 2, NOW())";

    //@ Separar o salvamento de arquivo em uma função e o salvamento em banco de dados em outra função.
    $query = mysqli_query($conn['link'], $sql);
    if ($query == true) {
        file_put_contents('log/log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Salvou o crédito ' . $id_mf_campanha . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents('log/log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Não salvou o crédito ' . $id_mf_campanha . PHP_EOL, FILE_APPEND);
    }
}
