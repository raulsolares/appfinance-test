<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Auth.php';

// Si no está logueado, ir al login
if (!Auth::check()) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../src/Finance.php';
$finance = new Finance($pdo, Auth::userId());
$totalBalance = $finance->getTotalBalance();
$accounts = $finance->getAccounts();

// Título de la página
$pageTitle = "Dashboard - Finanzas Pro";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0f172a">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background-color: #0f172a; color: #f8fafc; }
        .card { background-color: #1e293b; border-radius: 1rem; padding: 1.5rem; }
    </style>
</head>
<body class="p-4 mb-20">
    <!-- Header -->
    <header class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-2xl font-bold">Hola, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <p class="text-slate-400">Tu balance total</p>
        </div>
        <div class="text-3xl font-bold text-emerald-400">
            $<?php echo number_format($totalBalance, 2); ?>
        </div>
    </header>

    <!-- Gráfica Resumen -->
    <div class="card mb-6">
        <h2 class="text-lg font-semibold mb-4">Gastos por Categoría</h2>
        <canvas id="expensesChart" height="200"></canvas>
    </div>

    <!-- Cuentas y Tarjetas -->
    <section class="mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Tus Cuentas</h2>
            <button class="text-sm text-blue-400">+ Nueva</button>
        </div>
        <div class="grid grid-cols-1 gap-4">
            <?php foreach ($accounts as $acc): ?>
            <div class="card flex justify-between items-center" style="border-left: 4px solid <?php echo $acc['color']; ?>">
                <div>
                    <p class="font-medium"><?php echo htmlspecialchars($acc['name']); ?></p>
                    <p class="text-xs text-slate-400 uppercase"><?php echo $acc['type']; ?></p>
                </div>
                <div class="text-lg font-bold">
                    $<?php echo number_format($acc['balance'], 2); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Barra de Navegación Inferior (Mobile First) -->
    <nav class="fixed bottom-0 left-0 right-0 bg-slate-900 border-t border-slate-800 flex justify-around p-4">
        <a href="index.php" class="text-blue-400 text-center">
            <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-xs">Inicio</span>
        </a>
        <a href="add_transaction.php" class="bg-blue-600 rounded-full w-12 h-12 -mt-10 shadow-lg flex items-center justify-center text-white text-2xl font-bold">
            +
        </a>
        <a href="reports.php" class="text-slate-400 text-center">
            <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <span class="text-xs">Reportes</span>
        </a>
    </nav>

    <script>
        // Registrar Service Worker
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js', { scope: './' });
            });
        }

        // Gráfica de ejemplo
        const ctx = document.getElementById('expensesChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Comida', 'Transporte', 'Vivienda', 'Ocio'],
                datasets: [{
                    data: [300, 150, 1000, 200],
                    backgroundColor: ['#ef4444', '#f59e0b', '#3b82f6', '#8b5cf6'],
                    borderWidth: 0
                }]
            },
            options: {
                plugins: { legend: { position: 'bottom', labels: { color: '#f8fafc' } } }
            }
        });
    </script>
</body>
</html>
