<?php {
    class wh_status
    {

        //Status	Significado
        //processing        Transação está em processo de autorização.
        //authorized        Transação foi autorizada. Cliente possui saldo na conta e este valor foi reservado para futura captura, que deve acontecer em até 5 dias para transações criadas com api_key. Caso não seja capturada, a autorização é cancelada automaticamente pelo banco emissor, e o status dela permanece como authorized.
        //paid              Transação paga. Foi autorizada e capturada com sucesso. Para Boleto, significa que nossa API já identificou o pagamento de seu cliente.
        //refunded	        Transação estornada completamente.
        //waiting_payment	Transação aguardando pagamento (status válido para Boleto bancário).
        //pending_refund	Transação do tipo Boleto e que está aguardando confirmação do estorno solicitado.
        //refused           Transação recusada, não autorizada.
        //chargedback       Transação sofreu chargeback. Veja mais sobre isso em nossa central de ajuda
        //analyzing         Transação encaminhada para a análise manual feita por um especialista em prevenção a fraude.
        //pending_review	Transação pendente de revisão manual por parte do lojista. Uma transação ficará com esse status por até 48 horas corridas.

        //$id_instancia = $_GET['codigo']; //chatpro-yybwcu3f69   

        public function __construct()
        {
            include("../dados_conexao.php");

            $idContato = $_GET['id_contato'];

            $fingerprint = $_POST['fingerprint'];
            $event = $_POST['event'];
            $old_status = $_POST['old_status'];
            $desired_status = $_POST['desired_status'];
            $current_status = $_POST['current_status'];
            $object = $_POST['object'];
            $order = $_POST['order'];

            $object = $order['object'];
            $id = $order['id'];
            $company_id = $order['company_id'];
            $status = $order['status'];
            $amount = $order['amount'];
            $payment_link_id = $order['payment_link_id'];

            //( INSERE OS DADOS DO STATUS NO BANCO DE DADOS 
            $sql = "INSERT INTO tbl_fin_status(id_contato, fingerprint, event, old_status, desired_status, current_status, object, id, company_id, status, amount, payment_link_id, create_at) VALUES ($idContato, '$fingerprint', '$event', '$old_status', '$desired_status', '$current_status', '$object', '$id', '$company_id', '$status', '$amount', '$payment_link_id', NOW())";

            $resultado = mysqli_query($conn['link'], $sql);
            if (!$resultado) {
                $this->logSis('ERR', "Mysql Connect Erro: " . mysqli_error($conn['link']));
                exit(0);
            }

            if ($resultado != '1') {
                $this->logSis('ERR', 'Insert STATUS FINANCEIRO IN. Erro: ' . mysqli_error($conn['link']));
                $this->logSis('DEB', 'SQL : ' . $sql);
            } else {
                $this->logSis('SUC', 'Insert STATUS FINANCEIRO. ID_GATEWAY: ' . $id);

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
                    $this->idInstancia = $consultaContato['id_instancia'];
                    $this->APIurl = $consultaContato['endpoint'] . '/api/v1/';
                    $this->token = $consultaContato['token'];
                    
                    $this->logSis('DEB', 'Consulta Contato: ' . $this->numero . '    ' . $this->APIurl . '    ' . $this->token);
                } else { //( O CONTATO NÃO EXISTE 
                    $this->logSis('ERR', "Nao encontrado nenhum contato na FATURA: " . $idContato);
                }
                
                if($current_status == 'paid'){
                    $texto = "Seu pagamento foi confirmado\n" .
                        "Número da ordem: " . $id ."\n".
                        "Status: 🟢 *PAGAMENTO CONFIRMADO*\n\n".
                        "A seguir enviaremos as datas disponíveis para agendamento.";

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
    new wh_status();
} //# Geral
