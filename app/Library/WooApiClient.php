<?php

namespace Acelle\Library;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Acelle\Model\WooStore;
use Log;

class WooApiClient
{
    protected WooStore $store;
    protected Client $client;

    public function __construct(WooStore $store)
    {
        $this->store = $store;
        $baseUrl = rtrim($store->store_url, '/') . '/wp-json/wc/v3/';

        $options = [
            'base_uri' => $baseUrl,
            'timeout'  => 30.0,
            'verify'   => false, // Allow self-signed or internal SSL
            'headers'  => [
                'User-Agent' => 'MailClick-SyncEngine/1.0',
                'Accept'     => 'application/json',
            ],
        ];

        // Basic Auth for HTTPS, query params for HTTP
        if (!empty($store->consumer_key) && !empty($store->consumer_secret)) {
            if (str_starts_with($store->store_url, 'https://')) {
                $options['auth'] = [$store->consumer_key, $store->consumer_secret];
            } else {
                $options['query'] = [
                    'consumer_key' => $store->consumer_key,
                    'consumer_secret' => $store->consumer_secret,
                ];
            }
        }

        $this->client = new Client($options);
    }

    /**
     * Generic GET request with automatic pagination support.
     */
    public function getPaginated(string $endpoint, array $query = [], int $perPage = 100): array
    {
        $allResults = [];
        $page = 1;

        do {
            $currentQuery = array_merge($query, [
                'per_page' => $perPage,
                'page'     => $page,
            ]);

            try {
                $response = $this->client->get($endpoint, ['query' => $currentQuery]);
                $statusCode = $response->getStatusCode();

                if ($statusCode !== 200) {
                    Log::warning("WooApiClient non-200 response for {$endpoint}: {$statusCode}");
                    break;
                }

                $data = json_decode($response->getBody()->getContents(), true);

                if (!is_array($data) || empty($data)) {
                    break;
                }

                $allResults = array_merge($allResults, $data);

                // Get total pages from WooCommerce header
                $totalPages = (int) ($response->getHeaderLine('X-WP-TotalPages') ?: 1);

                $page++;
            } catch (GuzzleException $e) {
                Log::error("WooApiClient error requesting {$endpoint} page {$page}: " . $e->getMessage());
                break;
            } catch (\Exception $e) {
                Log::error("WooApiClient general error: " . $e->getMessage());
                break;
            }
        } while ($page <= $totalPages);

        return $allResults;
    }

    /**
     * Fetch Categories.
     */
    public function getCategories(array $params = []): array
    {
        return $this->getPaginated('products/categories', $params);
    }

    /**
     * Fetch Products (includes price, regular_price, sale_price, stock_quantity, categories, images).
     */
    public function getProducts(array $params = []): array
    {
        return $this->getPaginated('products', $params);
    }

    /**
     * Fetch Orders (includes status, total, customer_id, line_items, date_created).
     */
    public function getOrders(array $params = []): array
    {
        return $this->getPaginated('orders', $params);
    }

    /**
     * Fetch Customers.
     */
    public function getCustomers(array $params = []): array
    {
        return $this->getPaginated('customers', $params);
    }

    /**
     * Fetch Product Reviews.
     */
    public function getReviews(array $params = []): array
    {
        return $this->getPaginated('products/reviews', $params);
    }
}
