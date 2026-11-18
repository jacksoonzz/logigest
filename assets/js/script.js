
document.addEventListener('DOMContentLoaded', function () {

    const lista = document.getElementById('lista-produtos');
    const modelo = document.getElementById('modelo-produto');
    const botaoAdicionar = document.getElementById('adicionar-produto');

    // Executar somente na pagina de fretes
    if (!lista || !modelo || !botaoAdicionar) {
        return;
    }

    let contador = 0;

    function adicionarProduto() {

        // Copiar a estrutura do modelo HTML
        const copia = modelo.content.cloneNode(true);

        const linha = copia.querySelector('.linha-produto');

        const produto = copia.querySelector('.campo-produto');
        const quantidade = copia.querySelector('.campo-quantidade');
        const remover = copia.querySelector('.remover-produto');

        // Criar nomes diferentes para cada produto
        produto.name = `produtos[${contador}][produto_id]`;
        quantidade.name = `produtos[${contador}][quantidade]`;

        contador++;

        // Programar o botao remover
        remover.addEventListener('click', function () {

            if (lista.children.length > 1) {
                linha.remove();
            } else {
                alert('O frete precisa ter pelo menos um produto.');
            }

        });

        // Adicionar a nova linha ao formulario
        lista.appendChild(copia);
    }

    botaoAdicionar.addEventListener('click', adicionarProduto);

    // Criar a primeira linha automaticamente
    adicionarProduto();

});
