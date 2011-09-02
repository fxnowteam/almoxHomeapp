<?
session_start();
header("Content-Type: text/html; charset=ISO-8859-1",true);
/*
 * INSERE TIPOS DE PRODUTOS OU ATUALIZA PRODUTOS
 * p = produto
 */
include("../../inc/crislib.php");
include("../../inc/functions.php");
include("functions.php");
//faz conexão com banco
$con = conexao();
if(estaLogado() != true && permissaoMod("almox") != true){
	error("<span style=\"font-size: 11px;\">Oops! Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar este arquivo!</span>");
	exit;
}
$produto = str($_GET["produto"]);
$dbseller = str($_GET["dbseller"]);
$codigobarras = str($_GET["codigobarras"]);
$alerta = str($_GET["alerta"]);
$idreg = str($_GET["idreg"]);
//inserindo novo registro
if($_GET["a"] == "i"){
    if(empty($produto)){
        p("<b>Oops! Faltou o nome do produto!</b>",1);
		regLog("Tentou inserir um produto mas, faltou o nome.");
    }else{
        $sel2 = sel("almox_estoque_produtos","codigodbseller = '$dbseller'");
        if(total($sel2) > 0){
            p("<b>Oops! J&aacute; existe um produto com este c&oacute;digo do DBSeller!</b>",1);
			regLog("Tentou inserir um produto mas, ele j&aacute; est&aacute; cadastrado.");
        }else{
            $ins = ins("almox_estoque_produtos","nome, codigodbseller, codigobarras, alerta","'$produto', '$dbseller', '$codigobarras','$alerta'");
            criaAlertas();
            p("<b>Produto $nome cadastrado!</b>");
			regLog("Cadastrou o produto $nome.");
        }
    }
}

//atualizando registro já existente
if($_GET["a"] == "up"){
    $upd = upd("almox_estoque_produtos","codigodbseller = '$dbseller', codigobarras = '$codigobarras', alerta = '$alerta'",$idreg);
    criaAlertas();
    p("<b>Produto $nome atualizado!</b>");
	regLog("Atualizou o produto $nome.");
}
?>
