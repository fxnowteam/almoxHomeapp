<?php
header("Content-Type: text/html; charset=ISO-8859-1",true);
/*
 * MÓDULO MODELO
 * Menu da administração:
 * incluir no arquivo scripts/menuAdminMods.php
 */

/*
 * carrega javascript na tag <body>
 */
function onLoad($path){
    if($path == "/?painel&m=almox&p=entrada"){
        $v = "document.getElementById('codigobarras').focus();";
    }
    if($path == "/?painel&m=almox&p=produtos"){
        $v = "ajax('listaProd', 'Carregando lista de produtos...', 'mods/almox/listaProds.php');";
    }
    if($path == "/?painel&m=almox&p=pedidos"){
        $v = "ajax('listaPedidos', 'Carregando &uacute;ltimos pedidos...', 'mods/almox/listaPedidos.php');";
    }
    if(stripos($path,"/?painel&m=almox&p=destinos") !== false){ //usando stripos pois é usada a url também para exclusão de ítens
        $v = "ajax('listaDest', 'Carregando...', 'mods/almox/listaDest.php');";
    }
    if(stripos($path,"/?painel&m=almox&p=capa&idpedido=") !== false){ //usando stripos pois é usada a url também para exclusão de ítens
        $v = "ajax('listaSaida', 'Carregando...', 'mods/almox/listaSaida.php?idpedido=".$_GET["idpedido"]."');";
    }
    return $v;
}

/*
 * exibe conteúdo na página de administração
 */
function admMod($p = false){
	if(permissaoMod("almox") == true){
        include("mods/almox/functions.php");
        e("<script type=\"text/javascript\" src=\"mods/almox/js.js\"></script>");
        e("<link rel=\"stylesheet\" type=\"text/css\" href=\"mods/almox/css.css\" />");
        if($p == "capa"){ $subtitulo = "&raquo; Sa&iacute;da de estoque"; }
        if($p == "entrada"){ $subtitulo = "&raquo; Entrada de estoque"; }
        if($p == "pedidos"){ $subtitulo = "&raquo; Pedidos"; }
        if($p == "produtos"){ $subtitulo = "&raquo; Cadastro de produtos"; }
        if($p == "destinos"){ $subtitulo = "&raquo; Destinos"; }
        if($p == "editarestoque"){ $subtitulo = "&raquo; Editar estoque"; }
        if($p == "editarestoque" && $_GET["filtro"] == "alerta"){ $subtitulo = "&raquo; Editar estoque &raquo; &Iacute;tens com estoque baixo"; }
        if($p == "relatorios"){ $subtitulo = "&raquo; Relat&oacute;rios"; }
        e("<h3>Almoxarifado $subtitulo</h3>");
        verificaAlertas();
        ?>
				  <script type="text/javascript" src="inc/jquery-1.4.2.min.js"></script>
                  <script type="text/javascript" src="inc/jquery-ui-1.8.6.custom.min.js"></script>
                  <link rel="stylesheet" type="text/css" href="inc/jquery-ui-1.8.6.custom.css" />
                  <script type="text/javascript">
                  $(function() {
                    var availableTags = [
                      <?
                      $selmed = mysql_query("SELECT nome FROM almox_estoque_produtos ORDER BY nome ASC") or die(mysql_error());//usando mysql_query em vez de sel para selecionar as colunas no db
                      $totmed = total($selmed);
                      while($j = fetch($selmed)){
                          e("\"".$j["nome"]."\"");
                          $cont = $cont + 1;
                          if($cont < $totmed){
                              e(",
                              ");
                          }
                      }
                      ?>
                    ];
                    $( "#produto" ).autocomplete({
                      source: availableTags
                    });
                  });
                  </script><?
        /*
         * SAÍDA DE ESTOQUE
         */
        if($p == "capa"){
            if(!empty($_GET["idcancel"])){
                $idcancel = str($_GET["idcancel"]);
                $upd = mysql_query("UPDATE almox_estoque_pedidos SET status = '2' WHERE chave = '$idcancel'") or die(mysql_error());
                p("<b>Pedido cancelado com sucesso!</b>");
            }
            ?>
            <script type="text/javascript" src="inc/shortcut.js"></script>
            <script type="text/javascript">
            shortcut.add("CTRL+J",function()
            {
                saidaEstoque();
            });

            </script>
            <?
            /*
             * se tem a variável idpedido, é porque estamos tentando editar um pedido
             */
            if(!empty($_GET["idpedido"])){
                $chave = str($_GET["idpedido"]);
                $sel = sel("almox_estoque_pedidos","chave = '$chave'");
                $f = fetch($sel);
                if($f["status"] != 0){
                    if($f["status"] == 1){ $status = "cancelado"; }
                    if($f["status"] == 2){ $status = "entregue"; }
                    p("Oops! Voc&ecirc; n&atilde;o pode editar este pedido pois ele j&aacute; foi $status.",1);
                }else{

                }
            }
            ?>
            <form id="formsaida" name="formsaida" method="post" action="">
                <input type="hidden" id="idpedido" value="<? if($_GET["idpedido"]){ e($f["chave"]); }else{ e(sha1(md5(date("YmdHis")))); } ?>">
                <label>Destino: </label> <br>
                <select id="destino"<? if($_GET["idpedido"]){ e("disabled"); } ?>>
                    <option></option>
                    <?
                    $seldest = sel("almox_estoque_destino","","nome ASC");
                    while($g = fetch($seldest)){
                        ?>
                        <option value="<?= $g["nome"] ?>"<? if($f["destino"] == $g["nome"]){ e(" selected"); } ?>><?= $g["nome"] ?></option>
                        <?
                    }
                    ?>
                </select> <br>
                <fieldset style="width: 420px">
                    <legend>Dados do produto: </legend>
                    Qtde: <input type="text" id="qtde" style="width: 50px"> <label>C&oacute;digo de barras: </label> <input type="text" id="codigobarras">
                </fieldset>
                <input type="button" value="Inserir" onclick="saidaEstoque()">
            </form>
            <div id="listaSaida"></div>
            <?
        }
        
        /*
         * ENTRADA DE ESTOQUE
         */
        if($p == "entrada"){
            ?>
            <script type="text/javascript" src="inc/shortcut.js"></script>
            <script type="text/javascript">
            shortcut.add("CTRL+J",function(){
                insProd();
            });
            </script>
            <script type="text/javascript" src="inc/mod_almox_dataini_calend.js"></script>
            <form name="formm" method="post" action="">
                <label>Produto: </label> <input type="text" name="produto" id="produto" style="width: 411px"> <label>Qtde: </label><input type="text" name="qtdeitens" id="qtdeitens" value="0" onclick="this.value=''" style="width: 50px;"><br>
                <label id="labelqtde">Data de validade: </label> <input type="text" name="dataini" id="dataini"> <label id="labelcb">C&oacute;digo de barras: </label> <input type="text" name="codigobarras" id="codigobarras"> 
                <input type="button" value="Ok" style="width: 35px;" onclick="insProd();">
            </form>
            <div id="insProd"></div>
            <?
        }
        
        /*
         * LISTA DE PEDIDOS
         */
        if($p == "pedidos"){
            //editar ou cancelar pedidos ainda não salvos
            //salvar e imprimir pedidos
            //exibir últimos 100 pedidos
            //opção de filtro somente por determinado destino em determinado período
            ?>
            <script type="text/javascript" src="inc/mod_almox_dataini_calend.js"></script>
            <script type="text/javascript" src="inc/mod_almox_datafim_calend.js"></script>
            <?
            e("<fieldset>
                <legend>Filtrar</legend>
                <label>Destino: </label> <select id=\"destino\">
                <option></option>");
            $listadestinos = sel("almox_estoque_destino","","nome ASC");
            while($g = fetch($listadestinos)){
                e("<option value=\"".$g["nome"]."\">".$g["nome"]."</option>
                    ");
            }
            e("</select> ");
            e("<label>Per&iacute;odo: </label> <input type=\"text\" id=\"dataini\"> &agrave; <input type=\"text\" id=\"datafim\"> <input type=\"button\" value=\"Filtrar\" onclick=\"ajax('listaPedidos', 'Carregando &uacute;ltimos pedidos...', 'mods/almox/listaPedidos.php?destino='+getId('destino').value+'&dataini='+getId('dataini').value+'&datafim='+getId('datafim').value)\">
            </fieldset>");
            e("<div id=\"listaPedidos\"></div>");
        }
        
        /*
         * CADASTRO DE PRODUTOS
         */
        if($p == "produtos"){
            //não deixar incluir com mesmo nome. Motivo: em vez de id, se usa o nome para buscar na função estoque()
            //colocar qtde mínima para emitir alerta
            ?>
            <form name="formm" method="post" action="">
                <label>Produto: </label><br>
                <input type="text" name="produto" id="produto">  <br>
                <label>DBSeller: </label><br>
                <input type="text" name="dbseller" id="dbseller">  <br>
                <label>Alerta: </label><br>
                <input type="text" name="alerta" id="alerta">  <br>
                <input type="hidden" id="idreg" value="">
                <input type="button" id="botaoSubmit" value="Cadastrar" onclick="cadProd();">
            </form>
            <div id="cadProd"></div>
            <div id="listaProd"></div>
            <?
        }

        /*
         * PRÉ-CADASTRO DE DESTINOS ONDE SERÃO ENTREGUES OS MATERIAIS
         */
        if($p == "destinos"){
            /*
             * EXCLUIR DESTINO
             */
            if($_GET["a"] == "del"){
				$campo = campo("almox_estoque_destino","nome",$_GET["idr"]);
				regLog("Excluiu o destino $campo.");
                $del = del("almox_estoque_destino",$_GET["idr"]);
                p("<b>Destino exclu&iacute;do com sucesso!</b>");
            }
            ?>
            <form name="formm" method="post" action="">
                <label>Unidade/Setor: </label> 
                <input type="text" name="setor" id="setor">
                <input type="button" value="Cadastrar" onclick="cadDestino();">
            </form>
            <div id="listaDest"></div>
            <?
        }

        /*
         * PRÉ-CADASTRO DE DESTINOS ONDE SERÃO ENTREGUES OS MATERIAIS
         */
        if($p == "editarestoque"){
            /*
             * ATUALIZA TABELA
             */
            /*
            $cont = 0;
            if($_POST["edest"] == 1){ //abaixo do estoque
                $sel = sel("almox_estoque_produtos","qtde <= alerta","nome ASC");
                while($f = fetch($sel)){
                    $cont = $cont + 1;
                    $idregistro = $_POST["idregistro_$cont"];
                    $qtde = $_POST["qtde_$cont"];
                    $alerta = $_POST["alerta_$cont"];
                    $upd = upd("almox_estoque_produtos","qtde = '$qtde', alerta = '$alerta'",$idregistro);
                }
                criaAlertas();
                p("<b>Estoque atualizado com sucesso!</b>");
            }
            if($_POST["edest"] == 2){ //todos
                $sel = sel("almox_estoque_produtos","codigobarras = ''","nome ASC");
                while($f = fetch($sel)){
                    $cont = $cont + 1;
                    $idregistro = $_POST["idregistro_$cont"];
                    $qtde = $_POST["qtde_$cont"];
                    $alerta = $_POST["alerta_$cont"];
                    $upd = upd("almox_estoque_produtos","qtde = '$qtde', alerta = '$alerta'",$idregistro);
                }
                criaAlertas();
                p("<b>Estoque atualizado com sucesso!</b>");
            }
            $cont = 0;*/

            /*
             * LISTA PRODUTOS EDITÁVEIS (SEM CÓDIGO DE BARRAS)
             * PRODUTOS COM CÓDIGO DE BARRAS DEVEM SER INSERIDOS COM A LEITORA
             */
            $filtro = $_GET["filtro"];
            e("<div id=\"formedestoque\">");
            //e("<form name=\"formedestoque\" method=\"post\" action=\"\">");
            //e("<input type=\"submit\" id=\"submit\" value=\"Atualizar estoque!\">");
            e("[<a href=\"/scripts/printalmox_estoquebaixo.php\">Imprimir esta lista</a>]");
            e("<ul>");
            if($filtro == "alerta"){
                //e("<input type=\"hidden\" name=\"edest\" value=\"1\">");
                $sel = sel("almox_estoque_produtos","qtde < alerta","nome ASC");
                while($r = fetch($sel)){
                    $cont = $cont + 1;
                    //e("<input type=\"hidden\" name=\"idregistro_$cont\" value=\"".$r["id"]."\">");
                    e("<li><b>".$r["nome"]."</b>: ");
                    //e("<input type=\"text\" name=\"qtde_$cont\" value=\"");
                    e($r["qtde"]);
                    //e("\">");
                    e(" &iacute;tens em estoque (");
                    //e("<input type=\"text\" name=\"alerta_$cont\" value=\"");
                    e($r["alerta"]);
                    //e("\">");
                    e(" m&iacute;nimo)</li>
                    ");
                }
            }else{
                /*e("<input type=\"hidden\" name=\"edest\" value=\"2\">");
                $sel = sel("almox_estoque_produtos","codigobarras <> '1'","nome ASC");
                while($r = fetch($sel)){
                    $cont = $cont + 1;
                    e("<li><input type=\"hidden\" name=\"idregistro_$cont\" value=\"".$r["id"]."\"><b>".$r["nome"]."</b>: <input type=\"text\" name=\"qtde_$cont\" value=\"".$r["qtde"]."\"> &iacute;tens em estoque (<input type=\"text\" name=\"alerta_$cont\" value=\"".$r["alerta"]."\"> m&iacute;nimo)</li>
                    ");
                }*/
            }
            e("</ul>");
            //e("<input type=\"submit\" id=\"submit\" value=\"Atualizar estoque!\">");
            //e("</form>");
            e("</div>");
        }

        /*
         * RELATÓRIOS
         */
        if($p == "relatorios"){
            ?>
            <ul>
                <li><a href="/?painel&m=almox&p=relatorios&relatorio=1">Estoque atual</a>: seleciona os produtos a serem exibidos ou, exibe o total.</li>
                <li><a href="/?painel&m=almox&p=pedidos">Sa&iacute;das</a>: com filtro por Destino e Per&iacute;odo.</li>
                <li><a href="/?painel&m=almox&p=relatorios&relatorio=2">DBSeller</a>: com filtro por data.</li>
                <li><a href="/scripts/printalmox_datavalidade.php">&Iacute;tens que est&atilde;o para vencer</a>: alerta com 4 meses de anteced&ecirc;ncia.</li>
                <li><a href="/?painel&m=almox&p=editarestoque&filtro=alerta">&Iacute;tens abaixo do estoque</a>: para configurar os alertas, clique <a href="/?painel&m=almox&p=produtos">aqui</a>.</li>
            </ul>
            <?
            if($_GET["relatorio"] == 1){
                ?>
            <script type="text/javascript">
                $(document).ready(function() {
                        $('#selectAll').click(function() {
                                if(this.checked == true){
                                        $("input[type=checkbox]").each(function() {
                                                this.checked = true;
                                        });
                                } else {
                                        $("input[type=checkbox]").each(function() {
                                                this.checked = false;
                                        });
                                }
                        });
                });
            </script>
                <form name="relat01" method="post" action="/scripts/printalmox_estoque.php" target="_blank">
                    <fieldset style="text-align: left; width: 200px">
                        <legend><b>Tipo de relat&oacute;rio</b></legend>
                        <label>Completo: </label><input type="radio" name="tiporelat[]" value="completo" onclick="sDisplay('relfiltros','none');" checked>
                        <label>Filtrado: </label><input type="radio" name="tiporelat[]" value="filtrado" onclick="sDisplay('relfiltros','block');">
                    </fieldset>
                    <fieldset id="relfiltros" style="text-align: left; width: auto; display: none">
                        <legend><b>Filtros</b></legend>
                        <label><input type="checkbox" id="selectAll"> <b>Selecionar todos</b></label>
                        <div style="clear: both; margin: 10px;"></div>
                        <?
                        $selc = sel("almox_estoque_produtos","","nome ASC");
                        while($s = fetch($selc)){
                            e("<li><input type=\"checkbox\" name=\"filtro[]\" value=\"".$s["id"]."\"> <label>".$s["nome"]."</label></li>");
                        }
                        ?>
                    </fieldset>
                    <input type="submit" value="Imprimir">
                </form>
                <?
            }
            if($_GET["relatorio"] == 2){
                ?>
                <script type="text/javascript" src="inc/mod_almox_dataini_calend.js"></script>
                <form name="formrel02" method="post" action="/scripts/printalmox_dbseller.php" target="_blank">
                    <label>Informe a data: </label> <input type="text" name="dataini" id="dataini" value="<?= date("d/m/Y") ?>"> <input type="submit" value="Imprimir">
                </form>
                <?
            }
            /*
            if($_GET["relatorio"] == 3){
                $anohj = date("Y");
                $meshj = date("m");
                $messoma = $meshj + 3;
                if($messoma > 12){
                    $anohj = $anohj + 1;
                    if($messoma == 13){ $messoma = 1; }
                    if($messoma == 14){ $messoma = 2; }
                    if($messoma == 15){ $messoma = 3; }
                }
                if($messoma < 10){
                    $messoma = "0".$messoma;
                }
                $datavalidade = $anohj."-".$messoma."-28";
                $sel = mysql_query("SELECT * FROM almox_estoque WHERE validade BETWEEN '".date("Y-m-d")."' AND '$datavalidade'") or die(mysql_error());
                while($f = fetch($sel)){
                    e("<li>".$f["produto"].", ".$f["qtde"]." &iacute;tens em estoque, vence em ".data($f["validade"])."</li>");
                }
            }
             */
        }
	}else{
		semPermissao();
	}
}
?>
