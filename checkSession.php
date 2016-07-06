<?php

require_once "../config.php";
require_once COMMON_PATH . "includes/funcs/common_funcs.php";

$callback = array();
$session_ativa = true;

if (!isset($_SESSION['USUARIO']['USUARIO']) || $_SESSION['USUARIO']['USUARIO'] != $_GET['usuario_nome']) {
    $session_ativa = false;
} else if (session_id() != $_GET['session_id']) {
    $session_ativa = false;
} else if (!isset($_SESSION['CURRENT_DB']['CODIGO_DB']) || $_SESSION['CURRENT_DB']['CODIGO_DB'] != $_GET['codigo_db']) {
    $session_ativa = false;
}

$callback['session_ativa'] = $session_ativa;

if ($_GET['callback']) {
    echo $_GET['callback'] . "(" . json_utf8_encode($callback) . ");";
} else {
    echo json_utf8_encode($callback);
}