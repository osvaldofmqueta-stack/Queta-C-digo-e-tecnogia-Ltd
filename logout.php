<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
logoutCliente();
header('Location: /');
exit;
