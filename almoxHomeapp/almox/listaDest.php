<?
session_start();
header("Content-Type: text/html; charset=ISO-8859-1",true);
include("../../inc/crislib.php");
include("../../inc/functions.php");
include("functions.php");
//faz conexão com banco
$con = conexao();

if(estaLogado() != true && permissaoMod("almox") != true){
	error("<span style=\"font-size: 11px;\">Oops! Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar este arquivo!</span>");
	exit;
}
/*
 * INSERE NOVO DESTINO
 */
if($_GET["setor"] != ""){
    $ins = ins("almox_estoque_destino","nome, usuario","'".str($_GET["setor"])."', '".$_SESSION["usuario"]."'");
    p("<b>Destino inserido!</b>");
}

/*
 * LISTA DESTINOS
 */
$sel = sel("almox_estoque_destino","","nome ASC");
while($h = fetch($sel)){
    e("<li>".$h["nome"]." [<a href=\"?painel&m=almox&p=destinos&a=del&idr=".$h["id"]."\">excluir</a>]</li>");
}
?>
