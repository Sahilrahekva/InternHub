<?php
// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
include 'config.php';  // Assumes session_start() is here

// Your RapidAPI Key (Get free from https://rapidapi.com/apidojo/api/jsearch)
$rapidapi_key = '';  // Leave empty to use DB samples; replace with your key for live data
$top_mncs = ['Google', 'Microsoft', 'Amazon', 'IBM', 'Meta', 'Apple', 'Oracle', 'Cisco', 'Intel', 'Samsung'];

$action = $_GET['action'] ?? 'load_programs';

try {
    if ($action === 'login') {
        if (!isset($_POST['username']) || !isset($_POST['password'])) {
            throw new Exception('Missing username or password');
        }
        $username = trim($_POST['username']);
        $password = $_POST['password'];

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
        if (!isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['password'])) {
            throw new Exception('Missing fields');
        }
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

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

        $today = date('Y-m-d');
        $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
        $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));

        // Fetch from DB (use samples if no API key)
        $stmt = $pdo->prepare("
            SELECT * FROM programs 
            WHERE (end_date >= ? OR posted_date >= ?) AND status != 'Expired'
            ORDER BY posted_date DESC LIMIT 20
        ");
        $stmt->execute([$today, $thirtyDaysAgo]);
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If API key is set, try fetching live data (comment out if not needed)
        if (!empty($rapidapi_key)) {
            // Similar logic as before, but simplified
            // ... (keep your existing API fetch code here if desired)
        }

        // Filter based on profile
        $filteredPrograms = [];
        foreach ($programs as $program) {
            if ($program['type'] === 'certification') {
                $filteredPrograms[] = $program;
            } elseif ($program['type'] === 'internship' && $profile) {
                if (stripos($profile['degree'], 'computer') !== false || stripos($profile['degree'], 'it') !== false) {
                    if ($profile['passing_year'] >= 2020) {
                        $filteredPrograms[] = $program;
                    }
                }
            }
        }

        echo json_encode(['success' => true, 'data' => $filteredPrograms]);
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
