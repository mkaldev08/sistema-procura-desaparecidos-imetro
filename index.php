<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Filtros vindos da query string
$search = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$status = in_array($status, ['missing', 'found'], true) ? $status : '';

$perPage = 10;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

// Construir a cláusula WHERE dinamicamente
$condicoes       = [];
$tiposParametros = '';
$parametros      = [];

if ($search !== '') {
    $condicoes[]      = 'MATCH(full_name, last_seen_location, distinguishing_marks) AGAINST(? IN BOOLEAN MODE)';
    $tiposParametros .= 's';
    $parametros[]     = $search . '*';
}

if ($status !== '') {
    $condicoes[]      = 'status = ?';
    $tiposParametros .= 's';
    $parametros[]     = $status;
}

$whereSql = $condicoes ? 'WHERE ' . implode(' AND ', $condicoes) : '';

// Contar total de registos para a paginação
$sqlContagem = "SELECT COUNT(*) FROM missing_persons {$whereSql}";
$stmt = mysqli_prepare($conn, $sqlContagem);
if ($tiposParametros !== '') {
    mysqli_stmt_bind_param($stmt, $tiposParametros, ...$parametros);
}
mysqli_stmt_execute($stmt);
$resultadoContagem = mysqli_stmt_get_result($stmt);
$linhaContagem     = mysqli_fetch_row($resultadoContagem);
$totalRows         = (int)$linhaContagem[0];
$totalPages        = max(1, (int)ceil($totalRows / $perPage));
mysqli_stmt_close($stmt); // 

// Buscar os registos da página actual
$listSql = "SELECT id, full_name, age, gender, last_seen_location,
                   last_seen_date, photo_path, status
            FROM missing_persons
            {$whereSql}
            ORDER BY created_at DESC
            LIMIT {$perPage} OFFSET {$offset}";

$stmt = mysqli_prepare($conn, $listSql);
if ($tiposParametros !== '') {
    mysqli_stmt_bind_param($stmt, $tiposParametros, ...$parametros);
}
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$people    = mysqli_fetch_all($resultado, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Gerar URL de paginação preservando os filtros activos
function pageUrl(int $page, string $search, string $status): string
{
    $query = http_build_query(array_filter([
        'q'      => $search,
        'status' => $status,
        'page'   => $page,
    ], static fn ($v) => $v !== '' && $v !== null));

    return 'index.php?' . $query;
}

$pageTitle = 'Procurar desaparecidos';
require __DIR__ . '/includes/header.php';
?>

    <!-- Hero com barra de pesquisa integrada -->
    <header class="site-hero py-5">
        <div class="container px-4 px-lg-5 my-4">
            <div class="text-center text-white mb-4">
                <span class="hero-eyebrow">Sistema nacional de desaparecidos</span>
                <h1 class="display-5 fw-bolder">
                    <i class="bi-search-heart me-2"></i>Encontra-me
                </h1>
                <p class="lead fw-normal text-white-50 mb-0">
                    Ajude a procurar e reportar pessoas desaparecidas
                </p>
            </div>

            <form method="get" action="index.php" class="row g-2 justify-content-center">
                <div class="col-md-6">
                    <input type="text" name="q" class="form-control form-control-lg"
                           placeholder="Pesquisar por nome, local ou caracteristicas..."
                           value="<?= e($search) ?>">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-lg">
                        <option value="">Todos os estados</option>
                        <option value="missing" <?= $status === 'missing' ? 'selected' : '' ?>>Desaparecido</option>
                        <option value="found"   <?= $status === 'found'   ? 'selected' : '' ?>>Encontrado</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi-search me-1"></i>Procurar
                    </button>
                </div>
            </form>
        </div>
    </header>

    <!-- Lista de registos com paginação -->
    <section class="py-5">
        <div class="container px-4 px-lg-5 mt-2">

            <div class="mb-4">
                <div class="section-title-bar"></div>
                <h4 class="fw-bold mb-0">Registos</h4>
            </div>

            <p class="text-muted">
                <?= $totalRows ?> registo(s) encontrado(s)
                <?php if ($search !== ''): ?>para "<strong><?= e($search) ?></strong>"<?php endif; ?>
            </p>

            <?php if (empty($people)): ?>
                <div class="alert alert-info">
                    <i class="bi-info-circle me-1"></i> Nenhum registo encontrado.
                    <a href="register.php" class="alert-link">Registar um desaparecido</a>.
                </div>
            <?php else: ?>
                <div class="row gx-4 gx-lg-5 row-cols-1 row-cols-sm-2 row-cols-xl-3 justify-content-center">
                    <?php foreach ($people as $person): ?>
                        <div class="col mb-5">
                            <div class="card h-100 position-relative shadow-sm">
                                <?php if ($person['status'] === 'found'): ?>
                                    <span class="badge bg-success status-badge">Encontrado</span>
                                <?php else: ?>
                                    <span class="badge bg-danger status-badge">Desaparecido</span>
                                <?php endif; ?>

                                <?php if ($person['photo_path']): ?>
                                    <img class="card-img-top" src="<?= e(UPLOAD_URL . $person['photo_path']) ?>"
                                         alt="Foto de <?= e($person['full_name']) ?>">
                                <?php else: ?>
                                    <div class="photo-placeholder"><i class="bi-person"></i></div>
                                <?php endif; ?>

                                <div class="card-body p-4">
                                    <div class="text-center">
                                        <h5 class="fw-bolder mb-1"><?= e($person['full_name']) ?></h5>
                                        <div class="text-muted small mb-2">
                                            <?= $person['age'] !== null ? (int)$person['age'] . ' anos &middot; ' : '' ?>
                                            <?= e(genderLabel($person['gender'])) ?>
                                        </div>
                                        <div><i class="bi-geo-alt me-1"></i><?= e($person['last_seen_location']) ?></div>
                                        <div class="text-muted small">
                                            <i class="bi-calendar-event me-1"></i>
                                            Visto em <?= e(date('d/m/Y', strtotime($person['last_seen_date']))) ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                    <div class="text-center">
                                        <a class="btn btn-outline-dark mt-auto"
                                           href="person.php?id=<?= (int)$person['id'] ?>">
                                            Ver detalhes <i class="bi-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Paginação -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Paginacao">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= e(pageUrl($page - 1, $search, $status)) ?>">Anterior</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= e(pageUrl($i, $search, $status)) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= e(pageUrl($page + 1, $search, $status)) ?>">Seguinte</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

<?php require __DIR__ . '/includes/footer.php'; ?>
