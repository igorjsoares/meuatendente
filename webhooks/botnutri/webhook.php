<?php {
    class whatsAppBot
    {

        //$id_instancia = $_GET['codigo']; //chatpro-yybwcu3f69   

        public function __construct()
        {
            include("dados_conexao.php");
            include("servicos.php");

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
            if (isset($decoded['Type'])) {
                if ($decoded['Type'] == 'error_instace') {
                    $this->logSis('DEB', 'error_instace - Tentativa da API: ' . $decoded['ErrorCount']);
                    $this->reload($decoded['ErrorCount'], 0);
                    exit(0);
                }
                $this->logSis('DEB', 'Tipo de mensagem: ' . $decoded['Type']);
                if ($decoded['Type'] == 'receveid_message') {
                    $mensagemDeTexto = true;
                } else if (isset($decoded['Type']) && ($decoded['Type'] == 'receveid_audio_message' || $decoded['Type'] == 'whatsapp.StickerMessage' || $decoded['Type'] == 'receveid_video_message' || $decoded['Type'] == 'receveid_image_message' || $decoded['Type'] == 'receveid_document_message' || $decoded['Type'] == 'whatsapp.ContactMessage' || $decoded['Type'] == 'whatsapp.LocationMessage')) {
                    $this->logSis('ERR', 'Cliente tentou enviar mídias');
                    $mensagemDeTexto = false;
                } else {
                    exit(0);
                }
                //if (isset($decoded['Type']) && ($decoded['Type'] == 'receveid_message' || $decoded['Type'] == 'receveid_audio_message')) {
                $RemoteJid = $decoded['Body']['Info']['RemoteJid'];
                $RemoteJidArray = explode("@", $RemoteJid);
                $numero = $RemoteJidArray[0];
                $this->numerocliente = $numero;
                $this->numero = $numero;
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

                    if ($mensagemDeTexto == false) {
                        $this->logSis('DEB', 'Identificou mensagem de texto false.');
                        $this->sendMessage('ErroFormatoMensagem', $this->numerocliente, "Esse atendimento funciona somente com envio de texto.\nFavor enviar sempre mensagens de texto.", "");
                        exit(0);
                    }
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

                        if ($consultaContato['bloqueio_bot'] == 1) {
                            $this->logSis('DEB', "Usuário bloqueado bot tentando contato. Contato: " . $consultaContato['id_contato']);

                            exit(0);
                        }

                        $this->id_contato = $consultaContato['id_contato'];
                        $this->idContato = $consultaContato['id_contato'];
                        $nome = $consultaContato['nome'];
                        $email = $consultaContato['email'];
                        $fase = $consultaContato['fase'];
                        $teste = $consultaContato['teste'];

                        //( Verifica as ultimas 4 mensagens trocadas, se tanto as respostas quanto os envios tiverem sido duplicados para o código
                        //( Isso é pra tentar evitar BOT x BOT
                        $result = fctConsultaParaArray(
                            'ConsultaBotXBot',
                            "SELECT direcao, mensagem FROM tbl_interacoes WHERE id_contato = $this->idContato ORDER BY id_interacao DESC LIMIT 4",
                            array('direcao', 'mensagem')
                        );
                        $arrayMensagem = [];
                        $mensagemEnviada = '';
                        foreach ($result as $linha) { //traz as mensagens para um array simples
                            if ($mensagemEnviada == '' && $linha['direcao'] == 0) {
                                $mensagemEnviada = $linha['mensagem'];
                            }
                            array_push($arrayMensagem, $linha['mensagem']);
                        }
                        $contagem = array_values(array_count_values($arrayMensagem)); //conta os itens e redefine os indices do array
                        if (count($contagem) == 2 && $contagem[0] == 2 && $mensagemEnviada == $mensagem) { // Se tiver somente duas mensagens e cada uma tiver 2 mensagens é redundante
                            array_push($arrayMensagem, $linha['mensagem']);
                            $this->logSis('ERR', 'BOTXBOT PAROU. idContato: ' . $this->idContato);
                            exit(0);
                        }
                        /*else{
                            $this->logSis('ERR', 'BOTXBOT PASSOU. Contagem: ' . count($contagem).' Contagem0: '.$contagem[0].' Mensagem Enviada: '.$mensagemEnviada.' Mensagem do Cliente: '.$mensagem.' ArrayMensagem->'.print_r($arrayMensagem, true));
                            exit(0);
                        }*/
                    } else { //( O CONTATO NÃO EXISTE 
                        $this->primeirocontato = true;

                        //CONTATO NÃO EXISTE 
                        //( Insere o contato no banco de dados 
                        $sql = "INSERT INTO tbl_contatos(id_instancia, numero, lista_0, teste, created_at) VALUES ('$idInstancia', '$numero', 1, 0, NOW())";
                        $resultado = mysqli_query($conn['link'], $sql);
                        $this->id_contato = mysqli_insert_id($conn['link']);
                        $this->idContato = mysqli_insert_id($conn['link']);
                        if ($resultado != '1') {
                            $this->logSis('ERR', 'Insert Contatos. Erro: ' . $resultado . mysqli_connect_error());
                        }
                    }

                    //( Insere a interação que foi recebida no BD 
                    //& Quando inserir a mensagem do cliente, já trazer o ID para colocar na coluna id_retorno na mensagem que vamos enviar. 
                    //& Verificar também o retorno de erro, caso não consiga inserir o cliente. 
                    $resultado = $this->inserirInteracao($this->idInstancia, 0, $this->id_contato, '', '', '', '', '', '', $idMensagemWhats, $mensagem, 1);

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
                exit(0);
            }

            //( ULTIMA INTERAÇÃO DE MENU (TIPO 1) OU DE MARCAÇÃO DE HORÁRIO (TIPO 8) - O que provavelmente o cliente está respondendo 
            $sql = "SELECT id_interacao, menu_anterior, id_retorno, tipo, subtipo, opcoes_variaveis, menu_anterior FROM tbl_interacoes WHERE id_instancia = $this->idInstancia AND (tipo = 1 OR tipo = 8 OR tipo = 9) AND direcao = 1 AND id_contato = $this->id_contato ORDER BY data_envio DESC LIMIT 1";
            $query = mysqli_query($conn['link'], $sql);
            $numRow = mysqli_num_rows($query);
            $consultaUltima = mysqli_fetch_array($query, MYSQLI_ASSOC);
            $this->menuAnterior = $consultaUltima['menu_anterior'];
            $this->ultimoRetorno = $consultaUltima['id_retorno'];
            $this->opcoesVariaveis = $consultaUltima['opcoes_variaveis'];

            $this->logSis('DEB', 'ultimoRetorno: ' . $this->ultimoRetorno);
            $this->logSis('DEB', 'Sql: ' . $sql . ' consultaUltima->' . print_r($consultaUltima, true));

            //excluir espaços em excesso e dividir a mensagem em espaços.
            //A primeira palavra na mensagem é um comando, outras palavras são parâmetros
            $mensagem = explode(' ', trim($this->stringMensagemAtual));
            $this->mensagem = explode(' ', trim($this->stringMensagemAtual));

            if (mb_strtolower($mensagem[0], 'UTF-8') == 'menu') {
                $this->logSis('DEB', 'Identificado o comando menu');

                $this->envioMenuRaiz($this->numero, '');
                exit(0);
            }
            if (mb_strtolower($mensagem[0], 'UTF-8') == 'suporte') {
                $this->logSis('DEB', 'Identificado o comando suporte');
                $arrayRetorno = array(
                    'nome' => 'Suporte',
                    'mensagem' => "O suporte foi solicitado, em breve nossa equipe entrará em contato no seu Whatsapp."
                );

                $this->receptivo($this->numero, $arrayRetorno);
                exit(0);
            }
            /* if (mb_strtolower($mensagem[0], 'UTF-8') == 'link') {
                $this->logSis('DEB', 'Identificado o comando link');

                $this->solicitaLink($numero, 1, 10000, '1', 'Consulta Online', 10000, 1);
                exit(0);
            }
            if (mb_strtolower($mensagem[0], 'UTF-8') == 'marcar') {
                $this->logSis('DEB', 'Identificado o comando marcar');

                $this->marcarHorario($numero, $this->id_contato);
                exit(0);
            } */

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
                        $sql = "SELECT id_interacao, menu_anterior, id_retorno FROM tbl_interacoes WHERE id_instancia = $this->idInstancia AND (tipo = 1 OR tipo = 8) AND direcao = 1 AND id_contato = $this->id_contato AND menu_anterior != 0 AND id_retorno = $this->ultimoRetorno ORDER BY data_envio DESC LIMIT 2";
                        $query = mysqli_query($conn['link'], $sql);
                        $numRow = mysqli_num_rows($query);
                        $consultaUltima = mysqli_fetch_array($query, MYSQLI_ASSOC);
                        $this->logSis('DEB', 'Consulta Ultima. SQL: ' . $sql . ' ->' . print_r($consultaUltima, true));

                        $this->menuAnterior = $consultaUltima['menu_anterior'];
                        $this->ultimoRetorno = $consultaUltima['id_retorno'];

                        $arrayRetorno = $this->consultaRetorno($this->menuAnterior, '', '', '');
                        $this->ultimoRetorno = 0;
                        $this->direcaoEnvio($arrayRetorno['tipo'], $numero, $arrayRetorno);

                        //( É número mas não é igual a 0
                    } else {
                        $this->logSis('DEB', 'Não é igual a 0 -> ' . $primeiraPalavraCliente);

                        $arrayRetorno = $this->consultaRetorno('', $primeiraPalavraCliente, $this->ultimoRetorno, $consultaUltima);
                        $this->direcaoEnvio($arrayRetorno['tipo'], $numero, $arrayRetorno);
                    }
                } else { //( A mensagem é um texto 

                    $this->logSis('DEB', 'É TEXTO');

                    $opcaoEscolhida = $this->verficaPalavras($this->ultimoRetorno, $mensagem, '');
                    $this->logSis('DEB', 'Retorno Palavras: ' . $opcaoEscolhida);

                    if ($opcaoEscolhida == 0) {
                        $this->envioErro($numero, '');
                    } else {
                        $arrayRetorno = $this->consultaRetorno($opcaoEscolhida, '', $this->ultimoRetorno, $consultaUltima);
                        $this->direcaoEnvio($arrayRetorno['tipo'], $numero, $arrayRetorno);
                    }
                }
            }
        }

        //* Envio Menu raiz
        public function envioMenuRaiz($numero, $textoComplementar)
        {
            $arrayRetorno = $this->consultaRetorno($this->menuRaiz, '', '', '');
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
        public function consultaRetorno($id_retorno, $primeiraPalavraCliente, $ultimoRetorno, $consultaUltima)
        {
            $this->logSis('DEB', 'Entrou no Retorno. idRetorno: ' . $id_retorno . ' Palavra: ' . $primeiraPalavraCliente . ' UltimoRetorno: ' . $ultimoRetorno . ' Tipo da consulta: ' . $consultaUltima['tipo']);

            include("dados_conexao.php");
            include_once("horarios.php");
            include_once("servicos.php");



            if ($consultaUltima['tipo'] == 8) { //( Verifica se o retorno trata-se de uma marcação de horário


                //( Verifica qual o último subtipo para pesquisar o próximo retorno de acordo com o próximo subtipo
                if ($consultaUltima['subtipo'] == 'mes') {
                    $proximoSubtipo = 'dia';
                } else if ($consultaUltima['subtipo'] == 'dia') {
                    $proximoSubtipo = 'hora';
                } else if ($consultaUltima['subtipo'] == 'hora') { //( Envia a pergunta de confirmação
                    $this->logSis('DEB', 'Entrou no subtipo Hora');
                    if (is_numeric($primeiraPalavraCliente)) {
                        $this->logSis('DEB', 'É número');

                        //( Decodifica o Json que foi salvo no BD
                        $opcoes = json_decode($this->opcoesVariaveis, true);
                        $this->logSis('DEB', 'opcoes->' . print_r($opcoes, true));

                        $indice = array_search($primeiraPalavraCliente, array_column($opcoes, 'ind'));
                        $idHorario = $opcoes[$indice]['id'];
                        $this->logSis('DEB', 'idHorario->' . $idHorario);


                        //( Consulta o horário encontrado pra ver se está disponível ainda

                        $result = fctConsultaParaArray(
                            'ConsultaHorario',
                            "SELECT *, DATE_FORMAT(horario, '%d/%m/%Y %H:%i') AS hora_formatada FROM tbl_horarios WHERE status = 1 AND horario >= NOW() AND id_horario = $idHorario",
                            array('hora_formatada')
                        );
                        $this->logSis('DEB', 'result->' . print_r($result, true));

                        if ($result == false) {
                            //& VEr se realmente vai ser possível escolher um outro horário
                            //& Sugestão aqui seria voltar ao menu anterior
                            $this->sendMessage('MensageErro', $this->numero, "Esse horário não está mais disponível, favor escolher uma outra data.", "");
                        } else {
                            $horaFormatada = $result[0]['hora_formatada'];
                            $texto = "CONFIRME O HORÁRIO\n";
                            $texto .= "*$horaFormatada*\n\n";
                            $texto .= "Você confirma esse horário?";

                            $arrayRetorno = array(
                                "modo" => 9, //tipo confirmação
                                "subtipo" => 'horario',
                                "id_retorno" => '',
                                "opcoes" => $idHorario
                            );

                            //& Organizar o array retorno
                            $this->confirmacao($texto, $arrayRetorno);
                        }
                    } else {
                        $this->sendMessage('MensageErro', $this->numero, "Responda somente com o número referente à opção desejada.", "");
                    }
                }

                //( Faz a pesquisa do retorno
                $sql = "SELECT * FROM tbl_retornos WHERE tipo = 8 AND coringa = '$proximoSubtipo'";
            } else if ($consultaUltima['tipo'] == 9) { //( Uma solicitação de confirmação

                //( Verifica que é uma confirmação de horário 
                if ($consultaUltima['subtipo'] == 'horario') {

                    //( Verifica se tem SIM ou NÃO na mensagem do cliente
                    $nao = $this->verficaPalavras('', $this->mensagem, array('não', 'nao', 'NÃO', 'Nao', 'NAO', 'NO', 'no'));
                    $sim = $this->verficaPalavras('', $this->mensagem, array('sim', 'Sim', 'Si', 'si', 'SI', 'sin', 'Sin', 'SIN', 'SIM'));


                    if ($nao == 1) { //( Se tiver NÃO, é enviada o MENU RAIZ 
                        $this->envioMenuRaiz($this->numero, "*OPERAÇÃO CANCELADA*\n\n");
                    } else if ($sim == 1) { //( Se tiver SIM, é reservado o horário
                        $this->reservaHorario($this->opcoesVariaveis);
                    } else { //( Se na mensagem não tem nem SIM nem Não, é enviado a mensagem de erro dizendo que não entendeu
                        $this->sendMessage('MensageErro', $this->numero, "Não compreendi a sua resposta, favor responder exatamente como foi solicitado.", "");
                    }
                }
            } else if ($id_retorno == '') { //ou seja, não sei qual o retorno
                $sql = "SELECT * FROM tbl_retornos WHERE id_retorno = (SELECT resposta FROM tbl_opcoes WHERE id_instancia = $this->idInstancia AND indice = '$primeiraPalavraCliente' AND id_retorno = $ultimoRetorno)";
            } else { //Sei qual o retorno atual
                //$idInstancia = $this->idInstancia;
                $sql = "SELECT * FROM tbl_retornos WHERE id_instancia = $this->idInstancia AND id_retorno = $id_retorno";
            }

            $this->logSis('DEB', $sql);

            $query = mysqli_query($conn['link'], $sql);
            $consultaRetorno = mysqli_fetch_array($query, MYSQLI_ASSOC);
            $this->logSis('DEB', 'Sql: ' . $sql . ' consultaRetorno->' . print_r($consultaRetorno, true));

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

                //Teste DEploy
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
                $this->logSis('DEB', 'Retorno->' . print_r($retorno, true));

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
            } elseif ($tipo == 10) {
                if ($retorno['coringa'] == 1) {
                    $valor = 9900;
                    $nome = 'CONSULTA NUTRICIONAL ONLINE';
                } else if ($retorno['coringa'] == 2) {
                    $valor = 2000;
                    $nome = 'MENTORIA NUTRICIONAL ONLINE';
                }
                $this->solicitaLink($numero, $retorno['coringa'], $valor, $retorno['coringa'], $nome, $valor, 1);
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
                    //$textoEmail = "Olá! \n\nComo prometi, segue o link para acessar o conteúdo.\n\nQualquer dúvida pode nos responder esse e-mail ou chamar nosso atendimento no Whatsapp.\n\nhttps://nutrimarimartins.com.br/comoemagrecer.html\n\nEquipe Nutri Mari Martins";
                    $statusEnvioEmail = $this->enviarEmail($email, "");

                    //& Colocar aqui uma inteligência pra que o cliente reveja o e-mail e possa alterar o mesmo
                    if ($statusEnvioEmail == true) { //( Conseguiu enviar
                        //( Atualiza a TBL_CONTATOS com a fase 1, ou seja já enviou o e-mail 
                        $atualizacaoBD = $this->atualizaCampo('tbl_contatos', 'fase', 1, "id_contato='$idContato'");
                        $this->sendMessage("okEmail", $numero, "Enviei um e-mail com o conteúdo para $email, entre na sua caixa de e-mail e aproveite esse conteúdo feito com todo carinho pra você.\n\nEsse Whatsap aqui é o nosso canal oficial, sempre que quiser falar comigo, pode me chamar por aqui, envindo um oi.\n\nNutri Mari Martins.\n\n_Caso não receba o e-mail, verifique na caixa de SPAM do seu e-mail_", '');
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

            //$mensagem = $texto;
            $mensagem = "<html><body>";
            /*
            $mensagem .= "Olá! Que bom que você está disposto a cuidar da sua saúde e emagrecimento! ";
            $mensagem .= "Fico muito feliz de te receber aqui. Quero que saiba que estou te enviando materiais e ferramentas muito importantes para o emagrecimento e acompanhamento de resultados!";
            $mensagem .= "Acompanhe o vídeo para aprender como montar uma alimentação saudável, prazerosa e emagrecedora! E o link para o cálculo de imc, quantidade de água e plano alimentar estarão logo abaixo! Não se esqueça que no vídeo explicarei a importância de cada um. ";
            $mensagem .= "Um beijo da nutri";
            $mensagem .= "Qualquer dúvida pode nos responder esse e-mail ou chamar nosso atendimento no Whatsapp.\n\n";
            */
            $mensagem .= "<h2><sup><strong>Ol&aacute;! Que bom que voc&ecirc; est&aacute; disposto a cuidar da sua sa&uacute;de e emagrecimento!&nbsp;</strong></sup></h2>";
            $mensagem .= "<p>Fico muito feliz de te receber aqui. Quero que saiba que estou te enviando <strong>materiais e ferramentas muito importantes para o emagrecimento</strong> e acompanhamento de resultados!</p>";
            $mensagem .= "<p>Clique no link abaixo e assista ao&nbsp;v&iacute;deo para aprender como <strong>montar uma alimenta&ccedil;&atilde;o saud&aacute;vel</strong>, prazerosa e emagrecedora! Os&nbsp;links para o <strong>c&aacute;lculo de imc</strong>, <strong>quantidade de &aacute;gua e plano alimentar</strong>&nbsp;tamb&eacute;m estar&atilde;o no link abaixo!</p>";
            $mensagem .= "<h3>N&atilde;o se esque&ccedil;a que no v&iacute;deo explicarei a import&acirc;ncia de cada um.&nbsp;</h3>";
            $idContato = $this->idContato;
            $mensagem .= '<p><a href="https://nutrimarimartins.com.br/ferramentas/emagrecimentodefinitivo.php?idcontato=' . $idContato . '">Clique aqui para o conte&uacute;do gratuito.</a></p>';
            $mensagem .= "<p>Um beijo da nutri.</p>";
            $mensagem .= "</html></body>";


            $myEmail = "contato@meuatendente.com.br"; //é necessário informar um e-mail do próprio domínio
            $headers = "From: contato@meuatendente.com.br\r\n";
            $headers .= "Reply-To: contato@meuatendente.com.br\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

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
            $this->logSis('DEB', 'Requisição de envio de TEXTO. Motivo: ' . $motivo . ' Número: ' . $numero . ' Texto: ' . $text);
            $this->logSis('DEB', 'Requisição de envio de TEXTO. retorno: ' . print_r($retorno, true));
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
                    $subTipo = '';
                    $idRetorno = '';
                } else {
                    $tipo = $retorno['modo'];
                    $subTipo = $retorno['subtipo'];
                    $idRetorno = $retorno['id_retorno'];
                }
                if (isset($retorno['opcoes']) && $retorno['opcoes'] != '') {
                    $opcoes = $retorno['opcoes'];
                } else {
                    $opcoes = $retorno['opcoes'];
                }
                //$this->logSis('REQ', 'Chegou aqui - Instância: ' . $this->idInstancia . ' IdContato: ' . $this->id_contato . ' Tipo: ' . $tipo . ' IdInteracaiCliente: ' . $this->id_interacao_cliente . ' IdResposta: ' . $id_resposta . ' Motivo: ' . $motivo);

                $this->inserirInteracao($this->idInstancia, 1, $this->id_contato, $tipo, $subTipo, $opcoes, $this->ultimoRetorno, $idRetorno, $this->id_interacao_cliente, $id_resposta, $motivo, 1);
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

        //* Reserva o horário desejado e retorna uma confirmação ou negação e o menu raiz
        private function reservaHorario($idHorario)
        {
            include_once("servicos.php");

            $result = fctConsultaParaArray(
                'ConsultaPagamentoDisponível',
                "SELECT s.id_fin_status FROM tbl_fin_status s, tbl_fin_links l WHERE s.payment_link_id = l.id AND s.status = 'paid' AND l.id_produto = 1 AND l.id_contato = $this->idContato AND s.status_uso = 0 LIMIT 1",
                array('id_fin_status')
            );
            $this->logSis('DEB', 'Resultado Retornado: ' . json_encode($result));
            if ($result === false) {
                $this->logSis('DEB', 'Cliente tentou fazer a marcação só que já não tinha Ordem disponível. id_cliente: ' . $this->idContato);

                //& Dar opção do cliente solicitar o link de pagamento, visualizar os pagamentos pendentes ou entrar em contato com o suporte.
                $this->envioMenuRaiz($this->numero, "Não foi encontrado pagamento confirmado para esse produto, certifique-se que foi gerado um link de pagamento e que o pagamento foi mesmo efetuado.\n\n");
            } else {
                $idFinanceiro = $result[0]['id_fin_status'];
                $idContato = $this->idContato;
                $result = fctUpdate(
                    'ReservandoHorário',
                    "UPDATE tbl_horarios SET id_contato = $idContato, status = 2, id_order = '$idFinanceiro' WHERE id_horario = $idHorario"
                );

                if ($result === false) {
                    $this->logSis('ERRO', 'Cliente tentou fazer a reserva e não conseguiu. idCliente: ' . $this->idContato . ' id_horario: ' . $idHorario . ' id_order: ' . $idFinanceiro);
                    $this->sendMessage(
                        'MensageErro',
                        $this->numero,
                        "Não foi possível reservar o horário, favor enviar a palavra *'HORÁRIOS'* pra saber se está confirmado, caso não esteja, tente novamente fazer a reserva do horário, enviando a palavra *MENU* para reiniciar o processo.\nCaso o problema persista, envie a palavra *SUPORTE* para falar com nossos atendentes.",
                        ""
                    );
                } else {
                    $result = fctUpdate(
                        'AtualizandoOrder',
                        "UPDATE tbl_fin_status SET status_uso = 1 WHERE id_fin_status = '$idFinanceiro'"
                    );

                    if ($result === false) {
                        $this->logSis('ERRO', 'Cliente marcou o horário mas não atualizou a tabela tbl_fin_status, no campo status_uso. idCliente: ' . $this->idContato . ' id_horario: ' . $idHorario . ' id_order: ' . $idFinanceiro);
                        $this->sendMessage(
                            'MensageErro',
                            $this->numero,
                            "Favor contacte o suporte, envie a palavra *SUPORTE* para falar com nossos atendentes.",
                            ""
                        );
                    } else {
                        $this->envioMenuRaiz($this->numero, "Seu horário foi *RESERVADO COM SUCESSO!*\nNo dia e hora marcados entrarei em contato nesse número para a realização da consulta. Obrigada!\n\n");
                    }
                }
            }
        }

        //* Inserir interação 
        public function inserirInteracao($id_instancia, $direcao, $id_contato, $tipo, $subTipo, $opcoesVariaveis, $menuAnterior, $id_retorno, $resposta, $id_mensagem, $mensagem, $status)
        {
            include("dados_conexao.php");

            $sql = "INSERT INTO tbl_interacoes(id_instancia, direcao, id_contato, tipo, subtipo, opcoes_variaveis, menu_anterior, id_retorno, resposta, id_mensagem, mensagem, status, data_envio) VALUES ($id_instancia, $direcao, '$id_contato', '$tipo', '$subTipo', '$opcoesVariaveis', '$menuAnterior', '$id_retorno', '$resposta', '$id_mensagem', '$mensagem', $status, NOW())";
            $this->logSis('DEB', 'SQL : ' . $sql);

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
        private function solicitaLink($numero, $idProduto, $valorTotal, $itemId, $itemNome, $itemValor, $itemQuantidade)
        {
            $this->logSis('DEB', 'Entrou na solicitação do link');

            include("dados_conexao.php");

            $arrayDados = array(
                "api_key" => "ak_live_o44xwEuGQmAITbHmrpQkdDrB4wgWgH",
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
                    $id_produto = 1; // 1 = Consulta | 2 = Mentoria
                    $sql = "INSERT INTO tbl_fin_links(id_instancia, id_contato, id_produto, object, id, company_id, amount, item_external_id, item_title, item_unit_price, item_quantity, short_id, url, date_created, date_updated, expires_at, create_at) VALUES ($this->idInstancia, $this->id_contato, $idProduto, '$object', '$id', '$company_id', '$amount', '$item_external_id', '$item_title', '$item_unit_price', '$item_quantity', '$short_id', '$url', '$date_created', '$date_updated', '$expires_at', NOW())";

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
            $this->logSis('DEB', 'Ultimo retorno: ' . $this->ultimoRetorno . ' Menu Anterior: ' . $this->menuAnterior);

            $this->ultimoRetorno = $this->menuAnterior;


            //include("dados_conexao.php");
            include_once("horarios.php");
            include_once("servicos.php");

            switch ($retorno['coringa']) {

                    //( Caso o próximo retorno seja a pesquisa de meses
                case 'mes':
                    $this->logSis('DEB', 'Entrou no case mes');


                    $result = fctConsultaParaArray(
                        'ConsultaPagamentoDisponível',
                        "SELECT s.id_fin_status FROM tbl_fin_status s, tbl_fin_links l WHERE s.payment_link_id = l.id AND s.status = 'paid' AND l.id_produto = 1 AND l.id_contato = $this->idContato AND s.status_uso = 0 LIMIT 1",
                        array('id_fin_status')
                    );
                    $this->logSis('DEB', 'Resultado Retornado: ' . json_encode($result));
                    if ($result === false) {
                        $this->logSis('DEB', 'Cliente tentou iniciar a marcação só que já não tinha Ordem disponível. id_cliente: ' . $this->idContato);

                        $this->sendMessage('ErroMarcaçãoSemPagamento', $numero, "Não existe pagamantos confirmados para marcação.\nAntes de marcar você deve efetuar o pagamento solicitando um link de pagamento.\nPara solicitar um link de pagamento verifique a mensagem anterior e envie o número correspondente.", '');

                        exit(0);
                    }


                    $arrayMeses = fctConsultaMeses();

                    if ($arrayMeses == false) {
                        logSis('ERR', 'Usuário consultando e não encontrando nenhum horário disponível. Usuário: ' . $idContato);
                    } else {
                        $this->logSis('DEB', 'Entrou nos meses');

                        $texto = $retorno['mensagem'];
                        foreach ($arrayMeses as $value) {
                            $texto .= $value['mes'] . ' - ' . $value['nome_mes'] . "\n";
                            $this->logSis('DEB', 'mês pra dentro->' . $value['nome_mes']);
                        }
                        $jsonDados = json_encode($arrayMeses);
                        $arrayRetorno = array(
                            "modo" => $retorno['tipo'], //tipo
                            "subtipo" => 'mes',
                            "id_retorno" => $retorno['id_retorno'],
                            "opcoes" => $jsonDados
                        );

                        $this->sendMessage($retorno['nome'], $numero, $texto, $arrayRetorno);
                    }
                    break;

                    //( Caso o próximo retorno seja a pesquisa de dias    
                case 'dia':
                    $this->logSis('DEB', 'Entrou no case dia');

                    //( Analisa a mensagem do cliente para ver e tem alguma referência do mês
                    $mes = fctAnaliseMensagemMes($this->mensagem);

                    if ($mes == false) { //( Na mensagem do cliente não tem nada relacionado a mês
                        //& Verificar esse retorno de erro
                        $this->sendMessage('MensageErro', $this->numero, 'Não foi identificado na sua mensagem nenhum mês, favor enviar apenas o número referente ao mês desejado', "");
                    } else {

                        //( Faz a consulta dos dias disponíveis
                        $arrayDias = fctConsultaDias($mes);

                        if ($arrayDias == false) {
                            logSis('ERR', 'Usuário consultando e não encontrando nenhum horário disponível na consulta de dias. Usuário: ' . $this->idContato);
                        } else {
                            $this->logSis('DEB', 'Entrou nos dias');

                            $texto = $retorno['mensagem'];
                            foreach ($arrayDias as $value) {
                                $texto .= $value['dia'] . ' - ' . $value['nome_dia'] . "\n";
                                $this->logSis('DEB', 'dia pra dentro->' . $value['nome_dia']);
                            }

                            $arrayRetorno = array(
                                "modo" => $retorno['tipo'], //tipo
                                "subtipo" => 'dia',
                                "id_retorno" => $retorno['id_retorno'],
                                "opcoes" => $mes
                            );

                            $this->sendMessage($retorno['nome'], $numero, $texto, $arrayRetorno);
                        }
                    }



                    break;

                    //( Caso o próximo retorno seja a pesquisa de horários  
                case 'hora':
                    $this->logSis('DEB', 'Entrou no case hora.');
                    $primeiraPalavra = $this->mensagem[0];
                    $this->logSis('DEB', 'Entrou no case hora. Mensagem: ' . $primeiraPalavra);

                    if (is_numeric($primeiraPalavra)) { //( A mensagem enviada é um número
                        $this->logSis('DEB', 'A mensagem enviada é um número');

                        $mes = $this->opcoesVariaveis; //no caso das interações de marcação para HORA, traz o mês 
                        $this->logSis('DEB', 'Mensagem é numérica. Mês de referência: ' . $mes);

                        //( Faz a consulta dos horários disponíveis
                        $arrayHora = fctConsultaHorarios($primeiraPalavra, $mes);

                        if ($arrayHora == false) {
                            logSis('ERR', 'Usuário consultando e não encontrando nenhum horário disponível na consulta de dias. Usuário: ' . $this->idContato);
                        } else {
                            $this->logSis('DEB', 'Entrou nas horas');
                            $this->logSis('DEB', 'Dia semana: ' . $arrayHora[0]['dia_semana']);

                            $texto = $retorno['mensagem'];
                            //$textoComplementar = "*Dia " . $primeiraPalavra . '/' . $mes . "*\n";
                            $textoComplementar = "*Dia " . $primeiraPalavra . '/' . $mes . ' - ' . $arrayHora[0]['dia_semana'] . "*\n";
                            $montaTextoOpcoes = $this->montaTextoOpcoes($arrayHora, 'id_horario', 'hora');

                            $textoOpcoes = $montaTextoOpcoes['textoOpcoes'];
                            $arrayParaJson = $montaTextoOpcoes['arrayParaJson'];
                            //$arrayRetorno['opcoes_variaveis'] = json_encode($arrayParaJson);

                            $arrayRetorno = array(
                                "modo" => $retorno['tipo'], //tipo
                                "subtipo" => 'hora',
                                "id_retorno" => $retorno['id_retorno'],
                                "opcoes" => json_encode($arrayParaJson)
                            );

                            $texto = $textoComplementar . $texto . $textoOpcoes;

                            $this->sendMessage($retorno['nome'], $numero, $texto, $arrayRetorno);
                        }
                    } else { //( A mensagem enviada não é um número
                        $this->logSis('DEB', 'A mensagem enviada não é um número');

                        $this->sendMessage(
                            'MensageErro',
                            $this->numero,
                            "Favor enviar somento número referente ao dia escolhido.",
                            ""
                        );
                    }

                    break;

                    //& Fazer agora a escolha de fato do horário, com mensagem de confirmação se é realmente o horário desejado.
                    //& Trabalhar o envio do 0 para voltar ao menu anterior
                default:
                    # code...
                    break;
            }
        }

        //* Função utilizada para confirmar alguma informação 
        public function confirmacao($texto, $arrayRetorno)
        {
            $this->logSis('DEB', 'Entrou no confirmação. Texto: ' . $texto . ' ArrayRetorno-> ' . print_r($arrayRetorno));
            $texto .= "\nResponda SIM ou NÃO.";
            $this->sendMessage('CONFIRM', $this->numero, $texto, $arrayRetorno);
        }

        //* Monta o texto com as opções e devolve tanto o texto quanto o json
        public function montaTextoOpcoes($arrayOpcoes, $nomeIndice, $nomeValor)
        {
            $this->logSis('DEB', 'Monta texto. NomeIndice: ' . $nomeIndice . ' Nome valor: ' . $nomeValor . ' arrayOpcoes-> ' . print_r($arrayOpcoes, true));

            $textoOpcoes = "";
            $arrayParaJson = [];
            $indice = 0;
            foreach ($arrayOpcoes as $linha) {
                $indice += 1;

                $textoOpcoes .= "\n*" . $indice . "*. Às " . $linha[$nomeValor];

                array_push($arrayParaJson, array(
                    'ind' => $indice,
                    'id' => $linha[$nomeIndice]
                ));
            }
            return array(
                'textoOpcoes' => $textoOpcoes,
                'arrayParaJson' => $arrayParaJson
            );
        }

        //* Função que faz a análise das palavras dentro da mensagem e as palavras de cada opção em questão
        public function verficaPalavras($ultimoRetorno, $mensagem, $palavrasProprias)
        {
            if ($ultimoRetorno == '') {
                $palavrasEncontradas = count(array_intersect($mensagem, $palavrasProprias));
                if ($palavrasEncontradas > 0) {

                    return 1;
                    exit(0);
                }
            } else {
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
        public function retornoErro($texto)
        {
            $this->logSis('DEB', 'Entrou no retornoErro');
            $textoRetorno = "Houve um erro na comunicação, favor responder novamente a última pergunta.";
            if ($texto != '') {
                $textoRetorno .= "\nCaso persista, envie a palavra *SUPORTE* e informa o erro abaixo:\n";
                $textoRetorno .= "_" . $texto . "_";
            }
            $this->sendMessage('MensageErro', $this->numero, $textoRetorno, "");

            exit(0);
        }

        //* Função de LOG
        public function logSis($tipo, $texto)
        {
            file_put_contents('log.txt', "> " . $tipo . " " . date('d/m/Y h:i:s') . " " . $texto . PHP_EOL, FILE_APPEND);
        }

        //* Função de reload
        public function reload($tentativasAPI, $tentativasInternas)
        {
            $tentativasInternas += 1;
            $APIurl = "v4.chatpro.com.br/chatpro-9piq49nyf9" . '/api/v1/';
            $token = "69fa9a02548516e0e7507d0265b1caf2e3fde824";
            $method = 'reload';
            $url = 'https://' . $APIurl . $method;

            $options = stream_context_create(['http' => [
                'method'  => 'GET',
                'header'  => "Content-type: application/json\r\nAuthorization: $token\r\n"
            ]]);

            $response = file_get_contents($url, false, $options);

            file_put_contents('log_conexao.txt', "> REQ " . date('d/m/Y h:i:s') . " " . 'Resp Requisição: ' . $response . PHP_EOL, FILE_APPEND);


            $resposta = json_decode($response, true);
            $status = $resposta['status'];
            if ($status == "ok") {
                file_put_contents('log_conexao.txt', "> SUC " . date('d/m/Y h:i:s') . " " . 'Reload com sucesso: ' . PHP_EOL, FILE_APPEND);
            } else {
                file_put_contents('log_conexao.txt', "> ERR " . date('d/m/Y h:i:s') . " " . 'Não conseguiu recarregar. Tentativa API: ' . $tentativasAPI . ' Tentativas Internas: ' . $tentativasInternas . PHP_EOL, FILE_APPEND);

                $this->status($tentativasAPI, $tentativasInternas);
            }
        }

        //* Função para verificação de status
        public function status($tentativasAPI, $tentativasInternas)
        {
            sleep(10);

            $APIurl = "v4.chatpro.com.br/chatpro-9piq49nyf9" . '/api/v1/';
            $token = "69fa9a02548516e0e7507d0265b1caf2e3fde824";
            $method = 'status';
            $url = 'https://' . $APIurl . $method;

            $options = stream_context_create(['http' => [
                'method'  => 'GET',
                'header'  => "Content-type: application/json\r\nAuthorization: $token\r\n"
            ]]);

            $response = file_get_contents($url, false, $options);

            file_put_contents('log_conexao.txt', "> REQ " . date('d/m/Y h:i:s') . " " . 'Resp Requisição: ' . $response . PHP_EOL, FILE_APPEND);


            $resposta = json_decode($response, true);
            $conectado = $resposta['connected'];
            if ($conectado == "true") {
                $this->logSis('SUC', 'Status verificado e está ON');
                file_put_contents('log_conexao.txt', "> SUC " . date('d/m/Y h:i:s') . " " . 'Status verificado e está ON' . PHP_EOL, FILE_APPEND);
            } else {
                file_put_contents('log_conexao.txt', "> ERR " . date('d/m/Y h:i:s') . " " . 'Verificado Status, está OFF. Tentativa API: ' . $tentativasAPI . ' Tentativas Internas: ' . $tentativasInternas . PHP_EOL, FILE_APPEND);

                if ($tentativasInternas >= 5) {
                    include_once("servicos.php");

                    $ultimoEmailEnviado = fctConsultaParaArray(
                        'ConsultaUltimoEmail',
                        'SELECT ultima FROM tbl_conexao WHERE id_conexao = 1',
                        array('ultimo_email')
                    );

                    $ultimoEmailEnviado = $ultimoEmailEnviado[0]['ultimo_email'];
                    $agora = date('Y-m-d H:i:s');

                    $intervalo = abs(strtotime($ultimoEmailEnviado) - strtotime($agora)) / 60;
                    file_put_contents('log_conexao.txt', "> DEB " . date('d/m/Y h:i:s') . " " . 'Ultimo e-mail de erro enviado a: ' . $intervalo . PHP_EOL, FILE_APPEND);

                    if ($intervalo > 30) {
                        fctUpdate(
                            'AtualizaUltimoEmail',
                            'UPDATE tbl_conexao SET ultima = NOW() WHERE id_conexao = 1'
                        );

                        file_put_contents('log_conexao.txt', "> ERR " . date('d/m/Y h:i:s') . " " . 'Mais de 5 tentativas. Enviado email. Tentativa API: ' . $tentativasAPI . ' Tentativas Internas: ' . $tentativasInternas . PHP_EOL, FILE_APPEND);
                        $this->enviarEmail('igorjsoares@gmail.com', 'Instância NUTRI MARI MARTINS deslogada!');
                        exit(0);
                    } else {
                        exit(0);
                    }
                }
                $this->reload($tentativasAPI, $tentativasInternas);
            }
        }
    } //# Class whatsAppBot


    //executar a classe quando este arquivo for solicitado pela instância
    new whatsAppBot();
} //# Geral
