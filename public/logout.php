<?php
// public/logout.php
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../core/helpers.php';

session_destroy();
redirect('/public/login.php');
