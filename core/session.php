<?php
// core/session.php
// Gerenciamento de sessão

if (!session_id()) {
    session_start();
}

function getSessionValue($key, $default = null) {
    return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
}

function setSessionValue($key, $value) {
    $_SESSION[$key] = $value;
}
