<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("../dados_conexao.php");

if (isset($_FILES['acao'])) {
    $acao = $_FILES['acao'];
} else {
    $acao = filter_var($_POST['acao'], FILTER_SANITIZE_STRING);
}

switch ($acao) {

    case 'consultaCreditosEmpresas':
        $dados = $_POST['dados'];
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $perfil = filter_var($dados['perfil'], FILTER_SANITIZE_STRING);

        if ($perfil == 'MASTER') {
            $where = '';
        } else {
            $where = ' AND id_empresa = $idEmpresa';
        }

        $sql = "SELECT e.id_empresa, e.nome AS nome_empresa, SUM(c.valor) AS creditos FROM tbl_creditos c, tbl_empresas e WHERE c.id_empresa = e.id_empresa AND c.status = 1 $where GROUP BY c.id_empresa ORDER BY SUM(c.valor) ASC";
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayCreditosEmpresas = [];
        while ($campanha = mysqli_fetch_array($query)) {
            array_push($arrayCreditosEmpresas, array(
                'idEmpresa' => $campanha['id_empresa'],
                'nomeEmpresa' => $campanha['nome_empresa'],
                'creditos' => $campanha['creditos']
            ));
        }

        echo json_encode($arrayCreditosEmpresas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'consultaExtrato':
        $dados = $_POST['dados'];
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);

        $sql = "SELECT * FROM tbl_creditos WHERE id_empresa = $idEmpresa ORDER BY id_credito DESC";
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $real = 0;
        $pendente = 0;
        $disponivel = 0;
        $arrayExtrato = ['dados' => [], 'consolidado' => []];
        while ($credito = mysqli_fetch_array($query)) {
            if ($credito['status'] == 2) {
                $pendente += $credito['valor'];
            } else {
                $real += $credito['valor'];
            }

            array_push($arrayExtrato['dados'], array(
                'idCredito' => $credito['id_credito'],
                'tipo' => $credito['tipo'],
                'referencia' => $credito['referencia'],
                'valor' => $credito['valor'],
                'status' => $credito['status'],
                'create_at' => $credito['create_at']
            ));
        }

        $disponivel = $real + $pendente;

        array_push($arrayExtrato['consolidado'], array(
            'real' => $real,
            'pendente' => $pendente,
            'disponivel' => $disponivel
        ));

        echo json_encode($arrayExtrato, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

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

    case 'salvarCredito':

        $dados = $_POST['dados'];
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $valor = filter_var($dados['valor'], FILTER_SANITIZE_STRING);
        $descricao = filter_var($dados['descricao'], FILTER_SANITIZE_STRING);


        $sql = "INSERT INTO tbl_creditos(tipo, id_empresa, referencia, valor, id_usuario, descricao, status, create_at) VALUES (1, $idEmpresa, 0, $valor, $idUsuario, '$descricao', 1, NOW())";

        $query = mysqli_query($conn['link'], $sql);

        if ($query == true) {
            file_put_contents('log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Conseguiu realizar a recarga. Usuario: ' . $idUsuario . PHP_EOL, FILE_APPEND);
            echo 1;
        } else {
            file_put_contents('log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Não conseguiu realizar a recarga. Usuario: ' . $idUsuario . PHP_EOL, FILE_APPEND);
            echo 0;
            //echo $sql;
        }
        break;
}
