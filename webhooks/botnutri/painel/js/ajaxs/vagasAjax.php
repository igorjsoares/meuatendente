<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("../dados_conexao.php");

$acao = filter_var($_POST['acao'], FILTER_SANITIZE_STRING);


switch ($acao) {
    case 'createLeito':
        $dados = $_POST['dados'];

        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $idVaga = filter_var(@$dados['idVaga'], FILTER_SANITIZE_STRING);
        $tipo = filter_var(@$dados['tipo'], FILTER_SANITIZE_STRING);
        $codigo = strtoupper(filter_var($dados['codigo'], FILTER_SANITIZE_STRING));
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

        if ($tipo == 'edit') {
            $sql = "UPDATE tbl_leitos SET codigo = '$codigo', categoria = '$categoria', isolamento_contato = '$isolamentoContato', isolamento_aerosois = '$isolamentoAerosois', isolamento_goticulas = '$isolamentoGoticulas', ventilacao_mecanica = '$ventilacaoMecanica', ventilacao_mascara = '$ventilacaoMascara', ventilacao_cateter = '$ventilacaoCateter', droga = '$droga', acesso_avp = '$acessoAvp', acesso_cvc = '$acessoCvc', acesso_picc = '$acessoPicc', sedacao = '$sedacao', update_at = NOW() WHERE id_leito = $idVaga";
        } else {
            $sql = "INSERT INTO tbl_leitos (id_empresa, codigo, categoria, isolamento_contato, isolamento_aerosois, isolamento_goticulas, ventilacao_mecanica, ventilacao_mascara, ventilacao_cateter, droga, acesso_avp, acesso_cvc, acesso_picc, sedacao, especialidades, status, update_at, create_at) VALUES ($idEmpresa, '$codigo', $categoria, $isolamentoContato, $isolamentoAerosois, $isolamentoGoticulas, $ventilacaoMecanica, $ventilacaoMascara, $ventilacaoCateter, $droga, $acessoAvp, $acessoCvc, $acessoPicc, $sedacao, '', 1, NOW(), NOW())";
        }
        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo "1";
        } else {
            echo mysqli_error_list(($conn['link']))[0]['errno'];
        }

        break;

    case 'bloquearLeito':
        $dados = $_POST['dados'];

        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idVaga = filter_var($dados['idVaga'], FILTER_SANITIZE_STRING);
        $tipoBloqueio = filter_var($dados['tipoBloqueio'], FILTER_SANITIZE_STRING);

        $sql = "UPDATE tbl_leitos SET status = '$tipoBloqueio', update_at = NOW() WHERE id_leito = $idVaga";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo "1";
        } else {
            echo "Não foi possível bloquear esse usuário.";
        }

        break;

    case 'finalizarLeito':
        $dados = $_POST['dados'];

        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idVaga = filter_var(@$dados['idVaga'], FILTER_SANITIZE_STRING);
        $tipoBloqueio = filter_var(@$dados['tipoBloqueio'], FILTER_SANITIZE_STRING);
        $idSolicitacao = filter_var($dados['idSolicitacao'], FILTER_SANITIZE_STRING);

        $sql = "UPDATE tbl_solicitacoes SET status = 4, update_at = NOW() WHERE id_solicitacao = $idSolicitacao";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo "1";
        } else {
            echo "Não foi possível bloquear esse usuário.";
            //echo $sql;
        }

        break;

    case 'excluirVaga':
        $dados = $_POST['dados'];

        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idVaga = filter_var($dados['idVaga'], FILTER_SANITIZE_STRING);

        $sql = "UPDATE tbl_leitos SET status = 9, update_at = NOW() WHERE id_leito = $idVaga";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo "1";
        } else {
            echo "Não foi possível bloquear esse usuário.";
        }

        break;

    case 'consultaConsolidadoVagas':
        $dados = $_POST['dados'];
        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);

        $totalUti = 0;
        $totalEnf = 0;
        $totalPso = 0;
        $totalUtiQuantidade = 0;
        $totalEnfQuantidade = 0;
        $totalPsoQuantidade = 0;
        $bloqueadasUtiQuantidade = 0;
        $bloqueadasEnfQuantidade = 0;
        $bloqueadasPsoQuantidade = 0;

        //Consultas apenas livres
        $sql = "SELECT categoria, COUNT(id_leito) AS total FROM tbl_leitos WHERE id_empresa = $idEmpresa AND status = 1 GROUP BY categoria";
        //echo $sql;
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        while ($categoria = mysqli_fetch_array($query)) {
            switch ($categoria['categoria']) {
                case '1':
                    $totalUtiQuantidade += $categoria['total'];
                    break;
                case '2':
                    $totalEnfQuantidade += $categoria['total'];
                    break;
                case '3':
                    $totalPsoQuantidade += $categoria['total'];
                    break;
            }
        }

        //Consultas apenas bloqueadas
        $sql = "SELECT categoria, COUNT(id_leito) AS total FROM tbl_leitos WHERE id_empresa = $idEmpresa AND status = 0 GROUP BY categoria";
        //echo $sql;
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        while ($categoria = mysqli_fetch_array($query)) {
            switch ($categoria['categoria']) {
                case '1':
                    $bloqueadasUtiQuantidade += $categoria['total'];
                    break;
                case '2':
                    $bloqueadasEnfQuantidade += $categoria['total'];
                    break;
                case '3':
                    $bloqueadasPsoQuantidade += $categoria['total'];
                    break;
            }
        }



        //Consulta solicitações da empresa aceitas
        $sql = "SELECT categoria, count(id_solicitacao) AS total FROM tbl_solicitacoes WHERE id_empresa = $idEmpresa AND status = 2 GROUP BY categoria";
        //echo $sql;
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);


        while ($categoria = mysqli_fetch_array($query)) {
            switch ($categoria['categoria']) {
                case '1':
                    $totalUtiQuantidade -= $categoria['total'];
                    break;
                case '2':
                    $totalEnfQuantidade -= $categoria['total'];
                    break;
                case '3':
                    $totalPsoQuantidade -= $categoria['total'];
                    break;
            }
        }



        //Total de vagas
        $sql = "SELECT categoria, COUNT(id_leito) AS total FROM tbl_leitos WHERE id_empresa = $idEmpresa AND status != 9 GROUP BY categoria";
        //echo $sql;
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        //Consulta total de vagas
        while ($categoria = mysqli_fetch_array($query)) {
            switch ($categoria['categoria']) {
                case '1':
                    $totalUti = $categoria['total'];
                    break;
                case '2':
                    $totalEnf = $categoria['total'];
                    break;
                case '3':
                    $totalPso = $categoria['total'];
                    break;
            }
        }

        $arrayCategoria = [];

        array_push($arrayCategoria, array(
            'categoria' => 1,
            'livres' => $totalUtiQuantidade,
            'bloqueadas' => $bloqueadasUtiQuantidade,
            'total' => $totalUti
        ));
        array_push($arrayCategoria, array(
            'categoria' => 2,
            'livres' => $totalEnfQuantidade,
            'bloqueadas' => $bloqueadasEnfQuantidade,
            'total' => $totalEnf
        ));
        array_push($arrayCategoria, array(
            'categoria' => 3,
            'livres' => $totalPsoQuantidade,
            'bloqueadas' => $bloqueadasPsoQuantidade,
            'total' => $totalPso
        ));

        //var_dump($arrayCategoria);


        echo json_encode($arrayCategoria, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);



        break;

    case 'consultaVagasFiltrada':
        $dados = $_POST['dados'];
        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $filtroUti = filter_var($dados['filtroUti'], FILTER_SANITIZE_STRING);
        $filtroEnf = filter_var($dados['filtroEnf'], FILTER_SANITIZE_STRING);
        $filtroPso = filter_var($dados['filtroPso'], FILTER_SANITIZE_STRING);

        $where = '';
        if ($filtroUti == 1) {
            $where .= ' l.categoria = 1 ';
        }
        if ($filtroEnf == 1) {
            if($where != ''){
                $where .= ' OR ';
            }
            $where .= ' l.categoria = 2 ';
        }
        if ($filtroPso == 1) {
            if($where != ''){
                $where .= ' OR ';
            }
            $where .= ' l.categoria = 3 ';
        }
        if($where != ''){
            $where = ' AND ('.$where.') ';
        }

        $sql = "SELECT l.*, s.id_solicitacao, s.status AS status_solicitacao, s.nome_paciente, s.cpf FROM tbl_leitos l LEFT JOIN tbl_solicitacoes s ON l.id_leito = s.id_leito AND l.id_empresa = s.id_empresa AND s.status = 2 WHERE l.id_empresa = $idEmpresa AND l.status != 9 $where ORDER BY l.id_leito DESC";

        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayVagas = [];
        while ($vaga = mysqli_fetch_array($query)) {

            array_push($arrayVagas, array(
                'categoria' => $vaga['categoria'],
                'cpf' => $vaga['cpf'],
                'nome_paciente' => $vaga['nome_paciente'],
                'id_solicitacao' => $vaga['id_solicitacao'],
                'codigo' => $vaga['codigo'],
                'id_leito' => $vaga['id_leito'],
                'codigo' => $vaga['codigo'],
                'isolamento_contato' => $vaga['isolamento_contato'],
                'isolamento_aerosois' => $vaga['isolamento_aerosois'],
                'isolamento_goticulas' => $vaga['isolamento_goticulas'],
                'ventilacao_mecanica' => $vaga['ventilacao_mecanica'],
                'ventilacao_mascara' => $vaga['ventilacao_mascara'],
                'ventilacao_cateter' => $vaga['ventilacao_cateter'],
                'acesso_avp' => $vaga['acesso_avp'],
                'acesso_cvc' => $vaga['acesso_cvc'],
                'acesso_picc' => $vaga['acesso_picc'],
                'droga' => $vaga['droga'],
                'sedacao' => $vaga['sedacao'],
                'status' => utf8_encode($vaga['status']),
                'status_solicitacao' => $vaga['status_solicitacao']
            ));
        }
        if ($numRow != 0) {
            echo json_encode($arrayVagas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
            //echo $sql;
        } else {
            echo "Não encontrado";
            //echo $sql;
        }


        break;

    default:
        # code...
        break;
}
