<?php
mt_srand(42);
$data = ['sales' => [], 'products' => [], 'customers' => [],
         'stores' => [], 'inventory' => [], 'promotions' => []];

for ($i = 1; $i <= 10; $i++) {
    $data['products'][]  = ['product_id' => $i, 'product_name' => "Mock Product $i"];
    $data['inventory'][] = ['product_id' => $i, 'stock' => mt_rand(0, 200)];
}
for ($i = 1; $i <= 8; $i++) {
    $data['customers'][] = ['customer_id' => $i, 'fullname' => "Mock Customer $i"];
}
for ($i = 1; $i <= 3; $i++) {
    $data['stores'][] = ['store_id' => $i, 'store_name' => "Mock Store $i"];
}
$data['promotions'][] = ['promotion_id' => 1, 'promotion_name' => 'Mock Promo'];

for ($i = 0; $i < 300; $i++) {
    $sales = round(mt_rand(100, 2000) / 100, 2);
    $data['sales'][] = [
        'product_id'  => mt_rand(1, 10),
        'customer_id' => mt_rand(1, 8),
        'store_id'    => mt_rand(1, 3),
        'date'        => sprintf('1997-%02d-%02d', mt_rand(1, 12), mt_rand(1, 28)),
        'store_sales' => $sales,
        'store_cost'  => round($sales * 0.6, 2),
        'unit_sales'  => mt_rand(1, 5),
    ];
}
file_put_contents(__DIR__ . '/data.json', json_encode($data, JSON_PRETTY_PRINT));
echo "data.json dibuat\n";