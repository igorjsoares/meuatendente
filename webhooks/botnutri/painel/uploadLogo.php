
 

<?php

if (!isset($_SESSION)) {
    session_start();
}

if ($_SESSION['NP_Autenticacao'] != true) {
    session_destroy();
    header('Location: index.php');
    exit;
}
//@Código para converter a imagem para JPG, só que tem que estar salva no servidor, para depois excluir
//@ Fazer isso urgente
/*
        //Converte imagem em JPG
        $imagem_entrada = $original;
        $imagem_saida = $nomereal;

        switch ($extensao) {
            case '.gif':
                $img = imagecreatefromgif($imagem_entrada);

                break;
            case '.png':
                $img = imagecreatefrompng($imagem_entrada);

                break;
            case '.jpeg':
                $img = imagecreatefromjpeg($imagem_entrada);

                break;
            case '.jpeg':
                $img = imagecreatefromjpeg($imagem_entrada);

                break;
            case '.bmp':
                $img = imagecreatefromjpeg($imagem_entrada);

                break;
        }

        if ($extensao != 'jpg') {
            $w = imagesx($img);
            $h = imagesy($img);
            $trans = imagecolortransparent($img);
            if ($trans >= 0) {
                $rgb = imagecolorsforindex($img, $trans);
                $oldimg = $img;
                $img = imagecreatetruecolor($w, $h);
                $color = imagecolorallocate($img, $rgb['red'], $rgb['green'], $rgb['blue']);
                imagefilledrectangle($img, 0, 0, $w, $h, $color);
                imagecopy($img, $oldimg, 0, 0, 0, 0, $w, $h);
            }
            imagejpeg($img, $imagem_saida);

        }
*/


session_start();

//echo $_FILES['image']['name'];
//$extensoes = array('jpeg', 'jpg', 'png', 'gif', 'bmp'); // extensões válidas
$extensoes = array('jpg');
$pasta = 'assets/empresas/'; // upload pasta
$nome = $_SESSION['NP_id_empresa'];

if ($_FILES['file']) {
    $img = $_FILES['file']['name'];
    $tmp = $_FILES['file']['tmp_name'];

    //( Entende e converte o tamanho do arquivo profile para o tamanho exigido no message flow 
    $altura = "192";
    $largura = "192";
    $imagem_temporaria = imagecreatefromjpeg($tmp);
    $largura_original = imagesx($imagem_temporaria);
    $altura_original = imagesy($imagem_temporaria);
    $nova_largura = $largura ? $largura : floor(($largura_original / $altura_original) * $altura);
    $nova_altura = $altura ? $altura : floor(($altura_original / $largura_original) * $largura);
    $imgPerfilRedimensionada = imagecreatetruecolor($nova_largura, $nova_altura);
    imagecopyresampled($imgPerfilRedimensionada, $imagem_temporaria, 0, 0, 0, 0, $nova_largura, $nova_altura, $largura_original, $altura_original);
    //imagejpeg($imgPerfilRedimensionada, 'arquivo/' . $_FILES['arquivo']['name']);

    // Captura o tamanho da imagem e guarda nas variáveis
    //list($largura, $altura) = getimagesize($tmp);

    // Faz a Validação da imagem
    /* if ($largura < 200 && $altura < 200) {
        echo '<h7 style="color: red">Imagem com tamanho incorreto, tamanho mínimo 200px x 200px. (Tamanho da Imagem: ' . $largura . ' x ' . $altura . ' px.)</h7>';
    } else { */

    $nomereal = $nome . '.jpg'; //.strtolower(pathinfo($img, PATHINFO_EXTENSION));

    // obter a extensão do arquivo carregado
    $ext = strtolower(pathinfo($img, PATHINFO_EXTENSION));
    // pode carregar a mesma imagem usando a função rand
    //$final_image = rand(1000,1000000).$img;
    // formato válido do cheque
    if (in_array($ext, $extensoes)) {
        $pasta = $pasta . strtolower($nomereal);

        //( Salva o arquivo
        imagejpeg($imgPerfilRedimensionada, $pasta);

        //if (move_uploaded_file($tmp, $pasta)) {
        if (file_exists($pasta)) {
            echo '<h7 style="color: green">Arquivo salvo com sucesso!</h7>';
        } else {
            echo '<h7 style="color: red">Não foi possível fazer o upload do arquivo.</h7>';
        }
    } else {
        echo '<h7 style="color: red">Extensão inválida</h7>';
    }
    //}
}
