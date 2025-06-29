<?php
class User {
    private $conn;
    private $table_name = "users";

    public $id;
    public $full_name;
    public $username;
    public $email;
    public $phone_number;
    public $date_of_birth;
    public $state;
    public $city;
    public $password_hash;
    public $blood_type;
    public $height;
    public $weight;
    public $allergies;
    public $emergency_contact_name;
    public $emergency_contact_relation;
    public $emergency_contact_phone;
    public $profile_image;
    public $is_active;
    public $email_verified;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new user
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    full_name = :full_name,
                    username = :username,
                    email = :email,
                    phone_number = :phone_number,
                    date_of_birth = :date_of_birth,
                    state = :state,
                    city = :city,
                    password_hash = :password_hash";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->username = htmlspecialchars(strip_tags($this->username));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number));
        $this->state = htmlspecialchars(strip_tags($this->state));
        $this->city = htmlspecialchars(strip_tags($this->city));

        // Bind parameters
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":username", $this->username);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":date_of_birth", $this->date_of_birth);
        $stmt->bindParam(":state", $this->state);
        $stmt->bindParam(":city", $this->city);
        $stmt->bindParam(":password_hash", $this->password_hash);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Check if username exists
    public function usernameExists() {
        $query = "SELECT id, username, password_hash FROM " . $this->table_name . " WHERE username = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->username);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->id = $row['id'];
            $this->password_hash = $row['password_hash'];
            return true;
        }
        return false;
    }

    // Check if email exists
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    // Get user by ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->full_name = $row['full_name'];
            $this->username = $row['username'];
            $this->email = $row['email'];
            $this->phone_number = $row['phone_number'];
            $this->date_of_birth = $row['date_of_birth'];
            $this->state = $row['state'];
            $this->city = $row['city'];
            $this->blood_type = $row['blood_type'];
            $this->height = $row['height'];
            $this->weight = $row['weight'];
            $this->allergies = $row['allergies'];
            $this->emergency_contact_name = $row['emergency_contact_name'];
            $this->emergency_contact_relation = $row['emergency_contact_relation'];
            $this->emergency_contact_phone = $row['emergency_contact_phone'];
            $this->profile_image = $row['profile_image'];
            $this->is_active = $row['is_active'];
            $this->email_verified = $row['email_verified'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            return true;
        }
        return false;
    }

    // Update user profile
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    full_name = :full_name,
                    email = :email,
                    phone_number = :phone_number,
                    blood_type = :blood_type,
                    height = :height,
                    weight = :weight,
                    allergies = :allergies,
                    emergency_contact_name = :emergency_contact_name,
                    emergency_contact_relation = :emergency_contact_relation,
                    emergency_contact_phone = :emergency_contact_phone,
                    profile_image = :profile_image
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->full_name = htmlspecialchars(strip_tags($this->full_name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->phone_number = htmlspecialchars(strip_tags($this->phone_number));
        $this->allergies = htmlspecialchars(strip_tags($this->allergies));
        $this->emergency_contact_name = htmlspecialchars(strip_tags($this->emergency_contact_name));
        $this->emergency_contact_relation = htmlspecialchars(strip_tags($this->emergency_contact_relation));
        $this->emergency_contact_phone = htmlspecialchars(strip_tags($this->emergency_contact_phone));
        $this->profile_image = htmlspecialchars(strip_tags($this->profile_image));

        // Bind parameters
        $stmt->bindParam(":full_name", $this->full_name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":phone_number", $this->phone_number);
        $stmt->bindParam(":blood_type", $this->blood_type);
        $stmt->bindParam(":height", $this->height);
        $stmt->bindParam(":weight", $this->weight);
        $stmt->bindParam(":allergies", $this->allergies);
        $stmt->bindParam(":emergency_contact_name", $this->emergency_contact_name);
        $stmt->bindParam(":emergency_contact_relation", $this->emergency_contact_relation);
        $stmt->bindParam(":emergency_contact_phone", $this->emergency_contact_phone);
        $stmt->bindParam(":profile_image", $this->profile_image);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    // Get user data for API response (without sensitive info)
    public function getUserData() {
        return array(
            'id' => $this->id,
            'fullName' => $this->full_name,
            'username' => $this->username,
            'email' => $this->email,
            'phoneNumber' => $this->phone_number,
            'dateOfBirth' => $this->date_of_birth,
            'state' => $this->state,
            'city' => $this->city,
            'bloodType' => $this->blood_type,
            'height' => $this->height,
            'weight' => $this->weight,
            'allergies' => $this->allergies,
            'emergencyContactName' => $this->emergency_contact_name,
            'emergencyContactRelation' => $this->emergency_contact_relation,
            'emergencyContactPhone' => $this->emergency_contact_phone,
            'profileImage' => $this->profile_image,
            'isActive' => $this->is_active,
            'emailVerified' => $this->email_verified,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at
        );
    }
}
?> 