<?php {
    class whatsAppBot
    {

        //$id_instancia = $_GET['codigo']; //chatpro-yybwcu3f69   

        public function __construct()
        {
            date_default_timezone_set('Europe/Lisbon');
            include("dados_conexao.php");

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
            if (isset($decoded['Type']) && ($decoded['Type'] == 'receveid_message' || $decoded['Type'] == 'receveid_audio_message')) {
                $RemoteJid = $decoded['Body']['Info']['RemoteJid'];
                $RemoteJidArray = explode("@", $RemoteJid);
                $numero = $RemoteJidArray[0];
                $this->numerocliente = $numero;
                $this->numeroCliente = $numero;
                $tipoNumero = $RemoteJidArray[1];
                $idMensagemWhats = $decoded['Body']['Info']['Id'];
                $timestamp = $decoded['Body']['Info']['Timestamp'];
                $mensagem = $decoded['Body']['Text'];
                $this->stringMensagemAtual = $mensagem;


                //( Busca informações da instância CHATPRO no banco de dados 
                $this->consultaInstancia($idInstancia);

                //() Verifica se NÃO É uma mensagem recebida de um número ou GRUPO 
                if ($tipoNumero == 's.whatsapp.net') {

                    //( Verifica se a hora atual está dentro do horário de atendimento
                    if ($this->horarioAtendimento() == true) { //Dentro do horário de atendimento

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
                            $this->id_contato = $consultaContato['id_contato'];
                            $this->idContato = $consultaContato['id_contato'];
                            $nome = $consultaContato['nome'];
                            $email = $consultaContato['email'];
                            $fase = $consultaContato['fase'];
                            $teste = $consultaContato['teste'];
                        } else { //( O CONTATO NÃO EXISTE 
                            $this->primeirocontato = true;

                            //CONTATO NÃO EXISTE 
                            //( Insere o contato no banco de dados 
                            $sql = "INSERT INTO tbl_contatos(id_instancia, numero, lista_0, teste, created_at) VALUES ('$idInstancia', '$numero', 1, 0, NOW())";
                            $resultado = mysqli_query($conn['link'], $sql);
                            $this->id_contato = mysqli_insert_id($conn['link']);
                            if ($resultado != '1') {
                                $this->logSis('ERR', 'Insert Contatos. Erro: ' . $resultado . mysqli_connect_error());
                            }
                        }

                        //( Insere a interação que foi recebida no BD 
                        $resultado = $this->inserirInteracao($this->idInstancia, 0, $this->id_contato, '', '', '', '', '', '', '', $idMensagemWhats, $mensagem, 1);

                        if ($resultado == '1') {

                            $mensagem = explode(' ', trim($decoded['Body']['Text']));
                            $palavra = mb_strtolower($mensagem[0], 'UTF-8');

                            if ($this->primeirocontato == true) { //( Se for o primeiro contato
                                $texto = utf8_encode($this->msg_inicial) . "\n";
                                $this->envioMenuRaiz($numero, $texto);
                                //& Partir daqui, precisa entender se o menu raiz é um menu de produtos.
                            } else {
                                //( Consulta a última interação enviada pra ver se foi a solicitação de nome 
                                $ultimaInteracao = $this->verificaInteracao($idInstancia, $this->id_contato);
                                $tempoParaUltimaInteracao = $this->difDatasEmHoras($ultimaInteracao['dataEnvio'], date("Y-m-d H:i:s"));

                                $this->resposta($numero, $decoded, $ultimaInteracao);
                            }
                        } else {
                            $this->logSis('ERR', 'Erro ao tentar inserir a interação');
                        }
                    } else { //fora do horário de atendimento

                        $this->sendMessage("ForaHorario", $numero, utf8_encode($this->msg_fora_horario), "");
                    }
                }
            }
        }

        //* Função de verificação de horário de atendimento 
        public function horarioAtendimento()
        {
            //Consulta o horário atual pra ver se está dentro do horário de atendimento.
            include_once("servicos.php");

            //( Obtem o dia da semana 
            $diaSemana = array('DOM', 'SEG', 'TER', 'QUA', 'QUI', 'SEX', 'SAB');
            $hojeSemana = $diaSemana[date('w', strtotime(date('Y-m-d')))];

            //( Obtem a hora atual 
            $hojeHora = date('H:i');

            $resultDia = fctConsultaParaArray(
                'ConsultaAtendimentoDia',
                "SELECT * FROM tbl_atendimento WHERE dia = '$hojeSemana'",
                array('dia', 'horarios', 'status')
            );
            $resultDia = $resultDia[0];

            if ($resultDia['status'] == 0) {
                // Dia não disponível para atendimento
                return false;
            } else {
                //Verifica se a hora do atendimento ($hojeHora) está dentro dos horários de atendimento
                $noHorario = 0;
                $arrayHorarios = explode(',', $resultDia['horarios']);
                foreach ($arrayHorarios as $horario) {
                    $arrayHora = explode('-', $horario);
                    $primeiraHora = $arrayHora[0];
                    $segundaHora = $arrayHora[1];
                    if ($hojeHora >= $primeiraHora && $hojeHora <= $segundaHora) {
                        $noHorario = 1;
                    }
                }
                if ($noHorario == 0) { //Atendimento fora do horário
                    return false;
                } else { //Atendimento no horário
                    return true;
                }
            }
        }

        //* Função de resposta 
        public function resposta($numero, $mensagem, $ultimaInteracao)
        {
            include("dados_conexao.php");

            //( Procurar a última interação realizada para ver se tem tempo suficiente para envio do menu
            //( Caso o tempo da resposta seja maior que o tempo estipulado para $tempoMenu, ele chama o menu ao invez de qualquer coisa. 
            /* $sql = "SELECT *, data_envio AS ultima_interacao, TIMESTAMPDIFF(SECOND,data_envio,NOW()) AS segundos FROM tbl_interacoes WHERE id_instancia = $this->idInstancia AND direcao = 1 AND id_contato = $this->id_contato ORDER BY data_envio DESC LIMIT 1";
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
            } */
            $this->logSis('DEB', 'Tempo da última: ' . $ultimaInteracao['segundos']);


            if (count($ultimaInteracao) > 0 && $ultimaInteracao['segundos'] > $this->tempoMenu) {
                //( Identifica que tem tempo acima do configurado desde a última mensagem 
                $this->logSis('DEB', 'Indetificou que faz tempo desde a última ' . $ultimaInteracao['segundos'] . ' segundos');

                $this->envioMenuRaiz($numero, 'Bem vindo novamente!');
                exit(0);
            }

            //( ULTIMA INTERAÇÃO DE MENU - O que provavelmente o cliente está respondendo 
            /* $sql = "SELECT id_interacao, menu_anterior, id_retorno FROM tbl_interacoes WHERE id_instancia = $this->idInstancia AND tipo = 1 AND direcao = 1 AND id_contato = $this->id_contato ORDER BY data_envio DESC LIMIT 1";
            $query = mysqli_query($conn['link'], $sql);
            $numRow = mysqli_num_rows($query);
            $consultaUltima = mysqli_fetch_array($query, MYSQLI_ASSOC); */
            $this->menuAnterior = $ultimaInteracao['menu_anterior'];
            $this->ultimoRetorno = $ultimaInteracao['id_retorno'];
            $this->ultimaInteracaoTipo = $ultimaInteracao['tipo'];
            $this->ultimaInteracaoSubTipo = $ultimaInteracao['subtipo'];
            $this->ultimaInteracaoAcao = $ultimaInteracao['acao'];

            $this->logSis('DEB', 'ultimoRetorno: ' . $this->ultimoRetorno);

            //excluir espaços em excesso e dividir a mensagem em espaços.
            //A primeira palavra na mensagem é um comando, outras palavras são parâmetros
            $mensagem = explode(' ', trim($this->stringMensagemAtual));

            //( Pedaço de código para testes com comandos
            /* if (mb_strtolower($mensagem[0], 'UTF-8') == 'link') {
                $this->logSis('DEB', 'Identificado o comando link');
                $this->solicitaLink($numero, 10000, '1', 'Consulta Online', 10000, 1);
                exit(0);
            } */

            //Confirma se a mensagem realmente não foi enviada do Bot
            if (!$decoded['Body']['Info']['FromMe']) {
                $primeiraPalavraCliente = mb_strtolower($mensagem[0], 'UTF-8');
                $this->logSis('DEB', 'PRIMEIRA PALAVRA: ' . $primeiraPalavraCliente);

                //( Verifica se é um tratamento de pendencias
                if ($this->ultimaInteracaoTipo == 5) {
                    //( Verifica primeiro se é uma ação de resposta de pergunta ou o início 
                    if ($this->ultimaInteracaoAcao != '') { //( Ou seja, já existe uma ação, já foi solicitado alguma informação.
                        $this->respostaTratamentoPendenciasAcoes($this->ultimaInteracaoAcao, $this->ultimaInteracaoSubTipo, $mensagem, $this->stringMensagemAtual);
                        exit(0);
                    } else {
                        //( Caso sim, chama a função de resposta específica do tratamento de pendências.
                        $this->respostaTratamentoPendencias($this->ultimaInteracaoSubTipo, $mensagem, $this->stringMensagemAtual);
                        exit(0);
                    }
                }

                //( Verifica se é um número 
                if (is_numeric($primeiraPalavraCliente) || count($mensagem) == 1) { //Caso seja um número, faz verificação se existe algum menu pra esse número 
                    $this->logSis('DEB', 'É NÚMERO, ou APENAS uma palavra ' . $primeiraPalavraCliente);

                    if ($primeiraPalavraCliente == '0') { //Se o cliente escolher 0, tem que retornar
                        $this->logSis('DEB', 'É igual a 0 -> ' . $primeiraPalavraCliente);

                        //( Verifica aqui a última interação que nao seja 0 para retornar o menu_anterior a esse atual 
                        $sql = "SELECT id_interacao, tipo, subtipo, acao, opcoes_variaveis, menu_anterior, id_retorno FROM tbl_interacoes WHERE id_instancia = $this->idInstancia AND tipo = 1 AND direcao = 1 AND id_contato = $this->id_contato AND menu_anterior != 0 AND id_retorno = $this->ultimoRetorno ORDER BY data_envio DESC LIMIT 2";
                        $this->logSis('DEB', 'sql - consultaUltima ' . $sql);

                        $query = mysqli_query($conn['link'], $sql);
                        $numRow = mysqli_num_rows($query);
                        $consultaUltima = mysqli_fetch_array($query, MYSQLI_ASSOC);
                        $this->menuAnterior = $consultaUltima['menu_anterior'];
                        $this->ultimoRetorno = $consultaUltima['id_retorno'];
                        $this->acao = $consultaUltima['acao'];

                        $arrayRetorno = $this->consultaRetorno($this->menuAnterior, '', '');
                        $this->ultimoRetorno = 0;
                        $this->direcaoEnvio($arrayRetorno['tipo'], $numero, $arrayRetorno);
                    } else {

                        $this->logSis('DEB', 'Não é igual a 0 -> ' . $primeiraPalavraCliente);
                        $this->logSis('DEB', 'Subtipo -> ' . $ultimaInteracao['subtipo']);

                        //( Código para verificar as opções variáveis
                        if ($ultimaInteracao['subtipo'] != '') {
                            $this->respostaOpcoesVariaveis($ultimaInteracao['subtipo'], $ultimaInteracao['opcoes_variaveis'], $primeiraPalavraCliente);
                            exit(0);
                        }

                        $arrayRetorno = $this->consultaRetorno('', $primeiraPalavraCliente, $this->ultimoRetorno);
                        $this->direcaoEnvio($arrayRetorno['tipo'], $numero, $arrayRetorno);
                    }
                } else { //( A mensagem é um texto 
                    //& ====================
                    //& ====================
                    //& Caso seja um texto, considerar

                    $this->logSis('DEB', 'É TEXTO');

                    $opcaoEscolhida = $this->verficaPalavras($this->ultimoRetorno, $mensagem);
                    $this->logSis('DEB', 'Retorno Palavras: ' . $opcaoEscolhida);

                    if ($opcaoEscolhida == 0) {
                        $this->envioErro($numero, '');
                    } else {
                        $arrayRetorno = $this->consultaRetorno($opcaoEscolhida, '', $this->ultimoRetorno);
                        $this->direcaoEnvio($arrayRetorno['tipo'], $numero, $arrayRetorno);
                    }
                }
            }
        }

        //* Envio Menu raiz
        public function envioMenuRaiz($numero, $textoComplementar)
        {
            //$this->logSis('DEB', 'Entrou no envioMenuRaiz');

            $arrayRetorno = $this->consultaRetorno($this->menuRaiz, '', '');
            //& Entender aqui também se tem a opção do carrinho e do repetir último pedido
            //( Faz a verificação de opções variáveis (Carrinho, Ultima e produtos)
            $arrayOpcoes = $this->retornoOpcoesVariaveis($arrayRetorno['carrinho'], $arrayRetorno['repetir'], $arrayRetorno['filtro_tipo'], $arrayRetorno['filtro'], array());
            $textoOpcoes = '';

            //( Retornou alguma coisa da verificação de opções variáveis
            if ($arrayOpcoes != false) {
                $montaTextoOpcoes = $this->montaTextoOpcoes('', $arrayOpcoes, false);
                $textoOpcoes = $montaTextoOpcoes['textoOpcoes'];
                $arrayParaJson = $montaTextoOpcoes['arrayParaJson'];  //& O que fazer para mandar esse JSON para ser salvo na tbl_interacao
                $arrayRetorno['opcoes_variaveis'] = json_encode($arrayParaJson);
            }

            $texto = $textoComplementar . $arrayRetorno['mensagem'] . $textoOpcoes;
            $this->sendMessage($arrayRetorno['nome'], $numero, $texto, $arrayRetorno);
        }

        //* Respode quando o tipo so último menu é Categoria, subcategoria ou produto
        public function respostaOpcoesVariaveis($subtipo, $opcoesVariaveis, $mensagemCliente)
        {
            include_once("servicos.php");
            $this->logSis('DEB', 'Entrou para a verificação das Opções variáveis.');
            $this->logSis('DEB', 'Opções variáveis -> ' . $opcoesVariaveis);


            //& Tratando primeiro como se fosse só número 
            $arrayOpcoesVariaveis = json_decode($opcoesVariaveis, true);
            $this->logSis('DEB', 'Opções variáveis -> ' . print_r($arrayOpcoesVariaveis));

            $indice = array_search($mensagemCliente, array_column($arrayOpcoesVariaveis, 'ind'));
            $this->logSis('DEB', 'Indice -> ' . $indice);


            if ($indice == '' && $indice !== 0) { //Não encontrou
                return false;
                exit(0);
            } else {
                $idEncontrado = $arrayOpcoesVariaveis[$indice]['id'];
                $this->logSis('DEB', 'idEncontrado -> ' . $idEncontrado);
            }

            switch ($subtipo) {
                case 1: //Categoria
                    $this->logSis('DEB', 'Entrou para a verificação das Opções variáveis. CATEGORIA');

                    $retornoConsultaCategorias = fctConsultaParaArray(
                        'ConsultaCategoriaParaMensagem',
                        "SELECT * FROM tbl_categorias WHERE id = '$idEncontrado'",
                        array('mensagem')
                    );
                    $mensageRetorno = $retornoConsultaCategorias[0]['mensagem'];
                    $this->logSis('DEB', 'mensageRetorno' . $mensageRetorno);



                    //( Cria um arrayRetorno com os campos realmente úteis para salvar nas Interações. 
                    $arrayRetorno = array(
                        'modo' => 1,
                        'filtro_tipo' => 2,
                        'id_retorno' => 0
                    );
                    //( Faz a verificação de opções variáveis (Carrinho, Ultima e produtos)
                    $arrayOpcoes = $this->retornoOpcoesVariaveis(1, 0, 2, $idEncontrado, array());
                    $textoOpcoes = '';

                    //( Retornou alguma coisa da verificação de opções variáveis
                    if ($arrayOpcoes != false) {
                        $montaTextoOpcoes = $this->montaTextoOpcoes('', $arrayOpcoes, false);
                        $textoOpcoes = $montaTextoOpcoes['textoOpcoes'];
                        $arrayParaJson = $montaTextoOpcoes['arrayParaJson'];
                        $arrayRetorno['opcoes_variaveis'] = json_encode($arrayParaJson);
                    }

                    $texto = $mensageRetorno . $textoOpcoes;
                    $this->sendMessage('SubCategorias', $this->numerocliente, $texto, $arrayRetorno);

                    break;
                case 2: //SubCategoria

                    $this->logSis('DEB', 'Entrou para a verificação das Opções variáveis. SUBCATEGORIA');

                    $retornoConsultaCategorias = fctConsultaParaArray(
                        'ConsultaSubcategoriaParaMensagem',
                        "SELECT * FROM tbl_subcategorias WHERE id = '$idEncontrado'",
                        array('mensagem')
                    );
                    $mensageRetorno = $retornoConsultaCategorias[0]['mensagem'];
                    $this->logSis('DEB', 'mensageRetorno' . $mensageRetorno);



                    //( Cria um arrayRetorno com os campos realmente úteis para salvar nas Interações. 
                    $arrayRetorno = array(
                        'modo' => 1,
                        'filtro_tipo' => 3,
                        'id_retorno' => 0
                    );
                    //( Faz a verificação de opções variáveis (Carrinho, Ultima e produtos)
                    $arrayOpcoes = $this->retornoOpcoesVariaveis(1, 0, 3, $idEncontrado, array());
                    $textoOpcoes = '';

                    //( Retornou alguma coisa da verificação de opções variáveis
                    if ($arrayOpcoes != false) {
                        $montaTextoOpcoes = $this->montaTextoOpcoes('', $arrayOpcoes, true);
                        $textoOpcoes = $montaTextoOpcoes['textoOpcoes'];
                        $arrayParaJson = $montaTextoOpcoes['arrayParaJson'];
                        $arrayRetorno['opcoes_variaveis'] = json_encode($arrayParaJson);
                    }

                    $texto = $mensageRetorno . $textoOpcoes;
                    $this->sendMessage('SubCategorias', $this->numerocliente, $texto, $arrayRetorno);
                    break;
                case 3: //Produto -> Chama o cadastro de produtos

                    $this->logSis('DEB', 'Entrou para a verificação das Opções variáveis. PRODUTO');

                    $this->adicionaAoCarrinho($idEncontrado);
                    $this->consultaPendencias();

                    break;
            }
        }

        //* Monta o texto com as opções e devolve tanto o texto quanto o json
        public function montaTextoOpcoes($textoOpcoes, $arrayOpcoes, $produtos)
        {
            $textoOpcoes .= "\n";
            $arrayParaJson = [];
            $indice = 0;
            foreach ($arrayOpcoes as $linha) {
                $indice += 1;
                if ($produtos == true) {
                    $textoOferta = '';
                    if ($linha['nome_oferta'] != NULL) {
                        $textoOferta = ' + oferta de ' . $linha['nome_oferta'];
                    }
                    //$textoOpcoes .= "\n*" . $indice . ". " . strtoupper ($linha['nome']) . "* - ".$linha['valor']." \n```" . $linha['descricao'] . "```\n";
                    $textoOpcoes .= "\n*" . $indice . ". " . strtoupper($linha['nome']) . "* - " . $linha['valor'] . "€ \n _" . $linha['descricao'] . $textoOferta . "_\n";
                } else {
                    $textoOpcoes .= "\n*" . $indice . "*. " . $linha['nome'];
                }
                array_push($arrayParaJson, array(
                    'ind' => $indice,
                    'id' => $linha['id']
                ));
            }
            return array(
                'textoOpcoes' => $textoOpcoes,
                'arrayParaJson' => $arrayParaJson
            );
        }

        //* Cadastra produtos e ofertas no carrinho
        public function adicionaAoCarrinho($idEncontrado)
        {
            include_once("servicos.php");
            logSis('DEB', "Entrou na Adição do carrinho. IdEncontrado: " . $idEncontrado);


            //( Consulta o produto
            $consultaProduto = fctConsultaParaArray(
                'ConsultaProduto',
                "SELECT id, ofertas, valor, valor_promo FROM tbl_produtos WHERE id = '$idEncontrado' AND status = 1",
                array('id', 'valor', 'valor_promo', 'ofertas')
            );
            if ($consultaProduto == false) {
                $this->retornoErro('');
            }
            $consultaProduto = $consultaProduto[0];


            //( Verifica se existe valor promocional
            if ($consultaProduto['valor_promocional'] != 0) {
                $valor = $consultaProduto['valor_promocional'];
            } else {
                $valor = $consultaProduto['valor'];
            }
            logSis('DEB', " consultaProduto no retorno 1 -> " . print_r($consultaProduto, true));

            //( Insere o produto no 
            $resultInsert = fctInsert(
                'InsertProduto',
                "INSERT INTO tbl_carrinho(id_instancia, id_contato, id_produto, quantidade, valor, status, create_at) VALUES ($this->idInstancia, $this->idContato, $idEncontrado, 1, $valor, 1, NOW())"
            );
            logSis('DEB', " consultaProduto no retorno 2 -> " . print_r($consultaProduto, true));
            if ($resultInsert == false) {
                $this->retornoErro('');
            }
            $idProduto = $resultInsert;
            //( Verifica se esse produto tem alguma oferta
            if ($consultaProduto['ofertas'] != 0) {
                $idOferta = $consultaProduto['ofertas'];
                //( Caso tenha oferta vinculada, insere a oferta no carrinho
                $resultInsert = fctInsert(
                    'InsertOferta',
                    "INSERT INTO tbl_carrinho(id_instancia, id_contato, id_oferta, oferta_de_produto, quantidade, status, create_at) VALUES ($this->idInstancia, $this->idContato, $idOferta, $idProduto, 1, 1, NOW())"
                );
                if ($resultInsert == false) {
                    $this->retornoErro('');
                }
            }

            return true;
        }

        //* Verificação de pendências
        public function consultaPendencias()
        {
            $this->logSis('DEB', 'Entrou no consulta pendências.');
            include_once("servicos.php");

            //( Identificar no banco se tem alguma pendência em nome do cliente 
            // Primeiro ver produtos adquiridos
            // Ver a quantidade desses produtos -> Status 1
            // Ver o que quer adicionar -> Status 2
            // ver o que quer retirar -> Status 3
            // ver se tem alguma observação -> status 4
            // Segundo, ver se tem alguma oferta
            // Ver se tem mais de uma linha no oferta_prod para a escolha da oferta -> Status 5

            $resultPendencias = fctConsultaParaArray(
                'ConsultaPendencias',
                "SELECT c.*, p.nome, p.descricao FROM tbl_carrinho c LEFT JOIN tbl_produtos p ON c.id_produto = p.id WHERE c.id_instancia = $this->idInstancia AND c.id_contato = $this->idContato AND c.status != 0 ORDER BY c.id_oferta ASC LIMIT 1",
                array('id', 'id_produto', 'nome', 'descricao', 'id_oferta', 'quantidade', 'observacao', 'status')
            );

            //( Se não tiver pendência, retorna o menu raiz
            if ($resultPendencias === 0) { //Ou seja, vazio
                $this->envioMenuRaiz($this->numeroCliente, 'Escolha um produto ou visualize o carrinho para finalizar o pedido.');
                exit(0);
            }

            if ($resultPendencias === false) {
                $this->retornoErro('');
            }
            $resultPendencias = $resultPendencias[0];


            //( Se tiver pendência envia o menu de pendências
            //( Mas antes consulta as adições e retiradas até o momento
            $idCarrinho = $resultPendencias['id'];
            $resultInsumos = fctConsultaParaArray(
                'ConsultaInsumos',
                "SELECT ic.*, i.nome, i.valor_retirada, i.valor_adicao FROM tbl_insumos_carrinho ic, tbl_insumos i WHERE ic.id_insumo = i.id AND ic.id_carrinho = $idCarrinho",
                array('id', 'id_carrinho', 'id_insumo', 'adicao', 'nome', 'valor_retirada', 'valor_adicao')
            );

            $stringRetirada = "";
            $tringAdicao = "";
            foreach ($resultInsumos as $linha) {
                switch ($linha['adicao']) {
                    case 0: //( Retirada
                        if ($linha['valor_retirada'] == 0) {
                            $valor_retirada = '';
                        } else {
                            $valor_retirada = '(' . $linha['valor_retirada'] . ')';
                        }
                        $stringRetirada .= $linha['nome'] . ' ' . $valor_retirada . '  ';
                        break;

                    case 1: //( Adição
                        if ($linha['valor_adicao'] == 0) {
                            $valor_adicao = '';
                        } else {
                            $valor_adicao = '(' . $linha['valor_adicao'] . ')';
                        }
                        $stringAdicao .= $linha['nome'] . ' ' . $valor_adicao . '  ';
                        break;
                }
            }


            if ($resultPendencias['id_oferta'] == 0) { //( É uma pendência de produto
                $texto = "*" . $resultPendencias['nome'] . "*\n";
                $texto .= "_" . $resultPendencias['descricao'] . "_\n";
                $texto .= "Quantidade: " . $resultPendencias['quantidade'] . "\n";
                $texto .= "Acrescentar: " . $stringAdicao . "\n";
                $texto .= "Retirar: " . $stringRetirada . "\n";
                $texto .= "Obs.: " . $resultPendencias['observacao'] . "\n";
                $texto .= "\n";
                $texto .= "*1. Confirmar esse produto dessa forma*\n";
                $texto .= "*2*. Alterar a quantidade (apenas caso queira outro produto idêntico)\n";
                $texto .= "*3*. Acrescentar um item\n";
                $texto .= "*4*. Retirar algum item\n";
                $texto .= "*5*. Escrever uma mensagem sobre esse produto\n";
                $texto .= "*6*. Excluir esse produto do carrinho\n";


                $arrayRetorno = array(
                    'modo' => 5,
                    'filtro_tipo' => $idCarrinho,
                    'id_retorno' => 0
                );
                //$texto = utf8_encode($texto);
                $this->sendMessage('MenuPendencias', $this->numeroCliente, $texto, $arrayRetorno);

                exit(0);

                switch ($resultPendencias['status']) {
                    case 1: //( Confirmação de quantidade
                        $texto = "Quantidade: 1\n";
                        break;
                    case 2: //( Ver o que quer adicionar

                        break;
                    case 3: //( Ver o que quer retirar

                        break;
                    case 4: //( Ver se tem alguma observação

                        break;
                }
            } else { //( É uma pendência de oferta

                //( Vê se na tbl_ofertas_prod tem mais de uma linha vinculada a esse produto, caso sim manda para o cliente escolher
                switch ($resultPendencias['status']) {
                    case 5: //( Escolher produto

                        break;
                }
            }
        }

        //* Resposta para o tratamento de Pendencias (tipo 5)
        public function respostaTratamentoPendencias($idItem, $arrayMensagem, $stringMensagem)
        {
            include_once("servicos.php");
            $primeiraPalavraCliente = mb_strtolower($arrayMensagem[0], 'UTF-8');

            //( Verifica se é um número 
            if (is_numeric($primeiraPalavraCliente) || count($arrayMensagem) == 1) { //Caso seja um número, faz verificação se existe algum menu pra esse número 
                $this->logSis('DEB', 'Tratamento Pendencias - É NÚMERO, ou APENAS uma palavra ' . $primeiraPalavraCliente);

                switch ($primeiraPalavraCliente) {
                    case 1:  //( Confirmação do item
                        $resultAtualização = fctUpdate(
                            'UpdateConfirmarItemCarrinho',
                            "UPDATE tbl_carrinho SET status = 0 WHERE id = $idItem"
                        );
                        if ($resultAtualização == false) {
                            $this->retornoErro('');
                        }
                        $this->consultaPendencias();
                        break;

                    case 2: //( Alterar Quantidade
                        $arrayRetorno = array(
                            "modo" => 5,
                            "filtro_tipo" => $idItem,
                            "acao" => 'quant'
                        );
                        $this->sendMessage('PerguntaQuantidade', $this->numeroCliente, 'Favor enviar apenas a quantide desejada desse produto.', $arrayRetorno);
                        break;
                    case 3: //( Adicionar algum insumo
                        $resultRetiradas = fctConsultaParaArray(
                            'ConsultaRetiráveis',
                            "SELECT ip.*, i.nome, i.valor_adicao FROM tbl_insumos_prod ip, tbl_insumos i WHERE ip.id_insumo = i.id AND ip.adiciona = 1 AND ip.status=1 AND ip.id_produto = $idItem",
                            array('id', 'nome', 'quantidade', 'valor_adicao')
                        );
                        //( Obter a lista de insumos que podem ser adicionados
                        $arrayRetorno = array(
                            "modo" => 5,
                            "filtro_tipo" => $idItem,
                            "acao" => 'add'
                        );
                        $this->sendMessage('PerguntaAdicionar', $this->numeroCliente, 'Favor enviar apenas a quantidde desejada desse produto.', $arrayRetorno);
                        break;
                    case 4: //( Retirar algum insumo

                        //( Obter a lista de insumos que podem ser retirados
                        $resultRetiradas = fctConsultaParaArray(
                            'ConsultaRetiráveis',
                            "SELECT ip.*, i.nome, i.valor_retirada FROM tbl_insumos_prod ip, tbl_insumos i WHERE ip.id_insumo = i.id AND ip.retira = 1 AND ip.status=1 AND ip.id_produto = $idItem",
                            array('id', 'nome', 'quantidade', 'valor_retirada')
                        );

                        //& ===========================
                        //& ===========================
                        //& ===========================
                        //& ===========================
                        //& ===========================
                        //& FAZER ISSO AQUI FUNCIONAR

                        $montaTextoOpcoes = $this->montaTextoOpcoes($textoOpcoes, $resultRetiradas, false);
                        $textoOpcoes = $montaTextoOpcoes['textoOpcoes'];
                        $arrayParaJson = $montaTextoOpcoes['arrayParaJson'];  //& O que fazer para mandar esse JSON para ser salvo na tbl_interacao
                        $arrayRetorno['opcoes_variaveis'] = json_encode($arrayParaJson);


                        $texto = $textoComplementar . $arrayRetorno['mensagem'] . $textoOpcoes;
                        $this->sendMessage($arrayRetorno['nome'], $numero, $texto, $arrayRetorno);
                        //& Criar as opções com a mesma inteligência do Array de opções.
                        $arrayRetorno = array(
                            "modo" => 5,
                            "filtro_tipo" => $idItem,
                            "acao" => 'reti'
                        );
                        $this->sendMessage('PerguntaRetirar', $this->numeroCliente, 'Favor enviar apenas a quantidde desejada desse produto.', $arrayRetorno);
                        break;
                    case 5: //( Escrever mensagem
                        $arrayRetorno = array(
                            "modo" => 5,
                            "filtro_tipo" => $idItem,
                            "acao" => 'obs'
                        );
                        $this->sendMessage('PerguntaObservação', $this->numeroCliente, 'A sua próxima mensagem enviada será anexada a esse produto no seu pedido.', $arrayRetorno);
                        break;
                    case 6: //( Retirar o item do carrinho
                        $resultDelete = fctDelete(
                            'DeletarProdutoCarrinho',
                            "DELETE FROM tbl_carrinho WHERE id = $idItem OR oferta_de_produto = $idItem"
                        );
                        if ($resultDelete == false) {
                            $this->retornoErro('');
                        }
                        $this->consultaPendencias();
                        break;

                    default: //( Qualquer outro número
                        break;
                }
            } else { //( A mensagem é um texto 
                //& ====================
                //& ====================
                //& Caso seja um texto, considerar

                $this->logSis('DEB', 'É TEXTO');

                $opcaoEscolhida = $this->verficaPalavras($this->ultimoRetorno, $arrayMensagem);
                $this->logSis('DEB', 'Retorno Palavras: ' . $opcaoEscolhida);

                if ($opcaoEscolhida == 0) {
                    $this->envioErro($this->numeroCliente, '');
                } else {
                    $arrayRetorno = $this->consultaRetorno($opcaoEscolhida, '', $this->ultimoRetorno);
                    $this->direcaoEnvio($arrayRetorno['tipo'], $this->numeroCliente, $arrayRetorno);
                }
            }
        }

        //* Resposta para o tratamento de Pendencias (tipo 5)
        public function respostaTratamentoPendenciasAcoes($acao, $idItem, $arrayMensagem, $stringMensagem)
        {
            include_once("servicos.php");
            $primeiraPalavraCliente = mb_strtolower($arrayMensagem[0], 'UTF-8');

            //( Verifica se é um número 
            if (is_numeric($primeiraPalavraCliente) || count($arrayMensagem) == 1 || $acao == 'obs') { //Caso seja um número, faz verificação se existe algum menu pra esse número 
                $this->logSis('DEB', 'Tratamento Pendencias Ações - É NÚMERO, ou APENAS uma palavra ' . $primeiraPalavraCliente);

                switch ($acao) {
                    case 'quant':  //( Alteração de quantidade
                        if ($primeiraPalavraCliente == 0) {
                            $arrayRetorno = array(
                                "modo" => 5,
                                "subtipo" => $idItem,
                                "acao" => 'quant'
                            );
                            $this->sendMessage('PerguntaQuantidadeErroZero', $this->numeroCliente, "A quantidade não pode ser zero, favor insira uma quantidade válida.\n_Caso queira excluir o item, envie 1 e no carrinho escolha e opção de exclusão do produto._", $arrayRetorno);
                        } else {
                            $resultQuant = fctUpdate(
                                'AlteraçãoQuantidade',
                                "UPDATE tbl_carrinho SET quantidade = $primeiraPalavraCliente WHERE id= $idItem OR oferta_de_produto = $idItem"
                            );
                            if ($resultQuant == false) {
                                $this->retornoErro('');
                            }
                            $this->consultaPendencias();
                        }
                        break;
                    case 'add':  //( Adição de itens

                        //& Criar aqui uma solução de análise da mensagem do cliente, onde analisa se ele mandou mais de uma opção separado por , espaço ou traço.

                        break;
                    case 'reti':  //( Retirada de intens
                        //& Criar aqui uma solução de análise da mensagem do cliente, onde analisa se ele mandou mais de uma opção separado por , espaço ou traço.

                        break;
                    case 'obs':  //( Inserir observação
                        $resultObs = fctUpdate(
                            'AlteraçãoObservação',
                            "UPDATE tbl_carrinho SET observacao = '$stringMensagem' WHERE id= $idItem"
                        );
                        if ($resultObs == false) {
                            $this->retornoErro('');
                        }
                        $this->consultaPendencias();
                        break;
                }
            } else { //( A mensagem é um texto 
                //& ====================
                //& ====================
                //& Caso seja um texto, considerar

                $this->logSis('DEB', 'É TEXTO');

                $opcaoEscolhida = $this->verficaPalavras($this->ultimoRetorno, $arrayMensagem);
                $this->logSis('DEB', 'Retorno Palavras: ' . $opcaoEscolhida);

                if ($opcaoEscolhida == 0) {
                    $this->envioErro($this->numeroCliente, '');
                } else {
                    $arrayRetorno = $this->consultaRetorno($opcaoEscolhida, '', $this->ultimoRetorno);
                    $this->direcaoEnvio($arrayRetorno['tipo'], $this->numeroCliente, $arrayRetorno);
                }
            }
        }

        //* Envio de erro
        public function envioErro($numero, $textoComplementar)
        {
            $texto = $textoComplementar . utf8_encode($this->msg_erro);

            $this->logSis('DEB', 'Mandando mensagem de erro. Número: ' . $numero . ' Texto: ' . $texto);

            $this->sendMessage("Erro", $numero, $texto, '');
        }

        //* C O N S U L T A  R E T O R N O
        public function consultaRetorno($id_retorno, $primeiraPalavraCliente, $ultimoRetorno)
        {
            $this->logSis('DEB', 'Entrou no Retorno. idRetorno: ' . $id_retorno . ' Palavra: ' . $primeiraPalavraCliente . ' UltimoRetorno: ' . $ultimoRetorno);

            include("dados_conexao.php");

            if ($id_retorno == '') { //ou seja, não sei qual o retorno
                $sql = "SELECT * FROM tbl_retornos WHERE id_retorno = (SELECT resposta FROM tbl_opcoes WHERE id_instancia = $this->idInstancia AND indice = '$primeiraPalavraCliente' AND id_retorno = $ultimoRetorno)";
            } else { //Sei qual o retorno atual
                //$idInstancia = $this->idInstancia;
                $sql = "SELECT * FROM tbl_retornos WHERE id_instancia = $this->idInstancia AND id_retorno = $id_retorno";
            }

            $this->logSis('DEB', $sql);

            $query = mysqli_query($conn['link'], $sql);
            $consultaRetorno = mysqli_fetch_array($query, MYSQLI_ASSOC);
            $numRow = mysqli_num_rows($query);
            if (!$query) {
                $this->logSis('ERR', 'Mysql Connect: ' . mysqli_error($conn['link']));
                exit(0);
            }
            if ($numRow == 0) { //VERIFICA SE EXISTE NO BANCO DE DADOS
                $this->logSis('ERR', 'Não encontrou a mensagem inicial Instância. Instância: ' . $this->idInstancia);
                exit(0);
            } else {

                $id_retorno = $consultaRetorno['id_retorno'];  //ID da tabela retorno (chave)
                $mensagem = utf8_encode($consultaRetorno['mensagem']);

                if ($consultaRetorno['produtos'] == 1) { // é um retorno de PRODUTOS

                }

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
                    'carrinho' => $consultaRetorno['carrinho'],
                    'repetir' => $consultaRetorno['repetir'],
                    'produtos' => $consultaRetorno['produtos'],
                    'filtro_tipo' => $consultaRetorno['filtro_tipo'],
                    'filtro' => $consultaRetorno['filtro'],
                    'mensagem' => $mensagem,
                    'url' => $consultaRetorno['url'],
                    'lat' => $consultaRetorno['lat'],
                    'lng' => $consultaRetorno['lng'],
                    'name' => $consultaRetorno['name'],
                    'address' => $consultaRetorno['address']
                );
                return $retorno;
            }
        }

        //* Função para retornar as oções variáveis 
        public function retornoOpcoesVariaveis($carrinho, $repetir, $filtroTipo, $filtro, $arrayOpcoes)
        {
            //( Traz as opções variáveis do banco de dados 
            $this->logSis('DEB', 'Entrou no retornoOpcoesVar. FiltroTipo: ' . $filtroTipo . ' Filtro: ' . $filtro);
            include("dados_conexao.php");

            //( Verifica se tem que mostrar o carrinho
            if ($carrinho == 1) {
                //& inteligencia para entender se existe carrinho para mostrar
            }

            //( Verifica se está habilitado para mostrar a opção de repetição
            if ($repetir == 1) {
                //& inteligencia para entender se existe pedidos fechados para que possam se repetir
            }

            if ($filtroTipo == 1 || $filtroTipo == 2) { //SUBCATEGORIA E CATEGORIA
                switch ($filtroTipo) {
                    case 1: //categoria
                        $sql = "SELECT * FROM tbl_categorias WHERE id_instancia = $this->idInstancia AND status = 1";
                        break;
                    case 2: //subcategoria
                        $sql = "SELECT * FROM tbl_subcategorias WHERE id_instancia = $this->idInstancia AND id_categoria = $filtro AND status = 1";
                        break;
                }

                $result = fctConsultaParaArray(
                    'ConsultaCategoriaSubCategoria',
                    $sql,
                    array('id', 'nome', 'palavras', 'mensagem')
                );

                if ($result == false) {
                    $this->retornoErro('');
                } else {
                    //$result = $result[0];
                    foreach ($result as $linha) {
                        array_push($arrayOpcoes, array('indice' => 2, 'tipo' => $filtroTipo, 'id' => $linha['id'], 'nome' => $linha['nome'], 'palavras' => $linha['palavras'], 'mensagem' => $linha['mensage']));
                    }
                    $this->logSis('DEB', 'ArrayOpçõesCategorias ' . print_r($arrayOpcoes));

                    return $arrayOpcoes;
                }
            } else if ($filtroTipo == 3) { //PRODUTOS
                $result = fctConsultaParaArray(
                    'ConsultaProdutos',
                    //"SELECT * FROM tbl_produtos WHERE id_instancia = $this->idInstancia AND id_subcategoria = $filtro AND status = 1",
                    "SELECT p.*, o.nome AS nome_oferta FROM tbl_produtos p LEFT JOIN tbl_ofertas o ON p.ofertas = o.id AND o.status=1 WHERE p.id_instancia = $this->idInstancia AND p.id_subcategoria = $filtro AND p.status = 1 ORDER BY p.valor ASC",
                    array('id', 'nome', 'descricao', 'tamanho', 'valor', 'valor_promo', 'ofertas', 'nome_oferta')
                );

                if ($result == false) {
                    $this->retornoErro('');
                } else {

                    foreach ($result as $linha) {

                        array_push($arrayOpcoes, array('indice' => 3, 'tipo' => $filtroTipo, 'id' => $linha['id'], 'nome' => $linha['nome'], 'descricao' => $linha['descricao'], 'tamanho' => $linha['tamanho'], 'valor' => $linha['valor'], 'valor_promo' => $linha['valor_promo'], 'ofertas' => $linha['ofertas'], 'nome_oferta' => $linha['nome_oferta']));
                    }
                    //$this->logSis('DEB', 'ArrayOpçõesProdutos: ' . print_r($arrayOpcoes, true));

                    return $arrayOpcoes;
                }
            } else {
                return false;
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

        //* ENVIA UM E-MAIL
        public function enviarEmail($email, $texto)
        {
            $subject = 'NUTRI MARI MARTINS - Conteúdo';

            $mensagem = $texto;

            $myEmail = "contato@meuatendente.com.br"; //é necessário informar um e-mail do próprio domínio
            $headers = "From: contato@meuatendente.com.br\r\n";
            $headers .= "Reply-To: contato@meuatendente.com.br\r\n";

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
            $this->logSis('DEB', 'Envio da requisição. Motivo: ' . $motivo . ' Método: ' . $method);
            $this->logSis('DEB', 'Envio da requisição. Data: ' . print_r($data, true));
            $this->logSis('DEB', 'Envio da requisição. Retorno: ' . print_r($retorno, true));
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


            //return $response;

            $resposta = json_decode($response, true);
            $this->logSis('REQ', 'Resp Requisição: ' . print_r($resposta, true));
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
                    $opcoes_variaveis = '';
                } else {
                    $tipo = $retorno['modo'];
                    $subTipo = $retorno['filtro_tipo'];
                    $idRetorno = $retorno['id_retorno'];
                    if ($retorno['opcoes_variaveis'] != '') {
                        $opcoes_variaveis = $retorno['opcoes_variaveis'];
                    }
                }
                if (isset($retorno['acao'])) {
                    $acao = $retorno['acao'];
                } else {
                    $acao = '';
                }
                //$this->logSis('REQ', 'Chegou aqui - Instância: ' . $this->idInstancia . ' IdContato: ' . $this->id_contato . ' Tipo: ' . $tipo . ' IdInteracaiCliente: ' . $this->id_interacao_cliente . ' IdResposta: ' . $id_resposta . ' Motivo: ' . $motivo);

                $this->inserirInteracao($this->idInstancia, 1, $this->id_contato, $tipo, $subTipo, $acao, $opcoes_variaveis, $this->ultimoRetorno, $idRetorno, $this->id_interacao_cliente, $id_resposta, $motivo, 1);
            } else {
                if ($motivo == 'Receptivo') {
                    return false;
                    exit(0);
                }
                $this->logSis('ERR', 'Não teve resposta da requisição a tempo. Resposta: ' . $resposta['message']);
            }
        } //# FCT Envio Requisição

        //* Busca no BD qual a última interação
        private function verificaInteracao($idInstancia, $idContato)
        {
            include('dados_conexao.php');

            //( Consulta da interação no BD 
            $sql = "SELECT *, data_envio AS ultima_interacao, TIMESTAMPDIFF(SECOND,data_envio,NOW()) AS segundos FROM tbl_interacoes WHERE direcao= 1 AND id_instancia = $idInstancia AND id_contato = $idContato ORDER BY id_interacao DESC limit 1";
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
                    "id_interacao" => $consultaInteracao['id_interacao'],
                    "mensagem" => $consultaInteracao['mensagem'],
                    "id_retorno" => $consultaInteracao['id_retorno'],
                    "dataEnvio" => $consultaInteracao['data_envio'],
                    "tipo" => $consultaInteracao['tipo'],
                    "subtipo" => $consultaInteracao['subtipo'],
                    "acao" => $consultaInteracao['acao'],
                    "opcoes_variaveis" => $consultaInteracao['opcoes_variaveis'],
                    "menu_anterior" => $consultaInteracao['menu_anterior'],
                    "ultima_interacao" => $consultaInteracao['ultima_interacao'],
                    "segundos" => $consultaInteracao['segundos'],
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

        //* Inserir interação 
        public function inserirInteracao($id_instancia, $direcao, $id_contato, $tipo, $subTipo, $acao, $opcoesVariaveis, $menuAnterior, $id_retorno, $resposta, $id_mensagem, $mensagem, $status)
        {
            include("dados_conexao.php");

            $sql = "INSERT INTO tbl_interacoes(id_instancia, direcao, id_contato, tipo, subtipo, acao, opcoes_variaveis, menu_anterior, id_retorno, resposta, id_mensagem, mensagem, status, data_envio) VALUES ($id_instancia, $direcao, '$id_contato', '$tipo', '$subTipo', '$acao', '$opcoesVariaveis', '$menuAnterior', '$id_retorno', '$resposta', '$id_mensagem', '$mensagem', $status, NOW())";
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

        //* Funcção que solicita um link de pagamento
        private function solicitaLink($numero, $valorTotal, $itemId, $itemNome, $itemValor, $itemQuantidade)
        {
            $this->logSis('DEB', 'Entrou na solicitação do link');

            include("dados_conexao.php");

            $arrayDados = array(
                "api_key" => "ak_test_EfQ4KKaduJJHqYYDpPJvDjsuH5D1GG",
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
                    $sql = "INSERT INTO tbl_fin_links(id_instancia, id_contato, object, id, company_id, amount, item_external_id, item_title, item_unit_price, item_quantity, short_id, url, date_created, date_updated, expires_at, create_at) VALUES ($this->idInstancia, $this->id_contato, '$object', '$id', '$company_id', '$amount', '$item_external_id', '$item_title', '$item_unit_price', '$item_quantity', '$short_id', '$url', '$date_created', '$date_updated', '$expires_at', NOW())";

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

        //* Função que solicita um link de pagamento
        private function marcarHorario($numero, $retorno)
        {
            $this->logSis('DEB', 'Entrou na marcação de horário');
            $this->logSis('DEB', 'Coringa: ' . $retorno['coringa']);

            //include("dados_conexao.php");
            include("horarios.php");
            include("servicos.php");

            switch ($retorno['coringa']) {
                case 'mes':
                    $this->logSis('DEB', 'Entrou no case mes');
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

                        //& Está trazendo o menu corretamente e salvando já com o subtipo
                        //& Colocar agora o json com as opções na coluna opcoes_variaveis
                        //& A partir daí criar o CASE para DIA 
                        $this->sendMessage($retorno['nome'], $numero, $texto, $arrayRetorno);
                    }
                    break;

                default:
                    # code...
                    break;
            }
        }

        //* Função que faz a análise das palavras dentro da mensagem e as palavras de cada opção em questão
        public function verficaPalavras($ultimoRetorno, $mensagem)
        {

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

        //* Verifica a diferença entre datas e retorna em horas 
        public function difDatasEmHoras($dataInicio, $dataFim)
        {
            $datatime1 = new DateTime($dataInicio);
            $datatime2 = new DateTime($dataFim);

            $diff = $datatime1->diff($datatime2);
            $horas = $diff->h + ($diff->days * 24);

            return $horas;
        }

        //* Função que consulta a instância 
        private function consultaInstancia($idInstancia)
        {
            include("dados_conexao.php");

            $sql = "SELECT * FROM tbl_instancias WHERE id_instancia = $idInstancia";
            $query = mysqli_query($conn['link'], $sql);
            $consultaInstancia = mysqli_fetch_array($query, MYSQLI_ASSOC);
            $numRow = mysqli_num_rows($query);
            if (!$query) {
                $this->logSis('ERR', 'Mysql Connect: ' . mysqli_connect_error());
                exit(0);
            }
            if ($numRow == 0) { //VERIFICA SE EXISTE NO BANCO DE DADOS
                $this->logSis('ERR', "Instância N/E: " . $idInstancia);
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
                $this->msg_fora_horario =  $consultaInstancia['msg_fora_horario'];
                $this->limite = $consultaInstancia['limite'];
                $this->status = $consultaInstancia['status'];
                $this->nome = $consultaInstancia['nome'];
            }
        }

        //* Função de LOG
        public function retornoErro($texto)
        {
            $this->logSis('DEB', 'Entrou no retornoErro');
            $textoRetorno = "Houve um erro na comunicação, favor responder novamente a última pergunta.";
            if ($texto != '') {
                $textoRetorno += "\nCaso persista, envie a palavra *SUPORTE* e informa o erro abaixo:\n";
                $textoRetorno += "_" . $texto . "_";
            }
            $this->sendMessage("MensageErro", $this->numeroCliente, $textoRetorno, "");
            exit(0);
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
