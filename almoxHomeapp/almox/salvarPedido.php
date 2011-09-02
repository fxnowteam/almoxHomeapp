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

$chave = str($_GET["idpedido"]);
//verifica se há a quantidade pedida em estoque
$sela = sel("almox_estoque_pedidos_itens","chavepedido = '$chave'");
while($t = fetch($sela)){
    $selb = sel("almox_estoque","codigobarras = '".$t["codigobarras"]."'");
    $y = fetch($selb);
    if($y["qtde"] < $t["qtde"]){
        $erro = 1;
        e("- N&atilde;o temos a quantidade em estoque para o produto ".$t["item"].". Est&aacute; sendo pedido ".$t["qtde"]." &iacute;tens e temos ".$y["qtde"]." &iacute;tens em estoque. <br>");
		regLog("Tentou emitir a guia impressa ($chave) mas, n&atilde;o havia mais a qtde solicitada em estoque.");
    }
}

if($erro != 1){
    //descontar no estoque o número de ítens
    $selc = sel("almox_estoque_pedidos_itens","chavepedido = '$chave'");
    while($t = fetch($selc)){
        //debitar da tabela almox_estoque
        $sele = sel("almox_estoque","codigobarras = '".$t["codigobarras"]."'");
        $y = fetch($sele);
        $novoestoque = $y["qtde"] - $t["qtde"];
        $upd = mysql_query("UPDATE almox_estoque SET qtde = '$novoestoque' WHERE codigobarras = '".$t["codigobarras"]."'") or die(mysql_error());
        //debitar da tabela almox_estoque_produtos
        $self = sel("almox_estoque_produtos","nome = '".$t["item"]."'");
        $z = fetch($self);
        $novoestoque = $z["qtde"] - $t["qtde"];
        $upd = mysql_query("UPDATE almox_estoque_produtos SET qtde = '$novoestoque' WHERE nome = '".$t["item"]."'") or die(mysql_error());
    }
    $upd = mysql_query("UPDATE almox_estoque_pedidos SET status = '1' WHERE chave = '$chave'") or die(mysql_error());
	regLog("Salvou o pedido $chave e emitiu a guia impressa!");
    p("<b>Seu pedido foi salvo! Clique <a href=\"javascript:;\" onclick=\"window.open('scripts/printalmox_canhoto.php?idpedido=$chave','Imprimir','toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no,width=640,height=480');\">aqui</a> para imprimir o canhoto!</b>");
}
?>
