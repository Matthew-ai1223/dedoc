<?php
require_once '../config/database.php';

class Payment {
    private $conn;
    private $table_name = "payments";

    public $id;
    public $user_id;
    public $subscription_id;
    public $amount;
    public $currency;
    public $payment_method;
    public $transaction_id;
    public $status;
    public $payment_data;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new payment
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    user_id = :user_id,
                    subscription_id = :subscription_id,
                    amount = :amount,
                    currency = :currency,
                    payment_method = :payment_method,
                    transaction_id = :transaction_id,
                    status = :status,
                    payment_data = :payment_data";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":subscription_id", $this->subscription_id);
        $stmt->bindParam(":amount", $this->amount);
        $stmt->bindParam(":currency", $this->currency);
        $stmt->bindParam(":payment_method", $this->payment_method);
        $stmt->bindParam(":transaction_id", $this->transaction_id);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":payment_data", $this->payment_data);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get payment by transaction ID
    public function getByTransactionId($transaction_id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE transaction_id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $transaction_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->subscription_id = $row['subscription_id'];
            $this->amount = $row['amount'];
            $this->currency = $row['currency'];
            $this->payment_method = $row['payment_method'];
            $this->transaction_id = $row['transaction_id'];
            $this->status = $row['status'];
            $this->payment_data = $row['payment_data'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Get user's payment history
    public function getUserPayments($user_id, $limit = 10) {
        $query = "SELECT p.*, sp.name as plan_name 
                FROM " . $this->table_name . " p
                LEFT JOIN user_subscriptions us ON p.subscription_id = us.id
                LEFT JOIN subscription_plans sp ON us.plan_id = sp.id
                WHERE p.user_id = ?
                ORDER BY p.created_at DESC
                LIMIT ?";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update payment status
    public function updateStatus($payment_id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $payment_id);

        return $stmt->execute();
    }
}
?> 