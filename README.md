
# LogiGest - Gerenciador Logístico

Sistema web desenvolvido para gerenciamento de produtos,
endereços, transportadores e fretes.

Projeto acadêmico da disciplina de Fundamentos de Programação
para Internet, da UNIPAR.

## Objetivo

Desenvolver um sistema que permita cadastrar e gerenciar
informações logísticas, relacionando produtos, transportadores
e endereços na geração de fretes.

## Funcionalidades

- Dashboard com indicadores de produtos, transportadores e fretes
- Cadastro, listagem e edição de produtos
- Cadastro, listagem e edição de endereços
- Cadastro, listagem e edição de transportadores
- Geração de fretes com origem e destino
- Associação de vários produtos ao mesmo frete
- Adição e remoção dinâmica de produtos no formulário
- Listagem dos fretes cadastrados
- Relatórios de fretes filtrados por data
- Exportação dos relatórios em formato CSV

## Tecnologias utilizadas

- PHP 8
- HTML5
- CSS3
- JavaScript
- Bootstrap 5
- MySQL/MariaDB
- PDO
- XAMPP
- Git e GitHub

## Estrutura do projeto

- `index.php` - Dashboard principal
- `produtos.php` - Gerenciamento de produtos
- `enderecos.php` - Gerenciamento de endereços
- `transportadores.php` - Gerenciamento de transportadores
- `fretes.php` - Geração e listagem de fretes
- `relatorios.php` - Consulta e exportação de relatórios
- `includes/menu.php` - Menu lateral reutilizável
- `config/` - Configuração da conexão com MySQL
- `database/estrutura.sql` - Estrutura do banco de dados
- `assets/css/style.css` - Estilos da interface
- `assets/js/script.js` - Funcionalidades em JavaScript

## Como executar o projeto

### 1. Requisitos

- XAMPP com Apache, PHP e MySQL/MariaDB
- Navegador web
- Conexão com a internet para carregar o Bootstrap via CDN

### 2. Instalar o projeto

Clone o repositório ou baixe seus arquivos.

Coloque a pasta `logigest` dentro de:

`C:\xampp\htdocs\`

### 3. Configurar o banco de dados

1. Inicie Apache e MySQL no XAMPP.
2. Acesse `http://localhost/phpmyadmin/`.
3. Crie um banco de dados chamado `logigest`.
4. Selecione o banco recém-criado.
5. Clique em Importar.
6. Importe o arquivo `database/estrutura.sql`.

A importação deve ser realizada em um banco vazio.

### 4. Configurar a conexão

Dentro da pasta `config`, faça uma cópia do arquivo
`conexao.exemplo.php` e renomeie a cópia para `conexao.php`.

Confira os dados de acesso ao MySQL:

- Servidor: 127.0.0.1
- Banco: logigest
- Usuário: root
- Senha: conforme configuração local do MySQL

O arquivo `conexao.php` é ignorado pelo Git para não publicar
credenciais locais.

### 5. Acessar o sistema

Abra o navegador e acesse:

`http://localhost/logigest/`

## Como utilizar

1. Cadastre os produtos.
2. Cadastre os endereços que serão usados nas rotas.
3. Cadastre os transportadores.
4. Acesse a página Fretes.
5. Selecione o transportador, a origem e o destino.
6. Adicione um ou mais produtos e informe as quantidades.
7. Clique em Gerar frete.
8. Consulte os registros na página de Relatórios.

## Banco de dados

O sistema utiliza cinco tabelas:

- `produtos`
- `enderecos`
- `transportadores`
- `fretes`
- `frete_produtos`

As tabelas são relacionadas por chaves estrangeiras,
permitindo associar vários produtos a um mesmo frete.

## Observações

- Os registros de teste não estão incluídos no arquivo SQL.
- O projeto foi desenvolvido para execução em ambiente local.
- O cadastro de fretes não realiza baixa automática no estoque.

## Autor

Projeto desenvolvido por Jackson Monteiro Genesio.

Curso: Engenharia de Software - UNIPAR.
