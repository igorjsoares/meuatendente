<?php {
    class whatsAppBot
    {
        public function __construct()
        {

            //Recebe o corpo do Json enviado pela instância
            $json = file_get_contents('php://input');
            $decoded = json_decode($json, true); //Decodifica

            //Grava o JSON-body no arquivo de debug
            ob_start();
            var_dump($decoded);
            $input = ob_get_contents();
            ob_end_clean();

            //Coloca para salvar todas as requisições recebidas em um arquivo de log
		//Só para acompanhar o que está recebendo no início dos testes
            file_put_contents('inputs.log', $input . PHP_EOL, FILE_APPEND);



            // Verifica SE É uma mensagem recebida 
            if (isset($decoded['Type'])) {
                $this->logSis('DEB', 'Tipo de mensagem: ' . $decoded['Type']);
                if ($decoded['Type'] == 'receveid_message') {

		}
}
}
}
}