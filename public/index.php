<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Finance.php';

if (!Auth::check()) { header('Location: login.php'); exit; }

$finance = new Finance($pdo, Auth::userId());
$totalBalance = $finance->getTotalBalance();
$accounts = $finance->getAccounts();
$recent = $finance->getRecentTransactions(5);
$budgets = $finance->getBudgets();
$healthScore = $finance->getFinancialHealthScore();

$pageTitle = "Dashboard";
include __DIR__ . '/includes/layout_top.php';
?>

<!-- Header Premium -->
<div class="flex justify-between items-center mb-10 px-2 mt-4">
    <div class="flex items-center space-x-4">
        <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-indigo-600 to-emerald-500 p-[2px]">
            <div class="w-full h-full bg-slate-900 rounded-2xl flex items-center justify-center">
                <i data-lucide="user" class="w-6 h-6 text-indigo-400"></i>
            </div>
        </div>
        <div>
            <h1 class="text-xl font-bold tracking-tight"><?php echo htmlspecialchars($_SESSION['username']); ?></h1>
            <p class="text-xs text-slate-400 font-medium">Finanzas Pro Premium</p>
        </div>
    </div>
    <div class="flex space-x-3">
        <button class="w-10 h-10 glass rounded-xl flex items-center justify-center text-slate-300">
            <i data-lucide="bell" class="w-5 h-5"></i>
        </button>
    </div>
</div>

<!-- Card de Salud Financiera -->
<div class="glass rounded-[32px] p-6 mb-10 relative overflow-hidden premium-card">
    <div class="relative z-10 flex items-center justify-between">
        <div class="space-y-4">
            <p class="text-sm font-semibold text-indigo-200 tracking-wider uppercase">Balance Total</p>
            <h2 class="text-4xl font-extrabold tracking-tighter">$<?php echo number_format($totalBalance, 2); ?></h2>
            <div class="flex items-center space-x-2 text-emerald-400 text-xs font-bold bg-emerald-400/10 px-3 py-1.5 rounded-full w-fit">
                <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                <span>+12.5% este mes</span>
            </div>
        </div>
        
        <!-- Círculo de Score -->
        <div class="relative flex items-center justify-center w-24 h-24">
            <svg class="w-full h-full transform -rotate-90">
                <circle cx="48" cy="48" r="40" stroke="currentColor" stroke-width="8" fill="transparent" class="text-slate-800" />
                <circle cx="48" cy="48" r="40" stroke="currentColor" stroke-width="8" fill="transparent" 
                        class="text-emerald-500" stroke-dasharray="251.2" 
                        stroke-dashoffset="<?php echo 251.2 - (251.2 * $healthScore / 100); ?>" 
                        stroke-linecap="round" />
            </svg>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-xl font-black"><?php echo $healthScore; ?></span>
                <span class="text-[8px] uppercase tracking-tighter text-slate-400 font-bold">Score</span>
            </div>
        </div>
    </div>
</div>

<!-- Tus Cuentas (Scroll Horizontal) -->
<section class="mb-10">
    <div class="flex justify-between items-end mb-6 px-2">
        <h3 class="text-lg font-bold tracking-tight">Cuentas</h3>
        <a href="settings.php" class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Ver todo</a>
    </div>
    <div class="flex overflow-x-auto gap-4 pb-4 px-2 hide-scrollbar">
        <?php foreach ($accounts as $acc): ?>
        <div class="flex-none w-56 h-32 rounded-3xl p-5 relative overflow-hidden glass transition-transform active:scale-95" 
             style="border-left: 4px solid <?php echo $acc['color']; ?>">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1"><?php echo $acc['type']; ?></p>
            <p class="font-bold text-base mb-4 truncate"><?php echo htmlspecialchars($acc['name']); ?></p>
            <p class="text-xl font-black tracking-tighter">$<?php echo number_format($acc['balance'], 2); ?></p>
            <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-white/5 rounded-full blur-xl"></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Transacciones Recientes -->
<section class="mb-10 px-2">
    <div class="flex justify-between items-end mb-6">
        <h3 class="text-lg font-bold tracking-tight">Recientes</h3>
        <a href="reports.php" class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Historial</a>
    </div>
    <div class="space-y-3">
        <?php if (empty($recent)): ?>
            <p class="text-center text-slate-500 py-8 text-sm">No hay movimientos recientes</p>
        <?php endif; ?>
        <?php foreach ($recent as $t): ?>
        <div class="glass rounded-2xl p-4 flex items-center justify-between group transition-all hover:bg-slate-800/40">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white shadow-lg" 
                     style="background-color: <?php echo $t['category_color'] ?? '#334155'; ?>33; color: <?php echo $t['category_color'] ?? '#94a3b8'; ?>">
                    <i data-lucide="<?php echo $t['category_icon'] ?? 'credit-card'; ?>" class="w-6 h-6"></i>
                </div>
                <div>
                    <p class="text-sm font-bold truncate max-w-[120px]"><?php echo htmlspecialchars($t['description'] ?: ($t['category_name'] ?: 'Transacción')); ?></p>
                    <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest"><?php echo $t['account_name']; ?></p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm font-black tracking-tight <?php echo $t['type'] == 'income' ? 'text-emerald-400' : ($t['type'] == 'transfer' ? 'text-indigo-400' : 'text-slate-100'); ?>">
                    <?php echo $t['type'] == 'income' ? '+' : ($t['type'] == 'transfer' ? '' : '-'); ?>
                    $<?php echo number_format($t['amount'], 2); ?>
                </p>
                <p class="text-[10px] font-medium text-slate-500"><?php echo date('d M', strtotime($t['date'])); ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Presupuestos Activos -->
<?php if (!empty($budgets)): ?>
<section class="mb-6 px-2 pb-10">
    <h3 class="text-lg font-bold tracking-tight mb-6">Presupuestos</h3>
    <div class="grid grid-cols-1 gap-4">
        <?php foreach ($budgets as $b): ?>
        <?php 
            $percent = ($b['current_spent'] / $b['amount_limit']) * 100;
            $isOver = $percent > 100;
        ?>
        <div class="glass rounded-2xl p-4">
            <div class="flex justify-between items-center mb-3">
                <div class="flex items-center space-x-2">
                    <div class="w-2 h-2 rounded-full" style="background-color: <?php echo $b['color']; ?>"></div>
                    <p class="text-xs font-bold tracking-wide"><?php echo htmlspecialchars($b['name']); ?></p>
                </div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                    $<?php echo number_format($b['current_spent'], 0); ?> / $<?php echo number_format($b['amount_limit'], 0); ?>
                </p>
            </div>
            <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-1000" 
                     style="width: <?php echo min(100, $percent); ?>%; background-color: <?php echo $isOver ? '#ef4444' : $b['color']; ?>"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
