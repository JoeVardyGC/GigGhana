<?php
/**
 * GigGhana — ajax-search.php
 * Live AJAX search endpoint for jobs, providers, categories, and skills.
 * Location: gigghana/ajax-search.php
 *
 * Accepts GET parameters:
 *   ?q=search_term          — the search query (required)
 *   &type=jobs|providers|all — what to search (default: all)
 *   &limit=10               — max results per section (default: 8)
 *   &category=3             — optional category filter
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Only allow AJAX requests
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// Rate limit basic guard
session_start();
$now   = time();
$key   = 'ajax_search_hits';
$limit = 60; // max requests per minute
if (!isset($_SESSION[$key])) $_SESSION[$key] = ['count' => 0, 'start' => $now];
if ($now - $_SESSION[$key]['start'] > 60) {
    $_SESSION[$key] = ['count' => 0, 'start' => $now];
}
$_SESSION[$key]['count']++;
if ($_SESSION[$key]['count'] > $limit) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests. Please slow down.']);
    exit;
}

// Parse & sanitize inputs
$q      = trim(sanitize($_GET['q']        ?? ''));
$type   = sanitize($_GET['type']           ?? 'all');
$limit  = min(12, max(1, (int)($_GET['limit'] ?? 8)));
$catId  = (int)($_GET['category']          ?? 0);

// Require minimum 2 characters
if (strlen($q) < 2) {
    echo json_encode(['jobs' => [], 'providers' => [], 'categories' => [], 'query' => $q]);
    exit;
}

$results = [
    'query'      => $q,
    'jobs'       => [],
    'providers'  => [],
    'categories' => [],
    'skills'     => [],
    'total'      => 0,
];

try {
    $db     = getDB();
    $search = "%{$q}%";

    // ── JOBS ───────────────────────────────────────────────────────────────
    if (in_array($type, ['all', 'jobs'])) {
        $jobParams = [$search, $search, $search];
        $catClause = '';
        if ($catId) {
            $catClause = 'AND j.category_id = ?';
            $jobParams[] = $catId;
        }
        $jobParams[] = $limit;

        $stmt = $db->prepare("
            SELECT
                j.id,
                j.uuid,
                j.title,
                j.description,
                j.budget_min,
                j.budget_max,
                j.budget_type,
                j.location_type,
                j.experience_level,
                j.is_urgent,
                j.is_featured,
                j.proposal_count,
                j.created_at,
                c.name  AS category_name,
                c.id    AS category_id,
                u.first_name,
                u.last_name
            FROM jobs j
            LEFT JOIN categories c ON c.id = j.category_id
            JOIN  users u ON u.id = j.client_id
            WHERE j.status = 'open'
              AND (
                    j.title       LIKE ?
                 OR j.description LIKE ?
                 OR c.name        LIKE ?
              )
              $catClause
            ORDER BY
                j.is_urgent   DESC,
                j.is_featured DESC,
                j.created_at  DESC
            LIMIT ?
        ");
        $stmt->execute($jobParams);
        $jobs = $stmt->fetchAll();

        foreach ($jobs as &$job) {
            $job['budget_formatted'] = formatCurrency((float)$job['budget_min']);
            if ((float)$job['budget_max'] > (float)$job['budget_min']) {
                $job['budget_formatted'] .= ' – ' . formatCurrency((float)$job['budget_max']);
            }
            $job['time_ago']    = timeAgo($job['created_at']);
            $job['url']         = APP_URL . '/job-details.php?id=' . $job['id'];
            $job['description'] = mb_substr(strip_tags($job['description']), 0, 100) . '…';
        }
        unset($job);
        $results['jobs'] = $jobs;
    }

    // ── PROVIDERS ──────────────────────────────────────────────────────────
    if (in_array($type, ['all', 'providers'])) {
        $provParams = [$search, $search, $search, $search, $limit];

        $stmt = $db->prepare("
            SELECT
                u.id        AS user_id,
                u.first_name,
                u.last_name,
                u.avatar,
                u.location,
                p.id        AS provider_id,
                p.tagline,
                p.hourly_rate,
                p.availability,
                p.experience_level,
                p.rating_avg,
                p.rating_count,
                p.completed_jobs,
                p.is_verified,
                p.is_featured
            FROM users u
            JOIN providers p ON p.user_id = u.id
            WHERE u.is_active = 1
              AND u.role = 'provider'
              AND (
                    CONCAT(u.first_name, ' ', u.last_name) LIKE ?
                 OR p.tagline  LIKE ?
                 OR u.location LIKE ?
                 OR EXISTS (
                        SELECT 1
                        FROM provider_skills ps
                        JOIN skills s ON s.id = ps.skill_id
                        WHERE ps.provider_id = p.id
                          AND s.name LIKE ?
                    )
              )
            ORDER BY
                p.is_featured DESC,
                p.rating_avg  DESC,
                p.rating_count DESC
            LIMIT ?
        ");
        $stmt->execute($provParams);
        $providers = $stmt->fetchAll();

        foreach ($providers as &$prov) {
            // Fetch top 3 skills
            $stSk = $db->prepare("
                SELECT s.name
                FROM provider_skills ps
                JOIN skills s ON s.id = ps.skill_id
                WHERE ps.provider_id = ?
                LIMIT 3
            ");
            $stSk->execute([$prov['provider_id']]);
            $prov['skills']         = $stSk->fetchAll(PDO::FETCH_COLUMN);
            $prov['rate_formatted'] = formatCurrency((float)$prov['hourly_rate']) . '/hr';
            $prov['url']            = APP_URL . '/profile.php?id=' . $prov['user_id'];
            $prov['initials']       = strtoupper(
                substr($prov['first_name'], 0, 1) . substr($prov['last_name'], 0, 1)
            );
            $prov['stars'] = (function($r) {
                $out = '';
                for ($i = 1; $i <= 5; $i++) {
                    $out .= $r >= $i ? '★' : ($r >= $i - 0.5 ? '✦' : '☆');
                }
                return $out;
            })((float)$prov['rating_avg']);
        }
        unset($prov);
        $results['providers'] = $providers;
    }

    // ── CATEGORIES ─────────────────────────────────────────────────────────
    if (in_array($type, ['all', 'categories'])) {
        $stmt = $db->prepare("
            SELECT
                c.id,
                c.name,
                c.slug,
                c.icon,
                c.description,
                (SELECT COUNT(*) FROM jobs j WHERE j.category_id = c.id AND j.status = 'open') AS open_jobs
            FROM categories c
            WHERE c.is_active = 1
              AND (c.name LIKE ? OR c.description LIKE ?)
            ORDER BY open_jobs DESC
            LIMIT 5
        ");
        $stmt->execute([$search, $search]);
        $cats = $stmt->fetchAll();

        foreach ($cats as &$cat) {
            $cat['url'] = APP_URL . '/search/jobs.php?category=' . $cat['id'];
        }
        unset($cat);
        $results['categories'] = $cats;
    }

    // ── SKILLS ─────────────────────────────────────────────────────────────
    if (in_array($type, ['all', 'skills'])) {
        $stmt = $db->prepare("
            SELECT
                s.id,
                s.name,
                s.slug,
                c.name AS category_name,
                (SELECT COUNT(*) FROM job_skills js WHERE js.skill_id = s.id) AS job_count
            FROM skills s
            LEFT JOIN categories c ON c.id = s.category_id
            WHERE s.is_active = 1
              AND s.name LIKE ?
            ORDER BY job_count DESC
            LIMIT 6
        ");
        $stmt->execute([$search]);
        $skills = $stmt->fetchAll();

        foreach ($skills as &$sk) {
            $sk['url'] = APP_URL . '/search/providers.php?q=' . urlencode($sk['name']);
        }
        unset($sk);
        $results['skills'] = $skills;
    }

    // Total results count
    $results['total'] = count($results['jobs'])
                      + count($results['providers'])
                      + count($results['categories'])
                      + count($results['skills']);

} catch (Exception $e) {
    error_log('ajax-search error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Search temporarily unavailable.']);
    exit;
}

echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);