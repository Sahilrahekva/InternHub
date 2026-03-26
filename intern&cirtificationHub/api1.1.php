<?php
header('Content-Type: application/json');
include 'config.php';

// Your RapidAPI Key (Get free from https://rapidapi.com/apidojo/api/jsearch)
$rapidapi_key = 'YOUR_RAPIDAPI_KEY';  // Replace with your actual key! If empty, falls back to DB samples.
$top_mncs = ['Google', 'Microsoft', 'Amazon', 'IBM', 'Meta', 'Apple', 'Oracle', 'Cisco', 'Intel', 'Samsung'];

try {
    $today = date('Y-m-d');
    $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));  // Only current postings
    $sevenDaysAgo = date('Y-m-d', strtotime('-7 days'));

    // Check cache (DB) first; refresh if older than 24 hours
    $cache_stmt = $pdo->prepare("
        SELECT * FROM programs 
        WHERE posted_date >= ? AND source != 'fallback'
        ORDER BY posted_date DESC
    ");
    $cache_stmt->execute([$thirtyDaysAgo]);
    $cached = $cache_stmt->fetchAll(PDO::FETCH_ASSOC);

    $use_cache = !empty($cached) && (strtotime($cached[0]['updated_at']) > strtotime('-1 day'));

    if (!$use_cache && !empty($rapidapi_key)) {
        // Clear old cache
        $pdo->prepare("DELETE FROM programs WHERE posted_date < ?")->execute([$thirtyDaysAgo]);

        $programs = [];
        $page = 1;
        $per_page = 10;  // Limit to avoid rate limits

        // Fetch Internships via JSearch API
        foreach ($top_mncs as $company) {
            $query = urlencode("internship $company");
            $url = "https://jsearch.p.rapidapi.com/search?query=$query&num_pages=1&page=$page&date_posted=all";

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "X-RapidAPI-Key: $rapidapi_key",
                    "X-RapidAPI-Host: jsearch.p.rapidapi.com"
                ],
            ]);
            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($http_code === 200) {
                $data = json_decode($response, true);
                if (isset($data['data']) && is_array($data['data'])) {
                    foreach ($data['data'] as $job) {
                        if (count($programs) >= $per_page * count($top_mncs)) break;  // Limit total

                        // Parse to our format (JSearch has job_title, employer_name, job_description, job_posted_at_date)
                        $start_date = date('Y-m-d', strtotime($job['job_posted_at_date'] ?? $today));  // Approximate
                        $end_date = date('Y-m-d', strtotime('+3 months', strtotime($start_date)));  // Assume 3 months

                        $status = (strtotime($start_date) >= strtotime($sevenDaysAgo)) ? 'Launching Now' : 'Active';

                        // Cache in DB
                        $insert_stmt = $pdo->prepare("
                            INSERT INTO programs (company, type, title, description, start_date, end_date, status, posted_date, source) 
                            VALUES (?, 'internship', ?, ?, ?, ?, ?, ?, 'jsearch')
                            ON DUPLICATE KEY UPDATE description = VALUES(description), updated_at = CURRENT_TIMESTAMP
                        ");
                        $insert_stmt->execute([
                            $job['employer_name'] ?? $company,
                            $job['job_title'] ?? "Internship at $company",
                            substr($job['job_description'] ?? 'Exciting internship opportunity.', 0, 200) . '...',
                            $start_date, $end_date, $status, $start_date
                        ]);

                        $programs[] = [
                            'id' => $pdo->lastInsertId(),
                            'company' => $job['employer_name'] ?? $company,
                            'type' => 'internship',
                            'title' => $job['job_title'] ?? "Internship at $company",
                            'description' => substr($job['job_description'] ?? 'Real-time internship from API.', 0, 200) . '...',
                            'start_date' => $start_date,
                            'end_date' => $end_date,
                            'status' => $status,
                            'source' => 'jsearch'
                        ];
                    }
                }
            }
        }

        // Fetch Certifications (Example: Microsoft Learn RSS for free certs; parse simple XML)
        $ms_url = 'https://learn.microsoft.com/en-us/training/browse/rss/?sort=Newest';
        $ms_curl = curl_init();
        curl_setopt_array($ms_curl, [
            CURLOPT_URL => $ms_url,
            CURLOPT_RETURNTRANSFER => true,
        ]);
        $ms_response = curl_exec($ms_curl);
        curl_close($ms_curl);

        if ($ms_response) {
            $xml = simplexml_load_string($ms_response);
            if ($xml) {
                foreach ($xml->channel->item as $item) {
                    if (count($programs) >= 20) break;  // Limit
                    $title = (string)$item->title;
                    if (stripos($title, 'free') !== false || stripos($title, 'certification') !== false) {
                        $start_date = date('Y-m-d', strtotime((string)$item->pubDate));
                        $end_date = date('Y-m-d', strtotime('+6 months'));  // Ongoing
                        $status = (strtotime($start_date) >= strtotime($sevenDaysAgo)) ? 'Launching Now' : 'Active';

                        $insert_stmt = $pdo->prepare("
                            INSERT INTO programs (company, type, title, description, start_date, end_date, status, posted_date, source) 
                            VALUES ('Microsoft', 'certification', ?, ?, ?, ?, ?, ?, 'microsoft')
                        ");
                        $insert_stmt->execute([
                            $title,
                            substr((string)$item->description, 0, 200) . '...',
                            $start_date, $end_date, $status, $start_date
                        ]);

                        $programs[] = [
                            'company' => 'Microsoft',
                            'type' => 'certification',
                            'title' => $title,
                            'description' => substr((string)$item->description, 0, 200) . '...',
                            'start_date' => $start_date,
                            'end_date' => $end_date,
                            'status' => $status,
                            'source' => 'microsoft'
                        ];
                    }
                }
            }
        }

        // Similar for Google (use their public skills boost RSS or endpoint)
        // Add more if needed (e.g., AWS free tier announcements).

        // Fetch from DB now (cached)
        $stmt = $pdo->prepare("
            SELECT * FROM programs 
            WHERE posted_date >= ? 
            ORDER BY posted_date DESC LIMIT 20
        ");
        $stmt->execute([$thirtyDaysAgo]);
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        // Use cached or sample data
        $stmt = $pdo->prepare("
            SELECT * FROM programs 
            WHERE end_date >= ? OR posted_date >= ?
            ORDER BY posted_date DESC LIMIT 20
        ");
        $stmt->execute([$today, $thirtyDaysAgo]);
        $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update statuses dynamically
    foreach ($programs as &$program) {
        $posted = strtotime($program['posted_date'] ?? $today);
        if (($posted >= strtotime($sevenDaysAgo))) {
            $program['status'] = 'Launching Now';
        } elseif ($posted >= strtotime($thirtyDaysAgo)) {
            $program['status'] = 'Active';
        } else {
            $program['status'] = 'Expired';  // Hide if old
        }
        // Filter out expired
        if ($program['status'] === 'Expired') continue;
    }

    // Filter only non-expired
    $programs = array_filter($programs, function($p) { return $p['status'] !== 'Expired'; });

    echo json_encode(['success' => true, 'data' => array_values($programs)]);

} catch (Exception $e) {
    // Fallback to samples
    $fallback_stmt = $pdo->prepare("SELECT * FROM programs WHERE status != 'Expired' LIMIT 10");
    $fallback_stmt->execute();
    $fallback = $fallback_stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $fallback ?: [], 'error' => 'API fetch failed, using samples: ' . $e->getMessage()]);
}
?>
