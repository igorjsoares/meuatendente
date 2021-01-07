<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("../dados_conexao.php");

$acao = filter_var($_POST['acao'], FILTER_SANITIZE_STRING);


switch ($acao) {
    case 'consultaEmpresas':
        $dados = $_POST['dados'];
        $especialidades = filter_var($dados['especialidades'], FILTER_SANITIZE_URL);
        $cidade = filter_var($dados['cidade'], FILTER_SANITIZE_STRING);


        if ($especialidades != "") {
            $whereEspecialidades = '';
            $arrayEspecialidades = json_decode($especialidades, true);

            foreach ($arrayEspecialidades as $especialidade) {
                $valorEspecialidade = '%"' . $especialidade . '"%';
                $whereEspecialidades .=  " AND especialidades NOT LIKE '$valorEspecialidade'";
            }
        } else {
            $whereEspecialidades = '';
        }

        //var_dump($whereEspecialidades);

        $sql = "SELECT id_empresa, nome, endereco, latlong, id_contato, numero_contato FROM tbl_empresas WHERE tipo = 1 AND status = 1 AND cidade = '$cidade' $whereEspecialidades";

        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayEmpresas = [];
        while ($empresa = mysqli_fetch_array($query)) {

            array_push($arrayEmpresas, array(
                'id_empresa' => $empresa['id_empresa'],
                'nome' => $empresa['nome'],
                'endereco' => utf8_encode($empresa['endereco']),
                'latlong' => $empresa['latlong'],
                'id_contato' => $empresa['id_contato'],
                'numero_contato' => $empresa['numero_contato']
            ));
        }
        if ($numRow != 0) {
            echo json_encode($arrayEmpresas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);
        } else {
            echo "Não encontrado";
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

        //) Consulta quais são os leitos disponíveis dentro das especificações da consulta 
        $sql = "SELECT id_leito FROM tbl_leitos WHERE id_empresa = $idEmpresa AND categoria = $categoria AND status = 1 $whereDetalhes";
        //echo '<BR>'.$sql.'<BR>';
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        $arrayLeitos = [];
        while ($leitos = mysqli_fetch_array($query)) {
            array_push($arrayLeitos, utf8_encode($leitos['id_leito']));
        }

        //) Consulta quais leitos dessa empresa estão reservados 
        $sql = "SELECT s.id_leito FROM tbl_solicitacoes s, tbl_leitos l WHERE s.id_leito = l.id_leito AND l.id_empresa = $idEmpresa AND s.status = 2";
        $query = mysqli_query($conn['link'], $sql);
        $numRowOcupados = mysqli_num_rows($query);
        //echo '<BR>'.$sql.'<BR>';

        $arrayLeitosOcupados = [];
        while ($ocupados = mysqli_fetch_array($query)) {
            array_push($arrayLeitosOcupados, utf8_encode($ocupados['id_leito']));
        }

        //) Consulta quantas solicitações pendente nessa empresa tem dentro do detalhamento da consulta 
        $sql = "SELECT id_solicitacao FROM tbl_solicitacoes WHERE status = 1 AND id_empresa = $idEmpresa AND categoria = $categoria"; //retirado do final $whereDetalhes
        $query = mysqli_query($conn['link'], $sql);
        $numRowPendentes = mysqli_num_rows($query);
        //echo '<BR>'.$sql.'<BR>';

        $arrayLeitosPendentes = [];
        while ($pendentes = mysqli_fetch_array($query)) {
            array_push($arrayLeitosPendentes, utf8_encode($pendentes['id_solicitacao']));
        }
        //var_dump($arrayLeitosPendentes);

        //Traz somente os leitos que não estão reservados
        $arrayLeitosDisponiveis = array_diff($arrayLeitos, $arrayLeitosOcupados);

        //echo count($arrayLeitosDisponiveis);

        //Envia a quantidade de leitos disponíveis, menos a quantidade de solicitações dessa categoria pendentes nessa empresa
        echo (count($arrayLeitosDisponiveis) - count($arrayLeitosPendentes));


        break;

    case 'createSolicitacao':
        $dados = $_POST['dados'];

        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $idSolicitante = filter_var($dados['idSolicitante'], FILTER_SANITIZE_STRING);
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
        $nome = strtoupper(filter_var($dados['nome'], FILTER_SANITIZE_STRING));
        $cpf = filter_var($dados['cpf'], FILTER_SANITIZE_STRING);
        $aereas = filter_var($dados['aereas'], FILTER_SANITIZE_STRING);
        $pa = filter_var($dados['pa'], FILTER_SANITIZE_STRING);
        $fc = filter_var($dados['fc'], FILTER_SANITIZE_STRING);
        $fr = filter_var($dados['fr'], FILTER_SANITIZE_STRING);
        $spoz = filter_var($dados['spoz'], FILTER_SANITIZE_STRING);
        $hgt = filter_var($dados['hgt'], FILTER_SANITIZE_STRING);
        $consciencia = filter_var($dados['consciencia'], FILTER_SANITIZE_STRING);
        $cid = @$dados['cid']; //!Preciso encontrar um SANITIZE para o CID  que considere os caracteres e os espaços
        $quadro = filter_var($dados['quadro'], FILTER_SANITIZE_STRING);

        $sql = "INSERT INTO tbl_solicitacoes (id_empresa, id_leito, id_solicitante, categoria, isolamento_contato, isolamento_aerosois, isolamento_goticulas, ventilacao_mecanica, ventilacao_mascara, ventilacao_cateter, acesso_avp, acesso_cvc, acesso_picc, droga, sedacao, nome_paciente, cpf, aereas, pa, fc, fr, spoz, hgt, consciencia, cid, quadro, status, update_at, create_at) VALUES ('$idEmpresa', '', '$idSolicitante', '$categoria', $isolamentoContato, $isolamentoAerosois, $isolamentoGoticulas, $ventilacaoMecanica, $ventilacaoMascara, $ventilacaoCateter, $acessoAvp, $acessoCvc, $acessoPicc, $droga, $sedacao, '$nome', '$cpf', '$aereas', '$pa', '$fc', '$fr', '$spoz', '$hgt', '$consciencia', '$cid', '$quadro', 1, NOW(), NOW())";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            $idSolicitacao = mysqli_insert_id($conn['link']);
            file_put_contents('console.txt', date('d/m/Y h:i:s') . ' - ' . "Solicitação criada - " . $idSolicitacao . PHP_EOL, FILE_APPEND);

            $arrayResultado = [];
            array_push($arrayResultado, array(
                'resultado' => '1',
                'id_solicitacao' => $idSolicitacao
            ));
            echo json_encode($arrayResultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);


            criarAcao($idUsuario, $idSolicitacao, 1);
            chamarNotificacao($idSolicitacao, $idEmpresa, 1, false);
        } else {
            echo "Não foi possível inserir.";
            //echo $sql;
        }

        break;



    default:
        # code...
        break;
}

function criarAcao($idUsuario, $idSolicitacao, $acao)
{
    include("../dados_conexao.php");

    $sql = "INSERT INTO tbl_acoes (id_usuario, id_solicitacao, acao, hora) VALUES ('$idUsuario', '$idSolicitacao', '$acao', NOW())";

    $query = mysqli_query($conn['link'], $sql);
    if ($query == true) {
        //!Coloca no log
    } else {
        //!Coloca erro no log_erros
    }
}

function chamarNotificacao($idSolicitacao, $idEmpresa, $acao, $externa)
{
    include("notificacoesAjax.php");
    criarNotificacao($idSolicitacao, $idEmpresa, $acao, $externa);
}
