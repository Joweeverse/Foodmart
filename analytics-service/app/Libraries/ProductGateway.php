<?php
namespace App\Libraries;

class ProductGateway
{
    protected ServiceClient $client;

    public function __construct()
    {
        $this->client = new ServiceClient(config('Integration')->productUrl);
    }

    public function find($id): ?array
    {
        $json = $this->client->get('/products/' . $id);
        $p = $json['data'] ?? null;
        if (! $p) return null;

        return [
            'product_id' => $id,
            'name'       => $p['product_name'] ?? $p['name'] ?? 'Unknown',
        ];
    }
}