<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Finance.php';

if (!Auth::check()) { header('Location: login.php'); exit; }

$finance = new Finance($pdo, Auth::userId());
$expensesByCategory = $finance->getExpensesByCategory();
$monthlyComparison = $finance->getMonthlyComparison();

$pageTitle = "Reportes - Finanzas Pro";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style> body { background-color: #0f172a; color: #f8fafc; } .card { background-color: #1e293b; border-radius: 1rem; padding: 1.5rem; } </style>
</head>
<body class="p-4 mb-20">
    <header class="mb-8">
        <h1 class="text-2xl font-bold">Reportes Financieros</h1>
        <p class="text-slate-400">Análisis detallado de tus movimientos</p>
    </header>

    <!-- Gastos por Categoría -->
    <div class="card mb-6">
        <h2 class="text-lg font-semibold mb-4">Gastos por Categoría (Mes Actual)</h2>
        <div class="h-64">
            <canvas id="categoryChart"></canvas>
        </div>
    </div>

    <!-- Comparativa Mensual -->
    <div class="card mb-6">
        <h2 class="text-lg font-semibold mb-4">Ingresos vs Gastos (Últimos Meses)</h2>
        <div class="h-64">
            <canvas id="comparisonChart"></canvas>
        </div>
    </div>

    <!-- Barra de Navegación Inferior -->
    <nav class="fixed bottom-0 left-0 right-0 bg-slate-900 border-t border-slate-800 flex justify-around p-4 z-50">
        <a href="index.php" class="text-slate-400 text-center">
            <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="text-xs">Inicio</span>
        </a>
        <a href="add_transaction.php" class="bg-blue-600 rounded-full w-12 h-12 -mt-10 shadow-lg flex items-center justify-center text-white text-2xl font-bold">
            +
        </a>
        <a href="reports.php" class="text-blue-400 text-center">
            <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            <span class="text-xs">Reportes</span>
        </a>
        <a href="settings.php" class="text-slate-400 text-center">
            <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            <span class="text-xs">Ajustes</span>
        </a>
    </nav>

    <script>
        // Gráfica por Categoría
        const catCtx = document.getElementById('categoryChart').getContext('2d');
        const catData = <?php echo json_encode($expensesByCategory); ?>;
        
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: catData.map(d => d.name),
                datasets: [{
                    data: catData.map(d => d.total),
                    backgroundColor: catData.map(d => d.color || '#3b82f6'),
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { color: '#f8fafc' } } }
            }
        });

        // Gráfica de Comparación
        const compCtx = document.getElementById('comparisonChart').getContext('2d');
        const compData = <?php echo json_encode($monthlyComparison); ?>;

        new Chart(compCtx, {
            type: 'bar',
            data: {
                labels: compData.map(d => d.month),
                datasets: [
                    {
                        label: 'Ingresos',
                        data: compData.map(d => d.income),
                        backgroundColor: '#10b981',
                        borderRadius: 5
                    },
                    {
                        label: 'Gastos',
                        data: compData.map(d => d.expense),
                        backgroundColor: '#ef4444',
                        borderRadius: 5
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { ticks: { color: '#94a3b8' }, grid: { display: false } },
                    y: { ticks: { color: '#94a3b8' }, grid: { color: '#334155' } }
                },
                plugins: { legend: { labels: { color: '#f8fafc' } } }
            }
        });
    </script>
</body>
</html>
