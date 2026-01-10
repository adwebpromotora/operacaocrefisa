<?php
// public/components/header.php

require_once __DIR__ . '/../../core/session.php';
require_once __DIR__ . '/../../core/helpers.php';

$userName = getSessionValue("nome", "Usuário");
$userProfile = getSessionValue("user_profile", "Não Definido");

$is_gestor = (strcasecmp(trim($userProfile ?? ''), "gestor") === 0);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operação Crefisa - Gestão de Leads</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css"> <!-- seu css customizado, se tiver -->

    <style>
        body { font-family: 'Inter', sans-serif; }
        body.modal-open { overflow: hidden; }
    </style>
</head>
<body class="bg-gray-100 pt-20">

<nav class="fixed top-0 left-0 right-0 z-50 bg-white p-4 shadow-md">
    <div class="container mx-auto flex items-center justify-between">
        <div class="flex-shrink-0">
            <a href="/public/dashboard.php">
                <img src="/assets/images/logoadwebpromotora.png" alt="Logo" class="h-10 w-auto">
            </a>
        </div>

        <!-- Menu Desktop -->
        <div class="hidden md:flex items-center space-x-6 flex-grow justify-center">
            <a href="/public/dashboard.php" class="text-gray-800 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Dashboard</a>
            <a href="/public/simulacoes.php" class="text-gray-800 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Simulações</a>
            <a href="/public/atendimento.php" class="text-gray-800 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition">Atendimento</a>

            <?php if ($is_gestor): ?>
            <div class="relative" id="admin-menu-wrapper">
                <button class="text-gray-800 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition inline-flex items-center" id="admin-menu-btn">
                    Administrador
                    <svg class="w-2.5 h-2.5 ml-2 transition-transform" id="admin-arrow" fill="none" viewBox="0 0 10 6">
                        <path stroke="currentColor" stroke-width="2" d="m1 1 4 4 4-4"/>
                    </svg>
                </button>
                <div class="absolute hidden bg-white shadow-lg rounded-md mt-2 w-56 right-0 z-10" id="admin-submenu">
                    <a href="/public/importacao.php" class="block px-4 py-3 text-sm text-gray-700 hover:bg-gray-100">Importação de Leads</a>
                    <!-- Pode adicionar mais itens aqui no futuro -->
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Usuário e Sair (Desktop) -->
        <div class="hidden md:flex items-center space-x-4">
            <div class="text-right">
                <div class="text-gray-800 text-sm font-medium">Olá, <?= htmlspecialchars($userName) ?></div>
                <div class="text-gray-500 text-xs">(<?= htmlspecialchars($userProfile) ?>)</div>
            </div>
            <a href="/public/logout.php" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded transition">
                Sair
            </a>
        </div>

        <!-- Botão Menu Mobile -->
        <div class="md:hidden">
            <button id="mobile-menu-btn" class="text-gray-800 focus:outline-none">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Menu Mobile -->
    <div id="mobile-menu" class="hidden md:hidden mt-4 bg-white shadow-lg">
        <a href="/public/dashboard.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100">Dashboard</a>
        <a href="/public/simulacoes.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100">Simulações</a>
        <a href="/public/atendimento.php" class="block px-4 py-3 text-gray-800 hover:bg-gray-100">Atendimento</a>

        <?php if ($is_gestor): ?>
        <div class="border-t my-2"></div>
        <div class="px-4 py-2 text-gray-600 font-medium">Administrador</div>
        <a href="/public/importacao.php" class="block px-8 py-3 text-gray-800 hover:bg-gray-100">Importação de Leads</a>
        <?php endif; ?>

        <div class="border-t my-2"></div>
        <div class="px-4 py-3">
            <p class="text-gray-800 font-medium">Olá, <?= htmlspecialchars($userName) ?></p>
            <p class="text-gray-600 text-sm">(<?= htmlspecialchars($userProfile) ?>)</p>
        </div>
        <a href="/public/logout.php" class="block mx-4 mb-4 bg-red-600 text-white text-center font-medium py-3 rounded">
            Sair
        </a>
    </div>
</nav>

<script>
// Mobile menu toggle
document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
    document.getElementById('mobile-menu').classList.toggle('hidden');
});

// Admin submenu desktop
const adminBtn = document.getElementById('admin-menu-btn');
const adminSub = document.getElementById('admin-submenu');
const adminArrow = document.getElementById('admin-arrow');

if (adminBtn) {
    adminBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        adminSub.classList.toggle('hidden');
        adminArrow.classList.toggle('rotate-180');
    });

    document.addEventListener('click', (e) => {
        if (!adminBtn.contains(e.target)) {
            adminSub.classList.add('hidden');
            adminArrow.classList.remove('rotate-180');
        }
    });
}
</script>
