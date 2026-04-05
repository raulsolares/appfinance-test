<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Finance.php';

if (!Auth::check()) { header('Location: login.php'); exit; }

$finance = new Finance($pdo, Auth::userId());
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_account') {
            $finance->createAccount($_POST['name'], $_POST['type'], $_POST['balance'], $_POST['color']);
            $message = "Cuenta creada";
        } elseif ($_POST['action'] === 'add_category') {
            $stmt = $pdo->prepare("INSERT INTO categories (user_id, name, type, color, icon) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([Auth::userId(), $_POST['name'], $_POST['type'], $_POST['color'], $_POST['icon']]);
            $message = "Categoría añadida";
        } elseif ($_POST['action'] === 'set_budget') {
            $stmt = $pdo->prepare("INSERT INTO budgets (user_id, category_id, amount_limit, month, year) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE amount_limit = ?");
            $m = date('m'); $y = date('Y');
            $stmt->execute([Auth::userId(), $_POST['category_id'], $_POST['amount'], $m, $y, $_POST['amount']]);
            $message = "Presupuesto actualizado";
        }
    }
}

$accounts = $finance->getAccounts();
$categories = $finance->getCategories();
$pageTitle = "Ajustes y Cuentas";
include __DIR__ . '/includes/layout_top.php';
?>

<header class="mb-10 px-2 mt-4">
    <h1 class="text-3xl font-black tracking-tighter">Ajustes</h1>
    <p class="text-sm text-slate-400 font-medium">Gestiona tu ecosistema financiero</p>
</header>

<?php if ($message): ?>
    <div class="mx-2 mb-6 p-4 glass rounded-2xl border-l-4 border-indigo-500 text-indigo-300 text-xs font-bold animate-pulse">
        <?php echo strtoupper($message); ?>
    </div>
<?php endif; ?>

<!-- Cuentas -->
<section class="mb-10 px-2">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold tracking-tight">Cuentas Activas</h3>
        <button onclick="openModal('modalAccount')" class="w-8 h-8 glass rounded-full flex items-center justify-center text-indigo-400">
            <i data-lucide="plus" class="w-5 h-5"></i>
        </button>
    </div>
    <div class="space-y-3">
        <?php foreach ($accounts as $acc): ?>
        <div class="glass rounded-2xl p-4 flex items-center justify-between border-l-4" style="border-color: <?php echo $acc['color']; ?>">
            <div class="flex items-center space-x-4">
                <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400">
                    <i data-lucide="<?php echo $acc['type'] == 'bank' ? 'landmark' : ($acc['type'] == 'credit' ? 'credit-card' : 'banknote'); ?>" class="w-5 h-5"></i>
                </div>
                <div>
                    <p class="text-sm font-bold"><?php echo htmlspecialchars($acc['name']); ?></p>
                    <p class="text-[10px] font-bold text-slate-500 uppercase"><?php echo $acc['type']; ?></p>
                </div>
            </div>
            <p class="text-sm font-black">$<?php echo number_format($acc['balance'], 2); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Categorías y Presupuestos -->
<section class="mb-10 px-2">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-bold tracking-tight">Categorías</h3>
        <button onclick="openModal('modalCategory')" class="w-8 h-8 glass rounded-full flex items-center justify-center text-emerald-400">
            <i data-lucide="plus" class="w-5 h-5"></i>
        </button>
    </div>
    <div class="grid grid-cols-2 gap-3">
        <?php foreach ($categories as $cat): ?>
        <div class="glass rounded-2xl p-4 flex flex-col items-center text-center space-y-2 group transition-all active:scale-95" onclick="openBudgetModal(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name']); ?>')">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background-color: <?php echo $cat['color']; ?>22; color: <?php echo $cat['color']; ?>">
                <i data-lucide="<?php echo $cat['icon'] ?: 'tag'; ?>" class="w-5 h-5"></i>
            </div>
            <p class="text-[11px] font-bold truncate w-full"><?php echo htmlspecialchars($cat['name']); ?></p>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Modales -->
<div id="modalAccount" class="hidden fixed inset-0 z-[2000] flex items-end sm:items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
    <div class="glass w-full max-w-sm rounded-[32px] p-8 animate-in slide-in-from-bottom duration-300">
        <h3 class="text-xl font-black mb-6">Nueva Cuenta</h3>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="add_account">
            <input type="text" name="name" placeholder="Nombre de la cuenta" required class="w-full bg-slate-900/50 border border-white/10 rounded-2xl p-4 focus:ring-2 ring-indigo-500 outline-none">
            <select name="type" class="w-full bg-slate-900/50 border border-white/10 rounded-2xl p-4 outline-none">
                <option value="bank">Banco / Corriente</option>
                <option value="cash">Efectivo</option>
                <option value="credit">Tarjeta de Crédito</option>
            </select>
            <input type="number" name="balance" step="0.01" placeholder="Saldo Inicial" class="w-full bg-slate-900/50 border border-white/10 rounded-2xl p-4 outline-none">
            <div class="flex items-center space-x-4">
                <span class="text-xs font-bold text-slate-400 uppercase">Color</span>
                <input type="color" name="color" value="#6366f1" class="flex-1 h-12 bg-transparent border-none outline-none cursor-pointer">
            </div>
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeModal('modalAccount')" class="flex-1 py-4 font-bold text-slate-400">Cancelar</button>
                <button type="submit" class="flex-1 bg-indigo-600 rounded-2xl py-4 font-black shadow-lg shadow-indigo-600/20">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div id="modalBudget" class="hidden fixed inset-0 z-[2000] flex items-end sm:items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
    <div class="glass w-full max-w-sm rounded-[32px] p-8">
        <h3 class="text-xl font-black mb-2" id="budgetTitle">Presupuesto</h3>
        <p class="text-xs text-slate-400 mb-6 font-medium">Límite mensual para esta categoría</p>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="action" value="set_budget">
            <input type="hidden" name="category_id" id="budgetCatId">
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-2xl font-black text-slate-600">$</span>
                <input type="number" name="amount" placeholder="0.00" required class="w-full bg-slate-900/50 border border-white/10 rounded-2xl p-4 pl-10 text-2xl font-black focus:ring-2 ring-emerald-500 outline-none">
            </div>
            <div class="flex gap-3 pt-4">
                <button type="button" onclick="closeModal('modalBudget')" class="flex-1 py-4 font-bold text-slate-400">Cerrar</button>
                <button type="submit" class="flex-1 bg-emerald-600 rounded-2xl py-4 font-black">Establecer</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(id) { document.getElementById(id).classList.remove('hidden'); }
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
    function openBudgetModal(id, name) {
        document.getElementById('budgetCatId').value = id;
        document.getElementById('budgetTitle').innerText = 'Límite: ' + name;
        openModal('modalBudget');
    }
</script>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
