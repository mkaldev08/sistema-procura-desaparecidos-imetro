# Encontra-me — Sistema de cadastramento e procura de desaparecidos

Aplicação em **PHP + MySQL** cuja UI foi **customizada a partir do template
"Shop Homepage" da Start Bootstrap** (Bootstrap 5, licença MIT).

> Template original: https://startbootstrap.com/template/shop-homepage
> A licença MIT permite uso e modificação livres, mantendo o aviso de crédito
> (incluído em `css/styles.css` e no rodapé). A grelha de cards de "produtos"
> foi reaproveitada para apresentar pessoas; a navbar, o header hero e o footer
> escuros são os do template.

## Estrutura

```
desaparecidos/
├── config/
│   └── database.php        # Ligação PDO (editar credenciais aqui)
├── css/
│   └── styles.css          # Customizações sobre o template (crédito MIT)
├── includes/
│   ├── functions.php       # Helpers: escape, upload, validação
│   ├── header.php          # <head> + navbar do template
│   └── footer.php          # Footer do template + JS Bootstrap
├── uploads/                # Fotos enviadas (precisa de permissão de escrita)
├── index.php               # Hero + pesquisa + grelha de cards + paginação
├── register.php            # Formulário de registo + processamento
├── person.php              # Detalhe + marcar como encontrado
└── schema.sql              # Estrutura da base de dados
```

## Como executar

1. **Criar a base de dados**
   ```bash
   mysql -u root -p < schema.sql
   ```

2. **Configurar as credenciais** em `config/database.php`
   (host, utilizador, password).

3. **Permissões da pasta de uploads**
   ```bash
   chmod -R 775 uploads
   ```

4. **Arrancar o servidor embutido do PHP** (a partir da raiz do projeto)
   ```bash
   php -S localhost:8000
   ```
   Ou colocar a pasta dentro de `htdocs/` (XAMPP) / `www/` (WAMP).

5. Abrir `http://localhost:8000`

## Decisões técnicas

- **PDO + prepared statements** em todas as queries → imune a SQL injection.
- **Índice FULLTEXT** (`MATCH ... AGAINST` em boolean mode) para pesquisa por
  relevância, com `*` para correspondência parcial. Escala muito melhor do que
  `LIKE '%termo%'` à medida que a tabela cresce.
- **Validação do upload pelo MIME real** (`finfo`), não pela extensão do cliente,
  com nome de ficheiro aleatório (`random_bytes`) para evitar colisões e path traversal.
- **Padrão PRG** (Post/Redirect/Get) no registo, evitando submissões duplicadas.
- **Escape no output** (`htmlspecialchars`) em todos os dados dinâmicos → sem XSS.
- **Paginação** por `LIMIT/OFFSET` mantendo os filtros no URL.

## Como foi feita a customização do template

Partindo do `dist/index.html` do Shop Homepage, os elementos foram mapeados
para o domínio de desaparecidos:

| Elemento do template      | Adaptação                                            |
|---------------------------|------------------------------------------------------|
| Navbar + botão "Cart"     | Marca "Encontra-me" + botão "Registar desaparecido"  |
| Header hero "Shop in style" | Hero "Encontra-me" com barra de pesquisa integrada |
| Grelha de product cards   | Cards de pessoas (foto, idade/sexo, local, data)     |
| "Sale" badge do card      | Badge de estado (Desaparecido / Encontrado)          |
| Botão "View options"      | Botão "Ver detalhes" → `person.php`                  |
| Footer escuro             | Footer com aviso e crédito MIT                        |

As classes do template (`bg-dark`, `display-4 fw-bolder`, `card h-100`,
`row-cols-*`, `card-footer ... bg-transparent`) foram mantidas; só foi
acrescentado o mínimo de CSS próprio em `css/styles.css` (altura das fotos,
placeholder e posicionamento do badge).

Para mudar cores/tipografia, edita `css/styles.css` ou substitui o CSS do
Bootstrap por um tema (ex.: Bootswatch) no `<link>` do `includes/header.php`.

## Próximos passos sugeridos

- **Autenticação** para ações administrativas (marcar como encontrado, editar,
  apagar). Atualmente o `toggle_status` é público — é o primeiro ponto a fechar
  antes de qualquer uso real.
- **Token CSRF** nos formulários POST.
- **Redimensionar imagens** no upload (ex.: GD/Imagick) para poupar espaço.
- **Soft delete** (`deleted_at`) em vez de apagar registos.
- **Rate limiting** no registo para evitar spam.
```
