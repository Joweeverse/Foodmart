<?php
namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Libraries\AnalyticsCalculator;
use App\Libraries\CustomerGateway;
use App\Libraries\ProductGateway;
use App\Libraries\SalesGateway;
use App\Libraries\ServiceClient;

class AnalyticsController extends ResourceController
{
    protected function loadSales(int $year): ?array
    {
        $key = 'sales_rows_' . $year;
        $hit = cache($key);
        if ($hit !== null) return $hit;

        $rows = (new SalesGateway())->all($year);
        if ($rows !== null) cache()->save($key, $rows, 300); // cache 5 menit
        return $rows;
    }

    protected function params(): array
    {
        $year  = (int) ($this->request->getGet('year') ?? 1997);
        $limit = (int) ($this->request->getGet('limit') ?? 10);
        return [$year, max(1, min(100, $limit))];
    }

    public function topProducts()
    {
        [$year, $limit] = $this->params();

        $sales = $this->loadSales($year);
        if ($sales === null) {
            return $this->respond(['message' => 'Sales Service unavailable'], 502);
        }

        $top = (new AnalyticsCalculator())->topProducts($sales, $limit);

        $pg = new ProductGateway();
        foreach ($top as &$row) {
            $p = $pg->find($row['product_id']);
            $row['product_name'] = $p['name'] ?? null; // tetap tampil walau Product Service mati
        }
        unset($row);

        return $this->respond(['message' => 'success', 'year' => $year, 'data' => $top], 200);
    }

    public function topCustomers()
    {
        [$year, $limit] = $this->params();

        $sales = $this->loadSales($year);
        if ($sales === null) {
            return $this->respond(['message' => 'Sales Service unavailable'], 502);
        }

        $top = (new AnalyticsCalculator())->topCustomers($sales, $limit);

        $cg = new CustomerGateway();
        foreach ($top as &$row) {
            $c = $cg->find($row['customer_id']);
            $row['customer_name'] = $c['name'] ?? null;
        }
        unset($row);

        return $this->respond(['message' => 'success', 'year' => $year, 'data' => $top], 200);
    }

    public function salesTrend()
    {
        [$year] = $this->params();

        $sales = $this->loadSales($year);
        if ($sales === null) {
            return $this->respond(['message' => 'Sales Service unavailable'], 502);
        }

        $trend = (new AnalyticsCalculator())->salesTrend($sales);
        return $this->respond(['message' => 'success', 'year' => $year, 'data' => $trend], 200);
    }

    public function health()
    {
        $cfg = config('Integration');
        $targets = [
            'customer'  => [$cfg->customerUrl,  '/customers'],
            'product'   => [$cfg->productUrl,   '/products'],
            'store'     => [$cfg->storeUrl,     '/stores'],
            'sales'     => [$cfg->salesUrl,     '/sales'],
            'inventory' => [$cfg->inventoryUrl, '/inventory'],
            'promotion' => [$cfg->promotionUrl, '/promotions'],
        ];

        $result = [];
        foreach ($targets as $name => [$url, $path]) {
            $ok = $url !== '' && (new ServiceClient($url))->get($path, ['limit' => 1]) !== null;
            $result[$name] = $ok ? 'up' : 'down';
        }

        return $this->respond(['message' => 'success', 'services' => $result], 200);
    }
}