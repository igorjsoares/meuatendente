<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("../dados_conexao.php");

$acaophp = filter_var(@$_POST['acao'], FILTER_SANITIZE_STRING);


switch ($acaophp) {
    case 'verificarNotificacao':
        $dados = $_POST['dados'];

        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);

        echo consultarNotificaoes($idEmpresa, $idUsuario);

        break;
}


function consultarNotificaoes($idEmpresa, $idUsuario)
{
    file_put_contents('console.txt', date('d/m/Y h:i:s') . ' ' . "Consulta de notificação realizada" . PHP_EOL, FILE_APPEND);

    include("../dados_conexao.php");

    //$sql = "SELECT * FROM tbl_notificacoes n WHERE n.id_notificacao NOT IN (SELECT id_vis_notificacao FROM tbl_vis_notificacao WHERE id_usuario = $idUsuario) AND id_empresa = $idEmpresa AND status = 1";
    $sql = "SELECT acao, COUNT(id_notificacao) AS quant FROM tbl_notificacoes n WHERE n.id_notificacao NOT IN (SELECT id_notificacao FROM tbl_vis_notificacao WHERE id_usuario = $idUsuario) AND id_empresa = $idEmpresa AND status = 1 GROUP BY acao";

    $query = mysqli_query($conn['link'], $sql);
    $numRow = mysqli_num_rows($query);

    $arrayNotificacoes = [];
    while ($notificacao = mysqli_fetch_array($query)) {

        array_push($arrayNotificacoes, array(
            'acao' => $notificacao['acao'],
            'quant' => $notificacao['quant']
        ));
    }
    if ($numRow != 0) {
        return json_encode($arrayNotificacoes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
    } else {
        return json_encode('', JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
        //echo $sql;
    }
}

function criarNotificacao($idSolicitacao, $idEmpresa, $acao, $externa)
{
    if($externa == true){
        include("dados_conexao.php");
    }else{
        include("../dados_conexao.php");
    }

    $sql = "INSERT INTO tbl_notificacoes (id_solicitacao, id_empresa, acao, hora, status) VALUES ('$idSolicitacao', '$idEmpresa', $acao, NOW(), 1)";
    //echo $sql;
    $query = mysqli_query($conn['link'], $sql);
    if ($query == true) {
        if ($acao == 2 || $acao == 3) {
            $sql = "UPDATE tbl_notificacoes SET status = 0 WHERE acao = 1 AND id_solicitacao = $idSolicitacao";

            $query = mysqli_query($conn['link'], $sql);
            if ($query == true) {
                file_put_contents('console.txt', date('d/m/Y h:i:s') . ' - SUCE - ' . "Notificação atualizada - " . $idSolicitacao . PHP_EOL, FILE_APPEND);
            } else {
                file_put_contents('console.txt', date('d/m/Y h:i:s') . ' - ERRO - ' . "Notificação não atualizada - " . $idSolicitacao . PHP_EOL, FILE_APPEND);
            }
        }
        file_put_contents('console.txt', date('d/m/Y h:i:s') . ' - SUCE - ' . "Notificação criada - " . $idSolicitacao . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents('console.txt', date('d/m/Y h:i:s') . ' - ERRO - ' . "Notificação não criada - " . $idSolicitacao  . PHP_EOL, FILE_APPEND);
    }
}

function criarVisualizacao($idEmpresa, $idUsuario)
{
    include("../dados_conexao.php");

    $sql = "INSERT INTO tbl_vis_notificacao (id_notificacao, id_usuario, hora) SELECT id_notificacao, $idUsuario, now() FROM tbl_notificacoes n WHERE n.id_notificacao NOT IN (SELECT id_notificacao FROM tbl_vis_notificacao WHERE id_usuario = $idUsuario) AND id_empresa = $idEmpresa AND status = 1";
    //echo $sql;
    $query = mysqli_query($conn['link'], $sql);
    $idSolicitacao = mysqli_insert_id($conn['link']);
    if ($query == true) {
        file_put_contents('console.txt', date('d/m/Y h:i:s') . ' - SUCE - ' . "Visualização criada, usuario " . $idUsuario  . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents('console.txt', date('d/m/Y h:i:s') . ' - ERRO - ' . "Visualização não criada, usuario - " . $idUsuario  . PHP_EOL, FILE_APPEND);
    }
}
