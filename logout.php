<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$_SESSION = [];
session_destroy();

redirect('index.php');
