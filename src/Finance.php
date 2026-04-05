<?php
/**
 * Clase para manejar la Lógica de Finanzas
 */
class Finance {
    private $pdo;
    private $userId;

    public function __construct($pdo, $userId) {
        $this->pdo = $pdo;
        $this->userId = $userId;
    }

    /**
     * Obtener todas las cuentas del usuario
     */
    public function getAccounts() {
        $stmt = $this->pdo->prepare("SELECT * FROM accounts WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        return $stmt->fetchAll();
    }

    /**
     * Crear una nueva cuenta
     */
    public function createAccount($name, $type, $initial_balance = 0, $color = '#3b82f6') {
        $stmt = $this->pdo->prepare("INSERT INTO accounts (user_id, name, type, balance, color) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$this->userId, $name, $type, $initial_balance, $color]);
    }

    /**
     * Registrar una transacción (Ingreso o Gasto)
     */
    public function addTransaction($account_id, $category_id, $amount, $type, $description, $date, $file_path = null) {
        $this->pdo->beginTransaction();
        try {
            // Insertar transacción
            $stmt = $this->pdo->prepare("INSERT INTO transactions (user_id, account_id, category_id, amount, type, description, date, file_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$this->userId, $account_id, $category_id, $amount, $type, $description, $date, $file_path]);

            // Actualizar saldo de la cuenta
            $operator = ($type === 'income') ? '+' : '-';
            $stmt = $this->pdo->prepare("UPDATE accounts SET balance = balance $operator ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$amount, $account_id, $this->userId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Registrar un traspaso entre cuentas
     */
    public function transfer($from_account_id, $to_account_id, $amount, $description, $date) {
        $this->pdo->beginTransaction();
        try {
            // 1. Gasto en la cuenta origen
            $stmt = $this->pdo->prepare("INSERT INTO transactions (user_id, account_id, amount, type, description, date) VALUES (?, ?, ?, 'transfer', ?, ?)");
            $stmt->execute([$this->userId, $from_account_id, $amount, "Traspaso enviado: $description", $date]);
            $from_id = $this->pdo->lastInsertId();

            // 2. Ingreso en la cuenta destino
            $stmt = $this->pdo->prepare("INSERT INTO transactions (user_id, account_id, amount, type, description, date, transfer_id) VALUES (?, ?, ?, 'transfer', ?, ?, ?)");
            $stmt->execute([$this->userId, $to_account_id, $amount, "Traspaso recibido: $description", $date, $from_id]);
            $to_id = $this->pdo->lastInsertId();

            // Vincular la primera transacción con la segunda
            $stmt = $this->pdo->prepare("UPDATE transactions SET transfer_id = ? WHERE id = ?");
            $stmt->execute([$to_id, $from_id]);

            // 3. Actualizar saldos
            $stmt = $this->pdo->prepare("UPDATE accounts SET balance = balance - ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$amount, $from_account_id, $this->userId]);

            $stmt = $this->pdo->prepare("UPDATE accounts SET balance = balance + ? WHERE id = ? AND user_id = ?");
            $stmt->execute([$amount, $to_account_id, $this->userId]);

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Obtener resumen de saldos totales
     */
    public function getTotalBalance() {
        $stmt = $this->pdo->prepare("SELECT SUM(balance) as total FROM accounts WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        return $stmt->fetch()['total'] ?? 0;
    }

    /**
     * Obtener gastos agrupados por categoría para un mes específico
     */
    public function getExpensesByCategory($month = null, $year = null) {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');
        
        $stmt = $this->pdo->prepare("
            SELECT c.name, c.color, SUM(t.amount) as total 
            FROM transactions t
            JOIN categories c ON t.category_id = c.id
            WHERE t.user_id = ? AND t.type = 'expense' 
            AND MONTH(t.date) = ? AND YEAR(t.date) = ?
            GROUP BY c.id
        ");
        $stmt->execute([$this->userId, $month, $year]);
        return $stmt->fetchAll();
    }

    /**
     * Obtener ingresos vs gastos de los últimos 6 meses
     */
    public function getMonthlyComparison() {
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE_FORMAT(date, '%Y-%m') as month,
                SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense
            FROM transactions
            WHERE user_id = ?
            GROUP BY month
            ORDER BY month DESC
            LIMIT 6
        ");
        $stmt->execute([$this->userId]);
        return array_reverse($stmt->fetchAll());
    }

    /**
     * Obtener todas las categorías del usuario
     */
    public function getCategories($type = null) {
        $sql = "SELECT * FROM categories WHERE user_id = ?";
        $params = [$this->userId];
        if ($type) {
            $sql .= " AND type = ?";
            $params[] = $type;
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
