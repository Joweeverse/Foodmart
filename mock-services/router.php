<?php
header('Content-Type: application/json');

$data   = json_decode(file_get_contents(__DIR__ . '/data.json'), true);
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$legacy = file_exists(__DIR__ . '/nopage.flag'); // simulasi Sales tanpa pagination

function out($payload, $code = 200) {
    http_response_code($code);
    echo json_encode($payload);
    exit;
}

if ($path === '/sales') {
    $year  = (int) ($_GET['year'] ?? 1997);
    $page  = max(1, (int) ($_GET['page'] ?? 1));
    $limit = max(1, (int) ($_GET['limit'] ?? 1000));

    $rows = array_values(array_filter(
        $data['sales'],
        fn($r) => (int) substr($r['date'], 0, 4) === $year
    ));

    if ($legacy) {
        out(['message' => 'success', 'data' => $rows]);
    }
    out([
        'message' => 'success',
        'page'    => $page,
        'limit'   => $limit,
        'total'   => count($rows),
        'data'    => array_slice($rows, ($page - 1) * $limit, $limit),
    ]);
}

if (preg_match('#^/products/(\d+)$#', $path, $m)) {
    foreach ($data['products'] as $p) {
        if ($p['product_id'] == $m[1]) out(['message' => 'success', 'data' => $p]);
    }
    out(['message' => 'not found'], 404);
}

if (preg_match('#^/customers/(\d+)$#', $path, $m)) {
    foreach ($data['customers'] as $c) {
        if ($c['customer_id'] == $m[1]) out(['message' => 'success', 'data' => $c]);
    }
    out(['message' => 'not found'], 404);
}

$lists = ['/products' => 'products', '/customers' => 'customers',
          '/stores' => 'stores', '/inventory' => 'inventory',
          '/promotions' => 'promotions'];
if (isset($lists[$path])) {
    out(['message' => 'success', 'data' => $data[$lists[$path]]]);
}

out(['message' => 'not found'], 404);