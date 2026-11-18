
<?php

require __DIR__ . '/config/conexao.php';

// Buscar os dados cadastrados
$transportadores = $pdo->query(
    "SELECT * FROM transportadores ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

$enderecos = $pdo->query(
    "SELECT * FROM enderecos ORDER BY cidade"
)->fetchAll(PDO::FETCH_ASSOC);

$produtos = $pdo->query(
    "SELECT * FROM produtos ORDER BY nome"
)->fetchAll(PDO::FETCH_ASSOC);

$erroFrete = '';

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $transportadorId = filter_var(
        $_POST['transportador_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $origemId = filter_var(
        $_POST['origem_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $destinoId = filter_var(
        $_POST['destino_id'] ?? null,
        FILTER_VALIDATE_INT
    );

    $dataFrete = $_POST['data_frete'] ?? '';
    $itens = $_POST['produtos'] ?? [];

    // Conferir a data
    $dataValida = false;

    if (is_string($dataFrete)) {
        $data = DateTimeImmutable::createFromFormat(
            '!Y-m-d',
            $dataFrete
        );

        $dataValida = $data !== false &&
            $data->format('Y-m-d') === $dataFrete;
    }

    // Conferir os identificadores cadastrados
    $transportadoresIds = array_map(
        'intval',
        array_column($transportadores, 'id')
    );

    $enderecosIds = array_map(
        'intval',
        array_column($enderecos, 'id')
    );

    $produtosIds = array_map(
        'intval',
        array_column($produtos, 'id')
    );

    if (
        !in_array($transportadorId, $transportadoresIds, true) ||
        !in_array($origemId, $enderecosIds, true) ||
        !in_array($destinoId, $enderecosIds, true) ||
        $origemId === $destinoId ||
        !$dataValida ||
        !is_array($itens) ||
        count($itens) === 0
    ) {
        $erroFrete = 'Confira os dados do frete.';
    }

    // Validar os produtos escolhidos
    $produtosValidados = [];
    $idsUtilizados = [];

    if ($erroFrete === '') {

        foreach ($itens as $item) {

            if (!is_array($item)) {
                $erroFrete = 'Produto inválido.';
                break;
            }

            $produtoId = filter_var(
                $item['produto_id'] ?? null,
                FILTER_VALIDATE_INT
            );

            $quantidade = filter_var(
                $item['quantidade'] ?? null,
                FILTER_VALIDATE_INT
            );

            if (
                !$produtoId ||
                !$quantidade ||
                $quantidade < 1 ||
                !in_array($produtoId, $produtosIds, true) ||
                isset($idsUtilizados[$produtoId])
            ) {
                $erroFrete = 'Verifique os produtos e quantidades. Não repita o mesmo produto.';
                break;
            }

            $idsUtilizados[$produtoId] = true;

            $produtosValidados[] = [
                'produto_id' => $produtoId,
                'quantidade' => $quantidade
            ];
        }
    }

    // Salvar o frete e seus produtos
    if ($erroFrete === '') {

        try {

            $pdo->beginTransaction();

            // Cadastrar o frete
            $sql = "INSERT INTO fretes
                    (transportador_id, origem_id,
                     destino_id, data_frete)
                    VALUES
                    (:transportador, :origem,
                     :destino, :data)";

            $consulta = $pdo->prepare($sql);

            $consulta->execute([
                ':transportador' => $transportadorId,
                ':origem' => $origemId,
                ':destino' => $destinoId,
                ':data' => $dataFrete
            ]);

            // Recuperar o ID do frete criado
            $freteId = (int) $pdo->lastInsertId();

            // Preparar o cadastro dos produtos
            $sqlProdutos = "INSERT INTO frete_produtos
                            (frete_id, produto_id, quantidade)
                            VALUES
                            (:frete, :produto, :quantidade)";

            $consultaProdutos = $pdo->prepare($sqlProdutos);

            // Salvar cada produto relacionado ao frete
            foreach ($produtosValidados as $produto) {

                $consultaProdutos->execute([
                    ':frete' => $freteId,
                    ':produto' => $produto['produto_id'],
                    ':quantidade' => $produto['quantidade']
                ]);
            }

            // Confirmar todas as operações
            $pdo->commit();

            header(
                'Location: fretes.php?salvo=1&frete=' . $freteId
            );
            exit;

        } catch (PDOException $erro) {

            // Desfazer operações se houver erro
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log($erro->getMessage());

            $erroFrete = 'Não foi possível gerar o frete.';
        }
    }
}

/* Buscar os fretes cadastrados */

$sql = "SELECT
            f.id,
            f.data_frete,
            f.status,
            t.nome AS transportador,
            origem.descricao AS origem,
            origem.cidade AS cidade_origem,
            destino.descricao AS destino,
            destino.cidade AS cidade_destino,

            (
                SELECT GROUP_CONCAT(
                    CONCAT(p.nome, ' (', fp.quantidade, ')')
                    ORDER BY p.nome
                    SEPARATOR ', '
                )
                FROM frete_produtos fp
                INNER JOIN produtos p
                    ON p.id = fp.produto_id
                WHERE fp.frete_id = f.id
            ) AS produtos

        FROM fretes f

        INNER JOIN transportadores t
            ON t.id = f.transportador_id

        INNER JOIN enderecos origem
            ON origem.id = f.origem_id

        INNER JOIN enderecos destino
            ON destino.id = f.destino_id

        ORDER BY f.id DESC
        LIMIT 20";

$consulta = $pdo->query($sql);

$fretes = $consulta->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Fretes | LogiGest</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="sistema">

    <?php require __DIR__ . '/includes/menu.php'; ?>

    <main class="conteudo">

        <header class="cabecalho">
            <div>
                <h1>Novo frete</h1>
                <p>
                    Vincule um transportador e vários
                    produtos à entrega.
                </p>
            </div>
        </header>

        <?php if (isset($_GET['salvo'])): ?>
            <div class="alert alert-success">
                Frete gerado com sucesso!
                Número do frete:
                #<?= (int)($_GET['frete'] ?? 0) ?>
            </div>
        <?php endif; ?>

        <?php if ($erroFrete !== ''): ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($erroFrete, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <section class="card painel mb-4">
            <div class="card-body p-4">

                <h5>Informações do frete</h5>
                <p>
                    Preencha os dados para gerar
                    uma nova entrega.
                </p>

                <form method="POST" id="form-frete">

                    <div class="row g-3">

                        <!-- Transportador -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Transportador
                            </label>

                            <select
                                name="transportador_id"
                                class="form-select"
                                required
                            >
                                <option value="">
                                    Selecione o transportador
                                </option>

                                <?php foreach ($transportadores as $t): ?>
                                    <option value="<?= (int)$t['id'] ?>">
                                        <?= htmlspecialchars($t['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Data -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Data do frete
                            </label>

                            <input
                                type="date"
                                name="data_frete"
                                class="form-control"
                                required
                            >
                        </div>

                        <!-- Origem -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Endereço de origem
                            </label>

                            <select
                                name="origem_id"
                                class="form-select"
                                required
                            >
                                <option value="">
                                    Selecione a origem
                                </option>

                                <?php foreach ($enderecos as $e): ?>
                                    <option value="<?= (int)$e['id'] ?>">
                                        <?= htmlspecialchars($e['descricao']) ?>
                                        -
                                        <?= htmlspecialchars($e['cidade']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Destino -->
                        <div class="col-md-6">
                            <label class="form-label">
                                Endereço de destino
                            </label>

                            <select
                                name="destino_id"
                                class="form-select"
                                required
                            >
                                <option value="">
                                    Selecione o destino
                                </option>

                                <?php foreach ($enderecos as $e): ?>
                                    <option value="<?= (int)$e['id'] ?>">
                                        <?= htmlspecialchars($e['descricao']) ?>
                                        -
                                        <?= htmlspecialchars($e['cidade']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>

                    <hr class="my-4">

                    <h5>Produtos do frete</h5>
                    <p>
                        Adicione os produtos que serão
                        transportados nesta entrega.
                    </p>

                    <!-- Linhas de produtos -->
                    <div id="lista-produtos"></div>

                    <button
                        type="button"
                        id="adicionar-produto"
                        class="btn btn-outline-primary mt-2"
                    >
                        + Adicionar produto
                    </button>

                    <div class="mt-4">
                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Gerar frete
                        </button>
                    </div>

                </form>

            </div>
        </section>


<!-- Lista de fretes cadastrados -->
<section class="card painel mb-4">
    <div class="card-body p-4">

        <h5>Fretes cadastrados</h5>
        <p>Consulte os fretes gerados no sistema.</p>

        <div class="table-responsive">

            <table class="table table-hover align-middle">

                <thead>
                    <tr>
                        <th>Frete</th>
                        <th>Data</th>
                        <th>Origem</th>
                        <th>Destino</th>
                        <th>Transportador</th>
                        <th>Produtos</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if (count($fretes) === 0): ?>

                        <tr>
                            <td colspan="7"
                                class="text-center py-4">
                                Nenhum frete cadastrado.
                            </td>
                        </tr>

                    <?php else: ?>

                        <?php foreach ($fretes as $frete): ?>

                            <tr>
                                <td>
                                    #<?= (int)$frete['id'] ?>
                                </td>

                                <td>
                                    <?= date(
                                        'd/m/Y',
                                        strtotime($frete['data_frete'])
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $frete['origem'] . ' - ' .
                                        $frete['cidade_origem'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $frete['destino'] . ' - ' .
                                        $frete['cidade_destino'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $frete['transportador'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $frete['produtos'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <span class="badge bg-success">
                                        <?= htmlspecialchars(
                                            $frete['status'],
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>
</section>

        <!-- Modelo para criar linhas dinamicas -->
        <template id="modelo-produto">

            <div class="row g-3 align-items-end mb-3 linha-produto">

                <div class="col-md-7">
                    <label class="form-label">
                        Produto
                    </label>

                    <select class="form-select campo-produto" required>
                        <option value="">
                            Selecione um produto
                        </option>

                        <?php foreach ($produtos as $p): ?>
                            <option value="<?= (int)$p['id'] ?>">
                                <?= htmlspecialchars($p['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">
                        Quantidade
                    </label>

                    <input
                        type="number"
                        class="form-control campo-quantidade"
                        min="1"
                        step="1"
                        value="1"
                        required
                    >
                </div>

                <div class="col-md-2">
                    <button
                        type="button"
                        class="btn btn-outline-danger w-100 remover-produto"
                    >
                        Remover
                    </button>
                </div>

            </div>

        </template>

    </main>

</div>

<script src="assets/js/script.js"></script>

</body>
</html>
