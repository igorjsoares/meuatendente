    <?php {
        class whatsAppBot
        {

            //$id_instancia = $_GET['codigo']; //chatpro-yybwcu3f69   

            public function __construct()
            {
                include("dados_conexao.php");

                $tempoMenu = 7200; //Tempo entre a última mensagem e a possibilidade de enviar o menu novamente
                $idInstancia = 2;
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
                //file_put_contents('inputs.log',$input.PHP_EOL,FILE_APPEND); 

                //( Verifica se é uma mensagem recebida 
                if (isset($decoded['Type']) && ( $decoded['Type'] == 'receveid_message' || $decoded['Type'] == 'receveid_audio_message')) {
                    $RemoteJid = $decoded['Body']['Info']['RemoteJid'];
                    $RemoteJidArray = explode("@", $RemoteJid);
                    $numero = $RemoteJidArray[0];
                    $this->numerocliente = $numero;
                    $tipoNumero = $RemoteJidArray[1];
                    $idMensagemWhats = $decoded['Body']['Info']['Id'];
                    $timestamp = $decoded['Body']['Info']['Timestamp'];
                    $mensagem = $decoded['Body']['Text'];


                    //( Busca informações da instância CHATPRO no banco de dados 
                    $sql = "SELECT * FROM tbl_instancias WHERE id_instancia = $idInstancia";
                    $query = mysqli_query($conn['link'], $sql);
                    $consultaInstancia = mysqli_fetch_array($query, MYSQLI_ASSOC);
                    $numRow = mysqli_num_rows($query);
                    if (!$query) {
                        echo "Erro ao tentar conectar no MYSQL " . mysqli_connect_error();
                        file_put_contents('log.txt', "> ERR " . date('d/m/Y h:i:s') . " Mysql Connect: " . mysqli_connect_error() . PHP_EOL, FILE_APPEND);
                        exit(0);
                    }
                    if ($numRow == 0) { //VERIFICA SE EXISTE NO BANCO DE DADOS
                        file_put_contents('log.txt', "> ERR " . date('d/m/Y h:i:s') . " Instância N/E: " . $id_instancia . PHP_EOL, FILE_APPEND);
                        exit(0);
                    } else {
                        $this->APIurl  = $consultaInstancia['endpoint'] . '/api/v1/';
                        $this->token  = $consultaInstancia['token'];
                        $this->numerosuporte =  $consultaInstancia['numero_suporte'];
                        //$APIurl = $consultaInstancia['endpoint'] . '/api/v1/';
                        //$token = $consultaInstancia['token'];
                        $limite = $consultaInstancia['limite'];
                        $status = $consultaInstancia['status'];
                        $nome = $consultaInstancia['nome'];
                    }


                    //( Verifica se é uma mensagem recebida de um número ou GRUPO 
                    if ($tipoNumero == 's.whatsapp.net') {

                        //( Consulta o contato no BD 
                        $sql = "SELECT * FROM tbl_contatos WHERE numero = $numero AND id_instancia = $idInstancia";
                        $query = mysqli_query($conn['link'], $sql);
                        $consultaContato = mysqli_fetch_array($query, MYSQLI_ASSOC);
                        $numRow = mysqli_num_rows($query);

                        if (!$query) {
                            file_put_contents('log.txt', "> ERR " . date('d/m/Y h:i:s') . " Mysql Connect Num: " . mysqli_connect_error() . PHP_EOL, FILE_APPEND);
                            exit(0);
                        }

                        if ($numRow != 0) { //( O CONTATO EXISTE NO BANCO DE DADOS 
                            //CONTATO EXISTE
                            $this->id_contato = $consultaContato['id_contato'];
                            $nome = $consultaContato['nome'];
                            $this->primeirocontato = false;



                            //( Procura se essa mensagem já foi recebida e tratada, caso exista ele acaba tudo 
                            $sql = "SELECT id_interacao FROM tbl_interacoes WHERE id_mensagem = '$idMensagemWhats'";
                            $query = mysqli_query($conn['link'], $sql);
                            $consultaMensagemWhats = mysqli_fetch_array($query, MYSQLI_ASSOC);
                            $numRow = mysqli_num_rows($query);

                            if ($numRow > 0 && $consultaMensagemWhats['resposta'] == '') {
                                //@ EXISTE INTERAÇÕES SEM RESPOSTAS 
                            } else if ($numRow > 0) {
                                exit(0);
                            }
                        } else { //( O CONTATO NÃO EXISTE 

                            //CONTATO NÃO EXISTE 
                            //( Insere o contato no banco de dados 
                            $sql = "INSERT INTO tbl_contatos(id_instancia, numero, teste, created_at) VALUES ('$idInstancia', '$numero', 0, NOW())";
                            $resultado = mysqli_query($conn['link'], $sql);
                            $this->id_contato = mysqli_insert_id($conn['link']);
                            if ($resultado != '1') {
                                file_put_contents('log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Insert Contatos. Erro: ' . $resultado . mysqli_connect_error() . PHP_EOL, FILE_APPEND);
                            }
                            $this->primeirocontato = true;
                        }


                        //( Procurar a última interação realizada para ver se tem tempo suficiente para envio do menu 
                        $sql = "SELECT MAX(data_envio) AS ultima_interacao, TIMESTAMPDIFF(SECOND,MAX(data_envio),NOW()) AS segundos FROM tbl_interacoes WHERE direcao = '0' AND id_contato = '$this->id_contato'";
                        $query = mysqli_query($conn['link'], $sql);
                        $numRow = mysqli_num_rows($query);
                        $consultaUltima = mysqli_fetch_array($query, MYSQLI_ASSOC);

                        if ($numRow > 0 && $consultaUltima['segundos'] > $tempoMenu) {
                            $oQueChamar = "Menu";
                        }


                        //( Insere a interação que recebemos no BD 
                        $sql = "INSERT INTO tbl_interacoes(direcao, id_contato, tipo, resposta, id_mensagem, mensagem, status, data_envio) VALUES (0, $this->id_contato, '', '', '$idMensagemWhats', '$mensagem', 1, FROM_UNIXTIME($timestamp))";
                        $resultado = mysqli_query($conn['link'], $sql);
                        $this->id_interacao = mysqli_insert_id($conn['link']);
                        if ($resultado != '1') {

                            file_put_contents('log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Insert interação. Erro: ' . $resultado . mysqli_connect_error() . PHP_EOL, FILE_APPEND);
                        } else {

                            //excluir espaços em excesso e dividir a mensagem em espaços.
                            //A primeira palavra na mensagem é um comando, outras palavras são parâmetros
                            $mensagem = explode(' ', trim($decoded['Body']['Text']));

                            if ($this->primeirocontato != true) {
                                //Confirma se a mensagem realmente não foi enviada do Bot
                                if (!$decoded['Body']['Info']['FromMe']) {

                                    //( Inicia a verificação se a resposta já foi dada 
                                    switch (mb_strtolower($mensagem[0], 'UTF-8')) {
                                        case 'sim': {
                                                $comando = 'SIM';
                                                break;
                                            }
                                        case 'proposta': {
                                                $comando = 'PROPOSTA';
                                                break;
                                            }
                                        case 'formula': {
                                                $comando = 'FORMULA';
                                                break;
                                            }
                                        case 'fórmula': {
                                                $comando = 'FORMULA';
                                                break;
                                            }
                                    }

                                    //( Procura se esse TIPO DE RESPOSTA já foi dado 
                                    $idContato = $this->id_contato;
                                    $idInstancia = $this->idInstancia;
                                    $sql = "SELECT id_interacao FROM tbl_interacoes WHERE id_contato = $idContato and id_instancia = $idInstancia AND tipo = 2 AND mensagem = '$comando'";
                                    $query = mysqli_query($conn['link'], $sql);
                                    $numRow = mysqli_num_rows($query);

                                    if ($numRow > 0) { //( Resposta já foi dada 

                                        //( Procura se a resposta de DUPLICADA já foi dada mais de 2 vezes 
                                        $sql = "SELECT id_interacao FROM tbl_interacoes WHERE id_contato = $idContato and id_instancia = $idInstancia AND tipo = 2 AND mensagem = 'DUPLICADA'";
                                        $query = mysqli_query($conn['link'], $sql);
                                        $numRow = mysqli_num_rows($query);

                                        if ($numRow > 2) { //( Resposta já foi dada mais de 2 vezes 
                                            exit(0);
                                        } else {
                                            $this->opcao4($decoded['Body']['Info']['RemoteJid'], false);
                                        }
                                    } else {
                                        //verifique qual comando contém a primeira palavra e chamea a função
                                        //( Faz as verificações da mensagem e chama a função de acordo com o foi enviado 
                                        switch (mb_strtolower($mensagem[0], 'UTF-8')) {

                                            case 'sim': {
                                                    $this->opcao1($decoded['Body']['Info']['RemoteJid'], true);
                                                    break;
                                                }
                                            case 'proposta': {
                                                    $this->opcao3($decoded['Body']['Info']['RemoteJid'], true);
                                                    break;
                                                }
                                            case 'formula': {
                                                    $this->opcao2($decoded['Body']['Info']['RemoteJid'], true);
                                                    break;
                                                }
                                            case 'fórmula': {
                                                    $this->opcao2($decoded['Body']['Info']['RemoteJid'], true);
                                                    break;
                                                }
                                            default: {
                                                    $this->welcome($decoded['Body']['Info']['RemoteJid'], true);
                                                    break;
                                                }
                                        }
                                    }
                                }
                            } else {
                                $this->welcome($decoded['Body']['Info']['RemoteJid'], true);
                            }
                        }
                    }
                }
            }


            //* WELCOME - Primeira mensagem ou mensagem de erro 
            public function welcome($remoteJID, $noWelcome = false)
            {
                if ($this->primeirocontato == true) {
                    $welcomeString = "Eu sou o Xandão Santos, jornalista e estrategista em marketing político.  \nTomei a liberdade de te fazer este contato e saber se você tem o interesse em uma estratégia para a sua eleição. \nCaso tenha interesse, gostaria que você respondesse enviando somente a palavra *SIM* e aguarde...\n";
                    $this->sendMessage("Inicial", $remoteJID, $welcomeString);
                } elseif ($noWelcome == true) {
                    $welcomeString = "_Esse nosso primeiro contato é uma conversa automática que irá te informar sobre meus serviços e te encaminhará para que possamos nos falar._\nCaso não tenha interesse em receber mais mensagens, basta ignorar essa mensagem. Caso tenha, leia a mensagem anterior e envie o comando solicitado corretamente.";
                    $this->sendMessage("Erro", $remoteJID, $welcomeString);
                }
            }


            //* OPÇÃO 1 - SIM - O cliente tem interesse em saber mais 
            public function opcao1($remoteJID, $noWelcome = false)
            {
                $data = array(
                    'caption' => "\nVou lhe explicar como você poderá ter uma experiência fantástica com o que chamamos de *Marketing de Prospecção*. \n" .
                        "Mas antes, gostaria que você me conhecesse um pouco melhor, exatamente para que você tenha segurança de que está falando com quem entende de política.\n" .
                        "Segue um breve vídeo para que você possa conhecer quem é Xandão Santos.\n" .
                        "_Após o vídeo, para finalmente saber mais sobre a estratégia, envie a palavra_ *FORMULA* que em breve te enviarei um conteúdo sobre a estratégia.\n",
                    'url' => 'http://newprospect.com.br/boot/assets/biografia.mp4',
                    'number' => $remoteJID
                );
                $this->sendRequest("SIM", 'send_message_file_from_url', $data);
            }

            //* OPÇÃO 2 - PROPOSTA - Cliente está interessado em ver a proposta 
            public function opcao2($remoteJID, $noWelcome = false)
            {
                $data = array(
                    'caption' => "\nAcesse nosso site: http://www.formula60.com.br" .
                        "\n\nFicou interessado? Então digite *PROPOSTA* que eu entrarei em contato com você.",
                    'url' => 'http://newprospect.com.br/boot/assets/formula.mp4',
                    'number' => $remoteJID
                );
                $this->sendRequest("FORMULA", 'send_message_file_from_url', $data);
            }

            //* OPÇÃO 3 - DECIDIU - Cliente está interessado na proposta 
            public function opcao3($remoteJID, $noWelcome = false)
            {
                include("../dados_conexao.php");
                $numero = $this->numerocliente;
                $sql = "INSERT INTO tbl_novos(fonte, telefone, status, update_at, create_at) VALUES (1, $numero, 1, NOW(), NOW())";
                $resultado = mysqli_query($conn['link'], $sql);
                if ($resultado == '1') {
                    $this->sendMessage("PROPOSTA", $remoteJID, "\nParabéns pela sua decisão!\n" .
                        "Aguarde que em breve entraremos em contato aqui mesmo pelo Whatsapp.\n");
                } else {
                    file_put_contents('log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Aceitou Formula. Número: ' . $remoteJID . ' SQL: ' . $sql . ' Erro: ' . $resultado . mysqli_error($conn['link']) . PHP_EOL, FILE_APPEND);
                }
            }

            //* OPÇÃO 4 - DUPLICADA - Cliente enviou comando já passado 
            public function opcao4($remoteJID)
            {
                $this->sendMessage("DUPLICADA", $remoteJID, "\nEssa mensagem já foi enviada anteriormente.\n" .
                    "Basta subir nossa conversa que verá a mensagem, digite um comando de acordo com o final de uma mensagem já recebida.\n");
            }

            //* E N V I O  I M A G E M
            public function envioImagem($motivo, $remoteJID, $caption, $url)
            {
                $data = array(
                    'url' => $url,
                    'number' => $remoteJID,
                    'caption' => $caption
                );
                $this->sendRequest($motivo, 'send_message_file_from_url', $data);
            }

            //* P R E P A R O  E N V I O
            //Prepara para envio da mensagem de texto
            public function sendMessage($motivo, $remoteJID, $text)
            {
                $data = array('number' => $remoteJID, 'menssage' => $text);
                $this->sendRequest($motivo, 'send_message', $data);
            }

            //* E N V I O
            //Envia a requisição
            public function sendRequest($motivo, $method, $data)
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

                file_put_contents('log.txt', "> REQ " . date('d/m/Y h:i:s') . ' Resp Requisição: ' . $response . PHP_EOL, FILE_APPEND);

                //return $response;

                $resposta = json_decode($response, true);
                $statusEnvio = $resposta['message'];
                if ($statusEnvio == "Mensagem enviada com sucesso" || $statusEnvio == "Mensagem Enviada") {
                    $id_resposta = $resposta['requestMenssage']['id'];
                    $sql = "INSERT INTO tbl_interacoes(id_instancia, direcao, id_contato, tipo, resposta, id_mensagem, mensagem, status, data_envio) VALUES ($this->idInstancia, 1, $this->id_contato, 2, $this->id_interacao, '$id_resposta', '$motivo', 1, NOW())";
                    $resultado = mysqli_query($conn['link'], $sql);
                    $idInteracaoIn = mysqli_insert_id($conn['link']);
                    if ($resultado != '1') {
                        file_put_contents('log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Insert interação IN. Erro: ' . $resultado . PHP_EOL, FILE_APPEND);
                    } else {
                        file_put_contents('log.txt', "> SUC " . date('d/m/Y h:i:s') . ' Insert interação IN. ID_Interação: ' . $idInteracaoIn . PHP_EOL, FILE_APPEND);

                    }
                } else {
                    file_put_contents('log.txt', "> ERR " . date('d/m/Y h:i:s') . ' Não teve resposta da requisição a tempo' . $resposta . PHP_EOL, FILE_APPEND);
                }
            } //# FCT Envio Requisição


        } //# Class whatsAppBot


        //executar a classe quando este arquivo for solicitado pela instância
        new whatsAppBot();
    } //# Geral

    ?>