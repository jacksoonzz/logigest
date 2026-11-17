


<?php

require __DIR__ . '/config/conexao.php';

$erro = '';

$produtoFormulario = [
    'id' => '',
    'nome' => '',
    'descricao' => '',
    'peso' => '',
    'quantidade' => ''
];

// Salvar os dados enviados pelo formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $produtoFormulario = [
        'id' => trim($_POST['id'] ?? ''),
        'nome' => trim($_POST['nome'] ?? ''),
        'descricao' => trim($_POST['descricao'] ?? ''),
        'peso' => trim($_POST['peso'] ?? ''),
        'quantidade' => trim($_POST['quantidade'] ?? '')
    ];

    $id = $produtoFormulario['id'];
    $nome = $produtoFormulario['nome'];
    $descricao = $produtoFormulario['descricao'];
    $peso = $produtoFormulario['peso'];

    $quantidade = filter_var(
        $produtoFormulario['quantidade'],
        FILTER_VALIDATE_INT
    );

    if (
        $nome === '' ||
        !is_numeric($peso) ||
        (float) $peso <= 0 ||
        $quantidade === false ||
        $quantidade < 0
    ) {
        $erro = 'Preencha os campos corretamente.';

    } elseif ($id !== '' && (!ctype_digit($id) || (int)$id < 1)) {

        $erro = 'Identificador do produto inválido.';

    } else {

        if ($id === '') {

            // Cadastrar um novo produto
            $sql = "INSERT INTO produtos
                    (nome, descricao, peso, quantidade)
                    VALUES
                    (:nome, :descricao, :peso, :quantidade)";

        } else {

            // Atualizar um produto existente
            $sql = "UPDATE produtos
                    SET nome = :nome,
                        descricao = :descricao,
                        peso = :peso,
                        quantidade = :quantidade
                    WHERE id = :id";
        }

        $consulta = $pdo->prepare($sql);

        $dados = [
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':peso' => $peso,
            ':quantidade' => $quantidade
        ];

        if ($id !== '') {
            $dados[':id'] = (int) $id;
        }

        $consulta->execute($dados);

        if ($id === '') {
            header('Location: produtos.php?salvo=1');
        } else {
            header('Location: produtos.php?editado=1');
        }

        exit;
    }

// Carregar um produto para edição
} elseif (isset($_GET['editar'])) {

    $id = filter_var(
        $_GET['editar'],
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    if ($id === false) {
        $erro = 'Produto inválido.';
    } else {
        $sql = "SELECT * FROM produtos WHERE id = :id";

        $consulta = $pdo->prepare($sql);
        $consulta->execute([':id' => $id]);

        $produto = $consulta->fetch(PDO::FETCH_ASSOC);

        if ($produto) {
            $produtoFormulario = $produto;
        } else {
            $erro = 'Produto não encontrado.';
        }
    }
}

// Listar todos os produtos
$sql = "SELECT * FROM produtos ORDER BY id DESC";

$consulta = $pdo->query($sql);

$produtos = $consulta->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Produtos | LogiGest</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.9/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="sistema">

    <?php require __DIR__ . '/includes/menu.php'; ?>

    <main class="conteudo">

        <header class="cabecalho">
            <div>
                <h1>Produtos</h1>
                <p>Cadastre e organize os produtos transportados.</p>
            </div>
        </header>

<?php if (isset($_GET['salvo'])): ?>
    <div class="alert alert-success">
        Produto cadastrado com sucesso!
    </div>
<?php endif; ?>

<?php if (isset($_GET['editado'])): ?>
    <div class="alert alert-success">
        Produto atualizado com sucesso!
    </div>
<?php endif; ?>

<?php if ($erro !== ''): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

        <!-- Formulário de produtos -->
        <section class="card painel mb-4">
            <div class="card-body p-4">

                <h5>Cadastro de produto</h5>
                <p>Preencha os dados do produto.</p>

                <form method="POST">


<input
    type="hidden"
    name="id"
    value="<?= htmlspecialchars((string)$produtoFormulario['id'], ENT_QUOTES, 'UTF-8') ?>"
>

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label">Nome do produto</label>
                            <input type="text" name="nome"
                            value="<?= htmlspecialchars((string)$produtoFormulario['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                   class="form-control" 
                                   value="<?= htmlspecialchars((string)$produtoFormulario['nome'], ENT_QUOTES, 'UTF-8') ?>"
                                   required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Descrição</label>
                            <input type="text" name="descricao"
                                   class="form-control"
                                   value="<?= htmlspecialchars((string)$produtoFormulario['descricao'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Peso (kg)</label>
                            <input type="number" name="peso"
                                   class="form-control"
                                   value="<?= htmlspecialchars((string)$produtoFormulario['peso'], ENT_QUOTES, 'UTF-8') ?>"
                                   min="0.001" step="0.001" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Quantidade</label>
                            <input type="number" name="quantidade"
                                   class="form-control"
                                   value="<?= htmlspecialchars((string)$produtoFormulario['quantidade'], ENT_QUOTES, 'UTF-8') ?>"
                                   min="0" step="1" required>
                        </div>

                    </div>

                    <div class="mt-4">
                        
<button type="submit" class="btn btn-primary">
    <?= $produtoFormulario['id'] !== ''
        ? 'Salvar alterações'
        : 'Salvar produto' ?>
</button>
                    </div>

                </form>

            </div>
        </section>

        <!-- Lista de produtos -->
        <section class="card painel">
            <div class="card-body p-4">

                <h5>Lista de produtos</h5>
                <p>Produtos cadastrados no sistema.</p>

                <div class="table-responsive">
                    <table class="table table-hover">

                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Produto</th>
                                <th>Peso</th>
                                <th>Quantidade</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php if (count($produtos) === 0): ?>

                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    Nenhum produto cadastrado.
                                </td>
                            </tr>

                        <?php else: ?>

                            <?php foreach ($produtos as $produto): ?>
                                <tr>
                                    <td><?= $produto['id'] ?></td>

                                    <td><?= htmlspecialchars($produto['nome'], ENT_QUOTES, 'UTF-8') ?></td>

                                    <td><?= number_format($produto['peso'], 3, ',', '.') ?> kg</td>

                                    <td><?= $produto['quantidade'] ?></td>

                                    <td>
                                        
<a
    href="produtos.php?editar=<?= (int)$produto['id'] ?>"
    class="btn btn-sm btn-outline-primary"
>
    Editar
</a>

                                    </td>
                                </tr>
                            <?php endforeach; ?>

                        <?php endif; ?>

                        </tbody>

                    </table>
                </div>

            </div>
        </section>

    </main>

</div>

</body>
</html>
