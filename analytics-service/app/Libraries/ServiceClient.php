<?php
namespace App\Libraries;

use Config\Services;

class ServiceClient
{
    protected $client;
    protected string $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->client  = Services::curlrequest([
            'timeout'     => 15,
            'http_errors' => false,
            // XAMPP lokal sering gagal memverifikasi sertifikat HTTPS; hanya dimatikan saat development
            'verify'      => ENVIRONMENT !== 'development',
            'headers'     => ['Accept' => 'application/json'],
        ]);
    }

    public function get(string $path, array $query = []): ?array
    {
        try {
            $res = $this->client->get($this->baseUrl . $path, ['query' => $query]);
        } catch (\Throwable $e) {
            log_message('error', 'ServiceClient: ' . $e->getMessage());
            return null;
        }

        if ($res->getStatusCode() !== 200) {
            return null;
        }
        $json = json_decode($res->getBody(), true);
        return is_array($json) ? $json : null;
    }
}