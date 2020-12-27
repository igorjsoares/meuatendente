<?php

// Arquivo PHP para realizar funções da consulta e marcação de horários
// By Igor Soares

//* Consulta os meses disponíveis 
function fctConsultaMeses()
{
    logSis('DEB', 'Entrou no fctConsultaMeses');
    include_once("servicos.php");

    $resultado = fctConsultaParaArray(
        'ConsultaMês',
        "SELECT MONTH(horario) AS mes FROM tbl_horarios WHERE status = 1 AND horario >= NOW() GROUP BY MONTH(horario)",
        array('mes')
    );

    if ($resultado == false) {
        return false;
    } else {

        $arrayResultado = [];
        foreach ($resultado as $linha) {
            array_push($arrayResultado, array(
                'mes' => $linha['mes'],
                'nome_mes' => fctNomeMes($linha['mes'])
            ));
        }

        return $arrayResultado;
    }
}

//* Consulta os dias com horários disponíveis apartir do mês 
function fctConsultaDias($mes)
{
    include_once("servicos.php");

    $resultado = fctConsultaParaArray(
        'ConsultaDias',
        "SELECT day(horario) AS dia, WEEKDAY(horario) AS dia_semana FROM tbl_horarios WHERE status = 1 AND month(horario) = $mes AND horario >= NOW() GROUP BY day(horario)",
        array('dia', 'dia_semana')
    );
    logSis('DEB', "Array Resultado nos horários 1 -> " . print_r($resultado, true));

    if ($resultado == false) {
        return false;
    } else {
        $arrayResultado = [];
        while ($linha = $resultado) {
            array_push($arrayResultado, array(
                'dia' => $linha['dia'],
                'nome_dia' => fctNomeSemana($linha['dia_semana'])
            ));
        }
        logSis('DEB', "Array Resultado nos horários 2 -> " . print_r($arrayResultado, true));

        return $arrayResultado;
    }
}

//* Consulta os horários disponíveis a partir do dia e do mês 
function fctConsultaHorarios($dia, $mes)
{
    include_once("servicos.php");

    $resultado = fctConsultaParaArray(
        'ConsultaHorarios',
        "SELECT id_horario, day(horario) AS dia, WEEKDAY(horario) AS dia_semana, DATE_FORMAT(horario, '%H:%i') AS hora FROM tbl_horarios WHERE status = 1 AND month(horario) = $mes AND day(horario) = $dia",
        ''
    );

    if ($resultado == false) {
        return false;
    } else {
        $arrayResultado = [];
        while ($linha = $resultado) {
            array_push($arrayResultado, array(
                'id_horario' => $linha['dia'],
                'dia' => $linha['dia'],
                'dia_semana' => fctNomeSemanaAqui($linha['dia_semana']),
                'hora' => $linha['hora']
            ));
        }
        return $arrayResultado;
    }
}

//* Consulta os horários pendentes do cliente 
function fctConsultaMeusHorarios($idContato)
{
    include_once("servicos.php");

    $resultado = fctConsultaParaArray(
        'ConsultaMeusHorarios',
        "SELECT id_horario, day(horario) AS dia, WEEKDAY(horario) AS dia_semana, DATE_FORMAT(horario, '%H:%i') AS hora FROM tbl_horarios WHERE status = 1 AND id_contato = $idContato",
        ''
    );

    if ($resultado == false) {
        return false;
    } else {
        $arrayResultado = [];
        while ($linha = $resultado) {
            array_push($arrayResultado, array(
                'id_horario' => $linha['dia'],
                'dia' => $linha['dia'],
                'dia_semana' => fctNomeSemana($linha['dia_semana']),
                'hora' => $linha['hora']
            ));
        }
        return $arrayResultado;
    }
}

//* Função para retornar o nome do DIA DA SEMANA em português 
function fctNomeSemanaAqui($dia)
{
    switch ($dia) {
        case '1':
            return "Segunda";
            break;
        case '3':
            return "Terça";
            break;
        case '4':
            return "Quarta";
            break;
        case '5':
            return "Quinta";
            break;
        case '6':
            return "Sexta";
            break;
        case '7':
            return "Sábado";
            break;
        case '0':
            return "Domingo";
            break;
    }
}

//* Função que verifica a mensagem e retorna se a mensagem está falando sobre algum mês
function fctAnaliseMensagemMes($mensagem)
{
    $arrayMeses = array('janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro');
    $primeiraPalavra = mb_strtolower($mensagem[0], 'UTF-8');

    if (is_numeric($primeiraPalavra)) { // Se a primeira palavra for um número
        if ($primeiraPalavra <= 0 || $primeiraPalavra > 12) {
            return false;
        } else {
            return $primeiraPalavra;
        }
    } else { //Se a primeia palavra for um texto
        $arrayMeses = array(
            '1' => 'janeiro',
            '2' => 'fevereiro',
            '3' => 'março',
            '4' => 'abril',
            '5' => 'maio',
            '6' => 'junho',
            '7' => 'julho',
            '8' => 'agosto',
            '9' => 'setembro',
            '10' => 'outubro',
            '11' => 'novembro',
            '12' => 'dezembro'
        );

        $result = array_intersect($arrayMeses, $mensagem);
        
        if (count($result) > 0) { //Encontrou
            $result = array_values($result);
            return array_search($result[0], $arrayMeses);
        } else {
            return false;
        }
    }
}
