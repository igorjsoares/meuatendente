<?php

// Arquivo para serviços gerais 
// By Igor Soares

//* Função para fazer uma CONSULTA SQL COM RETORNO EM ARRAY 
function fctConsultaParaArray($nomeConsulta, $sql, $colunas)
{
    //logSis('DEB', 'Entrou no fctConsultaParaArray');

    include("dados_conexao.php");
    //logSis('DEB', "==SQL==" . $sql);

    $query = mysqli_query($conn['link'], $sql);
    $numRow = mysqli_num_rows($query);

    if (!$query) {
        logSis('ERR', $nomeConsulta . " - Mysql Connect Erro: " . mysqli_error($conn['link']));
        logSis('DEB', "==SQL==" . $sql);
        //exit(0);
        return false;
    }
    if ($numRow == 0) {
        logSis('ERR', $nomeConsulta . " - Não retornou nada " . $sql);

        return null;
    } else {

        $arrayResultado = [];

        //( While pelo resultado da consulta
        while ($linha = mysqli_fetch_array($query)) {
            $myObj = (object)[];
            //( ForEach pelo array das colunas enviadas como parâmetro 
            foreach ($colunas as $coluna) {
                //( Cria um objeto com as coluna enviada como chave e a referência na consulta
                $myObj->$coluna = utf8_encode($linha[$coluna]);
            }

            $myJSON = json_encode($myObj);
            $myArray = json_decode($myJSON, true);

            array_push($arrayResultado, $myArray);
        }

        logSis('DEB', $nomeConsulta . " - Array Resultado -> " . print_r($arrayResultado, true));

        return $arrayResultado;
    }
}

//* Função para fazer uma INSERÇÃO no banco de dados
function fctInserirNoBanco($nomeOperacao, $sql)
{
    logSis('DEB', $nomeOperacao . ' - Sql: ' . $sql);
    return 1;
   /*  include("dados_conexao.php");


    $query = mysqli_query($conn['link'], $sql);

    if (!$query) {
        logSis('ERR', $nomeOperacao . " - Mysql Connect Erro: " . mysqli_error($conn['link']));
        logSis('DEB', "==SQL==" . $sql);
        return false;
    }
    if ($query != '1') {
        logSis('ERR', $nomeOperacao . " - Não retornou nada " . $sql);
        return false;
    } else {
        $idInserido = mysqli_insert_id($conn['link']);
        return $idInserido;
        logSis('SUC', $nomeOperacao . " - Insert interação IN. ID_Interação: " . $idInserido);
    } */
}

//* Função para fazer uma update
function fctUpdate($nomeUpdate, $sql)
{
    include("dados_conexao.php");

    $query = mysqli_query($conn['link'], $sql);
    $linhasAfetadas = mysqli_affected_rows($conn['link']);

    if (!$query) {
        logSis('ERR', $nomeUpdate . ' - Mysql Connect: ' . mysqli_error($conn['link']));
        exit(0);
    }
    if ($query != true && $linhasAfetadas == 0) {
        return false;
        logSis('ERR', $nomeUpdate . ' - Não alterou nada no BD . Sql: ' . $sql);
    } else {
        return true;
    }
}

//* Função para retornar o nome do MÊS em português 
function fctNomeMes($mes)
{
    switch ($mes) {
        case '1':
            return "Janeiro";
            break;
        case '2':
            return "Fevereiro";
            break;
        case '3':
            return "Março";
            break;
        case '4':
            return "Abril";
            break;
        case '5':
            return "Maio";
            break;
        case '6':
            return "Junho";
            break;
        case '7':
            return "Julho";
            break;
        case '8':
            return "Agosto";
            break;
        case '9':
            return "Setembro";
            break;
        case '10':
            return "Outubro";
            break;
        case '11':
            return "Novembro";
            break;
        case '12':
            return "Dezembro";
            break;
    }
}

//* Função para retornar o nome do DIA DA SEMANA em português 
function fctNomeSemana($mes)
{
    switch ($$mes) {
        case '0':
            return "Segunda";
            break;
        case '1':
            return "Terça";
            break;
        case '2':
            return "Quarta";
            break;
        case '3':
            return "Quinta";
            break;
        case '4':
            return "Sexta";
            break;
        case '5':
            return "Sábado";
            break;
        case '6':
            return "Domingo";
            break;
    }
}

//* Função de LOG
function logSis($tipo, $texto)
{
    file_put_contents('log.txt', "> " . $tipo . " " . date('d/m/Y h:i:s') . " " . $texto . PHP_EOL, FILE_APPEND);
}
