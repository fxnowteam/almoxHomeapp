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
 * FILTRO
 */
if(!empty($_GET["destino"]) or !empty($_GET["dataini"]) or !empty($_GET["datafim"])){
    $destino = $_GET["destino"];
    $dataini = data($_GET["dataini"],2)." 00:00:00";
    $datafim = data($_GET["datafim"],2)." 23:59:59";
    if(empty($destino) or empty($dataini) or empty($datafim)){
        p("Oops! Voc&ecirc; deixou algum campo em branco!",1);
		regLog("Tentou emitir uma listagem de pedidos mas deixou algum campo em branco (destino, dataini ou datafim).");
    }else{
        $where = "WHERE destino = '$destino' and datahora BETWEEN '$dataini' AND '$datafim' ";
		regLog("Emitiu uma listagem filtrada de pedidos onde destino = $destino, $dataini e $datafim");
    }
}

/*
 * LISTAR PEDIDOS
 */
//$sel = sel("almox_estoque_pedidos",$where,"id DESC",100);
$sel = mysql_query("SELECT * FROM almox_estoque_pedidos $where ORDER BY id DESC LIMIT 100") or die(mysql_error());
if(total($sel) == 0){
    p("Nenhum resultado encontrado.",1);
	regLog("Tentou emitir uma listagem de pedidos por destino e per&iacute;odo, mas n&atilde;o obteve resultados.");
}else{
    while($r = fetch($sel)){
        $d = explode(" ",$r["datahora"]);
        e(data($d[0])." / ".$d[1]." - ".$r["destino"]);
        if($r["status"] == 0){
            e(", <a href=\"/?painel&m=almox&p=capa&idpedido=".$r["chave"]."\">editar</a>");
        }elseif($r["status"] == 1){
            e(", <span style=\"color: green\"><b>entregue</b></span> (<a href=\"javascript:;\" onclick=\"window.open('scripts/printalmox_canhoto.php?idpedido=".$r["chave"]."','Imprimir','toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no,width=640,height=480');\">imprimir canhoto</a>)");
        }else{
            e(", <span style=\"color: red\"><i>cancelado</i></span>");
        }
        e("<br>");
    }
}
?>
