<?php
header('Content-Type: application/json');
error_reporting(0); // ✅ Prevent HTML warnings in JSON
ini_set('display_errors', 0);

include 'config.php';

// 🔴 Put REAL key or keep empty
$rapidapi_key = '';  

$top_mncs = ['Google','Microsoft','Amazon','IBM','Meta','Apple','Oracle','Cisco','Intel','Samsung'];

try {
    $today = date('Y-m-d');
    $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
    $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));

    $stmt = $pdo->prepare("
        SELECT * FROM programs 
        WHERE posted_date >= ? 
        ORDER BY posted_date DESC 
        LIMIT 20
    ");
    $stmt->execute([$thirtyDaysAgo]);
    $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    /* =========================
       MICROSOFT RSS (SAFE)
       ========================= */
    $ms_url = 'https://learn.microsoft.com/en-us/training/browse/rss/?sort=Newest';
    $ms_response = @file_get_contents($ms_url); // ✅ suppress warnings

    if ($ms_response) {
        libxml_use_internal_errors(true); // ✅ prevent XML HTML errors
        $xml = simplexml_load_string($ms_response);

        if ($xml && isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                if (count($programs) >= 20) break;

                $title = (string)$item->title;
                if (stripos($title, 'free') !== false || stripos($title, 'certification') !== false) {
                    $programs[] = [
                        'company' => 'Microsoft',
                        'type' => 'certification',
                        'title' => $title,
                        'description' => substr(strip_tags((string)$item->description), 0, 200) . '...',
                        'start_date' => date('Y-m-d'),
                        'end_date' => date('Y-m-d', strtotime('+6 months')),
                        'status' => 'Active',
                        'source' => 'microsoft'
                    ];
                }
            }
        }
    }

    /* =========================
       STATUS FILTER
       ========================= */
    foreach ($programs as &$p) {
        $posted = strtotime($p['posted_date'] ?? $today);
        if ($posted >= strtotime($sevenDaysAgo)) {
            $p['status'] = 'Launching Now';
        } elseif ($posted >= strtotime($thirtyDaysAgo)) {
            $p['status'] = 'Active';
        } else {
            $p['status'] = 'Expired';
        }
    }

    $programs = array_values(array_filter($programs, fn($p) => $p['status'] !== 'Expired'));

    echo json_encode([
        'success' => true,
        'data' => $programs
    ]);
    exit;

} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'data' => [],
        'error' => 'Server error'
    ]);
    exit;
}
