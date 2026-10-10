<?php
namespace App\Libraries;

class AnalyticsCalculator
{
    public function topProducts(array $sales, int $limit): array
    {
        $agg = [];
        foreach ($sales as $s) {
            $id = $s['product_id'];
            $agg[$id]['product_id']    = $id;
            $agg[$id]['total_units']   = ($agg[$id]['total_units'] ?? 0) + $s['units'];
            $agg[$id]['total_revenue'] = ($agg[$id]['total_revenue'] ?? 0) + $s['revenue'];
        }
        usort($agg, fn($a, $b) => $b['total_units'] <=> $a['total_units']);
        $agg = array_slice($agg, 0, $limit);
        foreach ($agg as &$row) {
            $row['total_revenue'] = round($row['total_revenue'], 2);
        }
        unset($row);
        return $agg;
    }

    public function topCustomers(array $sales, int $limit): array
    {
        $agg = [];
        foreach ($sales as $s) {
            $id = $s['customer_id'];
            $agg[$id]['customer_id']  = $id;
            $agg[$id]['total_spent']  = ($agg[$id]['total_spent'] ?? 0) + $s['revenue'];
            $agg[$id]['total_orders'] = ($agg[$id]['total_orders'] ?? 0) + 1;
        }
        usort($agg, fn($a, $b) => $b['total_spent'] <=> $a['total_spent']);
        $agg = array_slice($agg, 0, $limit);
        foreach ($agg as &$row) {
            $row['total_spent'] = round($row['total_spent'], 2);
        }
        unset($row);
        return $agg;
    }

    public function salesTrend(array $sales): array
    {
        $agg = [];
        foreach ($sales as $s) {
            $month = $s['date'] !== '' ? substr($s['date'], 0, 7) : 'unknown'; // YYYY-MM
            $agg[$month]['period']  = $month;
            $agg[$month]['revenue'] = ($agg[$month]['revenue'] ?? 0) + $s['revenue'];
            $agg[$month]['cost']    = ($agg[$month]['cost'] ?? 0) + $s['cost'];
            $agg[$month]['units']   = ($agg[$month]['units'] ?? 0) + $s['units'];
        }
        ksort($agg);
        foreach ($agg as &$row) {
            $row['profit']  = round($row['revenue'] - $row['cost'], 2);
            $row['revenue'] = round($row['revenue'], 2);
            $row['cost']    = round($row['cost'], 2);
        }
        unset($row);
        return array_values($agg);
    }
}