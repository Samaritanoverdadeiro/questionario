<?php
// logout.php
session_start(); // só iniciar a sessão, sem incluir auth.php

session_unset();
session_destroy();

// Redirecionar para a página de login
header("Location: ../index.php");
exit();
