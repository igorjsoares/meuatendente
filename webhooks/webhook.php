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
            file_put_contents('inputs.log', $input . PHP_EOL, FILE_APPEND);



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
                    $conf_cad_dados =  $consultaInstancia['conf_cad_dados'];
                    $mensagem_cad_dados =  $consultaInstancia['mensagem_cad_dados'];
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


                    //( Insere a interação que recebemos no BD 
                    $sql = "INSERT INTO tbl_interacoes(direcao, id_contato, tipo, resposta, id_mensagem, mensagem, status, data_envio) VALUES (0, $this->id_contato, '', '', '$idMensagemWhats', '$mensagem', 1, FROM_UNIXTIME($timestamp))";
                    $resultado = mysqli_query($conn['link'], $sql);
                    $this->id_interacao = mysqli_insert_id($conn['link']);
                    if ($resultado != '1') {

                        $this->logSis('ERR', 'Insert interação. Erro: ' . $resultado . mysqli_connect_error());
                    } else {

                        
                    }
                }
            }
        }


        //* Função de LOG
        public function logSis($tipo, $texto)
        {
            file_put_contents('log.txt', "> " . $tipo . " " . date('d/m/Y h:i:s') . $texto . PHP_EOL, FILE_APPEND);
        }
    } //# Class whatsAppBot


    //executar a classe quando este arquivo for solicitado pela instância
    new whatsAppBot();
} //# Geral
