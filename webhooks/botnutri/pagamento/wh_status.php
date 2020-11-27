<?php {
    class wh_status
    {

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
                //& CONTINUAR AQUI PARA AÇÕES PÓS CONFIRMAÇÃO DE PAGAMENTO
            }
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
