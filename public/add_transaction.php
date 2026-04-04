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
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Operación - Finanzas Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style> body { background-color: #0f172a; color: #f8fafc; } </style>
</head>
<body class="p-6">
    <div class="flex items-center mb-6">
        <a href="index.php" class="text-blue-400 mr-4 text-xl">&larr;</a>
        <h1 class="text-2xl font-bold">Nueva Operación</h1>
    </div>

    <?php if ($message): ?>
        <div class="bg-blue-600/20 text-blue-400 p-4 rounded-xl mb-6 border border-blue-600/50"><?php echo $message; ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-6">
        <div class="bg-slate-800 p-2 rounded-xl flex">
            <label class="flex-1 text-center py-2 rounded-lg cursor-pointer peer-checked:bg-red-600">
                <input type="radio" name="type" value="expense" checked class="hidden" onchange="toggleForm(this.value)"> 
                <span class="block py-1 rounded-lg transition-all" id="label-expense">Gasto</span>
            </label>
            <label class="flex-1 text-center py-2 rounded-lg cursor-pointer">
                <input type="radio" name="type" value="income" class="hidden" onchange="toggleForm(this.value)"> 
                <span class="block py-1 rounded-lg transition-all" id="label-income">Ingreso</span>
            </label>
            <label class="flex-1 text-center py-2 rounded-lg cursor-pointer">
                <input type="radio" name="type" value="transfer" class="hidden" onchange="toggleForm(this.value)"> 
                <span class="block py-1 rounded-lg transition-all" id="label-transfer">Traspaso</span>
            </label>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Importe</label>
            <input type="number" name="amount" step="0.01" required placeholder="0.00"
                class="w-full bg-slate-900 border border-slate-700 rounded-lg p-4 text-2xl font-bold text-center text-blue-400 focus:outline-none">
        </div>

        <div id="section-standard">
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Cuenta</label>
                <select name="account_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3">
                    <?php foreach($accounts as $acc): ?>
                        <option value="<?php echo $acc['id']; ?>"><?php echo htmlspecialchars($acc['name']); ?> ($<?php echo $acc['balance']; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Categoría</label>
                <select name="category_id" id="category_id" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3">
                    <option value="">Sin Categoría</option>
                    <!-- Las opciones se cargan por JS según tipo -->
                </select>
            </div>
        </div>

        <div id="section-transfer" class="hidden grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2 text-red-400">Origen</label>
                <select name="account_from" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3">
                    <?php foreach($accounts as $acc): ?>
                        <option value="<?php echo $acc['id']; ?>"><?php echo htmlspecialchars($acc['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2 text-emerald-400">Destino</label>
                <select name="account_to" class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3">
                    <?php foreach($accounts as $acc): ?>
                        <option value="<?php echo $acc['id']; ?>"><?php echo htmlspecialchars($acc['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Fecha</label>
            <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" 
                class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3">
        </div>

        <div>
            <label class="block text-sm font-medium mb-2">Nota / Descripción</label>
            <input type="text" name="description" placeholder="Ej. Comida en restaurante"
                class="w-full bg-slate-900 border border-slate-700 rounded-lg p-3">
        </div>

        <div id="section-file">
            <label class="block text-sm font-medium mb-2">Adjuntar Ticket / Factura</label>
            <div class="relative">
                <input type="file" name="invoice" accept="image/*,application/pdf"
                    class="w-full opacity-0 absolute inset-0 cursor-pointer h-full">
                <div class="bg-slate-900 border-2 border-dashed border-slate-700 rounded-lg p-8 text-center text-slate-400">
                    Pulsa para subir archivo o tomar foto
                </div>
            </div>
        </div>

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl transition-all shadow-lg text-lg">
            Registrar Ahora
        </button>
    </form>

    <script>
        const categoriesExpense = <?php echo json_encode($categories_expense); ?>;
        const categoriesIncome = <?php echo json_encode($categories_income); ?>;

        function toggleForm(val) {
            const standard = document.getElementById('section-standard');
            const transfer = document.getElementById('section-transfer');
            const file = document.getElementById('section-file');
            const catSelect = document.getElementById('category_id');

            // Actualizar Estilos visuales de los tabs
            ['expense', 'income', 'transfer'].forEach(t => {
                const el = document.getElementById('label-' + t);
                if (t === val) {
                    el.classList.add(t === 'expense' ? 'bg-red-600' : (t === 'income' ? 'bg-emerald-600' : 'bg-blue-600'));
                    el.classList.add('text-white');
                } else {
                    el.classList.remove('bg-red-600', 'bg-emerald-600', 'bg-blue-600', 'text-white');
                }
            });

            if (val === 'transfer') {
                standard.classList.add('hidden');
                transfer.classList.remove('hidden');
                file.classList.add('hidden');
            } else {
                standard.classList.remove('hidden');
                transfer.classList.add('hidden');
                file.classList.remove('hidden');
                
                // Actualizar categorías según el tipo
                const cats = val === 'income' ? categoriesIncome : categoriesExpense;
                catSelect.innerHTML = '<option value="">Sin Categoría</option>' + 
                    cats.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
            }
        }
        // Inicializar con Gasto
        toggleForm('expense');
    </script>
</body>
</html>
