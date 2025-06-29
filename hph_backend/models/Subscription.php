<?php
require_once '../config/database.php';

class Subscription {
    private $conn;
    private $table_name = "user_subscriptions";

    public $id;
    public $user_id;
    public $plan_id;
    public $status;
    public $start_date;
    public $end_date;
    public $last_payment_date;
    public $next_payment_date;
    public $payment_method;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new subscription
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    user_id = :user_id,
                    plan_id = :plan_id,
                    status = :status,
                    start_date = :start_date,
                    end_date = :end_date,
                    payment_method = :payment_method";

        $stmt = $this->conn->prepare($query);

        // Bind parameters
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":plan_id", $this->plan_id);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":end_date", $this->end_date);
        $stmt->bindParam(":payment_method", $this->payment_method);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Get user's current subscription
    public function getCurrentSubscription($user_id) {
        $query = "SELECT us.*, sp.name as plan_name, sp.price, sp.duration_days, sp.features
                FROM " . $this->table_name . " us
                LEFT JOIN subscription_plans sp ON us.plan_id = sp.id
                WHERE us.user_id = ? AND us.status = 'active'
                ORDER BY us.created_at DESC
                LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->plan_id = $row['plan_id'];
            $this->status = $row['status'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
            $this->last_payment_date = $row['last_payment_date'];
            $this->next_payment_date = $row['next_payment_date'];
            $this->payment_method = $row['payment_method'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];

            // Calculate time remaining
            $time_remaining = $this->calculateTimeRemaining($row['end_date']);

            return array(
                'subscription' => array(
                    'id' => $row['id'],
                    'plan' => $row['plan_name'],
                    'status' => $row['status'],
                    'startDate' => $row['start_date'],
                    'endDate' => $row['end_date'],
                    'lastPaymentDate' => $row['last_payment_date'],
                    'nextPaymentDate' => $row['next_payment_date'],
                    'paymentMethod' => $row['payment_method'],
                    'timeRemaining' => $time_remaining
                ),
                'plan' => array(
                    'id' => $row['plan_id'],
                    'name' => $row['plan_name'],
                    'price' => $row['price'],
                    'durationDays' => $row['duration_days'],
                    'features' => json_decode($row['features'], true)
                )
            );
        }
        return null;
    }

    // Calculate time remaining until subscription expires
    private function calculateTimeRemaining($end_date) {
        if (!$end_date) return null;

        $end = new DateTime($end_date);
        $now = new DateTime();
        $interval = $now->diff($end);

        if ($interval->invert == 1) {
            // Subscription has expired
            return array('days' => 0, 'hours' => 0, 'minutes' => 0);
        }

        return array(
            'days' => $interval->days,
            'hours' => $interval->h,
            'minutes' => $interval->i
        );
    }

    // Update subscription status
    public function updateStatus($subscription_id, $status) {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status, updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $subscription_id);

        return $stmt->execute();
    }

    // Update payment dates
    public function updatePaymentDates($subscription_id, $last_payment_date, $next_payment_date) {
        $query = "UPDATE " . $this->table_name . "
                SET last_payment_date = :last_payment_date,
                    next_payment_date = :next_payment_date,
                    updated_at = CURRENT_TIMESTAMP
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":last_payment_date", $last_payment_date);
        $stmt->bindParam(":next_payment_date", $next_payment_date);
        $stmt->bindParam(":id", $subscription_id);

        return $stmt->execute();
    }

    // Get all subscription plans
    public function getAllPlans() {
        $query = "SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY price ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get subscription plan by ID
    public function getPlanById($plan_id) {
        $query = "SELECT * FROM subscription_plans WHERE id = ? AND is_active = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $plan_id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return null;
    }
}
?> 