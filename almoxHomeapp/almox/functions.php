<?	
//soma ou subtrai do estoque
function estoque($produto,$operacao,$qtde = false){
    $sel = sel("almox_estoque_produtos","nome = '$produto'");
    $r = fetch($sel);

    //verifica se está sendo informado a quantidade
    if($qtde == false){
        $nqtde = 1;
    }else{
        $nqtde = $qtde;
    }
    //faz calculo do novo valor da qtde de ítens em estoque
    if($operacao == "+"){
        $novoestoque = $r["qtde"] + $nqtde;
    }else{
        $novoestoque = $r["qtde"] - $nqtde;
    }
    $upd = mysql_query("UPDATE almox_estoque_produtos SET qtde = '$novoestoque' WHERE nome = '$produto'") or die(mysql_error());
    
    //verifica se o novo estoque está abaixo do nível de alerta
    if($novoestoque <= $r["alerta"]){
        $upd2 = mysql_query("UPDATE almox_estoque_configs SET alertaestoque = '1'") or die(mysql_error()); //se tiver, atualiza tabela verificadora
    }else{
        $upd3 = mysql_query("UPDATE almox_estoque_configs SET alertaestoque = '0'") or die(mysql_error()); 
    }
    return $novoestoque;
}

function verificaAlertas(){
    $sel = sel("almox_estoque_configs","");
    $r = fetch($sel);
    if($r["alertaestoque"] == 1){
        e("<div style=\"border: solid 2px #ccc; background-color: #eee; padding: 5px; text-align: center; font-weight: bold; color: red; margin: 20px;\">");
        e("ATEN&Ccedil;&Atilde;O!! H&aacute; produtos com n&iacute;vel baixo de estoque! Clique <a href=\"?painel&m=almox&p=editarestoque&filtro=alerta\">aqui</a> para ver!");
        e("</div>");
    }
}

function criaAlertas(){
    $sel = sel("almox_estoque_produtos","qtde <= alerta and alerta <> '0'");
    if(total($sel) > 0){
        $upd2 = mysql_query("UPDATE almox_estoque_configs SET alertaestoque = '1'") or die(mysql_error()); //se tiver, atualiza tabela verificadora
    }else{
        $upd3 = mysql_query("UPDATE almox_estoque_configs SET alertaestoque = '0'") or die(mysql_error()); 
    }
}

//encontra o código dbseller do produto X
function codigoDBSeller($nomeProduto){
    $sel = mysql_query("SELECT nome,codigodbseller FROM almox_estoque_produtos WHERE nome = '$nomeProduto'") or die(mysql_error());
    $t = fetch($sel);
    return $t["codigodbseller"];
}
?> 