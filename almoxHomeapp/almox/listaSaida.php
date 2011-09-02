<?
session_start();
header("Content-Type: text/html; charset=ISO-8859-1",true);
/*
 * INSERE PRODUTOS AO PEDIDO E LISTA PRODUTOS DO PEDIDO
 * p = entrada
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

$chave = str($_GET["idpedido"]);
$destino = str($_GET["destino"]);
//remover item da lista de pedidos
if($_GET["a"] == "ri"){
    $idItem = str($_GET["idItem"]);
	$campo = campo("almox_estoque_pedidos_itens","",$idItem);
    $delitem = del("almox_estoque_pedidos_itens",$idItem);//deleta item e mostra a lista de produtos novamente, abaixo...
	regLog("Removeu um &iacute;tem do pedido chave = $campo");
    //verifica se o ítem é referência (primeiro registro deste tipo de ítem. Exibe o contador de quantidade do tipo de ítem em questão). Ser referência significa que há vários registros de um mesmo ítem. Então, para exibir o contador, é exibido somente deste ítem
    /*
    $buscaitemref = sel("almox_estoque_pedidos_itens","id = '$idItem'");
    $b = fetch($buscaitemref);
    if($b["qtde"] > 1){//se for, exclui o último registro deste tipo de ítem
        $buscaultimoregistro = sel("almox_estoque_pedidos_itens","item = '".$b["item"]."' and chavepedido = '$chave'","id DESC","1");
        $c = fetch($buscaultimoregistro);
        $delitem = del("almox_estoque_pedidos_itens",$c["id"]);
        $nqtde = $b["qtde"] - 1;
        $upd = upd("almox_estoque_pedidos_itens","qtde = '$nqtde'",$idItem);
    }else{//se não for, exclui o ítem
        $delitem = del("almox_estoque_pedidos_itens",$idItem);//deleta item e mostra a lista de produtos novamente, abaixo...
    }*/
}
//se houver uma chave e um destino, executa
if(!empty($chave) && !empty($destino)){
    //variáveis
    $codigobarras = str($_GET["codigobarras"]);
    $qtde = str($_GET["qtde"]);
    if(empty($codigobarras) or empty($qtde)){
        p("Oops! A quantidade e o c&oacute;digo de barras s&atilde;o obrigat&oacute;rios!",1);
		regLog("Tentou inserir um produto no pedido ($chave) mas, n&atilde;o informou qtde e c&oacute;digo de barras.");
    }else{
        //função insere produto
        function insereProduto($chave,$codigobarras,$qtde){
            //verifica se já há este produto/codigo de barras na lista
            $selb = sel("almox_estoque_pedidos_itens","chavepedido = '$chave' and codigobarras = '$codigobarras'");
            if(total($selb) > 0){
                $f = fetch($selb);
                $qtde = $qtde + $f["qtde"];
                //verifica se ainda há ítens em estoque para este produto/codigo de barras
                $sel = sel("almox_estoque","codigobarras = '$codigobarras'");
                $q = fetch($sel);
                if($q["qtde"] < $qtde){ //se não houver
                    p("Oops! N&atilde;o temos esta quantidade em estoque! Temos ".$q["qtde"]." &iacute;tens em estoque.",1); //informa
					regLog("Tentou inserir um produto ($codigobarras) no pedido mas, n&atilde;o havia qtde em estoque.");
                }else{ //se houver
                    //inclui no pedido
                    $produto = $q["produto"];
                    //$ins = ins("almox_estoque_pedidos_itens","chavepedido, item, codigobarras, qtde","'$chave', '$produto', '$codigobarras', '$qtde'");
                    $upd = upd("almox_estoque_pedidos_itens","qtde = '$qtde'",$f["id"]);
					regLog("Adicionou &iacute;tens de um produto ($codigobarras) no pedido ($chave).");
                }
            }else{
                //verifica se ainda há ítens em estoque para este produto/codigo de barras
                $sel = sel("almox_estoque","codigobarras = '$codigobarras'");
                $q = fetch($sel);
                if($q["qtde"] < $qtde){ //se não houver
                    p("Oops! N&atilde;o temos esta quantidade em estoque! Temos ".$q["qtde"]." &iacute;tens em estoque.",1); //informa
					regLog("Tentou inserir um produto ($codigobarras) no pedido mas, n&atilde;o havia qtde em estoque.");
                }else{ //se houver
                    //inclui no pedido
                    $produto = $q["produto"];
                    $ins = ins("almox_estoque_pedidos_itens","chavepedido, item, codigobarras, qtde","'$chave', '$produto', '$codigobarras', '$qtde'");
					regLog("Inseriu produtos ($codigobarras) no pedido ($chave).");
                }
            }


            /*if(!empty($codigobarras)){
                //busca nome do produto no banco de dados
                $buscanome = sel("almox_estoque","codigobarras = '$codigobarras'");
                $t = fetch($buscanome);
                $produto = $t["produto"];
                $qtde = 0;
                //verifica se o código de barras/produto já foi inserido
                $buscacodigolista = sel("almox_estoque_pedidos_itens","chavepedido = '$chave' and codigobarras = '$codigobarras'");
                if(total($buscacodigolista) > 0){
                    p("Oops! Este &iacute;tem j&aacute; est&aacute; na lista!",1);
                }else{
                    //insere o novo ítem ao pedido
                    $ins = ins("almox_estoque_pedidos_itens","chavepedido, item, codigobarras, qtde","'$chave', '$produto', '$codigobarras', '$qtde'");
                    //verifica se já existe um ítem do mesmo tipo no pedido
                    $buscaitemlista = sel("almox_estoque_pedidos_itens","chavepedido = '$chave' and item = '$produto'","id ASC","1");
                    if(total($buscaitemlista) > 0){ //se sim, atualiza contador do primeiro registro, para exibir qtde na lista
                        $d = fetch($buscaitemlista);
                        //nova quantidade
                        $nqtde = $d["qtde"] + 1;
                        //modifica quantidade de todos os ítens com este nome ($produto) para 0 (zero)
                        $upd1 = mysql_query("UPDATE almox_estoque_pedidos_itens SET qtde = '0' WHERE chavepedido = '$chave' and item = '$produto'") or die(mysql_error());
                        //em seguida atualiza quantidade do primeiro registro deste ítem ($produto). Este registro será a referência
                        $upd2 = upd("almox_estoque_pedidos_itens","qtde = '$nqtde'",$d["id"]);
                    }
                }
            }else{
                if(empty($produto) && empty($qtde)){
                    p("<b>Se o produto n&atilde;o cont&eacute;m c&oacute;digo de barras, voc&ecirc; precisa informar o nome do produto e a quantidade a ser descontada do estoque.</b>",1);
                }else{
                    //verifica se já existe um ítem do mesmo tipo no pedido
                    $buscaitemlista = sel("almox_estoque_pedidos_itens","chavepedido = '$chave' and item = '$produto'","id ASC","1");
                    if(total($buscaitemlista) > 0){ //se sim, atualiza contador do primeiro registro, para exibir qtde na lista
                        $d = fetch($buscaitemlista);
                        $nqtde = $d["qtde"] + 1; //nova quantidade
                        $upd = upd("almox_estoque_pedidos_itens","qtde = '$nqtde'",$d["id"]);
                    }
                    $ins = ins("almox_estoque_pedidos_itens","chavepedido, item, codigobarras, qtde","'$chave', '$produto', '$codigobarras', '$qtde'");
                }
            }*/
        }
    }
    //insere
    $buscapedido = sel("almox_estoque_pedidos","chave = '$chave' and destino = '$destino'");
    if(total($buscapedido) > 0){ # se estiver, apenas acrescenta o produto
        insereProduto($chave,$codigobarras,$qtde);
    }else{ # se não, abre o pedido e acrescenta o produto
        //status = 0: em andamento | //status = 1: pedido salvo e fechado | //status = 2: pedido cancelado
        $insped = ins("almox_estoque_pedidos","chave, destino, datahora, usuario, status","'$chave', '".$destino."', '".date("Y-m-d H:i:s")."', '".$_SESSION["usuario"]."', '0'");
		regLog("Registrou um novo pedido.");
        insereProduto($chave,$codigobarras,$qtde);
    }
}

//se houver chave, há um pedido, então exibe a relação de produtos
if(!empty($chave)){
    e("<script type=\"text/javascript\" src=\"inc/crislib.js\"></script>");
    e("<script type=\"text/javascript\" src=\"mods/almox/js.js\"></script>");
    # lista os produtos com botão cancelar em cada um
    $exibeitens = sel("almox_estoque_pedidos_itens","chavepedido = '$chave'","");
    $peganome = sel("almox_estoque_pedidos","chave = '$chave'");
    $a = fetch($peganome);
    e("<div id=\"listaDeSaida\">");
    e("<h4>Pedido para ".$a["destino"]."</h4>");
    while($f = fetch($exibeitens)){
        if($f["qtde"] > 0){
            e("<li id=\"".$f["id"]."\">".$f["item"]);
            e(" (".$f["qtde"].")");
            e(" (c&oacute;d. barras: ".$f["codigobarras"].")");
            e(" [<a href=\"javascript:;\" onclick=\"removerItem('$chave','".$f["id"]."')\">remover &iacute;tem</a>]</li>
                ");
        }
    }
    # exibe botão salvar | imprimir | cancelar
	if($a["status"] != 2){
		e("<p><a href=\"javascript:;\" onclick=\"salvarPedido('$chave')\">Salvar pedido para imprimir</a> | ");
		e("<a href=\"/?painel&m=almox&p=capa&idcancel=$chave\">Cancelar pedido</a></p>");
	}
    e("<div id=\"salvarPedido\"></div>");
    e("</div>");
}
?>
