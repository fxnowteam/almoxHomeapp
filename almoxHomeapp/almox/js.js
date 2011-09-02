//insere produto no db
function insProd(){
    ajax('insProd', 'Inserindo...', 'mods/almox/insProd.php?a=i&produto='+getId('produto').value+'&qtdeitens='+getId('qtdeitens').value+'&dataini='+getId('dataini').value+'&codigobarras='+getId('codigobarras').value);
    getId('codigobarras').value = "";
    getId('qtdeitens').value = "";
    getId('codigobarras').focus();
}

//cadastra produto no db
function cadProd(){
    ajax('cadProd', 'Inserindo...', 'mods/almox/cadProd.php?a=i&produto='+getId('produto').value+'&dbseller='+getId('dbseller').value+'&alerta='+getId('alerta').value);
    ajax('listaProd', 'Carregando lista de produtos...', 'mods/almox/listaProds.php');
    getId('produto').value = "";
    getId('dbseller').value = "";
    getId('alerta').value = "";
    getId('produto').focus();
}

//cadastra destinos
function cadDestino(){
    ajax('listaDest', 'Cadastrando...', 'mods/almox/listaDest.php?a=i&setor='+getId('setor').value);
    getId('setor').value = "";
    getId('setor').focus();
}

//insere produto na lista de saída
function saidaEstoque(){
    ajax('listaSaida', 'Inserindo...', 'mods/almox/listaSaida.php?a=i&idpedido='+getId('idpedido').value+'&destino='+getId('destino').value+'&qtde='+getId('qtde').value+'&codigobarras='+getId('codigobarras').value);
    getId('codigobarras').value = "";
    getId('qtde').value = "";
    getId('qtde').focus();
	getId('destino').disabled = "disabled";
}

//remove item da lista de saída de estoque
function removerItem(chave,idItem){
    ajax('listaDeSaida', 'Removendo...', 'mods/almox/listaSaida.php?a=ri&idItem='+idItem+'&idpedido='+chave);
}

//salva pedido e exibe botão para impressão
function salvarPedido(chave){
    ajax('salvarPedido', 'Salvando... aguarde...', 'mods/almox/salvarPedido.php?idpedido='+chave);
}

//envia dados para o formulário de edição de produtos
function edProd(produto,dbseller,codigobarras,alerta,idreg){
    getId('produto').value = produto;
    getId('produto').disabled = true;
    getId('dbseller').value = dbseller;
    //if(codigobarras == 1){
    //    getId('codigobarras').checked = true;
    //    getId('codigobarras').value = 1;
    //}else{
    //    getId('codigobarras').checked = false;
    //    getId('codigobarras').value = 0;
    //}
    getId('alerta').value = alerta;
    getId('idreg').value = idreg;
    //document.getElementById('botaoSubmit').onclick = altProd
    getId('botaoSubmit').setAttribute('onclick','altProd()');
    getId('botaoSubmit').value = 'Alterar';
}

//envia dados do formulário para serem alterados
function altProd(){
    ajax('cadProd', 'Atualizando...', 'mods/almox/cadProd.php?a=up&dbseller='+getId('dbseller').value+'&alerta='+getId('alerta').value+'&idreg='+getId('idreg').value);
    getId('produto').value = '';
    getId('produto').disabled = false;
    getId('dbseller').value = '';
    getId('alerta').value = '';
    getId('idreg').value = '';
    getId('botaoSubmit').setAttribute('onclick','cadProd()');
    document.getElementById('botaoSubmit').value = 'Cadastrar';
    ajax('listaProd', 'Carregando lista de produtos...', 'mods/almox/listaProds.php');
}

//seta se o valor do checkbox em listaProds.php é false ou true
function setValCheck(){
    if(getId('codigobarras').value == 0){
        getId('codigobarras').value = 1;
        getId('codigobarras').checked = true;
    }else{
        getId('codigobarras').value = 0;
        getId('codigobarras').checked = false;
    }
}