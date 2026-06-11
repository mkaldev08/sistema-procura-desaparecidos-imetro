<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Utilizador já autenticado não precisa de entrar
if (estaAutenticado()) {
    redirect('index.php');
}

$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password']      ?? '';

    if ($username === '') {
        $errors['username'] = 'Introduza o nome de utilizador.';
    }
    if ($password === '') {
        $errors['password'] = 'Introduza a senha.';
    }

    if (empty($errors)) {
        $stmt = mysqli_prepare($conn, 'SELECT id, username, password FROM users WHERE username = ?');
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        $resultado  = mysqli_stmt_get_result($stmt);
        $utilizador = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);

        if ($utilizador && !empty($utilizador['password']) && password_verify($password, $utilizador['password'])) {
            $_SESSION['user_id']  = $utilizador['id'];
            $_SESSION['username'] = $utilizador['username'];
            redirect('index.php');
        }

        $errors['geral'] = 'Nome de utilizador ou senha incorrectos.';
    }
}

$pageTitle = 'Entrar';
require __DIR__ . '/includes/header.php';
?>

<div class="container px-4 px-lg-5 py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5">
            <h2 class="fw-bolder mb-4"><i class="bi-box-arrow-in-right me-2"></i>Entrar</h2>

            <?php if (isset($_GET['created'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-1"></i>Conta criada com sucesso. Pode entrar agora.
                </div>
            <?php endif; ?>

            <?php if (isset($errors['geral'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-1"></i><?= e($errors['geral']) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="login.php" novalidate>

                <div class="mb-3">
                    <label class="form-label">Nome de utilizador</label>
                    <input type="text" name="username" autofocus
                        class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                        value="<?= e($old['username'] ?? '') ?>">
                    <div class="invalid-feedback"><?= e($errors['username'] ?? '') ?></div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Senha</label>
                    <input type="password" name="password"
                        class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>">
                    <div class="invalid-feedback"><?= e($errors['password'] ?? '') ?></div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi-box-arrow-in-right me-1"></i>Entrar
                    </button>
                    <a href="register-user.php" class="btn btn-outline-secondary">
                        Criar nova conta
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
