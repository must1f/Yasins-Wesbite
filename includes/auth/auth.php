<?php
/**
 * Authentication Helper Functions
 */

require_once __DIR__ . '/../../config/config.php';

/**
 * Register a new user
 */
function registerUser($name, $email, $password, $userType) {
    try {
        $pdo = getDatabaseConnection();

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Email already registered'];
        }

        // Hash password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insert user
        $stmt = $pdo->prepare("
            INSERT INTO users (name, email, password_hash, user_type)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$name, $email, $passwordHash, $userType]);
        $userId = $pdo->lastInsertId();

        // Create profile based on user type
        if ($userType === 'applicant') {
            $stmt = $pdo->prepare("INSERT INTO applicant_profiles (user_id) VALUES (?)");
            $stmt->execute([$userId]);
        } elseif ($userType === 'employer') {
            $stmt = $pdo->prepare("INSERT INTO employer_profiles (user_id, company_name) VALUES (?, ?)");
            $stmt->execute([$userId, $name]);
        }

        return ['success' => true, 'message' => 'Registration successful', 'user_id' => $userId];

    } catch (PDOException $e) {
        error_log("Registration Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed. Please try again.'];
    }
}

/**
 * Login user
 */
function loginUser($email, $password) {
    try {
        $pdo = getDatabaseConnection();

        $stmt = $pdo->prepare("
            SELECT user_id, name, email, password_hash, user_type, is_active
            FROM users
            WHERE email = ?
        ");

        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Account is deactivated'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }

        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();

        return ['success' => true, 'message' => 'Login successful', 'user_type' => $user['user_type']];

    } catch (PDOException $e) {
        error_log("Login Error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed. Please try again.'];
    }
}

/**
 * Logout user
 */
function logoutUser() {
    session_unset();
    session_destroy();
    session_start();
}

/**
 * Check session timeout
 */
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        logoutUser();
        return false;
    }
    $_SESSION['last_activity'] = time();
    return true;
}

/**
 * Get user profile
 */
function getUserProfile($userId, $userType) {
    try {
        $pdo = getDatabaseConnection();

        if ($userType === 'applicant') {
            $stmt = $pdo->prepare("
                SELECT u.*, ap.*
                FROM users u
                LEFT JOIN applicant_profiles ap ON u.user_id = ap.user_id
                WHERE u.user_id = ?
            ");
        } else {
            $stmt = $pdo->prepare("
                SELECT u.*, ep.*
                FROM users u
                LEFT JOIN employer_profiles ep ON u.user_id = ep.user_id
                WHERE u.user_id = ?
            ");
        }

        $stmt->execute([$userId]);
        return $stmt->fetch();

    } catch (PDOException $e) {
        error_log("Get Profile Error: " . $e->getMessage());
        return null;
    }
}
?>
