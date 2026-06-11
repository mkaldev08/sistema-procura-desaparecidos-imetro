<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Utilizador já autenticado não precisa de criar conta
if (estaAutenticado()) {
    redirect('index.php');
}

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $username        = trim($_POST['username']         ?? '');
    $email           = trim($_POST['email']            ?? '');
    $phoneNumber     = trim($_POST['phone_number']     ?? '');
    $password        = $_POST['password']              ?? '';
    $passwordConfirm = $_POST['password_confirm']      ?? '';

    // Validação: nome de utilizador
    if ($username === '') {
        $errors['username'] = 'O nome de utilizador é obrigatório.';
    } elseif (mb_strlen($username) > 100) {
        $errors['username'] = 'O nome é demasiado longo (máx. 100 caracteres).';
    }

    // Validação: telefone (obrigatório, máx. 9 caracteres para caber na coluna)
    if ($phoneNumber === '') {
        $errors['phone_number'] = 'O telefone é obrigatório.';
    } elseif (mb_strlen($phoneNumber) > 9) {
        $errors['phone_number'] = 'Telefone inválido — máximo 9 dígitos.';
    }

    // Validação: email (opcional)
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Email inválido.';
    }

    // Validação: senha — verificar vazio antes de verificar comprimento
    if ($password === '') {
        $errors['password'] = 'A senha é obrigatória.';
    } elseif (mb_strlen($password) < 6) {
        $errors['password'] = 'A senha deve ter pelo menos 6 caracteres.';
    }

    // Validação: confirmação de senha (só verificar se a senha já passou)
    if (!isset($errors['password']) && $password !== $passwordConfirm) {
        $errors['password_confirm'] = 'As senhas não coincidem.';
    }

    if (empty($errors)) {
        $senhaHash = password_hash($password, PASSWORD_DEFAULT);
        $emailNulo = $email !== '' ? $email : null;

        $sql  = 'INSERT INTO users (username, email, phone_number, password) VALUES (?, ?, ?, ?)';
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'ssss', $username, $emailNulo, $phoneNumber, $senhaHash);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            redirect('login.php?created=1');
        }

        // Tratar violação de unicidade (username, email ou phone_number repetido)
        $erroBd = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);

        if (str_contains($erroBd, 'Duplicate entry')) {
            $errors['geral'] = 'Já existe uma conta com esse nome de utilizador, email ou telefone.';
        } else {
            $errors['geral'] = 'Erro ao criar conta. Tente novamente.';
        }
    }
}

$pageTitle = 'Criar conta';
require __DIR__ . '/includes/header.php';
?>

<div class="container px-4 px-lg-5 py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <h2 class="fw-bolder mb-4"><i class="bi-person-plus me-2"></i>Criar conta</h2>

            <?php if (!empty($errors) && !isset($errors['geral'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i>Corrija os erros assinalados abaixo.
                </div>
            <?php endif; ?>

            <?php if (isset($errors['geral'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i><?= e($errors['geral']) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="register-user.php" novalidate>

                <div class="mb-3">
                    <label class="form-label">Nome de utilizador *</label>
                    <input type="text" name="username"
                        class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                        value="<?= e($old['username'] ?? '') ?>">
                    <div class="invalid-feedback"><?= e($errors['username'] ?? '') ?></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Telefone *</label>
                    <input type="text" name="phone_number" maxlength="9" placeholder="9XXXXXXXX"
                        class="form-control <?= isset($errors['phone_number']) ? 'is-invalid' : '' ?>"
                        value="<?= e($old['phone_number'] ?? '') ?>">
                    <div class="invalid-feedback"><?= e($errors['phone_number'] ?? '') ?></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email"
                        class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                        value="<?= e($old['email'] ?? '') ?>">
                    <div class="invalid-feedback"><?= e($errors['email'] ?? '') ?></div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Senha *</label>
                    <input type="password" name="password"
                        class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>">
                    <div class="invalid-feedback"><?= e($errors['password'] ?? '') ?></div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Confirmar senha *</label>
                    <input type="password" name="password_confirm"
                        class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>">
                    <div class="invalid-feedback"><?= e($errors['password_confirm'] ?? '') ?></div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi-person-check me-1"></i>Criar conta
                    </button>
                    <a href="login.php" class="btn btn-outline-secondary">
                        Já tenho conta &mdash; Entrar
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
