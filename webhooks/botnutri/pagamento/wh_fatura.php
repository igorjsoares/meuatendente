<?php {
    class wh_faturas
    {

        //$id_instancia = $_GET['codigo']; //chatpro-yybwcu3f69   

        public function __construct()
        {
            include("../dados_conexao.php");

            $idContato = $_GET['id_contato'];

            $id_fin_fatura = $_POST['id_fin_fatura'];
            $id = $_POST['id'];
            $fingerprint = $_POST['fingerprint'];
            $event = $_POST['event'];
            $old_status = $_POST['old_status'];
            $desired_status = $_POST['desired_status'];
            $current_status = $_POST['current_status'];
            $object = $_POST['object'];
            $acquirer_id = $_POST['transaction']['acquirer_id'];
            $tid = $_POST['transaction']['tid'];
            $date_created = $_POST['transaction']['date_created'];
            $date_updated = $_POST['transaction']['date_updated'];
            $amount = $_POST['transaction']['amount'];
            $authorized_amount = $_POST['transaction']['authorized_amount'];
            $paid_amount = $_POST['transaction']['paid_amount'];
            $payment_method = $_POST['transaction']['payment_method'];
            $boleto_url = $_POST['transaction']['boleto_url'];
            $boleto_barcode = $_POST['transaction']['boleto_barcode'];
            $boleto_expiration_date = $_POST['transaction']['boleto_expiration_date'];
            $order_id = $_POST['transaction']['order_id'];




            //( INSERE OS DADOS DO STATUS NO BANCO DE DADOS 
            $sql = "INSERT INTO tbl_fin_faturas(id, id_contato, fingerprint, event, old_status, desired_status, current_status, object, acquirer_id, tid, date_created, date_updated, amount, authorized_amount, paid_amount, payment_method, boleto_url, boleto_barcode, boleto_expiration_date, order_id, create_at) VALUES ('$id', $idContato, '$fingerprint', '$event', '$old_status', '$desired_status', '$current_status', '$object', '$acquirer_id', '$tid', '$date_created', '$date_updated', '$amount', '$authorized_amount', '$paid_amount', '$payment_method', '$boleto_url', '$boleto_barcode', '$boleto_expiration_date', '$order_id', NOW())";

            $resultado = mysqli_query($conn['link'], $sql);
            if (!$resultado) {
                $this->logSis('ERR', "Mysql Connect Erro: " . mysqli_error($conn['link']));
                exit(0);
            }

            if ($resultado != '1') {
                $this->logSis('ERR', 'Insert FATURA FINANCEIRO IN. Erro: ' . mysqli_error($conn['link']));
                $this->logSis('DEB', 'SQL : ' . $sql);
            } else {
                $this->logSis('SUC', 'Insert FATURA FINANCEIRO. ID_GATEWAY: ' . $id);


                //( Consulta o contato no BD o endPoint e o token
                $sql = "SELECT c.numero, i.id_instancia, endpoint, token FROM tbl_contatos c, tbl_instancias i WHERE c.id_contato = $idContato AND c.id_instancia = i.id_instancia";
                $query = mysqli_query($conn['link'], $sql);
                $consultaContato = mysqli_fetch_array($query, MYSQLI_ASSOC);
                $numRow = mysqli_num_rows($query);
                $this->logSis('SQL', "SQL: " . $sql);

                if (!$query) {
                    $this->logSis('ERR', "Mysql Connect Erro: " . mysqli_error($conn['link']));
                    exit(0);
                }

                if ($numRow != 0) { //( O CONTATO EXISTE NO BANCO DE DADOS  
                    $this->numero = $consultaContato['numero'];
                    $this->id_instancia = $consultaContato['id_instancia'];
                    $this->APIurl = $consultaContato['endpoint'] . '/api/v1/';
                    $this->token = $consultaContato['token'];
                    
                    $this->logSis('DEB', 'Consulta Contato: ' . $this->numero . '    ' . $this->APIurl . '    ' . $this->token);
                } else { //( O CONTATO NÃO EXISTE 
                    $this->logSis('ERR', "Nao encontrado nenhum contato na FATURA: " . $idContato);
                }


                //( Verifica se é um boleto
                if ($payment_method == 'boleto') {

                    $texto = "Você optou por pagamento via *Boleto Bancário*\n" .
                        "Número da ordem: " . $order_id ."\n".
                        "Neste método de pagamento, aguardaremos a compensação do pagamento, e assim que confirmada entraremos em contato por aqui com link para marcarmos o horário do atendimento.\n" .
                        "Clique no link abaixo para acessar o boleto, ou copie o código de barras abaixo do link.\n\n" .
                        $boleto_url . "\n\n" .
                        $boleto_barcode;

                    $this->logSis('DEB', 'Texto: ' . $texto);

                    $this->sendMessage('criacaoBoleto', $this->numero, $texto, '');
                }
            }
        }

        //* E N V I O  T E X T O
        //Prepara para envio da mensagem de texto
        public function sendMessage($motivo, $numero, $text, $retorno)
        {

            $data = array('number' => $numero . '@s.whatsapp.net', 'menssage' => $text);
            $this->sendRequest($motivo, 'send_message', $data, $retorno);
        }

        //* E N V I O
        //Envia a requisição
        public function sendRequest($motivo, $method, $data, $retorno)
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
                $this->logSis('REQ', 'Chegou aqui - Instância: ' . $this->idInstancia . ' IdContato: ' . $this->id_contato . ' Tipo: ' . $tipo . ' IdInteracaiCliente: ' . $this->id_interacao_cliente . ' IdResposta: ' . $id_resposta . ' Motivo: ' . $motivo);

                $this->inserirInteracao($this->idInstancia, 1, $this->id_contato, $tipo, $this->ultimoRetorno, $idRetorno, $this->id_interacao_cliente, $id_resposta, $motivo, 1);
            } else {
                if ($motivo == 'Receptivo') {
                    return false;
                    exit(0);
                }
                $this->logSis('ERR', 'Não teve resposta da requisição a tempo' . $resposta);
            }
        } //# FCT Envio Requisição

        //* Inserir interação 
        public function inserirInteracao($id_instancia, $direcao, $id_contato, $tipo, $menuAnterior, $id_retorno, $resposta, $id_mensagem, $mensagem, $status)
        {
            include("../dados_conexao.php");

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

        //* Função de LOG
        public function logSis($tipo, $texto)
        {
            file_put_contents('../log.txt', "> " . $tipo . " " . date('d/m/Y h:i:s') . " " . $texto . PHP_EOL, FILE_APPEND);
        }
    } //# Class whatsAppBot


    //executar a classe quando este arquivo for solicitado pela instância
    new wh_faturas();
} //# Geral
