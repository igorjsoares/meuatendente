<?php
header("Content-Type: text/html; charset=UTF-8", true);

include("dados_conexao.php");

$email = $_POST['email'];
$uid = $_POST['uid'];

//Entender se esse usuário já existe e se está ativo
$sql = "SELECT u.*, e.nome AS nome_empresa, e.tipo, e.status AS status_empresa FROM tbl_usuarios u, tbl_empresas e WHERE u.id_empresa = e.id_empresa AND u.email = '$email'";
$query = mysqli_query($conn['link'], $sql);
$numRows = mysqli_num_rows($query);
$usuario = mysqli_fetch_array($query);


if ($usuario['userid'] == '' && $usuario['status'] != 0) { //usuário existe, porém é o primeiro cadastro > Atualizar coluna userid
    $sql = "UPDATE tbl_usuarios SET userid = '$uid' WHERE email = '$email'";


    $query = mysqli_query($conn['link'], $sql);
    if ($query == true) {
        $_SESSION['NP_Autenticacao'] = true;
        $_SESSION['NP_id_usuario'] = $usuario['id_usuario'];
        $_SESSION['NP_email'] = $email;
        $_SESSION['NP_id_empresa'] = $usuario['id_empresa'];
        $_SESSION['NP_tipo_empresa'] = $usuario['tipo'];
        $_SESSION['NP_status_empresa'] = $usuario['status_empresa'];
        $_SESSION['NP_nome_empresa'] = $usuario['nome_empresa'];
        $_SESSION['NP_nome_usuario'] = $usuario['nome'];
        $arrayNome = explode(" ", $usuario['nome']);
        if (count($arrayNome) > 2) {
            $_SESSION['NP_nome_usuario_abreviado'] = $arrayNome[0] . ' ' . $arrayNome[count($arrayNome) - 1];
        } else {
            $_SESSION['NP_nome_usuario_abreviado'] = $usuario['nome'];
        }
        $_SESSION['NP_telefone_usuario'] = $usuario['telefone'];
        $_SESSION['NP_uid'] = $uid;
        $_SESSION['NP_perfil_usuario'] = $usuario['perfil'];
        $_SESSION['NP_status'] = 1;
        echo "Novo usuario";
    } else {
        $_SESSION['NP_Autenticacao'] = false;
        $_SESSION['NP_id_usuario'] = "";
        $_SESSION['NP_id_empresa'] = "";
        $_SESSION['NP_tipo_empresa'] = "";
        $_SESSION['NP_status_empresa'] = "";
        $_SESSION['NP_nome_empresa'] = "";
        $_SESSION['NP_nome_usuario'] = "";
        $_SESSION['NP_nome_usuario_abreviado'] = "";
        $_SESSION['NP_telefone_usuario'] = "";
        $_SESSION['NP_email'] = "";
        $_SESSION['NP_uid'] = "";
        $_SESSION['NP_perfil_usuario'] = "";
        $_SESSION['NP_status'] = 0;
        echo "Erro: Novo usuario ";
    }
} else {  //usuário existe
    if ($usuario['status'] == 1 || $usuario['status'] == 2) { //Ativo
        $_SESSION['NP_Autenticacao'] = true;
        $_SESSION['NP_id_usuario'] = $usuario['id_usuario'];
        $_SESSION['NP_email'] = $email;
        $_SESSION['NP_id_empresa'] = $usuario['id_empresa'];
        $_SESSION['NP_tipo_empresa'] = $usuario['tipo'];
        $_SESSION['NP_status_empresa'] = $usuario['status_empresa'];
        $_SESSION['NP_nome_empresa'] = $usuario['nome_empresa'];
        $_SESSION['NP_nome_usuario'] = $usuario['nome'];
        $arrayNome = explode(" ", $usuario['nome']);
        if (count($arrayNome) > 2) {
            $_SESSION['NP_nome_usuario_abreviado'] = $arrayNome[0] . ' ' . $arrayNome[count($arrayNome) - 1];
        } else {
            $_SESSION['NP_nome_usuario_abreviado'] = $usuario['nome'];
        }
        $_SESSION['NP_telefone_usuario'] = $usuario['telefone'];
        $_SESSION['NP_uid'] = $uid;
        $_SESSION['NP_perfil_usuario'] = $usuario['perfil'];
        $_SESSION['NP_status'] = $usuario['status'];
        $_SESSION['NP_create_at'] = $usuario['create_at'];

        /* $data_inicio = new DateTime($usuario['create_at']);
        $data_fim = new DateTime();

        // Resgata diferença entre as datas
        $dateInterval = $data_inicio->diff($data_fim);
        $_SESSION['NP_prazo_teste'] = (7 - $dateInterval->days); */

        echo "Usuario ativo";
    } else { //Bloqueado
        $_SESSION['NP_Autenticacao'] = false;
        $_SESSION['NP_id_usuario'] = "";
        $_SESSION['NP_id_empresa'] = "";
        $_SESSION['NP_tipo_empresa'] = "";
        $_SESSION['NP_status_empresa'] = "";
        $_SESSION['NP_nome_empresa'] = "";
        $_SESSION['NP_nome_usuario'] = "";
        $_SESSION['NP_nome_usuario_abreviado'] = "";
        $_SESSION['NP_telefone_usuario'] = "";
        $_SESSION['NP_email'] = "";
        $_SESSION['NP_uid'] = "";
        $_SESSION['NP_perfil_usuario'] = "";
        $_SESSION['NP_status'] = $usuario['status'];
        $_SESSION['NP_create_at'] = "";
        echo "Usuario bloqueado";
    }
}
