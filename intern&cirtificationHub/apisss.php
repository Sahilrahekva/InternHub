<?php
header('Content-Type: application/json');
include 'config.php';

try {
    // Fetch active/launching programs (simulate "current" by checking dates)
    $today = date('Y-m-d');
    $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));  // For "launching now"

    $stmt = $pdo->prepare("
        SELECT * FROM programs 
        WHERE end_date >= ? AND start_date <= ? 
        ORDER BY start_date DESC
    ");
    $stmt->execute([$today, $today]);
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Simulate API enhancement: In real use, fetch from external API here (e.g., LinkedIn)
    // Example: $externalData = json_decode(file_get_contents('https://api.linkedin.com/v2/jobs'), true);
    // Then merge with DB and update status.

    // Update status dynamically
    foreach ($programs as &$program) {
        if ($program['start_date'] >= $sevenDaysAgo) {
            $program['status'] = 'Launching Now';
        } else {
            $program['status'] = 'Active';
        }
        // Hide expired (already filtered, but for completeness)
        if ($program['end_date'] < $today) {
            $program['status'] = 'Expired';
        }
    }

    echo json_encode(['success' => true, 'data' => $programs]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
