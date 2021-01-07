<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("../dados_conexao.php");

$acao = filter_var($_POST['acao'], FILTER_SANITIZE_STRING);


switch ($acao) {


    case 'createCliente':
        $dados = $_POST['dados'];


        $nomeEmpresa = strtoupper(filter_var($dados['nomeEmpresa'], FILTER_SANITIZE_STRING));
        $nome = strtoupper(filter_var($dados['nome'], FILTER_SANITIZE_STRING));
        $email = strtolower(filter_var($dados['email'], FILTER_SANITIZE_STRING));
        $telefone = filter_var($dados['telefone'], FILTER_SANITIZE_STRING);
        $telefone = str_replace('+55 ', '', $telefone);
        $telefone = str_replace('(', '', $telefone);
        $telefone = str_replace(')', '', $telefone);
        $telefone = str_replace(' ', '', $telefone);
        $telefone = str_replace('-', '', $telefone);
        $cpf = filter_var($dados['cpf'], FILTER_SANITIZE_STRING);
        $cpf = str_replace('.', '', $cpf);
        $cpf = str_replace('-', '', $cpf);
        $tipo = filter_var($dados['tipo'], FILTER_SANITIZE_STRING);

        $sql = "INSERT INTO tbl_empresas(nome, tipo, status, update_at, create_at) VALUES ('$nomeEmpresa', '$tipo', 1, NOW(), NOW())";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            $idEmpresaInserido = mysqli_insert_id($conn['link']);
            $sql = "INSERT INTO tbl_usuarios(id_empresa, nome, email, telefone, perfil, status, cpf, update_at, create_at) VALUES ($idEmpresaInserido, '$nome', '$email', '$telefone', 'Administrador', 1, '$cpf', NOW(), NOW())";

            $query = mysqli_query($conn['link'], $sql);

            if ($query == true) {
                echo "1";
            } else {
                echo "Não foi possível inserir.";
                //echo $sql;
            }
        } else {
            echo "Não foi possível inserir.";
            ////echo $sql;
        }



        break;

    case 'updateCliente':
        $dados = $_POST['dados'];

        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idEmpresa = filter_var(@$dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $nomeEmpresa = filter_var($dados['nomeEmpresa'], FILTER_SANITIZE_STRING);


        $sql = "UPDATE tbl_empresas SET nome = '$nomeEmpresa', update_at = NOW() WHERE id_empresa = $idEmpresa";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo "1";
        } else {
            echo "Não foi possível editar.";
            //echo $sql;
        }

        break;

    case 'bloquearUsuario':
        $dados = $_POST['dados'];

        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $tipoBloqueio = filter_var($dados['tipoBloqueio'], FILTER_SANITIZE_STRING);

        $sql = "UPDATE tbl_empresas SET status = '$tipoBloqueio', update_at = NOW() WHERE id_empresa = $idEmpresa";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo "1";
        } else {
            echo "Não foi possível bloquear esse usuário.";
        }

        break;

    case 'excluirUsuario':
        $dados = $_POST['dados'];

        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);

        $sql = "UPDATE tbl_usuarios SET status = 9, update_at = NOW() WHERE id_usuario = $idUsuario";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo "1";
        } else {
            echo "Não foi possível bloquear esse usuário.";
        }

        break;

    default:
        # code...
        break;
}
