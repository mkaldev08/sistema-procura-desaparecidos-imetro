<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Sistema de cadastramento e procura de desaparecidos" />
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>Encontra-me</title>

    <!--
        UI base: Start Bootstrap "Shop Homepage" (MIT License)
        https://startbootstrap.com/template/shop-homepage
        Customized for the Encontra-me missing persons system.
    -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet" />
    
    <link href="css/styles.css" rel="stylesheet" />
</head>

<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="bi-search-heart me-1"></i>Encontra-me
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'index.php' ? 'active' : '' ?>"
                            href="index.php">Procurar</a>
                    </li>
                </ul>
                <?php if (estaAutenticado()): ?>
                    <span class="navbar-text text-white-50 me-3">
                        <i class="bi-person-check me-1"></i><?= e($_SESSION['username']) ?>
                    </span>
                    <a class="btn btn-primary me-3" href="register.php">
                        <i class="bi-plus-circle me-1"></i>Registar desaparecido
                    </a>
                    <a class="btn btn-outline-secondary btn-sm me-1" href="logout.php">
                        <i class="bi-box-arrow-right me-1"></i>Sair
                    </a>
                <?php else: ?>
                    <a class="btn btn-outline-light me-2" href="register-user.php">
                        <i class="bi-person-plus me-1"></i>Criar conta
                    </a>
                    <a class="btn btn-primary" href="login.php">
                        <i class="bi-box-arrow-in-right me-1"></i>Entrar
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main>
