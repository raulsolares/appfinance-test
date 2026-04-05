<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Finance.php';
require_once __DIR__ . '/../src/Uploader.php';

if (!Auth::check()) { header('Location: login.php'); exit; }

$finance = new Finance($pdo, Auth::userId());
$accounts = $finance->getAccounts();
$categories_income = $finance->getCategories('income');
$categories_expense = $finance->getCategories('expense');

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $type = $_POST['type'];
        $amount = (float)$_POST['amount'];
        $date = $_POST['date'];
        $description = $_POST['description'];
        $uploader = new Uploader(__DIR__ . '/uploads/');
        $file_path = $uploader->upload($_FILES['invoice'] ?? null);

        if ($type === 'transfer') {
            $from = $_POST['account_from'];
            $to = $_POST['account_to'];
            $finance->transfer($from, $to, $amount, $description, $date);
        } else {
            $account_id = $_POST['account_id'];
            $category_id = $_POST['category_id'] ?: null;
            $finance->addTransaction($account_id, $category_id, $amount, $type, $description, $date, $file_path);
        }
        $message = "Operación registrada con éxito";
    } catch (Exception $e) { $message = "Error: " . $e->getMessage(); }
}

$pageTitle = "Nueva Operación";
include __DIR__ . '/includes/layout_top.php';
?>

<div class="flex items-center space-x-4 mb-8 mt-4 px-2">
    <a href="index.php" class="w-10 h-10 glass rounded-xl flex items-center justify-center text-slate-400">
        <i data-lucide="chevron-left" class="w-6 h-6"></i>
    </a>
    <h1 class="text-2xl font-black tracking-tighter">Nueva Operación</h1>
</div>

<?php if ($message): ?>
    <div class="mx-2 mb-6 p-4 glass rounded-2xl border-l-4 border-indigo-500 text-indigo-300 text-xs font-bold animate-pulse">
        <?php echo strtoupper($message); ?>
    </div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="space-y-6 px-2 pb-10">
    <!-- Selector de Tipo Premium -->
    <div class="glass p-1.5 rounded-[24px] flex">
        <label class="flex-1 cursor-pointer">
            <input type="radio" name="type" value="expense" checked class="hidden" onchange="toggleForm(this.value)"> 
            <div id="btn-expense" class="py-3 rounded-[20px] text-center text-xs font-bold transition-all bg-red-500 text-white">GASTO</div>
        </label>
        <label class="flex-1 cursor-pointer">
            <input type="radio" name="type" value="income" class="hidden" onchange="toggleForm(this.value)"> 
            <div id="btn-income" class="py-3 rounded-[20px] text-center text-xs font-bold text-slate-500 transition-all">INGRESO</div>
        </label>
        <label class="flex-1 cursor-pointer">
            <input type="radio" name="type" value="transfer" class="hidden" onchange="toggleForm(this.value)"> 
            <div id="btn-transfer" class="py-3 rounded-[20px] text-center text-xs font-bold text-slate-500 transition-all">TRASPASO</div>
        </label>
    </div>

    <!-- Importe Grande -->
    <div class="text-center py-6">
        <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] mb-2">Importe</p>
        <div class="relative inline-block">
            <span class="absolute -left-8 top-1/2 -translate-y-1/2 text-3xl font-black text-slate-700">$</span>
            <input type="number" name="amount" step="0.01" required placeholder="0.00"
                class="bg-transparent border-none text-6xl font-black text-center text-white focus:outline-none w-full max-w-[250px] placeholder-slate-800">
        </div>
    </div>

    <div id="section-standard" class="space-y-4">
        <div class="glass rounded-3xl p-5">
            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Cuenta de origen</label>
            <select name="account_id" class="w-full bg-transparent border-none text-sm font-bold focus:ring-0 p-0">
                <?php foreach($accounts as $acc): ?>
                    <option value="<?php echo $acc['id']; ?>" class="bg-slate-900"><?php echo htmlspecialchars($acc['name']); ?> ($<?php echo number_format($acc['balance'], 2); ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="glass rounded-3xl p-5">
            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Categoría</label>
            <select name="category_id" id="category_id" class="w-full bg-transparent border-none text-sm font-bold focus:ring-0 p-0">
                <option value="" class="bg-slate-900 text-slate-500 italic">Seleccionar categoría...</option>
                <!-- Se carga por JS -->
            </select>
        </div>
    </div>

    <div id="section-transfer" class="hidden space-y-4">
        <div class="glass rounded-3xl p-5 border-l-4 border-red-500/50">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Desde</label>
            <select name="account_from" class="w-full bg-transparent border-none text-sm font-bold focus:ring-0 p-0">
                <?php foreach($accounts as $acc): ?>
                    <option value="<?php echo $acc['id']; ?>" class="bg-slate-900"><?php echo htmlspecialchars($acc['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="glass rounded-3xl p-5 border-l-4 border-emerald-500/50">
            <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Hacia</label>
            <select name="account_to" class="w-full bg-transparent border-none text-sm font-bold focus:ring-0 p-0">
                <?php foreach($accounts as $acc): ?>
                    <option value="<?php echo $acc['id']; ?>" class="bg-slate-900"><?php echo htmlspecialchars($acc['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="glass rounded-3xl p-5">
            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Fecha</label>
            <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" 
                class="w-full bg-transparent border-none text-sm font-bold focus:ring-0 p-0 invert">
        </div>
        <div class="glass rounded-3xl p-5">
            <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Factura/Ticket</label>
            <input type="file" name="invoice" class="hidden" id="fileInput">
            <button type="button" onclick="document.getElementById('fileInput').click()" class="text-xs font-bold text-indigo-400">SUBIR ARCHIVO</button>
        </div>
    </div>

    <div class="glass rounded-3xl p-5">
        <label class="block text-[10px] font-black text-slate-500 uppercase tracking-widest mb-3">Concepto</label>
        <input type="text" name="description" placeholder="¿En qué has gastado?"
            class="w-full bg-transparent border-none text-sm font-bold focus:ring-0 p-0 placeholder-slate-700">
    </div>

    <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-black py-5 rounded-[24px] transition-all shadow-xl shadow-indigo-600/20 text-lg uppercase tracking-widest">
        Confirmar Operación
    </button>
</form>

<script>
    const categoriesExpense = <?php echo json_encode($categories_expense); ?>;
    const categoriesIncome = <?php echo json_encode($categories_income); ?>;

    function toggleForm(val) {
        const standard = document.getElementById('section-standard');
        const transfer = document.getElementById('section-transfer');
        const catSelect = document.getElementById('category_id');
        
        // Estilos de botones
        ['expense', 'income', 'transfer'].forEach(t => {
            const btn = document.getElementById('btn-' + t);
            if (t === val) {
                btn.classList.remove('text-slate-500');
                btn.classList.add('text-white', t === 'expense' ? 'bg-red-500' : (t === 'income' ? 'bg-emerald-500' : 'bg-indigo-600'));
            } else {
                btn.classList.add('text-slate-500');
                btn.classList.remove('bg-red-500', 'bg-emerald-500', 'bg-indigo-600', 'text-white');
            }
        });

        if (val === 'transfer') {
            standard.classList.add('hidden');
            transfer.classList.remove('hidden');
        } else {
            standard.classList.remove('hidden');
            transfer.classList.add('hidden');
            const cats = val === 'income' ? categoriesIncome : categoriesExpense;
            catSelect.innerHTML = '<option value="" class="bg-slate-900 italic text-slate-500">Seleccionar categoría...</option>' + 
                cats.map(c => `<option value="${c.id}" class="bg-slate-900">${c.name}</option>`).join('');
        }
    }
    toggleForm('expense');
</script>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
