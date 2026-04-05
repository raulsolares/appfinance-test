<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Finance.php';

if (!Auth::check()) { header('Location: login.php'); exit; }

$finance = new Finance($pdo, Auth::userId());
$expensesByCategory = $finance->getExpensesByCategory();
$monthlyComparison = $finance->getMonthlyComparison();

$pageTitle = "Análisis Financiero";
include __DIR__ . '/includes/layout_top.php';
?>

<header class="mb-10 px-2 mt-4">
    <h1 class="text-3xl font-black tracking-tighter">Análisis</h1>
    <p class="text-sm text-slate-400 font-medium">Estadísticas de tus movimientos</p>
</header>

<!-- Resumen Rápido -->
<div class="grid grid-cols-2 gap-4 mb-8 px-2">
    <div class="glass rounded-3xl p-5 border-l-4 border-emerald-500">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Ingresos Mes</p>
        <p class="text-xl font-black text-emerald-400">$<?php 
            $currentMonth = end($monthlyComparison);
            echo number_format($currentMonth['income'] ?? 0, 0); 
        ?></p>
    </div>
    <div class="glass rounded-3xl p-5 border-l-4 border-red-500">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Gastos Mes</p>
        <p class="text-xl font-black text-red-400">$<?php 
            echo number_format($currentMonth['expense'] ?? 0, 0); 
        ?></p>
    </div>
</div>

<!-- Gráfica por Categoría -->
<div class="glass rounded-[32px] p-6 mb-8 px-2">
    <div class="flex items-center space-x-2 mb-6">
        <i data-lucide="pie-chart" class="w-5 h-5 text-indigo-400"></i>
        <h2 class="text-lg font-bold tracking-tight">Gastos por Categoría</h2>
    </div>
    <div class="h-64 relative">
        <canvas id="categoryChart"></canvas>
    </div>
</div>

<!-- Comparativa Mensual -->
<div class="glass rounded-[32px] p-6 mb-12 px-2">
    <div class="flex items-center space-x-2 mb-6">
        <i data-lucide="bar-chart-big" class="w-5 h-5 text-indigo-400"></i>
        <h2 class="text-lg font-bold tracking-tight">Historial 6 Meses</h2>
    </div>
    <div class="h-64">
        <canvas id="comparisonChart"></canvas>
    </div>
</div>

<script>
    // Configuración común para Charts Premium
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.font.weight = '600';

    // Gráfica por Categoría
    const catCtx = document.getElementById('categoryChart').getContext('2d');
    const catData = <?php echo json_encode($expensesByCategory); ?>;
    
    new Chart(catCtx, {
        type: 'doughnut',
        data: {
            labels: catData.map(d => d.name),
            datasets: [{
                data: catData.map(d => d.total),
                backgroundColor: catData.map(d => d.color || '#6366f1'),
                borderWidth: 0,
                hoverOffset: 20
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { 
                legend: { 
                    position: 'bottom',
                    labels: { 
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    } 
                } 
            }
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
                    borderRadius: 8,
                    maxBarThickness: 20
                },
                {
                    label: 'Gastos',
                    data: compData.map(d => d.expense),
                    backgroundColor: '#ef4444',
                    borderRadius: 8,
                    maxBarThickness: 20
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { grid: { display: false }, border: { display: false } },
                y: { grid: { color: 'rgba(255,255,255,0.05)' }, border: { display: false }, ticks: { display: false } }
            },
            plugins: { 
                legend: { 
                    display: true, 
                    position: 'top',
                    labels: { usePointStyle: true }
                } 
            }
        }
    });
</script>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
