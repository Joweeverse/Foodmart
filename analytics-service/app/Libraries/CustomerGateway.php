<?php
namespace App\Libraries;

class CustomerGateway
{
    protected ServiceClient $client;

    public function __construct()
    {
        $this->client = new ServiceClient(config('Integration')->customerUrl);
    }

    public function find($id): ?array
    {
        $json = $this->client->get('/customers/' . $id);
        $c = $json['data'] ?? null;
        if (! $c) return null;

        $name = $c['fullname'] ?? trim(($c['fname'] ?? '') . ' ' . ($c['lname'] ?? ''));
        return ['customer_id' => $id, 'name' => $name !== '' ? $name : 'Unknown'];
    }
}