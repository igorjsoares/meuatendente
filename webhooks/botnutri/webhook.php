<?php {
    class whatsAppBot
    {

        //$id_instancia = $_GET['codigo']; //chatpro-yybwcu3f69   

        public function __construct()
        {
            include("dados_conexao.php");

            //& Alterar aqui depois os dados para a consulta no BD 
            $this->tempoMenu = 7200; //Tempo entre a última mensagem e a possibilidade de enviar o menu novamente
            $idInstancia = 1;
            $this->idInstancia = $idInstancia;

            //Recebe o corpo do Json enviado pela instância
            $json = file_get_contents('php://input');
            $decoded = json_decode($json, true); //Decodifica

            //Grava o JSON-body no arquivo de debug
            ob_start();
            var_dump($decoded);
            $input = ob_get_contents();
            ob_end_clean();

            //Coloca para salvar todas as requisições recebidas em um arquivo de log
            //file_put_contents('inputs.log', $input . PHP_EOL, FILE_APPEND);



            //() Verifica SE É uma mensagem recebida 
            if (isset($decoded['Type']) && ($decoded['Type'] == 'receveid_message' || $decoded['Type'] == 'receveid_audio_message')) {
                $RemoteJid = $decoded['Body']['Info']['RemoteJid'];
                $RemoteJidArray = explode("@", $RemoteJid);
                $numero = $RemoteJidArray[0];
                $this->numerocliente = $numero;
                $tipoNumero = $RemoteJidArray[1];
                $idMensagemWhats = $decoded['Body']['Info']['Id'];
                $timestamp = $decoded['Body']['Info']['Timestamp'];
                $mensagem = $decoded['Body']['Text'];
                $this->stringMensagemAtual = $mensagem;


                //( Busca informações da instância CHATPRO no banco de dados 
                $sql = "SELECT * FROM tbl_instancias WHERE id_instancia = $idInstancia";
                $query = mysqli_query($conn['link'], $sql);
                $consultaInstancia = mysqli_fetch_array($query, MYSQLI_ASSOC);
                $numRow = mysqli_num_rows($query);
                if (!$query) {
                    echo "Erro ao tentar conectar no MYSQL " . mysqli_connect_error();
                    $this->logSis('ERR', 'Mysql Connect: ' . mysqli_connect_error());

                    exit(0);
                }
                if ($numRow == 0) { //VERIFICA SE EXISTE NO BANCO DE DADOS
                    $this->logSis('ERR', "Instância N/E: " . $id_instancia);
                    exit(0);
                } else {
                    $this->APIurl  = $consultaInstancia['endpoint'] . '/api/v1/';
                    $this->token  = $consultaInstancia['token'];
                    $this->numerosuporte =  $consultaInstancia['numero_suporte'];
                    $this->conf_cad_dados =  $consultaInstancia['conf_cad_dados'];
                    $this->msg_cad_dados =  $consultaInstancia['msg_cad_dados'];
                    $this->msg_inicial =  $consultaInstancia['msg_inicial'];
                    $this->menuRaiz =  $consultaInstancia['menu_raiz'];
                    $limite = $consultaInstancia['limite'];
                    $status = $consultaInstancia['status'];
                    $nome = $consultaInstancia['nome'];
                }


                //() Verifica se NÃO É uma mensagem recebida de um número ou GRUPO 
                if ($tipoNumero == 's.whatsapp.net') {

                    //( Consulta o contato no BD 
                    $sql = "SELECT * FROM tbl_contatos WHERE numero = $numero AND id_instancia = $idInstancia";
                    $query = mysqli_query($conn['link'], $sql);
                    $consultaContato = mysqli_fetch_array($query, MYSQLI_ASSOC);
                    $numRow = mysqli_num_rows($query);

                    if (!$query) {
                        $this->logSis('ERR', "Mysql Connect Erro: " . mysqli_error($conn['link']));
                        exit(0);
                    }

                    if ($numRow != 0) { //( O CONTATO EXISTE NO BANCO DE DADOS  
                        $this->id_contato = $consultaContato['id_contato'];
                        $nome = $consultaContato['nome'];
                        $email = $consultaContato['email'];
                        $fase = $consultaContato['fase'];
                        $teste = $consultaContato['teste'];
                    } else { //( O CONTATO NÃO EXISTE 
                        $this->primeirocontato = true;

                        //CONTATO NÃO EXISTE 
                        //( Insere o contato no banco de dados 
                        $sql = "INSERT INTO tbl_contatos(id_instancia, numero, teste, created_at) VALUES ('$idInstancia', '$numero', 0, NOW())";
                        $resultado = mysqli_query($conn['link'], $sql);
                        $this->id_contato = mysqli_insert_id($conn['link']);
                        if ($resultado != '1') {
                            $this->logSis('ERR', 'Insert Contatos. Erro: ' . $resultado . mysqli_connect_error());
                        }
                    }

                    //( Insere a interação que foi recebida no BD 
                    //& Quando inserir a mensagem do cliente, já trazer o ID para colocar na coluna id_retorno na mensagem que vamos enviar. 
                    //& Verificar também o retorno de erro, caso não consiga inserir o cliente. 
                    $resultado = $this->inserirInteracao($this->idInstancia, 0, $this->id_contato, '', '', '', '', $idMensagemWhats, $mensagem, 1);

                    if ($resultado == '1') {
                        $mensagem = explode(' ', trim($decoded['Body']['Text']));
                        $palavra = mb_strtolower($mensagem[0], 'UTF-8');

                        if ($this->primeirocontato == true) { //( Se for o primeiro contato
                            //( Verifica se o e-mail é valido
                            $this->validaEmail($palavra, $numero, true, $this->id_contato);
                        } else if ($email == '') { //Sem e-mail cadastrado
                            //( Verifica se o e-mail é valido
                            $this->validaEmail($palavra, $numero, false, $this->id_contato);
                        } else {

                            //( Consulta a última interação enviada pra ver se foi a solicitação de nome 
                            $ultimaInteracao = $this->verificaInteracao($idInstancia, $this->id_contato);
                            $tempoParaUltimaInteracao = $this->difDatasEmHoras($ultimaInteracao['dataEnvio'], date("Y-m-d H:i:s"));

                            if ($nome == '') {
                                //( Caso não tenha enviado ainda a pergunta do nome
                                if ($ultimaInteracao['mensagem'] != 'solicitaNome') {

                                    if ($tempoParaUltimaInteracao >= 2) { //Se tiver mais de 2 horas sem interação, dar umas boas vindas ao cliente
                                        $texto = 'Olá, que bom que está de volta! Para que eu possa te conhecer melhor, qual o seu nome?';
                                    } else {
                                        $texto = 'Olá, para que possamos seguir com o atendimento, por favor digite seu nome?';
                                    }
                                    $this->sendMessage("solicitaNome", $numero, $texto, '');
                                } else { //( Caso a última interação tenha sido solicitado o nome. 
                                    //( Verifica a mensagem em busca do primeiro nome 
                                    $nome = $this->verificaNome($decoded['Body']['Text']);
                                    if ($nome == "" || strlen($nome) < 2) { // não trouxe nada 
                                        $texto = 'Não compreendi, pode por favor enviar somente o seu primeiro nome.';
                                        $this->sendMessage("solicitaNome", $numero, $texto, '');
                                    } else { // encontrou o primeiro nome
                                        //( Salva o nome no banco 
                                        $resultadoAtualizaNome = $this->atualizaCampo('tbl_contatos', 'nome', $nome, 'id_instancia = ' . $idInstancia . ' AND id_contato = ' . $this->id_contato);
                                        if ($resultadoAtualizaNome == true) {
                                            $textoComplementar = "Prazer em conhecer você $nome!\n\n";

                                            $this->envioMenuRaiz($numero, $textoComplementar);
                                        }
                                    }
                                }
                            } else {
                                $this->logSis('DEB', 'Indetificou que tem nome');

                                $this->resposta($numero, $decoded);
                            }
                        }
                    }
                }
            }
        }

        public function resposta($numero, $mensagem)
        {
            include("dados_conexao.php");

            //( Procurar a última interação realizada para ver se tem tempo suficiente para envio do menu
            //( Caso o tempo da resposta seja maior que o tempo estipulado para $tempoMenu, ele chama o menu ao invez de qualquer coisa. 
            $sql = "SELECT data_envio AS ultima_interacao, TIMESTAMPDIFF(SECOND,data_envio,NOW()) AS segundos FROM tbl_interacoes WHERE id_instancia = $this->idInstancia AND direcao = 1 AND id_contato = $this->id_contato ORDER BY data_envio DESC LIMIT 1";
            $this->logSis('DEB', 'SQL: ' . $sql);
            $query = mysqli_query($conn['link'], $sql);
            $numRow = mysqli_num_rows($query);
            $consultaUltima = mysqli_fetch_array($query, MYSQLI_ASSOC);
            if (!$query) {
                $this->logSis('ERR', "Mysql Connect Erro: " . mysqli_error($conn['link']));
                exit(0);
            }
            if ($numRow == 0) { //VERIFICA SE EXISTE NO BANCO DE DADOS
                $this->logSis('ERR', 'Não encontrou a interação na consulta da Resposta. Número: ' . $numero);
                exit(0);
            }
            $this->logSis('DEB', 'Tempo da última: ' . $consultaUltima['segundos']);


            if ($numRow > 0 && $consultaUltima['segundos'] > $this->tempoMenu) {
                $this->logSis('DEB', 'Indetificou que faz tempo desde a última ' . $consultaUltima['segundos'] . ' segundos');

                $this->envioMenuRaiz($numero, '');
            }

            //( ULTIMA INTERAÇÃO DE MENU - O que provavelmente o cliente está respondendo 
            $sql = "SELECT id_interacao, id_retorno FROM tbl_interacoes WHERE id_instancia = $this->idInstancia AND tipo = 1 AND direcao = 1 AND id_contato = $this->id_contato ORDER BY data_envio DESC LIMIT 2";
            $query = mysqli_query($conn['link'], $sql);
            $numRow = mysqli_num_rows($query);
            $consultaUltima = mysqli_fetch_array($query, MYSQLI_ASSOC);
            $this->ultimoRetorno = $consultaUltima['id_retorno'];

            $this->logSis('DEB', 'ultimoRetorno: ' . $this->ultimoRetorno);

            //excluir espaços em excesso e dividir a mensagem em espaços.
            //A primeira palavra na mensagem é um comando, outras palavras são parâmetros
            $mensagem = explode(' ', trim($this->stringMensagemAtual));

            //Confirma se a mensagem realmente não foi enviada do Bot
            if (!$decoded['Body']['Info']['FromMe']) {
                $primeiraPalavraCliente = mb_strtolower($mensagem[0], 'UTF-8');
                $this->logSis('DEB', 'PRIMEIRA PALAVRA: ' . $primeiraPalavraCliente);

                //( Verifica se é um número 
                if (is_numeric($primeiraPalavraCliente)) { //Caso seja um número, faz verificação se existe algum menu pra esse número 
                    $this->logSis('DEB', 'É NÚMERO ' . $primeiraPalavraCliente);

                    if ($primeiraPalavraCliente == 0) { //Se o cliente escolher 0, tem que retornar
                        //$arrayRetorno = $this->consultaRetorno($this->penultimoRetorno, '', '');
                        //$this->direcaoEnvio($arrayRetorno['tipo'], $numero, $arrayRetorno);

                    } else {
                        $arrayRetorno = $this->consultaRetorno('', $primeiraPalavraCliente, $this->ultimoRetorno);
                        $this->direcaoEnvio($arrayRetorno['tipo'], $numero, $arrayRetorno);
                    }
                } else { // Caso não seja um número, ele vai analisar as palavras
                    //& NÃO É UM NÚMERO A RESPOSTA DO CLIENTE
                    //& IDENTIFICAR SE NA MENSAGEM DO CLIENTE TEM ALGUMA PALAVRA QUE SEJA RESPOSTA NA COLUNA PALAVRAS 
                }
            }
        }

        //* Envio Menu raiz
        public function envioMenuRaiz($numero, $textoComplementar)
        {
            $arrayRetorno = $this->consultaRetorno($this->menuRaiz, '', '');
            $texto = $textoComplementar . $arrayRetorno['mensagem'];
            $this->sendMessage($arrayRetorno['nome'], $numero, $texto, $arrayRetorno);
        }

        //* C O N S U L T A  R E T O R N O
        public function consultaRetorno($id_retorno, $primeiraPalavraCliente, $ultimoRetorno)
        {
            include("dados_conexao.php");

            if ($id_retorno == '') { //ou seja, não sei qual o retorno
                $sql = "SELECT * FROM tbl_retornos WHERE id_retorno = (SELECT resposta FROM tbl_opcoes WHERE id_instancia = $this->idInstancia AND indice = '$primeiraPalavraCliente' AND id_retorno = $ultimoRetorno)";
            } else { //Sei qual o retorno atual
                //$idInstancia = $this->idInstancia;
                $sql = "SELECT * FROM tbl_retornos WHERE id_instancia = $this->idInstancia AND id_retorno = $id_retorno";
            }

            $this->logSis('DEB', $sql);

            $query = mysqli_query($conn['link'], $sql);
            $consultaRetorno = mysqli_fetch_array($query, MYSQLI_ASSOC);
            $numRow = mysqli_num_rows($query);
            if (!$query) {
                $this->logSis('ERR', 'Mysql Connect: ' . mysqli_error($conn['link']));
                exit(0);
            }
            if ($numRow == 0) { //VERIFICA SE EXISTE NO BANCO DE DADOS
                $this->logSis('ERR', 'Não encontrou a mensagem inicial Instância. Instância: ' . $this->idInstancia);
                exit(0);
            } else {
                //& VERIFICAR AQUI SE VAI TER AMBIGUIDADE COM A PRIMEIRA CONSULTA 
                $id_retorno = $consultaRetorno['id_retorno'];  //ID da tabela retorno (chave)
                $mensagem = utf8_encode($consultaRetorno['mensagem']);
                //Consulta das opções
                $sql = "SELECT * FROM tbl_opcoes WHERE listavel = 1 AND id_instancia = $this->idInstancia AND id_retorno = $id_retorno ORDER BY indice ASC";
                $this->logSis('DEB', $sql);

                $query = mysqli_query($conn['link'], $sql);
                $numRow = mysqli_num_rows($query);

                if ($numRow != 0) {
                    $mensagem .= "\n";
                    while ($opcao = mysqli_fetch_array($query)) {
                        $mensagem .= '*' . $opcao['indice'] . '.* ' . utf8_encode($opcao['mensagem']) . "\n";
                    }
                    if ($consultaRetorno['modo'] == 1 && $consultaRetorno['id_retorno'] != $this->menuRaiz) {
                        $mensagem .= "*0.* Voltar ao menu anterior\n";
                    }
                }

                $retorno = array(
                    'id_retorno' => $consultaRetorno['id_retorno'],
                    'nome' => $consultaRetorno['nome'],
                    'modo' => $consultaRetorno['modo'],
                    'tipo' => $consultaRetorno['tipo'],
                    'coringa' => $consultaRetorno['coringa'], //para tipo 6 (Inclusão lista) -> lista_X
                    'mensagem' => $mensagem,
                    'url' => $consultaRetorno['url'],
                    'lat' => $consultaRetorno['lat'],
                    'lng' => $consultaRetorno['lng'],
                    'name' => $consultaRetorno['name'],
                    'address' => $consultaRetorno['address']
                );
                return $retorno;
            }
        }

        //* D I R E C I O N A M E N T O  E N V I O
        //
        public function direcaoEnvio($tipo, $numero, $retorno)
        {
            if ($tipo == 1) { //texto
                $this->sendMessage($retorno['nome'], $numero, $retorno['mensagem'], $retorno);
            } elseif ($tipo == 2) { //imagem
                $this->envioImagem($retorno['nome'], $numero, $retorno);
            } elseif ($tipo == 3) { //arquivo
                $this->envioArquivo($retorno['nome'], $numero, $retorno);
            } elseif ($tipo == 4) { //localização
                $this->envioLocalizacao($retorno['nome'], $numero, $retorno);
            } elseif ($tipo == 6) { //Inclusão em lista
                //$this->InOutListas($retorno['nome'], $numero, $retorno, 1);
            } elseif ($tipo == 7) { //Exclusão em lista
                //$this->InOutListas($retorno['nome'], $numero, $retorno, 0);
            }
        }

        //* PRIMEIRO CONTATO - Primeiras mensagens ou mensagem de erro 
        public function primeiroContato($remoteJID, $primeiroContato)
        {
            if ($primeiroContato == true) {
                if ($this->conf_cad_dados == 1) { //( Solicitar os dados ao cliente 
                    $aberturaString = $this->msg_cad_dados;
                } else {
                    $aberturaString = $this->msg_inicial;
                }
                $this->sendMessage("Inicial", $remoteJID, $aberturaString, '');
            }
        }

        //* Validação de e-mail
        public function validaEmail($palavra, $numero, $primeiroContato, $idContato)
        {
            if ($primeiroContato == true) {
                $msgBoasVindas = "Que bom que você está aqui! Parabéns pela sua atitude.\n\n";
            } else {
                $msgBoasVindas = "";
            }

            //( Validação do e-mail
            if (filter_var($palavra, FILTER_VALIDATE_EMAIL)) {
                $validado = true;
            } else {
                $this->logSis('DEB', 'Email invalido. Número: ' . $numero . ' Enviado: ' . $palavra);
                $validado = false;
            }

            //( E-mail válido
            if ($validado == true) {
                $email = $palavra;
                //( Atualização do email na tbl_contatos
                $atualizacaoBD = $this->atualizaCampo('tbl_contatos', 'email', $email, "id_contato='$idContato'");

                if ($atualizacaoBD == true) { //( Conseguiu atualizar 
                    //( Envio do e-mail 
                    $textoEmail = "Olá! \n\nComo prometi, segue o link para acessar o conteúdo.\n\nQualquer dúvida pode nos responder esse e-mail ou chamar nosso atendimento no Whatsapp.\n\nhttps://nutrimarimartins.com.br/comoemagrecer.html\n\nEquipe Nutri Mari Martins";
                    $statusEnvioEmail = $this->enviarEmail($email, $textoEmail);

                    //& Colocar aqui uma inteligência pra que o cliente reveja o e-mail e possa alterar o mesmo
                    if ($statusEnvioEmail == true) { //( Conseguiu enviar
                        //( Atualiza a TBL_CONTATOS com a fase 1, ou seja já enviou o e-mail 
                        $atualizacaoBD = $this->atualizaCampo('tbl_contatos', 'fase', 1, "id_contato='$idContato'");
                        $this->sendMessage("okEmail", $numero, "Enviei um e-mail com o conteúdo para $email, entre na sua caixa de e-mail e aproveite esse conteúo feito com todo carinho pra você.\n\nEsse Whatsap aqui é o nosso canal oficial, sempre que quiser falar comigo, pode me chamar por aqui, envindo um oi.\n\nNutri Mari Martins.\n\n_Caso não receba, verifique na caixa de SPAM do seu e-mail_", '');
                    } else { //( Não enviou
                        $this->sendMessage("okEmail", $numero, "Em breve você receberá o nosso conteúdo no e-mail $email.\n\nEsse Whatsap aqui é o nosso canal oficial, sempre que quiser falar comigo, pode me chamar por aqui, enviando um oi.\n\nNutri Mari Martins.\n\n_Caso não receba, verifique na caixa de SPAM do seu e-mail_", '');
                    }
                } else { //( Não atualizou
                    $this->sendMessage("ErroBDEmail", $numero, "No momento não conseguimos registrar o seu e-mail na nossa base de dados.\n\nFavor enviar um e-mail para contato@nutrimarimartins.com.br", '');
                }

                //( e-mail invalido
            } else {
                $texto = $msgBoasVindas . "Não identificamos um e-mal válido na sua mensagem.\nPara receber nosso conteúdo, favor envie uma mensagem somente com o seu e-mail. ";
                $this->sendMessage("ErroEmail", $numero, $texto, '');
            }
        }

        public function envioMenu($numero, $textoComplementar)
        {
            //& Envio do MENU
        }

        //* Atualização de campo genérico em tabela genérica
        private function atualizaCampo($tabela, $campo, $valor, $where)
        {
            include("dados_conexao.php");
            $sql = "UPDATE $tabela SET $campo = '$valor' WHERE $where";
            //$this->logSis("DEB", $sql);

            $query = mysqli_query($conn['link'], $sql);
            $linhasAfetadas = mysqli_affected_rows($conn['link']);

            if (!$query) {
                $this->logSis('ERR', 'Mysql Connect: ' . mysqli_error($conn['link']));
                exit(0);
            }
            if ($query != true && $linhasAfetadas == 0) {
                return false;
                $this->logSis('ERR', 'Não alterou no BD . Tbl: ' . $tabela . ' Campo: ' . $campo . ' Valor: ' . $valor);
            } else {
                return true;
            }
        }

        //* ENVIA UM E-MAIL
        public function enviarEmail($email, $texto)
        {
            $subject = 'NUTRI MARI MARTINS - Conteúdo';

            $mensagem = $texto;

            $myEmail = "contato@meuatendente.com.br"; //é necessário informar um e-mail do próprio domínio
            $headers = "From: contato@meuatendente.com.br\r\n";
            $headers .= "Reply-To: contato@meuatendente.com.br\r\n";

            $corpo = $mensagem . "\n";

            $email_to = $email;

            $status = mail($email_to, $subject, $corpo, $headers);

            if ($status) {
                return true;
            } else {
                $this->logSis('ERR', 'Não enviou o e-mail da Finalização. E-mail: ' . $email);
                return false;
            }
        }

        //* E N V I O  T E X T O
        //Prepara para envio da mensagem de texto
        public function sendMessage($motivo, $numero, $text, $retorno)
        {

            $data = array('number' => $numero . '@s.whatsapp.net', 'menssage' => $text);
            $this->sendRequest($motivo, 'send_message', $data, $retorno);
        }

        //* E N V I O  I M A G E M
        //
        public function envioImagem($motivo, $remoteJID, $retorno)
        {
            $data = array(
                'url' => $retorno['url'],
                'number' => $remoteJID,
                'caption' => $retorno['mensagem']
            );
            $this->sendRequest($motivo, $remoteJID, 'send_message_file_from_url', $data, $retorno);
        }

        //* E N V I O  A R Q U I V O
        //
        public function envioArquivo($motivo, $remoteJID, $retorno)
        {
            $data = array(
                'url' => $retorno['url'],
                'number' => $remoteJID
            );
            $this->sendRequest($motivo, $remoteJID, 'send_message_file_from_url', $data, $retorno);
        }

        //* E N V I O  L O C A L I Z A Ç Ã O
        //
        public function envioLocalizacao($motivo, $remoteJID, $retorno)
        {
            $data = array(
                'address' => $retorno['address'],
                'lat' => $retorno['lat'],
                'lng' => $retorno['lng'],
                'name' => $retorno['name'],
                'number' => $remoteJID
            );
            $this->sendRequest($motivo, $remoteJID, 'send_location', $data, $retorno);
        }

        //* E N V I O
        //Envia a requisição
        public function sendRequest($motivo, $method, $data, $retorno)
        {
            include("dados_conexao.php");

            $url = 'https://' . $this->APIurl . $method;
            if (is_array($data)) {
                $data = json_encode($data);
            }

            $options = stream_context_create(['http' => [
                'method'  => 'POST',
                'header'  => "Content-type: application/json\r\nAuthorization: $this->token\r\n",
                'content' => $data
            ]]);

            $response = file_get_contents($url, false, $options);

            $this->logSis('REQ', 'Resp Requisição: ' . $response);

            //return $response;

            $resposta = json_decode($response, true);
            $statusEnvio = $resposta['message'];
            if ($statusEnvio == "Mensagem enviada com sucesso" || $statusEnvio == "Mensagem Enviada") {
                $id_resposta = $resposta['requestMenssage']['id'];
                if ($retorno == '') {
                    $tipo = '';
                    $idRetorno = '';
                } else {
                    $tipo = $retorno['modo'];
                    $idRetorno = $retorno['id_retorno'];
                }
                $this->logSis('REQ', 'Chegou aqui - Instância: ' . $this->idInstancia . ' IdContato: ' . $this->id_contato . ' Tipo: ' . $tipo . ' IdInteracaiCliente: ' . $this->id_interacao_cliente . ' IdResposta: ' . $id_resposta . ' Motivo: ' . $motivo);

                $this->inserirInteracao($this->idInstancia, 1, $this->id_contato, $tipo, $this->ultimoRetorno, $idRetorno, $this->id_interacao_cliente, $id_resposta, $motivo, 1);
            } else {
                $this->logSis('ERR', 'Não teve resposta da requisição a tempo' . $resposta);
            }
        } //# FCT Envio Requisição

        //* Busca no BD qual a última interação
        private function verificaInteracao($idInstancia, $idContato)
        {
            include('dados_conexao.php');

            //( Consulta da interação no BD 
            $sql = "SELECT mensagem, data_envio FROM tbl_interacoes WHERE direcao= 1 AND id_instancia = $idInstancia AND id_contato = $idContato ORDER BY id_interacao DESC limit 1";
            $query = mysqli_query($conn['link'], $sql);
            $consultaInteracao = mysqli_fetch_array($query, MYSQLI_ASSOC);
            $numRow = mysqli_num_rows($query);

            if (!$query) {
                $this->logSis('ERR', "Mysql Connect idContato: " . $idContato . " Erro: " . mysqli_error($conn['link']));

                exit(0);
            }

            if ($numRow != 0) {
                //$this->logSis('DEB', "Resultado da última interação. Mensagem:  " . $consultaInteracao['mensagem'] . " Data: " . $consultaInteracao['data_envio']);

                return array(
                    "mensagem" => $consultaInteracao['mensagem'],
                    "id_retorno" => $consultaInteracao['id_retorno'],
                    "dataEnvio" => $consultaInteracao['data_envio']
                );
            }
        }

        //* Verifica o nome 
        private function verificaNome($mensagem)
        {
            $mensagem = mb_strtolower($mensagem);
            $mensagem = str_replace('  ', ' ', $mensagem);
            $mensagem = explode(' ', trim($mensagem));
            $excluidas = array("meu", ",", "xamo", "xamu", "nome", "mim", "chamam", "min", "é", "e", "me", "chamo", "aqui", "eu", "sou", "a", "o", "pode", "me", "chamar", "de", "sou", "chame", "chamo-me",);
            $resultado = array_values(array_diff($mensagem, $excluidas));

            $nome = mb_strtolower($resultado[0], 'UTF-8');

            return ucfirst($nome);
        }

        //* Inserir interação 
        public function inserirInteracao($id_instancia, $direcao, $id_contato, $tipo, $menuAnterior, $id_retorno, $resposta, $id_mensagem, $mensagem, $status)
        {
            include("dados_conexao.php");

            $sql = "INSERT INTO tbl_interacoes(id_instancia, direcao, id_contato, tipo, menu_anterior, id_retorno, resposta, id_mensagem, mensagem, status, data_envio) VALUES ($id_instancia, $direcao, '$id_contato', '$tipo', '$menuAnterior', '$id_retorno', '$resposta', '$id_mensagem', '$mensagem', $status, NOW())";
            //$this->logSis('DEB', 'SQL : ' . $sql);

            $resultado = mysqli_query($conn['link'], $sql);
            if (!$resultado) {
                $this->logSis('ERR', "Mysql Connect Erro: " . mysqli_error($conn['link']));
                exit(0);
            }
            if ($direcao == 0) {
                $this->id_interacao_cliente = mysqli_insert_id($conn['link']);
            }
            $this->id_interacao = mysqli_insert_id($conn['link']);

            if ($resultado != '1') {
                $this->logSis('ERR', 'Insert interação IN. Erro: ' . mysqli_error($conn['link']));
                $this->logSis('DEB', 'SQL : ' . $sql);
            } else {
                return 1;
                $this->logSis('SUC', 'Insert interação IN. ID_Interação: ' . $this->id_interacao);
            }
            mysqli_close($conn['link']);
        }

        //* Verifica a diferença entre datas e retorna em horas 
        public function difDatasEmHoras($dataInicio, $dataFim)
        {
            $datatime1 = new DateTime($dataInicio);
            $datatime2 = new DateTime($dataFim);

            $diff = $datatime1->diff($datatime2);
            $horas = $diff->h + ($diff->days * 24);

            return $horas;
        }

        //* Função de LOG
        public function logSis($tipo, $texto)
        {
            file_put_contents('log.txt', "> " . $tipo . " " . date('d/m/Y h:i:s') . " " . $texto . PHP_EOL, FILE_APPEND);
        }
    } //# Class whatsAppBot


    //executar a classe quando este arquivo for solicitado pela instância
    new whatsAppBot();
} //# Geral
