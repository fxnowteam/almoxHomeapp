<?
session_start();
header("Content-Type: text/html; charset=ISO-8859-1",true);
/*
 * INSERE PRODUTOS AO ESTOQUE
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
if($_GET["a"] == "i"){
    $produto = str($_GET["produto"]);
    $codigobarras = $_GET["codigobarras"];
    $qtde = str($_GET["qtdeitens"]);
    $val = explode("/",$_GET["dataini"]); //dataini = é usado este nome para usar a mesma biblioteca jquery de calendario de outro arquivo
    $validade = $val[2]."-".$val[1]."-".$val[0];
    if(empty($produto)) {
        p("<b>Oops! Voc&ecirc; n&atilde;o informou qual produto quer cadastrar!</b>",1);
		regLog("Tentou inserir um produto no estoque mas, n&atilde;o informou qual produto.");
    }elseif(empty($qtde)){
        p("<b>Oops! Voc&ecirc; precisa informar a quantidade de &iacute;tens.</b>",1);
		regLog("Tentou inserir um produto no estoque mas, n&atilde;o informou a quantidade.");
    }elseif(empty($codigobarras)){
        p("<b>Oops! Voc&ecirc; precisa informar o c&oacute;digo de barras.</b>",1);
		regLog("Tentou inserir um produto no estoque mas, n&atilde;o informou o c&oacute;digo de barras.");
    }else{
        //verifica se o produto já existe na tabela de produtos
        $sel = sel("almox_estoque_produtos","nome = '$produto'");
        if(total($sel) > 0){
            //insere no banco
            $sel2 = sel("almox_estoque","codigobarras = '$codigobarras'");
            if(total($sel2) == 0){
                $ins = ins("almox_estoque","produto, codigobarras, qtde, datainc, validade, usuario","'$produto', '$codigobarras', '$qtde', '".date("Y-m-d")."', '$validade', '".$_SESSION["usuario"]."'");
				regLog("Inseriu $qtde &iacute;tens de $produto no estoque.");
            }else{
                $u = fetch($sel2);
                $contagem = $u["qtde"] + $qtde;
                $upd = mysql_query("UPDATE almox_estoque SET qtde = '$contagem' WHERE codigobarras = '$codigobarras'") or die(mysql_error());
				regLog("Atualizou o estoque do $produto. Antes: ".$u["qtde"].". Depois: $contagem. Qtde inserida: $qtde");
            }
            //edita estoque
            $itensestoque = estoque($produto,"+",$qtde);
            p("<b>&Iacute;tem $produto inserido com $itensestoque &iacute;tens em estoque!</b>");
        }else{
            p("<b>Este produto n&atilde;o est&aacute; cadastrado. Clique <a href=\"?painel&m=almox&p=produtos\">aqui</a> para cadastra-lo.</b>",1);
			regLog("Tentou inserir um produto em estoque mas, este n&atilde;o estava previamente cadastrado.");
        }
    }
}
?>
