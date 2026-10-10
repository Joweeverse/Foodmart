<?php
$d = json_decode(file_get_contents(__DIR__ . '/data.json'), true);
$u = []; $c = []; $m = [];
foreach ($d['sales'] as $s) {
    $mo = substr($s['date'], 0, 7);
    $u[$s['product_id']]  = ($u[$s['product_id']] ?? 0) + $s['unit_sales'];
    $c[$s['customer_id']] = ($c[$s['customer_id']] ?? 0) + $s['store_sales'];
    $m[$mo]               = ($m[$mo] ?? 0) + $s['store_sales'];
}
arsort($u); arsort($c); ksort($m);
echo "Top 3 produk (unit):\n";    print_r(array_slice($u, 0, 3, true));
echo "Top 3 customer (spent):\n"; print_r(array_map(fn($v) => round($v, 2), array_slice($c, 0, 3, true)));
echo "Revenue per bulan:\n";      print_r(array_map(fn($v) => round($v, 2), $m));