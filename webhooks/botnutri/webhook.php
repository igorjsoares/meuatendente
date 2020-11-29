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
                    $this->msg_erro =  $consultaInstancia['msg_erro'];
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
                        $sql = "INSERT INTO tbl_contatos(id_instancia, numero, lista_0, teste, created_at) VALUES ('$idInstancia', '$numero', 1, 0, NOW())";
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
            $sql = "SELECT id_interacao, menu_anterior, id_retorno FROM tbl_interacoes WHERE id_instancia = $this->idInstancia AND tipo = 1 AND direcao = 1 AND id_contato = $this->id_contato ORDER BY data_envio DESC LIMIT 1";
            $query = mysqli_query($conn['link'], $sql);
            $numRow = mysqli_num_rows($query);
            $consultaUltima = mysqli_fetch_array($query, MYSQLI_ASSOC);
            $this->menuAnterior = $consultaUltima['menu_anterior'];
            $this->ultimoRetorno = $consultaUltima['id_retorno'];

            $this->logSis('DEB', 'ultimoRetorno: ' . $this->ultimoRetorno);

            //excluir espaços em excesso e dividir a mensagem em espaços.
            //A primeira palavra na mensagem é um comando, outras palavras são parâmetros
            $mensagem = explode(' ', trim($this->stringMensagemAtual));

            if (mb_strtolower($mensagem[0], 'UTF-8') == 'link') {
                $this->logSis('DEB', 'Identificado o comando link');

                $this->solicitaLink($numero, 10000, '1', 'Consulta Online', 10000, 1);
                exit(0);
            }
            if (mb_strtolower($mensagem[0], 'UTF-8') == 'marcar') {
                $this->logSis('DEB', 'Identificado o comando marcar');

                $this->marcarHorario($numero, $this->id_contato);
                exit(0);
            }

            //Confirma se a mensagem realmente não foi enviada do Bot
            if (!$decoded['Body']['Info']['FromMe']) {
                $primeiraPalavraCliente = mb_strtolower($mensagem[0], 'UTF-8');
                $this->logSis('DEB', 'PRIMEIRA PALAVRA: ' . $primeiraPalavraCliente);

                //( Verifica se é um número 
                if (is_numeric($primeiraPalavraCliente) || count($mensagem) == 1) { //Caso seja um número, faz verificação se existe algum menu pra esse número 
                    $this->logSis('DEB', 'É NÚMERO, ou APENAS uma palavra' . $primeiraPalavraCliente);

                    if ($primeiraPalavraCliente == '0') { //Se o cliente escolher 0, tem que retornar
                        $this->logSis('DEB', 'É igual a 0 -> ' . $primeiraPalavraCliente);

                        //( Verifica aqui a última interação que nao seja 0 para retornar o menu_anterior a esse atual 
                        $sql = "SELECT id_interacao, menu_anterior, id_retorno FROM tbl_interacoes WHERE id_instancia = $this->idInstancia AND tipo = 1 AND direcao = 1 AND id_contato = $this->id_contato AND menu_anterior != 0 AND id_retorno = $this->ultimoRetorno ORDER BY data_envio DESC LIMIT 2";
                        $query = mysqli_query($conn['link'], $sql);
                        $numRow = mysqli_num_rows($query);
                        $consultaUltima = mysqli_fetch_array($query, MYSQLI_ASSOC);
                        $this->menuAnterior = $consultaUltima['menu_anterior'];
                        $this->ultimoRetorno = $consultaUltima['id_retorno'];

                        $arrayRetorno = $this->consultaRetorno($this->menuAnterior, '', '');
                        $this->ultimoRetorno = 0;
                        $this->direcaoEnvio($arrayRetorno['tipo'], $numero, $arrayRetorno);
                    } else {
                        $this->logSis('DEB', 'Não é igual a 0 -> ' . $primeiraPalavraCliente);

                        $arrayRetorno = $this->consultaRetorno('', $primeiraPalavraCliente, $this->ultimoRetorno);
                        $this->direcaoEnvio($arrayRetorno['tipo'], $numero, $arrayRetorno);
                    }
                } else { //( A mensagem é um texto 

                    $this->logSis('DEB', 'É TEXTO');

                    $opcaoEscolhida = $this->verficaPalavras($this->ultimoRetorno, $mensagem);
                    $this->logSis('DEB', 'Retorno Palavras: ' . $opcaoEscolhida);

                    if ($opcaoEscolhida == 0) {
                        $this->envioErro($numero, '');
                    } else {
                        $arrayRetorno = $this->consultaRetorno($opcaoEscolhida, '', $this->ultimoRetorno);
                        $this->direcaoEnvio($arrayRetorno['tipo'], $numero, $arrayRetorno);
                    }
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

        //* Envio de erro
        public function envioErro($numero, $textoComplementar)
        {
            $texto = $textoComplementar . utf8_encode($this->msg_erro);

            $this->logSis('DEB', 'Mandando mensagem de erro. Número: ' . $numero . ' Texto: ' . $texto);

            $this->sendMessage("Erro", $numero, $texto, '');
        }

        //* C O N S U L T A  R E T O R N O
        public function consultaRetorno($id_retorno, $primeiraPalavraCliente, $ultimoRetorno)
        {
            $this->logSis('DEB', 'Entrou no Retorno. idRetorno: ' . $id_retorno . ' Palavra: ' . $primeiraPalavraCliente . ' UltimoRetorno: ' . $ultimoRetorno);

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

                $mensagem = "";
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
            } elseif ($tipo == 5) { //Envio receptivo
                $this->receptivo($numero, $retorno);
            } elseif ($tipo == 6) { //Inclusão em lista
                //$this->InOutListas($retorno['nome'], $numero, $retorno, 1);
            } elseif ($tipo == 7) { //Exclusão em lista
                $this->InOutListas($retorno['nome'], $numero, $retorno, 0);
            } elseif ($tipo == 8) { //Marcação horário
                $this->marcarHorario($numero, $retorno);
            }
        }

        //* I N C L U S Ã O  E  E X C L U S Ã O  D E  L I S T A
        //
        public function InOutListas($motivo, $remoteJID, $retorno, $valor)
        {
            include("dados_conexao.php");
            $coluna = 'lista_' . $retorno['coringa'];

            $id_contato = $this->id_contato;
            $sql = "UPDATE tbl_contatos SET $coluna = $valor WHERE id_contato = $id_contato";
            $resuldadoUpdateLista = mysqli_query($conn['link'], $sql);

            if ($resuldadoUpdateLista == true) {
                $this->sendMessage('Retorno', $remoteJID, $retorno['mensagem'], $retorno);
            } else {
                $this->sendMessage('Retorno', $remoteJID, $this->mensagem_erro, $retorno);
            }
        }

        //* OPÇÃO SUPORTE
        //Envia uma mensagem de texto para o número especificado na instância
        public function receptivo($numero, $retorno)
        {
            $texto = "*SUPORTE SOLICITADO*\n" .
                "*Numero:* " . $numero . "\n" .
                "*ID_contato:* " . $this->id_contato . "\n" .
                "http://wa.me/" . $numero;
            $this->logSis('DEB', 'Suporte ' . $texto . ' Para: ' . $this->numerosuporte);


            $data = array('number' => $this->numerosuporte . '@s.whatsapp.net', 'menssage' => $texto);
            $retornoEnvio = $this->sendRequest('Receptivo', 'send_message', $data, '');

            if ($retornoEnvio == true) {
                $this->sendMessage($retorno['nome'], $numero, $retorno['mensagem'], $retorno);
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
                //( Identifica se é uma função receptiva, aqui retorna a resposta da requisição
                if ($motivo == 'Receptivo') {
                    return true;
                    exit(0);
                }
                $id_resposta = $resposta['requestMenssage']['id'];
                if ($retorno == '') {
                    $tipo = '';
                    $idRetorno = '';
                } else {
                    $tipo = $retorno['modo'];
                    $idRetorno = $retorno['id_retorno'];
                }
                //$this->logSis('REQ', 'Chegou aqui - Instância: ' . $this->idInstancia . ' IdContato: ' . $this->id_contato . ' Tipo: ' . $tipo . ' IdInteracaiCliente: ' . $this->id_interacao_cliente . ' IdResposta: ' . $id_resposta . ' Motivo: ' . $motivo);

                $this->inserirInteracao($this->idInstancia, 1, $this->id_contato, $tipo, $this->ultimoRetorno, $idRetorno, $this->id_interacao_cliente, $id_resposta, $motivo, 1);
            } else {
                if ($motivo == 'Receptivo') {
                    return false;
                    exit(0);
                }
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

        //* Funcção que solicita um link de pagamento
        private function solicitaLink($numero, $valorTotal, $itemId, $itemNome, $itemValor, $itemQuantidade)
        {
            $this->logSis('DEB', 'Entrou na solicitação do link');

            include("dados_conexao.php");

            $arrayDados = array(
                "api_key" => "ak_test_EfQ4KKaduJJHqYYDpPJvDjsuH5D1GG",
                "amount" => $valorTotal,
                "items" => array(array(
                    "id" => $itemId,
                    "title" => $itemNome,
                    "unit_price" => $itemValor,
                    "quantity" => $itemQuantidade,
                    "tangible" => true,
                )),
                "payment_config" => array(
                    "boleto" => array(
                        "enabled" => true,
                        "expires_in" => 3
                    ),
                    "credit_card" => array(
                        "enabled" => true,
                        "free_installments" => 1,
                        "interest_rate" => 0.01,
                        "max_installments" => 12
                    ),
                    "default_payment_method" => "credit_card"
                ),
                "postback_config" => array(
                    "orders" => "https://meuatendente.com/webhooks/botnutri/pagamento/wh_status.php?id_contato=$this->id_contato",
                    "transactions" => "https://meuatendente.com/webhooks/botnutri/pagamento/wh_fatura.php?id_contato=$this->id_contato",
                ),
                "max_orders" => 1,
                "expires_in" => 60
            );

            $jsonDados = json_encode($arrayDados);
            $this->logSis('SUC', 'Json Dados -> ' . $jsonDados);

            $pagarme = curl_init();

            curl_setopt($pagarme, CURLOPT_URL, "https://api.pagar.me/1/payment_links");
            curl_setopt($pagarme, CURLOPT_RETURNTRANSFER, 1);
            //curl_setopt($pagarme, CURLOPT_POSTFIELDS, "{ \"query\": \"{ me { name } }\"}");
            //curl_setopt($pagarme, CURLOPT_POSTFIELDS, "{\"query\":\"{\\n  card(id: $idCard) {\\n    id\\n    title\\n    creatorEmail\\n    fields{\\n      name\\n      value\\n    }\\n  }\\n}\"}");
            curl_setopt($pagarme, CURLOPT_POSTFIELDS, $jsonDados);

            curl_setopt($pagarme, CURLOPT_POST, 1);

            $headers = array();
            $headers[] = 'Accept: application/json';
            $headers[] = 'Content-Type: application/json';
            curl_setopt($pagarme, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($pagarme);

            if (curl_errno($pagarme)) {
                $this->logSis('ERR', 'Erro na criação do link. Erro: ' . curl_error($pagarme));
            } else { //CASO A REQUISIÇÃO NÃO RETORNE ERRO
                $arrayResult = json_decode($result, true);
                if (isset($arrayResult['id'])) {
                    $this->logSis('SUC', 'Link criado. Link1: ' . $arrayResult['url']);

                    $id_fin_link = $arrayResult['id_fin_link'];
                    $object = $arrayResult['object'];
                    $id = $arrayResult['id'];
                    $company_id = $arrayResult['company_id'];
                    $amount = $arrayResult['amount'];
                    $item_external_id = $arrayResult['item_external_id'];
                    $item_title = $arrayResult['item_title'];
                    $item_unit_price = $arrayResult['item_unit_price'];
                    $item_quantity = $arrayResult['item_quantity'];
                    $short_id = $arrayResult['short_id'];
                    $url = $arrayResult['url'];
                    $date_created = $arrayResult['date_created'];
                    $date_updated = $arrayResult['date_updated'];
                    $expires_at = $arrayResult['expires_at'];
                    $create_at = $arrayResult['create_at'];



                    //( INSERE OS DADOS DO LINK NO BANCO DE DADOS 
                    $sql = "INSERT INTO tbl_fin_links(id_instancia, id_contato, object, id, company_id, amount, item_external_id, item_title, item_unit_price, item_quantity, short_id, url, date_created, date_updated, expires_at, create_at) VALUES ($this->idInstancia, $this->id_contato, '$object', '$id', '$company_id', '$amount', '$item_external_id', '$item_title', '$item_unit_price', '$item_quantity', '$short_id', '$url', '$date_created', '$date_updated', '$expires_at', NOW())";

                    $resultado = mysqli_query($conn['link'], $sql);
                    if (!$resultado) {
                        $this->logSis('ERR', "Mysql Connect Erro: " . mysqli_error($conn['link']));
                        exit(0);
                    }

                    if ($resultado != '1') {
                        $this->logSis('ERR', 'Insert LINK FINANCEIRO IN. Erro: ' . mysqli_error($conn['link']));
                        $this->logSis('DEB', 'SQL : ' . $sql);
                    } else {
                        $this->logSis('SUC', 'Insert LINK FINANCEIRO. ID_GATEWAY: ' . $id);

                        //( Envia mensagem para o cliente com o link gerado

                        $texto = "*Seu link de pagamento foi gerado com sucesso!*\n\n_Ao acessar o link abaixo você será direcionado para a página de pagamento da PAGAR.ME._\n\n" . $url . "\nAssim que confirmarmos o pagamento, entraremos em contato por aqui para marcar o horário.";
                        $this->sendMessage('EnvioLink', $numero, $texto, '');
                    }
                } else {
                    $this->logSis('ERR', 'Erro ao tentar gerar o link ' . $result);
                }
            }
        }

        //* Funcção que solicita um link de pagamento
        private function marcarHorario($numero, $retorno)
        {
            $this->logSis('DEB', 'Entrou na marcação de horário');
            $this->logSis('DEB', 'Coringa: ' . $retorno['coringa']);
            
            include("dados_conexao.php");
            include("horarios.php");
            include("servicos.php");

            switch ($retorno['coringa']) {
                case 'mes':
                    $arrayMeses = fctConsultaMeses();
                    if ($arrayMeses == false) {
                        logSis('ERR', 'Usuário consultando e não encontrando nenhum horário disponível. Usuário: ' . $idContato);
                    } else {
                        $texto = $retorno['mensagem'];
                        foreach ($arrayMeses as $value) {
                            $texto .= $value['mes'] . ' - ' . $value['nome_mes'] . "\n";
                        }
                        $jsonDados = json_encode($arrayMeses);
                        $arrayRetorno = array(
                            "modo" => $retorno['tipo'], //tipo
                            "id_retorno" => $retorno['id_retorno'],
                            "opcoes" => $jsonDados
                        );

                        $this->sendMessage($retorno['nome'], $numero, $texto, $arrayRetorno);
                    }
                    break;

                default:
                    # code...
                    break;
            }
        }

        //* Função que faz a análise das palavras dentro da mensagem e as palavras de cada opção em questão
        public function verficaPalavras($ultimoRetorno, $mensagem)
        {

            include("dados_conexao.php");
            $sql = "SELECT id_opcao, resposta, palavras FROM tbl_opcoes WHERE id_instancia = $this->idInstancia AND id_retorno = $ultimoRetorno";
            $query = mysqli_query($conn['link'], $sql);

            while ($opcao = mysqli_fetch_array($query)) {

                $palavras = explode(',', trim($opcao['palavras']));
                $palavrasEncontradas = count(array_intersect($mensagem, $palavras));

                if ($palavrasEncontradas > 0) {

                    return $opcao['resposta'];
                    exit(0);
                }
            }

            return 0;
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
