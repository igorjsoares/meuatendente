<?php {
    class whatsAppBot
    {

        //$id_instancia = $_GET['codigo']; //chatpro-yybwcu3f69   

        public function __construct()
        {
            include("../dados_conexao.php");

            //& Alterar aqui depois os dados para a consulta no BD 
            $tempoMenu = 7200; //Tempo entre a última mensagem e a possibilidade de enviar o menu novamente
            $idInstancia = 3;
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
                        $this->logSis('ERR', "Mysql Connect Num: " . mysqli_connect_error());

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

                        //( Procurar a última interação realizada para ver se tem tempo suficiente para envio do menu 
                        $sql = "SELECT MAX(data_envio) AS ultima_interacao, TIMESTAMPDIFF(SECOND,MAX(data_envio),NOW()) AS segundos FROM tbl_interacoes WHERE direcao = '0' AND id_contato = '$this->id_contato'";
                        $query = mysqli_query($conn['link'], $sql);
                        $numRow = mysqli_num_rows($query);
                        $consultaUltima = mysqli_fetch_array($query, MYSQLI_ASSOC);

                        //& Olhar isso aqui 
                        if ($numRow > 0 && $consultaUltima['segundos'] > $tempoMenu) {
                            $oQueChamar = "Menu";
                        }
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

                    //( Insere a interação que recebida no BD 
                $this->inserirInteracao($this->idInstancia, 0, $this->id_contato, '', '', $idMensagemWhats, $mensagem, 1);

                    $sql = "INSERT INTO tbl_interacoes(direcao, id_contato, tipo, resposta, id_mensagem, mensagem, status, data_envio) VALUES ()";
                    $resultado = mysqli_query($conn['link'], $sql);
                    $this->id_interacao = mysqli_insert_id($conn['link']);
                    if ($resultado != '1') {
                        $this->logSis('ERR', 'Insert interação. Erro: ' . $resultado . mysqli_connect_error());
                    } else {
                        if ($this->primeirocontato == true) { //( Se for o primeiro contato
                            $this->ftcAbertura($decoded['Body']['Info']['RemoteJid'], true);
                        }
                    }
                }
            }
        }


        //* ABERTURA - Primeiras mensagens ou mensagem de erro 
        public function ftcAbertura($remoteJID, $primeiroContato)
        {
            if ($primeiroContato == true) {
                if ($this->conf_cad_dados == 1) { //( Solicitar os dados ao cliente 
                    $aberturaString = $this->msg_cad_dados;
                } else {
                    $aberturaString = $this->msg_inicial;
                }
                $this->sendMessage("Inicial", $remoteJID, $aberturaString);
            }
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
            include("../dados_conexao.php");

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
                $this->inserirInteracao($this->idInstancia, 1, $this->id_contato, 2, $this->id_interacao, $id_resposta, $motivo, 1);
                
            } else {
                $this->logSis('ERR', 'Não teve resposta da requisição a tempo' . $resposta);
            }
        } //# FCT Envio Requisição

        public function inserirInteracao($id_instancia, $direcao, $id_contato, $tipo, $resposta, $id_mensagem, $mensagem, $status){
            include("../dados_conexao.php");

            $sql = "INSERT INTO tbl_interacoes(id_instancia, direcao, id_contato, tipo, resposta, id_mensagem, mensagem, status, data_envio) VALUES ($id_instancia, $direcao, $id_contato, $tipo, '$resposta', '$id_mensagem', '$mensagem', $status, NOW())";
                $resultado = mysqli_query($conn['link'], $sql);
                $idInteracaoIn = mysqli_insert_id($conn['link']);
                if ($resultado != '1') {
                    $this->logSis('ERR', 'Insert interação IN. Erro: ' . mysqli_connect_error());
                } else {
                    $this->logSis('SUC', 'Insert interação IN. ID_Interação: ' . $idInteracaoIn);
                }
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
