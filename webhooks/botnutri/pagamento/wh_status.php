<?php {
    class wh_status
    {

        //$id_instancia = $_GET['codigo']; //chatpro-yybwcu3f69   

        public function __construct()
        {
            include("../dados_conexao.php");

            //Recebe o corpo do Json enviado pela instância
            //$json = file_get_contents('php://input');
            
            parse_str(file_get_contents("php://input"), $data);
            // Cast it to an object
            $data = (object)$data;
            
            var_dump($data);
            
            file_put_contents('inputs2.log', $data . PHP_EOL, FILE_APPEND);

            $decoded = json_decode($data, true); //Decodifica

            //Grava o JSON-body no arquivo de debug
            ob_start();
            var_dump($decoded);
            $input = ob_get_contents();
            ob_end_clean();

            //Coloca para salvar todas as requisições recebidas em um arquivo de log
            file_put_contents('inputs.log', $input . PHP_EOL, FILE_APPEND);

            $object = $decoded['object'];
            $id = $decoded['id'];
            $company_id = $decoded['company_id'];
            $status = $decoded['status'];
            $amount = $decoded['amount'];
            $payment_link_id = $decoded['payment_link_id'];

            //( INSERE OS DADOS DO STATUS NO BANCO DE DADOS 
            $sql = "INSERT INTO tbl_fin_status(object, id, company_id, status, amount, payment_link_id, create_at) VALUES ('$object', '$id', '$company_id', '$status', '$amount', '$payment_link_id', NOW())";

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
