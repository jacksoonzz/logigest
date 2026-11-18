
<?php
$paginaAtual = basename($_SERVER['SCRIPT_NAME']);
?>

<aside class="menu-lateral">
    <div class="logo">
        <h2>LogiGest</h2>
        <p>Gerenciador Logístico</p>
    </div>

    <nav class="navegacao">
        <a href="index.php"
           class="<?= $paginaAtual === 'index.php' ? 'ativo' : '' ?>">
            Dashboard
        </a>

        <a href="produtos.php"
           class="<?= $paginaAtual === 'produtos.php' ? 'ativo' : '' ?>">
            Produtos
        </a>

        
        <a href="enderecos.php"
        class="<?= $paginaAtual === 'enderecos.php' ? 'ativo' : '' ?>">
            Endereços
        </a>

        
        <a href="transportadores.php"
        class="<?= $paginaAtual === 'transportadores.php' ? 'ativo' : '' ?>">
            Transportadores
        </a>
        <span>Fretes</span>
        <span>Relatórios</span>
    </nav>
</aside>
