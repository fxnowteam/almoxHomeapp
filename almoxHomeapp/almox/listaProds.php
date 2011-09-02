<?
session_start();
header("Content-Type: text/html; charset=ISO-8859-1",true);
/*
 * LISTA PRODUTOS DA LISTAGEM DE PEDIDO
 * p = capa
 */
//header("Content-Type: text/html; charset=ISO-8859-1",true);
include("../../inc/crislib.php");
include("../../inc/functions.php");
include("functions.php");
//faz conexão com banco
$con = conexao();

if(estaLogado() != true && permissaoMod("almox") != true){
	error("<span style=\"font-size: 11px;\">Oops! Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar este arquivo!</span>");
	exit;
}

$sel = sel("almox_estoque_produtos","","nome ASC");
while($g = fetch($sel)){
    e($g["codigodbseller"]." - <a href=\"javascript:;\" onclick=\"edProd('".$g["nome"]."', '".$g["codigodbseller"]."', '".$g["codigobarras"]."', '".$g["alerta"]."', '".$g["id"]."')\">".$g["nome"]."</a> <br>");
}
?>
