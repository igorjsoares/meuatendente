<?php
header("Content-Type: text/html; charset=UTF-8", true);
include("../dados_conexao.php");

$acao = filter_var($_POST['acao'], FILTER_SANITIZE_STRING);


switch ($acao) {

    case 'logout':
        echo  "<script>console.log('Entrou no LogoutPHP')</script>";
        echo  "<script>alert('Entrou no LogoutPHP')</script>";
        session_destroy();
        echo '1';
        break;

        /* case 'updateUsuario':
        $dados = $_POST['dados'];
        
        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $telefone = filter_var($dados['telefone'], FILTER_SANITIZE_STRING);
            
        $sql = "UPDATE tbl_usuarios SET contato = '$telefone' WHERE id_usuario = $idUsuario";
        
        $query = mysqli_query($conn['link'],$sql);
        if($query == true){
            echo "1";
        }else{
            echo "Não foi possível inserir.";
        }
        
    break; */

    case 'updateProfile':
        $dados = $_POST['dados'];

        $id_empresa = filter_var($dados['id_empresa'], FILTER_SANITIZE_STRING);
        $id_usuario = filter_var($dados['id_usuario'], FILTER_SANITIZE_STRING);
        $nome = strtoupper(filter_var($dados['nome'], FILTER_SANITIZE_STRING));
        $telefone = filter_var($dados['telefone'], FILTER_SANITIZE_STRING);
        $senha = filter_var($dados['senha'], FILTER_SANITIZE_STRING);

        if ($senha != '') { //Altera também a senha
            $sql = "UPDATE tbl_usuarios SET nome = '$nome', telefone = '$telefone', senha = md5('$senha') WHERE id_usuario = $id_usuario";
            //$sql = "UPDATE tbl_usuarios SET senha = MD5('$senha') WHERE email = '$email'";
        } else {
            $sql = "UPDATE tbl_usuarios SET nome = '$nome', telefone = '$telefone' WHERE id_usuario = $id_usuario";
        }
        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            $_SESSION['nome'] = $nome;
            $_SESSION['telefone'] = $telefone;
            echo "1";
        } else {
            echo mysqli_connect_error();
        }

        break;

    case 'updateEmpresa':
        $dados = $_POST['dados'];

        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $nome = filter_var($dados['nome'], FILTER_SANITIZE_STRING);
        $razaoSocial = filter_var($dados['razaoSocial'], FILTER_SANITIZE_STRING);
        $cnpj = filter_var($dados['cnpj'], FILTER_SANITIZE_STRING);
        $cnpj = str_replace('.', '', $cnpj);
        $cnpj = str_replace('/', '', $cnpj);
        $cnpj = str_replace('-', '', $cnpj);
        $cidade = filter_var($dados['cidade'], FILTER_SANITIZE_STRING);
        $endereco = filter_var($dados['endereco'], FILTER_SANITIZE_STRING);
        $latitude = filter_var($dados['latitude'], FILTER_SANITIZE_STRING);
        $longitude = filter_var($dados['longitude'], FILTER_SANITIZE_STRING);
        $latlong = $latitude . ", " . $longitude;
        $tipo = filter_var($dados['tipo'], FILTER_SANITIZE_STRING);
        $status = strtolower(filter_var($dados['status'], FILTER_SANITIZE_STRING));


        $sql = "UPDATE tbl_empresas SET nome = '$nome', razao_social = '$razaoSocial', cnpj = '$cnpj', 
        tipo = '$tipo', cidade = '$cidade', endereco = '$endereco', latlong = '$latlong', status = '$status', update_at = NOW() WHERE id_empresa = $idEmpresa";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo "1";
        } else {
            echo mysqli_connect_error();
        }

        break;

    case 'createUsuario':
        $dados = $_POST['dados'];

        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $nome = strtoupper(filter_var($dados['nome'], FILTER_SANITIZE_STRING));
        $email = strtolower(filter_var($dados['email'], FILTER_SANITIZE_STRING));
        $telefone = filter_var($dados['telefone'], FILTER_SANITIZE_STRING);
        $telefone = str_replace('+55 ', '', $telefone);
        $telefone = str_replace('(', '', $telefone);
        $telefone = str_replace(')', '', $telefone);
        $telefone = str_replace(' ', '', $telefone);
        $telefone = str_replace('-', '', $telefone);
        $perfil = filter_var($dados['perfil'], FILTER_SANITIZE_STRING);
        $cpf = filter_var($dados['cpf'], FILTER_SANITIZE_STRING);
        $cpf = str_replace('.', '', $cpf);
        $cpf = str_replace('-', '', $cpf);


        $sql = "INSERT INTO tbl_usuarios(id_empresa, nome, email, telefone, perfil, status, cpf, update_at, create_at) VALUES ($idEmpresa, '$nome', '$email', '$telefone', '$perfil', 1, '$cpf', NOW(), NOW())";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo "1";
        } else {
            echo "Não foi possível inserir.";
            //echo $sql;
        }

        break;

    case 'updateUsuario':
        $dados = $_POST['dados'];

        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $telefone = filter_var($dados['telefone'], FILTER_SANITIZE_STRING);
        $telefone = str_replace('+55 ', '', $telefone);
        $telefone = str_replace('(', '', $telefone);
        $telefone = str_replace(')', '', $telefone);
        $telefone = str_replace(' ', '', $telefone);
        $telefone = str_replace('-', '', $telefone);
        $perfil = filter_var($dados['perfil'], FILTER_SANITIZE_STRING);


        $sql = "UPDATE tbl_usuarios SET telefone = '$telefone', perfil = '$perfil', update_at = NOW() WHERE id_usuario = $idUsuario";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo "1";
        } else {
            echo "Não foi possível editar.";
        }

        break;

    case 'updateEspecialidade':
        $dados = $_POST['dados'];

        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        //! Precisa utilizar o FILTRO, mas ele substitui as aspas
        $especialidades = filter_var($dados['especialidades'], FILTER_SANITIZE_URL);
        //$especialidades = $dados['especialidades'];

        $sql = "UPDATE tbl_empresas SET especialidades = '$especialidades' WHERE id_empresa = $idEmpresa";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo "1";
        } else {
            echo "Não foi possível editar.";
        }

        break;


    case 'bloquearUsuario':
        $dados = $_POST['dados'];

        $idUsuario = filter_var($dados['idUsuario'], FILTER_SANITIZE_STRING);
        $tipoBloqueio = filter_var($dados['tipoBloqueio'], FILTER_SANITIZE_STRING);

        $sql = "UPDATE tbl_usuarios SET status = '$tipoBloqueio', update_at = NOW() WHERE id_usuario = $idUsuario";

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

    case 'createNovo':
        $dados = $_POST['dados'];

        $nomeEmpresa = filter_var($dados['nomeEmpresa'], FILTER_SANITIZE_STRING);
        $nome = strtoupper(filter_var($dados['nome'], FILTER_SANITIZE_STRING));
        $email = strtolower(filter_var($dados['email'], FILTER_SANITIZE_STRING));
        $telefone = filter_var($dados['telefone'], FILTER_SANITIZE_STRING);
        $telefone = str_replace('+55 ', '', $telefone);
        $telefone = str_replace('(', '', $telefone);
        $telefone = str_replace(')', '', $telefone);
        $telefone = str_replace(' ', '', $telefone);
        $telefone = str_replace('-', '', $telefone);


        $sql = "INSERT INTO tbl_novos(empresa, nome, email, telefone, status, update_at, create_at) VALUES ('$nomeEmpresa', '$nome', '$email', '$telefone', 1, NOW(), NOW())";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo "1";
        } else {
            echo "Não foi possível inserir.";
            //echo $sql;
        }

        break;

    case 'primeiroAcesso':
        $dados = $_POST['dados'];

        $email = strtolower(filter_var($dados['email'], FILTER_SANITIZE_STRING));
        $cpf = filter_var($dados['cpf'], FILTER_SANITIZE_STRING);
        $cpf = str_replace('.', '', $cpf);
        $cpf = str_replace('-', '', $cpf);


        $sql = "SELECT userid FROM tbl_usuarios WHERE email = '$email' AND cpf = '$cpf'";

        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);
        while ($usuario = mysqli_fetch_array($query)) {
            $userid = $usuario['userid'];
        }
        if ($numRow != 0) {
            if ($userid == '') { //Primeiro acesso
                echo "Primeiro acesso";
            } else {
                echo "Não é primeiro acesso";
            }
        } else {
            echo "Não encontrado";
            //echo $sql;
        }

        break;

    case 'primeiroAcesso':
        $dados = $_POST['dados'];

        $email = strtolower(filter_var($dados['email'], FILTER_SANITIZE_STRING));
        $cpf = filter_var($dados['cpf'], FILTER_SANITIZE_STRING);
        $cpf = str_replace('.', '', $cpf);
        $cpf = str_replace('-', '', $cpf);


        $sql = "SELECT userid FROM tbl_usuarios WHERE email = '$email' AND cpf = '$cpf'";

        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);
        while ($usuario = mysqli_fetch_array($query)) {
            $userid = $usuario['userid'];
        }
        if ($numRow != 0) {
            if ($userid == '') { //Primeiro acesso
                echo "Primeiro acesso";
            } else {
                echo "Não é primeiro acesso";
            }
        } else {
            echo "Não encontrado";
            //echo $sql;
        }

        break;

    

    case 'consultaCidades':


        $sql = "SELECT * FROM tbl_cidades";

        $arrayCidades = [];
        $query = mysqli_query($conn['link'], $sql);

        while ($cidade = mysqli_fetch_array($query)) {
            array_push($arrayCidades, array("id_cidade" => $cidade['id_cidade'], "nome" => $cidade['nome'], "uf" => $cidade['uf']));
        }
        echo json_encode($arrayCidades, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'consultaEmpresasMaster':


        $sql = "SELECT id_empresa, nome, tipo FROM tbl_empresas WHERE id_empresa != 1";

        $arrayEmpresas = [];
        $query = mysqli_query($conn['link'], $sql);

        while ($empresa = mysqli_fetch_array($query)) {
            array_push($arrayEmpresas, array("id_empresa" => $empresa['id_empresa'], "nome" => $empresa['nome'], "tipo" => $empresa['tipo']));
        }
        echo json_encode($arrayEmpresas, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'consultaStatusWhatsapp':
        $dados = $_POST['dados'];

        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);

        $sql = "SELECT e.id_contato, e.numero_contato, u.nome AS nome_contato FROM tbl_empresas e, tbl_usuarios u WHERE e.id_contato = u.id_usuario AND e.id_empresa = $idEmpresa";

        $arrayEmpresa = [];
        $query = mysqli_query($conn['link'], $sql);
        $numRow = mysqli_num_rows($query);

        if($numRow > 0){
        while ($empresa = mysqli_fetch_array($query)) {
            $arrayNome = explode(" ", $empresa['nome_contato']);
            if (count($arrayNome) > 2) {
                $nome_contato = $arrayNome[0] . ' ' . $arrayNome[count($arrayNome) - 1];
            } else {
                $nome_contato = $empresa['nome_contato'];
            }
            array_push($arrayEmpresa, array("id_empresa" => $idEmpresa, "id_contato" => $empresa['id_contato'], "nome_contato" => $nome_contato));
        }
    }else{
        array_push($arrayEmpresa, array("id_empresa" => '', "id_contato" => '', "nome_contato" => ''));

    }
        echo json_encode($arrayEmpresa, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR, true);

        break;

    case 'selecaoEmpresasMaster':
        $dados = $_POST['dados'];

        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $nomeEmpresa = filter_var($dados['nomeEmpresa'], FILTER_SANITIZE_STRING);
        $tipoEmpresa = filter_var($dados['tipoEmpresa'], FILTER_SANITIZE_STRING);

        $_SESSION['VM_id_empresa'] = $idEmpresa;
        $_SESSION['VM_nome_empresa'] = $nomeEmpresa;
        $_SESSION['VM_tipo_empresa'] = $tipoEmpresa;

        echo 1;

        break;

    case 'alterarStatusWhatsapp':
        $dados = $_POST['dados'];

        $idEmpresa = filter_var($dados['idEmpresa'], FILTER_SANITIZE_STRING);
        $idContato = filter_var($dados['idContato'], FILTER_SANITIZE_STRING);
        $numeroContato = filter_var($dados['numeroContato'], FILTER_SANITIZE_STRING);

        $sql = "UPDATE tbl_empresas SET id_contato = '$idContato', numero_contato = '$numeroContato' WHERE id_empresa = $idEmpresa";

        $query = mysqli_query($conn['link'], $sql);
        if ($query == true) {
            echo "1";
        } else {
            echo "Não foi possível mudar o contato.";
            //echo $sql;
        }

        break;

        case 'alterarStatusProspecto':
            $dados = $_POST['dados'];
    
            $idNovos = filter_var($dados['idNovos'], FILTER_SANITIZE_STRING);
            $status = filter_var($dados['status'], FILTER_SANITIZE_STRING);
    
            $sql = "UPDATE tbl_novos SET status = '$status', update_at = NOW() WHERE id_novos = $idNovos";
    
            $query = mysqli_query($conn['link'], $sql);
            if ($query == true) {
                echo "1";
            } else {
                echo "Não foi possível bloquear esse usuário.";
                //echo $sql;
            }
    
            break;

    case 'updateComentarioProspecto':
        $dados = $_POST['dados'];

        $idNovos = filter_var($dados['idNovos'], FILTER_SANITIZE_STRING);
        $descricao = filter_var($dados['descricao'], FILTER_SANITIZE_STRING);

        $sql = "UPDATE tbl_novos SET descricao = '$descricao', update_at = NOW() WHERE id_novos = $idNovos";

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
