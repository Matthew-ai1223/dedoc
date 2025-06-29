-- DeDoc Health System Database Schema

-- Create database
CREATE DATABASE IF NOT EXISTS dedoc_health;
USE dedoc_health;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone_number VARCHAR(20) NOT NULL,
    date_of_birth DATE NOT NULL,
    state VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    blood_type ENUM('A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-') DEFAULT NULL,
    height DECIMAL(5,2) DEFAULT NULL,
    weight DECIMAL(5,2) DEFAULT NULL,
    allergies TEXT DEFAULT NULL,
    emergency_contact_name VARCHAR(255) DEFAULT NULL,
    emergency_contact_relation VARCHAR(100) DEFAULT NULL,
    emergency_contact_phone VARCHAR(20) DEFAULT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Subscription plans table
CREATE TABLE subscription_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    duration_days INT NOT NULL,
    features JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User subscriptions table
CREATE TABLE user_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    plan_id INT NOT NULL,
    status ENUM('active', 'expired', 'cancelled', 'pending') DEFAULT 'pending',
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP NULL,
    last_payment_date TIMESTAMP NULL,
    next_payment_date TIMESTAMP NULL,
    payment_method VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (plan_id) REFERENCES subscription_plans(id) ON DELETE CASCADE
);

-- Payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subscription_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'NGN',
    payment_method VARCHAR(100) NOT NULL,
    transaction_id VARCHAR(255) UNIQUE,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_data JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subscription_id) REFERENCES user_subscriptions(id) ON DELETE CASCADE
);

-- Health tips table
CREATE TABLE health_tips (
    id INT AUTO_INCREMENT PRIMARY KEY,
    header VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    category VARCHAR(100) NOT NULL,
    icon VARCHAR(100) DEFAULT 'fas fa-heart',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Doctor speeches table
CREATE TABLE doctor_speeches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_name VARCHAR(255) NOT NULL,
    header VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    icon VARCHAR(100) DEFAULT 'fas fa-user-md',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Appointments table
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    doctor_name VARCHAR(255) NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    appointment_type VARCHAR(100) NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled', 'rescheduled') DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Medications table
CREATE TABLE medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    medication_name VARCHAR(255) NOT NULL,
    dosage VARCHAR(100) NOT NULL,
    frequency VARCHAR(100) NOT NULL,
    time_to_take TIME NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    is_active BOOLEAN DEFAULT TRUE,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Medical records table
CREATE TABLE medical_records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    record_type VARCHAR(100) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    file_path VARCHAR(255),
    doctor_name VARCHAR(255),
    record_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Messages table
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    sender_type ENUM('user', 'doctor', 'system') DEFAULT 'system',
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default subscription plans
INSERT INTO subscription_plans (name, description, price, duration_days, features) VALUES
('Basic', 'Basic health monitoring and tips', 0.00, 30, '["Health Tips", "Basic Dashboard", "Limited Support"]'),
('Premium', 'Full access to all features including AI doctor', 5000.00, 30, '["AI Doctor Consultation", "Priority Support", "Advanced Analytics", "Unlimited Health Tips", "Medical Records Storage"]'),
('Annual Premium', 'Premium plan with annual discount', 50000.00, 365, '["AI Doctor Consultation", "Priority Support", "Advanced Analytics", "Unlimited Health Tips", "Medical Records Storage", "Annual Discount"]');

-- Insert sample health tips
INSERT INTO health_tips (header, content, category, icon) VALUES
('Stay Hydrated', 'Drink at least 8 glasses of water daily to maintain good health and proper body function.', 'General Health', 'fas fa-tint'),
('Regular Exercise', 'Aim for at least 30 minutes of moderate exercise daily to keep your heart healthy.', 'Fitness', 'fas fa-dumbbell'),
('Healthy Sleep', 'Get 7-9 hours of quality sleep each night to support your immune system and mental health.', 'Sleep', 'fas fa-bed'),
('Balanced Diet', 'Include fruits, vegetables, lean proteins, and whole grains in your daily meals.', 'Nutrition', 'fas fa-apple-alt'),
('Stress Management', 'Practice meditation or deep breathing exercises to manage daily stress effectively.', 'Mental Health', 'fas fa-brain');

-- Insert sample doctor speeches
INSERT INTO doctor_speeches (doctor_name, header, content, icon) VALUES
('Dr. Sarah Johnson', 'Daily Wellness', 'Remember to take your medications on time and stay active. Your health is your wealth!', 'fas fa-user-md'),
('Dr. Michael Chen', 'Preventive Care', 'Regular check-ups are essential. Don\'t wait until you\'re sick to see a doctor.', 'fas fa-stethoscope'),
('Dr. Emily Davis', 'Mental Health', 'Your mental health is just as important as your physical health. Take time to relax and de-stress.', 'fas fa-heart');

-- Create indexes for better performance
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_subscriptions_user_id ON user_subscriptions(user_id);
CREATE INDEX idx_subscriptions_status ON user_subscriptions(status);
CREATE INDEX idx_payments_user_id ON payments(user_id);
CREATE INDEX idx_payments_status ON payments(status);
CREATE INDEX idx_appointments_user_id ON appointments(user_id);
CREATE INDEX idx_medications_user_id ON medications(user_id);
CREATE INDEX idx_messages_user_id ON messages(user_id); 