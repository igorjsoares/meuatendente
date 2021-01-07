<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("../dados_conexao.php");

$acao = filter_var($_POST['acao'], FILTER_SANITIZE_STRING);


switch ($acao) {
    case 'negarSolicitacao':
        $dados = $_POST['dados'];

        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idSolicitacao = filter_var($dados['idSolicitacao'], FILTER_SANITIZE_STRING);
        $motivo = filter_var($dados['motivo'], FILTER_SANITIZE_STRING);
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $idSolicitante = filter_var($dados['idSolicitante'], FILTER_SANITIZE_STRING);

        $sql = "UPDATE tbl_solicitacoes SET status = 3, motivo = '$motivo', update_at = NOW() WHERE id_solicitacao = $idSolicitacao AND status = 1";

        $query = mysqli_query($conn['link'], $sql);
        $linhasAfetadas = mysqli_affected_rows($conn['link']);

        if ($query == true && $linhasAfetadas != 0) {
            echo "1";
            criarAcao($idUsuario, $idSolicitacao, 3, false);
            chamarNotificacao($idSolicitacao, $idSolicitante, 3, false);
        } else {
            echo "Não foi possível negar a solicitação.";
        }

        break;

    case 'finalizarSolicitacao':
        $dados = $_POST['dados'];

        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idSolicitacao = filter_var($dados['idSolicitacao'], FILTER_SANITIZE_STRING);

        $sql = "UPDATE tbl_solicitacoes SET status = 4, update_at = NOW() WHERE id_solicitacao = $idSolicitacao";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo "1";
            criarAcao($idUsuario, $idSolicitacao, 4, false);
        } else {
            echo "Não foi possível realizar essa ação.";
        }

        break;

    case 'reservarVaga':
        $dados = $_POST['dados'];

        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idSolicitacao = filter_var($dados['idSolicitacao'], FILTER_SANITIZE_STRING);
        $idLeito = filter_var($dados['idLeito'], FILTER_SANITIZE_STRING);
        $outrasVagas = filter_var($dados['outrasVagas'], FILTER_SANITIZE_STRING);
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $idSolicitante = filter_var($dados['idSolicitante'], FILTER_SANITIZE_STRING);

        $sql = "UPDATE tbl_solicitacoes SET id_leito = $idLeito, status = 2, outras = $outrasVagas, update_at = NOW() WHERE id_solicitacao = $idSolicitacao AND status = 1";

        $query = mysqli_query($conn['link'], $sql);
        $linhasAfetadas = mysqli_affected_rows($conn['link']);

        if ($query == true && $linhasAfetadas != 0) {
            echo "1";
            criarAcao($idUsuario, $idSolicitacao, 2, false);
            chamarNotificacao($idSolicitacao, $idSolicitante, 2, false);
        } else {
            echo "Não foi possível realziar esta ação";
            //echo $sql;
        }

        break;

    case 'consultaVagas':
        $dados = $_POST['dados'];
        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $categoria = filter_var($dados['categoria'], FILTER_SANITIZE_STRING);
        $isolamentoContato = filter_var($dados['isolamentoContato'], FILTER_SANITIZE_STRING);
        $isolamentoAerosois = filter_var($dados['isolamentoAerosois'], FILTER_SANITIZE_STRING);
        $isolamentoGoticulas = filter_var($dados['isolamentoGoticulas'], FILTER_SANITIZE_STRING);
        $ventilacaoMecanica = filter_var($dados['ventilacaoMecanica'], FILTER_SANITIZE_STRING);
        $ventilacaoMascara = filter_var($dados['ventilacaoMascara'], FILTER_SANITIZE_STRING);
        $ventilacaoCateter = filter_var($dados['ventilacaoCateter'], FILTER_SANITIZE_STRING);
        $acessoAvp = filter_var($dados['acessoAvp'], FILTER_SANITIZE_STRING);
        $acessoCvc = filter_var($dados['acessoCvc'], FILTER_SANITIZE_STRING);
        $acessoPicc = filter_var($dados['acessoPicc'], FILTER_SANITIZE_STRING);
        $droga = filter_var($dados['droga'], FILTER_SANITIZE_STRING);
        $sedacao = filter_var($dados['sedacao'], FILTER_SANITIZE_STRING);

        if ($isolamentoContato == 1) {
            $isolamentoContato = 1;
        } else {
            $isolamentoContato = "'%'";
        };
        if ($isolamentoAerosois == 1) {
            $isolamentoAerosois = 1;
        } else {
            $isolamentoAerosois = "'%'";
        };
        if ($isolamentoGoticulas == 1) {
            $isolamentoGoticulas = 1;
        } else {
            $isolamentoGoticulas = "'%'";
        };
        if ($ventilacaoMecanica == 1) {
            $ventilacaoMecanica = 1;
        } else {
            $ventilacaoMecanica = "'%'";
        };
        if ($ventilacaoMascara == 1) {
            $ventilacaoMascara = 1;
        } else {
            $ventilacaoMascara = "'%'";
        };
        if ($ventilacaoCateter == 1) {
            $ventilacaoCateter = 1;
        } else {
            $ventilacaoCateter = "'%'";
        };
        if ($acessoAvp == 1) {
            $acessoAvp = 1;
        } else {
            $acessoAvp = "'%'";
        };
        if ($acessoCvc == 1) {
            $acessoCvc = 1;
        } else {
            $acessoCvc = "'%'";
        };
        if ($acessoPicc == 1) {
            $acessoPicc = 1;
        } else {
            $acessoPicc = "'%'";
        };
        if ($droga == 1) {
            $droga = 1;
        } else {
            $droga = "'%'";
        };
        if ($sedacao == 1) {
            $sedacao = 1;
        } else {
            $sedacao = "'%'";
        };

        $whereDetalhes = "AND isolamento_contato LIKE $isolamentoContato AND isolamento_aerosois LIKE $isolamentoAerosois AND isolamento_goticulas LIKE $isolamentoGoticulas AND ventilacao_mecanica LIKE $ventilacaoMecanica AND ventilacao_mascara LIKE $ventilacaoMascara AND ventilacao_cateter LIKE $ventilacaoCateter AND droga LIKE $droga AND acesso_avp LIKE $acessoAvp AND acesso_cvc LIKE $acessoCvc AND acesso_picc LIKE $acessoPicc AND sedacao LIKE $sedacao";

        $sql = "SELECT id_leito, codigo FROM tbl_leitos WHERE id_empresa = $idEmpresa AND categoria = $categoria AND status = 1 $whereDetalhes";
        //echo $sql;
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayLeitos = [];
        while ($leitos = mysqli_fetch_array($query)) {
            //array_push($arrayLeitos, utf8_encode($leitos['id_leito']));
            array_push($arrayLeitos, array(
                'id_leito' => $leitos['id_leito'],
                'codigo' => $leitos['codigo']
            ));
        }
        //print_r($arrayLeitos);
        $sql = "SELECT s.id_leito, l.codigo FROM tbl_solicitacoes s, tbl_leitos l WHERE s.id_leito = l.id_leito AND l.id_empresa = $idEmpresa AND (s.status = 1 OR s.status = 2)";
        $query = mysqli_query($conn['link'], $sql);
        $numRowOcupados = mysqli_num_rows($query);
        //echo $sql;
        $arrayLeitosOcupados = [];
        while ($ocupados = mysqli_fetch_array($query)) {
            //array_push($arrayLeitosOcupados, utf8_encode($ocupados['id_leito']));
            array_push($arrayLeitosOcupados, array(
                'id_leito' => $ocupados['id_leito'],
                'codigo' => $ocupados['codigo']
            ));
        }


        //$arrayLeitosDisponiveis = array_diff($arrayLeitos['id_leito'], $arrayLeitosOcupados['id_leito']);
        $arrayLeitosDisponiveisTemp = [];
        $arrayLeitosDisponiveisTemp = array_diff(array_map('serialize', $arrayLeitos), array_map('serialize', $arrayLeitosOcupados));
        $arrayLeitosDisponiveis = array_map('unserialize', $arrayLeitosDisponiveisTemp);
        $arrayLeitosDisponiveis = array_values($arrayLeitosDisponiveis);

        /* echo '<pre>';
        print_r($results);

        echo "<BR>";
        var_dump($arrayLeitos);
        echo "<BR>";
        var_dump($arrayLeitosOcupados);
        echo "<BR>";
        var_dump($arrayLeitosDisponiveis);
        echo "<BR>"; */

        echo json_encode($arrayLeitosDisponiveis, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);


        //echo count($arrayLeitosDisponiveis);


        break;

    case 'consultaVaga': //Para compor o Callout de outras
        $dados = $_POST['dados'];
        $idVaga = filter_var($dados['idVaga'], FILTER_SANITIZE_STRING);

        $sql = "SELECT * FROM tbl_leitos WHERE id_leito = $idVaga";
        //echo $sql;
        $query = mysqli_query($conn['link'], $sql);

        $arrayExigencias = [];
        while ($exigencias = mysqli_fetch_array($query)) {
            if ($exigencias['isolamento_contato'] == 0) {
                array_push($arrayExigencias, "Isolamento Contato");
            }
            if ($exigencias['isolamento_aerosois'] == 0) {
                array_push($arrayExigencias, "Isolamento Aerosóis");
            }
            if ($exigencias['isolamento_goticulas'] == 0) {
                array_push($arrayExigencias, "Isolamento Gotículas");
            }
            if ($exigencias['ventilacao_mecanica'] == 0) {
                array_push($arrayExigencias, "Ventilação Mecânica");
            }
            if ($exigencias['ventilacao_mascara'] == 0) {
                array_push($arrayExigencias, "Ventilação Máscara");
            }
            if ($exigencias['ventilacao_cateter'] == 0) {
                array_push($arrayExigencias, "Ventilação Cateter");
            }
            if ($exigencias['acesso_avp'] == 0) {
                array_push($arrayExigencias, "Acesso CVC");
            }
            if ($exigencias['acesso_cvc'] == 0) {
                array_push($arrayExigencias, "Acesso AVP");
            }
            if ($exigencias['acesso_picc'] == 0) {
                array_push($arrayExigencias, "Acesso PICC");
            }
            if ($exigencias['droga'] == 0) {
                array_push($arrayExigencias, "Droga (DVA)");
            }
            if ($exigencias['sedacao'] == 0) {
                array_push($arrayExigencias, "Sedação");
            }
        }
        echo json_encode($arrayExigencias, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'consultaConsolidadoVagas':
        $dados = $_POST['dados'];
        $idUsuario = filter_var(@$dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $tipoEmpresa = filter_var($dados['tipoEmpresa'], FILTER_SANITIZE_STRING);
        $notificacoes = filter_var($dados['notificacoes'], FILTER_SANITIZE_STRING);

        if ($tipoEmpresa == 1) { //anunciante
            $where = 'id_empresa = ' . $idEmpresa;
        } else {
            $where = 'id_solicitante = ' . $idEmpresa;
        }

        $sql = "SELECT status, COUNT(id_solicitacao) AS total FROM tbl_solicitacoes WHERE $where GROUP BY status";
        //echo $sql;
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayStatus = [];
        while ($status = mysqli_fetch_array($query)) {
            //array_push($arrayStatus, utf8_encode($Status['id_leito']));
            array_push($arrayStatus, array(
                'status' => $status['status'],
                'total' => $status['total']
            ));
        }


        echo json_encode($arrayStatus, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
        //echo $notificacoes;
        if ($notificacoes == 1) {
            chamarVisualizacao($idEmpresa, $idUsuario);
        }

        break;

        //* CONSULTA DO CONSOLIDADO DE SOLICITAÇÕES 
    case 'consultaNegadas24h':
        $dados = $_POST['dados'];
        $idUsuario = filter_var(@$dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $tipoEmpresa = filter_var($dados['tipoEmpresa'], FILTER_SANITIZE_STRING);
        $notificacoes = filter_var($dados['notificacoes'], FILTER_SANITIZE_STRING);

        if ($tipoEmpresa == 1) { //anunciante
            $where = 'id_empresa = ' . $idEmpresa;
        } else {
            $where = 'id_solicitante = ' . $idEmpresa;
        }

        $sql = "SELECT status, COUNT(id_solicitacao) AS total FROM tbl_solicitacoes WHERE $where AND status = 3 AND update_at >= NOW() - INTERVAL 24 HOUR GROUP BY status";
        //echo $sql;
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayStatus = [];
        while ($status = mysqli_fetch_array($query)) {
            //array_push($arrayStatus, utf8_encode($Status['id_leito']));
            array_push($arrayStatus, array(
                'status' => $status['status'],
                'total' => $status['total']
            ));
        }


        echo json_encode($arrayStatus, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
        //echo $notificacoes;
        if ($notificacoes == 1) {
            chamarVisualizacao($idEmpresa, $idUsuario);
        }

        break;

        //* CONSOLIDADO ÚLTIMOS REGISTROS DE ACORDO COM A SELEÇÃO 
    case 'consultaUltimasTabelas':
        $dados = $_POST['dados'];
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $tipoEmpresa = filter_var($dados['tipoEmpresa'], FILTER_SANITIZE_STRING);
        $quantidade = filter_var($dados['quantidade'], FILTER_SANITIZE_STRING);
        $status = filter_var($dados['status'], FILTER_SANITIZE_STRING);

        if ($tipoEmpresa == 1) { //anunciante
            $where = 's.id_empresa = ' . $idEmpresa. ' AND s.id_empresa = e.id_empresa';
        } else {
            $where = 's.id_solicitante = ' . $idEmpresa. ' AND s.id_solicitante = e.id_empresa';
        }

        $sql = "SELECT s.id_solicitacao, s.categoria, s.nome_paciente, e.nome AS nome_empresa FROM tbl_solicitacoes s, tbl_empresas e  WHERE $where AND s.status = $status ORDER BY s.update_at DESC LIMIT $quantidade";

        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arraySolicitacoes = [];
        while ($solicitacao = mysqli_fetch_array($query)) {

            array_push($arraySolicitacoes, array(
                'idSolicitacao' => $solicitacao['id_solicitacao'],
                'categoria' => $solicitacao['categoria'],
                'nomePaciente' => $solicitacao['nome_paciente'],
                'nomeEmpresa' => $solicitacao['nome_empresa']
            ));
        }
        if ($numRow != 0) {
            echo json_encode($arraySolicitacoes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
            //echo $sql;
        } else {
            echo "Não encontrado";
            //echo $sql;
        }


        break;

        //* CONSOLIDADO 10 PRMEIROS PARA HOME
    case 'consultaConsolidadoSolicitacoesDez':
        $dados = $_POST['dados'];
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);

        $sql = "SELECT s.id_solicitacao, s.categoria, s.nome_paciente, e.nome AS nome_empresa FROM tbl_solicitacoes s, tbl_empresas e  WHERE s.id_solicitante = e.id_empresa AND s.id_solicitante = $idEmpresa AND s.status = 2 ORDER BY s.update_at DESC LIMIT 10";

        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arraySolicitacoes = [];
        while ($solicitacao = mysqli_fetch_array($query)) {

            array_push($arraySolicitacoes, array(
                'idSolicitacao' => $solicitacao['id_solicitacao'],
                'categoria' => $solicitacao['categoria'],
                'nomePaciente' => $solicitacao['nome_paciente'],
                'nomeEmpresa' => $solicitacao['nome_empresa']
            ));
        }
        if ($numRow != 0) {
            echo json_encode($arraySolicitacoes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
            //echo $sql;
        } else {
            echo "Não encontrado";
            //echo $sql;
        }


        break;




    case 'consultaSolicitacoesStatus':
        $dados = $_POST['dados'];
        $idSolicitacao = filter_var($dados['idSolicitacao'], FILTER_SANITIZE_STRING);

        $sql = "SELECT status FROM tbl_solicitacoes WHERE id_solicitacao = $idSolicitacao";
        //echo $sql;
        $query = mysqli_query($conn['link'], $sql);
        //$numRow = mysqli_num_rows($query);

        $arrayStatus = [];
        while ($status = mysqli_fetch_array($query)) {

            array_push($arrayStatus, array(
                'status' => $status['status']
            ));
        }


        echo json_encode($arrayStatus, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'consultaAcoes':
        $dados = $_POST['dados'];
        $idSolicitacao = filter_var($dados['idSolicitacao'], FILTER_SANITIZE_STRING);

        $sql = "SELECT a.id_usuario, a.acao, DATE_FORMAT(a.hora, '%d/%m/%Y %H:%i') AS hora_formatada, u.nome AS nome_usuario FROM tbl_acoes a, tbl_usuarios u WHERE a.id_usuario = u.id_usuario AND a.id_solicitacao = $idSolicitacao ORDER BY a.hora ASC";
        //echo $sql;
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayAcao = [];
        while ($acao = mysqli_fetch_array($query)) {
            $arrayNome = explode(" ", $acao['nome_usuario']);

            $nomeUsuario = $arrayNome[0] . " " . $arrayNome[count($arrayNome) - 1];

            array_push($arrayAcao, array(
                'idUsuario' => $acao['id_usuario'],
                'acao' => $acao['acao'],
                'horaFormatada' => $acao['hora_formatada'],
                'nomeUsuario' => $nomeUsuario
            ));
        }


        echo json_encode($arrayAcao, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;
}

function criarAcao($idUsuario, $idSolicitacao, $acao, $externa)
{
    if ($externa == true) {
        file_put_contents('debug.txt', "> DEBUG " . date('d/m/Y h:i:s') . ' Entrou na criação da Ação com os campos -> idUsuario: ' . $idUsuario . ' idSolicitacao: ' . $idSolicitacao . ' Ação: ' . $acao . PHP_EOL, FILE_APPEND);
        include("dados_conexao.php");
    } else {
        include("../dados_conexao.php");
    }
    $sql = "INSERT INTO tbl_acoes (id_usuario, id_solicitacao, acao, hora) VALUES ('$idUsuario', '$idSolicitacao', '$acao', NOW())";

    $query = mysqli_query($conn['link'], $sql);
    if ($query == true) {
        //!Coloca no log
        file_put_contents('debug.txt', "> DEBUG " . date('d/m/Y h:i:s') . ' Segundo ele deu certo kkk: ' . PHP_EOL, FILE_APPEND);
    } else {
        //!Coloca erro no log_erros
        file_put_contents('debug.txt', "> DEBUG " . date('d/m/Y h:i:s') . ' MysqlError: ' . mysqli_connect_error() . " : " . mysqli_errno($conn['link']) . ": " . mysqli_error($conn['link']) . PHP_EOL, FILE_APPEND);
    }
}

function chamarNotificacao($idSolicitacao, $idEmpresa, $acao, $externa)
{
    include("notificacoesAjax.php");
    criarNotificacao($idSolicitacao, $idEmpresa, $acao, $externa);
}
function chamarVisualizacao($idEmpresa, $idUsuario)
{
    include("notificacoesAjax.php");
    criarVisualizacao($idEmpresa, $idUsuario);
}
