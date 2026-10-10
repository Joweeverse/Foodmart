<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Integration extends BaseConfig
{
    public string $customerUrl  = '';
    public string $productUrl   = '';
    public string $storeUrl     = '';
    public string $salesUrl     = '';
    public string $inventoryUrl = '';
    public string $promotionUrl = '';
    public int    $pageSize     = 1000;
    public int    $maxPages     = 20;
}