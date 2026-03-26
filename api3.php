<?php
header('Content-Type: application/json');
include 'config.php';  // Includes session_start() and DB connection

$action = $_GET['action'] ?? 'load_programs';

try {
    if ($action === 'login') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$username || !$password) {
            throw new Exception('Missing username or password');
        }

        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            echo json_encode(['success' => true, 'message' => 'Login successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        }
    } elseif ($action === 'signup') {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
        if (!$username || !$email || !$password) {
            throw new Exception('Missing username, email, or password');
        }

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        echo json_encode(['success' => true, 'message' => 'Signup successful']);
    } elseif ($action === 'check_auth') {
        echo json_encode(['logged_in' => isset($_SESSION['user_id'])]);
    } elseif ($action === 'load_programs') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT * FROM profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("SELECT * FROM programs WHERE end_date >= CURDATE() ORDER BY posted_date DESC");
        $stmt->execute();
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $filtered = [];
        foreach ($programs as $program) {
            if ($program['type'] === 'certification') {
                $filtered[] = $program;  // Always show certs
            } elseif ($program['type'] === 'internship' && $profile) {
                // Eligibility: CS/IT degree and passing year >= 2020
                if ((stripos($profile['degree'], 'computer') !== false || stripos($profile['degree'], 'it') !== false) && $profile['passing_year'] >= 2020) {
                    $filtered[] = $program;
                }
            }
        }

        echo json_encode(['success' => true, 'data' => $filtered]);
    } elseif ($action === 'save_profile') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $degree = trim($_POST['degree'] ?? '');
        $passing_year = (int)($_POST['passing_year'] ?? 0);
        $skills = trim($_POST['skills'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO profiles (user_id, degree, passing_year, skills, completed) VALUES (?, ?, ?, ?, 1) ON DUPLICATE KEY UPDATE degree = VALUES(degree), passing_year = VALUES(passing_year), skills = VALUES(skills), completed = 1");
        $stmt->execute([$user_id, $degree, $passing_year, $skills]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'save_project') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $stmt = $pdo->prepare("INSERT INTO projects (user_id, title, description, completed) VALUES (?, ?, ?, 1)");
        $stmt->execute([$user_id, $title, $description]);
        echo json_encode(['success' => true]);
    } elseif ($action === 'check_profile') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not authenticated']);
            exit;
        }
        $user_id = $_SESSION['user_id'];
        $stmt = $pdo->prepare("SELECT completed FROM profiles WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $profile = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(['completed' => $profile ? (bool)$profile['completed'] : false]);
    } else {
        throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
