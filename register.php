<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

exigirAutenticacao();

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    // Recolher e normalizar os dados do formulário

    $fullName = trim($_POST['full_name'] ?? '');
    $lastSeenLocation = trim($_POST['last_seen_location'] ?? '');
    $lastSeenDate = trim($_POST['last_seen_date'] ?? '');
    $reporterName = trim($_POST['reporter_name'] ?? '');
    $reporterPhone = trim($_POST['reporter_phone'] ?? '');
    $reporterEmail = trim($_POST['reporter_email'] ?? '');
    $gender = $_POST['gender'];
    $eyeColor = trim($_POST['eye_color'] ?? '');
    $hairColor = trim($_POST['hair_color'] ?? '');
    $marks = trim($_POST['distinguishing_marks'] ?? '');
    $circumstances = trim($_POST['circumstances'] ?? '');

    $age      = ($_POST['age']       ?? '') !== '' ? (int) $_POST['age']       : null;
    $heightCm = ($_POST['height_cm'] ?? '') !== '' ? (int) $_POST['height_cm'] : null;
    $userId   = (int) $_SESSION['user_id'];


    // Validação dos campos obrigatórios
    if ($fullName === '') {
        $errors['full_name'] = 'O nome completo é obrigatório.';
    } elseif (mb_strlen($fullName) > 150) {
        $errors['full_name'] = 'O nome é demasiado longo.';
    }

    if ($lastSeenLocation === '') {
        $errors['last_seen_location'] = 'Indique o último local onde foi visto.';
    }

    if ($lastSeenDate === '') {
        $errors['last_seen_date'] = 'Indique a data do desaparecimento.';
    } elseif (!isValidDate($lastSeenDate)) {
        $errors['last_seen_date'] = 'Data inválida.';
    } elseif ($lastSeenDate > date('Y-m-d')) {
        $errors['last_seen_date'] = 'A data não pode ser no futuro.';
    }

    if ($reporterName === '') {
        $errors['reporter_name'] = 'O nome de quem reporta é obrigatório.';
    }

    if ($reporterPhone === '') {
        $errors['reporter_phone'] = 'O telefone de contacto é obrigatório.';
    }

    if ($reporterEmail !== '' && !filter_var($reporterEmail, FILTER_VALIDATE_EMAIL)) {
        $errors['reporter_email'] = 'Email inválido.';
    }

    if ($age !== null && ($age < 0 || $age > 130)) {
        $errors['age'] = 'Idade inválida.';
    }

    // Tratar o upload da fotografia (só se não houver outros erros)
    $photoPath = null;
    if (empty($errors)) {
        try {
            $photoPath = handlePhotoUpload($_FILES['photo'] ?? []);
        } catch (RuntimeException $e) {
            $errors['photo'] = $e->getMessage();
        }
    }

    // Guardar o registo na base de dados
    if (empty($errors)) {
        // Campos opcionais: guardar NULL em vez de string vazia
        $eyeColor = $eyeColor !== '' ? $eyeColor : null;
        $hairColor = $hairColor !== '' ? $hairColor : null;
        $marks = $marks !== '' ? $marks : null;
        $circumstances = $circumstances !== '' ? $circumstances : null;
        $reporterEmail = $reporterEmail !== '' ? $reporterEmail : null;

        $sql = 'INSERT INTO missing_persons
                    (full_name, age, gender, height_cm, eye_color, hair_color,
                     distinguishing_marks, last_seen_location, last_seen_date,
                     circumstances, photo_path, reporter_name, reporter_phone,
                     reporter_email, user_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';

        $stmt = mysqli_prepare($conn, $sql);
        // 15 parâmetros — 14 strings + user_id inteiro
        mysqli_stmt_bind_param(
            $stmt,
            'ssssssssssssssi',
            $fullName,
            $age,
            $gender,
            $heightCm,
            $eyeColor,
            $hairColor,
            $marks,
            $lastSeenLocation,
            $lastSeenDate,
            $circumstances,
            $photoPath,
            $reporterName,
            $reporterPhone,
            $reporterEmail,
            $userId
        );
        mysqli_stmt_execute($stmt);
        $novoId = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        // Post | redirect | GET
        // Padrão PRG: redirecionar após POST para evitar duplicação ao recarregar
        redirect('person.php?id=' . $novoId . '&created=1');
    }
}

// Repopular o formulário com o valor anterior (após erro de validação)
function old(string $key, array $old): string
{
    return e($old[$key] ?? '');
}

$pageTitle = 'Registar desaparecido';
require __DIR__ . '/includes/header.php';
?>

<div class="container px-4 px-lg-5 py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h2 class="fw-bolder mb-4"><i class="bi-plus-circle me-2"></i>Registar desaparecido</h2>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    Corrija os erros assinalados abaixo.
                </div>
            <?php endif; ?>

            <form method="post" action="register.php" enctype="multipart/form-data" novalidate>

                <fieldset class="mb-4">
                    <legend class="h5 border-bottom pb-2">Dados da pessoa</legend>

                    <div class="mb-3">
                        <label class="form-label">Nome completo *</label>
                        <input type="text" name="full_name"
                            class="form-control <?= isset($errors['full_name']) ? 'is-invalid' : '' ?>"
                            value="<?= old('full_name', $old) ?>">
                        <div class="invalid-feedback"><?= e($errors['full_name'] ?? '') ?></div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Idade</label>
                            <input type="number" name="age" min="0" max="130"
                                class="form-control <?= isset($errors['age']) ? 'is-invalid' : '' ?>"
                                value="<?= old('age', $old) ?>">
                            <div class="invalid-feedback"><?= e($errors['age'] ?? '') ?></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Sexo</label>
                            <select name="gender" class="form-select">
                                <option value="male" <?= ($old['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Masculino
                                </option>
                                <option value="female" <?= ($old['gender'] ?? '') === 'female' ? 'selected' : '' ?>>
                                    Feminino</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Altura (cm)</label>
                            <input type="number" name="height_cm" min="30" max="260" class="form-control"
                                value="<?= old('height_cm', $old) ?>">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cor dos olhos</label>
                            <input type="text" name="eye_color" class="form-control"
                                value="<?= old('eye_color', $old) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cor do cabelo</label>
                            <input type="text" name="hair_color" class="form-control"
                                value="<?= old('hair_color', $old) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sinais particulares</label>
                        <textarea name="distinguishing_marks" rows="2" class="form-control"
                            placeholder="Cicatrizes, tatuagens, óculos..."><?= old('distinguishing_marks', $old) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fotografia</label>
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                            class="form-control <?= isset($errors['photo']) ? 'is-invalid' : '' ?>">
                        <div class="form-text">JPG, PNG ou WEBP. Máximo 3 MB.</div>
                        <div class="invalid-feedback"><?= e($errors['photo'] ?? '') ?></div>
                    </div>
                </fieldset>

                <fieldset class="mb-4">
                    <legend class="h5 border-bottom pb-2">Desaparecimento</legend>

                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Último local visto *</label>
                            <input type="text" name="last_seen_location"
                                class="form-control <?= isset($errors['last_seen_location']) ? 'is-invalid' : '' ?>"
                                value="<?= old('last_seen_location', $old) ?>">
                            <div class="invalid-feedback"><?= e($errors['last_seen_location'] ?? '') ?></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Data do desaparecimento *</label>
                            <input type="date" name="last_seen_date" max="<?= date('Y-m-d') ?>"
                                class="form-control <?= isset($errors['last_seen_date']) ? 'is-invalid' : '' ?>"
                                value="<?= old('last_seen_date', $old) ?>">
                            <div class="invalid-feedback"><?= e($errors['last_seen_date'] ?? '') ?></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Circunstâncias</label>
                        <textarea name="circumstances" rows="3" class="form-control"
                            placeholder="O que aconteceu, com quem estava, roupa que vestia..."><?= old('circumstances', $old) ?></textarea>
                    </div>
                </fieldset>

                <fieldset class="mb-4">
                    <legend class="h5 border-bottom pb-2">Contacto de quem reporta</legend>

                    <div class="mb-3">
                        <label class="form-label">Nome *</label>
                        <input type="text" name="reporter_name"
                            class="form-control <?= isset($errors['reporter_name']) ? 'is-invalid' : '' ?>"
                            value="<?= old('reporter_name', $old) ?>">
                        <div class="invalid-feedback"><?= e($errors['reporter_name'] ?? '') ?></div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Telefone *</label>
                            <input type="text" name="reporter_phone"
                                class="form-control <?= isset($errors['reporter_phone']) ? 'is-invalid' : '' ?>"
                                value="<?= old('reporter_phone', $old) ?>">
                            <div class="invalid-feedback"><?= e($errors['reporter_phone'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="reporter_email"
                                class="form-control <?= isset($errors['reporter_email']) ? 'is-invalid' : '' ?>"
                                value="<?= old('reporter_email', $old) ?>">
                            <div class="invalid-feedback"><?= e($errors['reporter_email'] ?? '') ?></div>
                        </div>
                    </div>
                </fieldset>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-circle"></i> Registar
                    </button>
                    <a href="index.php" class="btn btn-outline-secondary btn-lg">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>