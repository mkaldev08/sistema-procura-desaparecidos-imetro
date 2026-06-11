<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('index.php');
}

// Processar a alteração de estado: desaparecido ↔ encontrado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_status') {
    $stmt = mysqli_prepare($conn,
        "UPDATE missing_persons
         SET status = IF(status = 'missing', 'found', 'missing')
         WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    redirect('person.php?id=' . $id);
}

// Buscar o registo pelo ID
$stmt = mysqli_prepare($conn, 'SELECT * FROM missing_persons WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$person    = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

if (!$person) {
    http_response_code(404);
    $pageTitle = 'Não encontrado';
    require __DIR__ . '/includes/header.php';
    echo '<div class="container"><div class="alert alert-warning">Registo não encontrado.</div></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$justCreated = isset($_GET['created']);
$pageTitle   = $person['full_name'];
require __DIR__ . '/includes/header.php';
?>

<div class="container px-4 px-lg-5 py-5">
    <a href="index.php" class="btn btn-link mb-3 px-0">
        <i class="bi bi-arrow-left"></i> Voltar à lista
    </a>

    <?php if ($justCreated): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle"></i> Registo criado com sucesso.
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="row g-0">
            <div class="col-md-4">
                <?php if ($person['photo_path']): ?>
                    <img src="<?= e(UPLOAD_URL . $person['photo_path']) ?>"
                         class="img-fluid rounded-start detail-photo w-100"
                         alt="Foto de <?= e($person['full_name']) ?>">
                <?php else: ?>
                    <div class="photo-placeholder h-100"><i class="bi bi-person"></i></div>
                <?php endif; ?>
            </div>

            <div class="col-md-8">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h3 class="card-title"><?= e($person['full_name']) ?></h3>
                        <?php if ($person['status'] === 'found'): ?>
                            <span class="badge bg-success fs-6">Encontrado</span>
                        <?php else: ?>
                            <span class="badge bg-danger fs-6">Desaparecido</span>
                        <?php endif; ?>
                    </div>

                    <dl class="row mt-3 mb-0">
                        <dt class="col-sm-4">Idade</dt>
                        <dd class="col-sm-8"><?= $person['age'] !== null ? (int)$person['age'] . ' anos' : '—' ?></dd>

                        <dt class="col-sm-4">Sexo</dt>
                        <dd class="col-sm-8"><?= e(genderLabel($person['gender'])) ?></dd>

                        <dt class="col-sm-4">Altura</dt>
                        <dd class="col-sm-8"><?= $person['height_cm'] !== null ? (int)$person['height_cm'] . ' cm' : '—' ?></dd>

                        <dt class="col-sm-4">Olhos / Cabelo</dt>
                        <dd class="col-sm-8">
                            <?= e($person['eye_color'] ?: '—') ?> / <?= e($person['hair_color'] ?: '—') ?>
                        </dd>

                        <dt class="col-sm-4">Sinais particulares</dt>
                        <dd class="col-sm-8"><?= nl2br(e($person['distinguishing_marks'] ?: '—')) ?></dd>

                        <dt class="col-sm-4">Último local</dt>
                        <dd class="col-sm-8"><i class="bi bi-geo-alt"></i> <?= e($person['last_seen_location']) ?></dd>

                        <dt class="col-sm-4">Data</dt>
                        <dd class="col-sm-8"><?= e(date('d/m/Y', strtotime($person['last_seen_date']))) ?></dd>

                        <dt class="col-sm-4">Circunstâncias</dt>
                        <dd class="col-sm-8"><?= nl2br(e($person['circumstances'] ?: '—')) ?></dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="card-footer bg-light">
            <h6 class="mb-2"><i class="bi bi-telephone"></i> Contacto</h6>
            <p class="mb-1"><strong><?= e($person['reporter_name']) ?></strong></p>
            <p class="mb-1"><?= e($person['reporter_phone']) ?></p>
            <?php if ($person['reporter_email']): ?>
                <p class="mb-0">
                    <a href="mailto:<?= e($person['reporter_email']) ?>"><?= e($person['reporter_email']) ?></a>
                </p>
            <?php endif; ?>

            <form method="post" action="person.php?id=<?= (int)$person['id'] ?>" class="mt-3">
                <input type="hidden" name="action" value="toggle_status">
                <?php if ($person['status'] === 'missing'): ?>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-check2-all"></i> Marcar como encontrado
                    </button>
                <?php else: ?>
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-arrow-counterclockwise"></i> Reabrir (marcar como desaparecido)
                    </button>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
