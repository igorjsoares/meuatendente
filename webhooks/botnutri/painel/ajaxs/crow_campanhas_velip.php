<?php

include "../dados_conexao.php";

logSis('DEB', 'Iniciando processo de Créditos Velip');


//* Consulta das campanhas finaliadas e ainda não processadas o crédito
$sql = "SELECT cv.*, c.valor AS creditos FROM tbl_velip_campanhas cv, tbl_creditos c WHERE cv.id_campanha_velip = c.referencia AND c.tipo = 2 AND cv.fim < NOW() AND cv.creditos = 0";
$query = mysqli_query($conn['link'], $sql);

$numCampanhasEncontradas = mysqli_num_rows($query);
$numCampanhasAlteradas = 0;

while ($conta = mysqli_fetch_array($query)) {
    $idCampanha = $conta['id_campanha'];
    $creditos = $conta['creditos'];
    $idVelip = $conta['id_campanha_velip'];

    $creditosConsumidos = buscaDadosVelip($idVelip);

    if ($creditosConsumidos != 'ERRO' && $creditosConsumidos != $creditos) {
        if (atualizaCreditos($idVelip, $creditosConsumidos) == 1) {
            if (atualizaCampanha($idCampanha) == 1) {
                $numCampanhasAlteradas += 1;
            };
        }
    }
}

logSis('DEB','Finalizando processo de Créditos Velip. Alteradas '.$numCampanhasAlteradas.' de '.$numCampanhasEncontradas.' encontradas.');


//* Busca de dados dentro do fornecedor VELIP 
function buscaDadosVelip($idVelip)
{
    $target_url = "https://vox.velip.com.br/pop/torpedo/ChangeCampaign.php";
    $post = array("user" => "resolv", "password" => "velocidade", 'cp_id' => $idVelip);

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
        logSis('ERR', 'Não conseguiu consultar a campanha na VELIP. Resultado: ' . $idVelip);

        return "ERRO";
    } else {
        curl_close($ch);

        $xml = simplexml_load_string($resultVelip);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);

        return $array['campaing']['cp_answered'];
    }
}

//* Atualização da tabela de créditos com a quantidade consumida 
function atualizaCreditos($idVelip, $creditos)
{
    include '../dados_conexao.php';

    $creditos = -abs($creditos);

    $sql = "UPDATE tbl_creditos SET valor = $creditos WHERE referencia = '$idVelip' AND tipo = 2";

    $query = mysqli_query($conn['link'], $sql);
    $linhasAfetadas = mysqli_affected_rows($conn['link']);

    if ($query != true && $linhasAfetadas == 0) {
        return 0;
        logSis('ERR', 'Não conseguiu atualizar os créditos . Resultado: ' . $idVelip);
    } else {
        return 1;
    }
}

//* Atualiação da tabela campanha informando a finalização do processo 
function atualizaCampanha($idCampanha)
{
    include '../dados_conexao.php';

    $sql = "UPDATE tbl_velip_campanhas SET creditos = 1 WHERE id_campanha = '$idCampanha'";

    $query = mysqli_query($conn['link'], $sql);
    $linhasAfetadas = mysqli_affected_rows($conn['link']);

    if ($query != true && $linhasAfetadas == 0) {
        return 0;
        logSis('ERR', 'Não conseguiu atualizar a campanha, nos créditos . Resultado: ' . $idCampanha);
    } else {
        return 1;
    }
}

//* Função de LOG
function logSis($tipo, $texto)
{
    file_put_contents('log.txt', "> " . $tipo . " " . date('d/m/Y h:i:s') . " "  . $texto . PHP_EOL, FILE_APPEND);
}
