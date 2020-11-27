<?php {
    class wh_faturas
    {

        //$id_instancia = $_GET['codigo']; //chatpro-yybwcu3f69   

        public function __construct()
        {
            include("../dados_conexao.php");

            $idContato = $_POST['id_contato'];

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
            $sql = "INSERT INTO tbl_fin_faturas(id_contato, id_fin_fatura, id, fingerprint, event, old_status, desired_status, current_status, object, acquirer_id, tid, date_created, date_updated, amount, authorized_amount, paid_amount, payment_method, boleto_url, boleto_barcode, boleto_expiration_date, order_id, create_at) VALUES ($idContato, id_fin_fatura, '$id', '$fingerprint', '$event', '$old_status', '$desired_status', '$current_status', '$object', '$acquirer_id', '$tid', '$date_created', '$date_updated', '$amount', '$authorized_amount', '$paid_amount', '$payment_method', '$boleto_url', '$boleto_barcode', '$boleto_expiration_date', '$order_id', NOW())";

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
    new wh_faturas();
} //# Geral
