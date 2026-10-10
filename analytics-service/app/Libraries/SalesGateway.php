<?php
namespace App\Libraries;

class SalesGateway
{
    protected ServiceClient $client;

    public function __construct()
    {
        $this->client = new ServiceClient(config('Integration')->salesUrl);
    }

    /** Tarik baris sales (dibatasi maxPages) dan normalisasi field-nya. */
    public function all(int $year): ?array
    {
        $cfg  = config('Integration');
        $rows = [];

        for ($page = 1; $page <= $cfg->maxPages; $page++) {
            $json = $this->client->get('/sales', [
                'year'  => $year,
                'page'  => $page,
                'limit' => $cfg->pageSize,
            ]);
            if ($json === null) {
                return $page === 1 ? null : $rows; // gagal total vs sebagian
            }

            $batch = $json['data'] ?? [];
            if (empty($batch)) break;

            foreach ($batch as $r) {
                $rows[] = [
                    'product_id'  => $r['product_id']  ?? null,
                    'customer_id' => $r['customer_id'] ?? null,
                    'store_id'    => $r['store_id']    ?? null,
                    'date'        => $r['date']        ?? '',
                    'revenue'     => (float) ($r['store_sales'] ?? 0),
                    'cost'        => (float) ($r['store_cost']  ?? 0),
                    'units'       => (float) ($r['unit_sales']  ?? 0),
                ];
            }

            if (! isset($json['page'])) break;          // Sales belum mendukung pagination
            if (count($batch) < $cfg->pageSize) break;  // halaman terakhir
        }
        return $rows;
    }
}