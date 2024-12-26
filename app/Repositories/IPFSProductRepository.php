<?php

namespace App\Repositories;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\CursorPaginator;

class IPFSProductRepository
{
    private $client;
    private $indexHash; // IPFS hash of product index file

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://127.0.0.1:5001/api/v0/',
            'timeout' => 30,
        ]);
    }

    private function getProductIndex()
    {
        // Get or create product index from IPFS
        try {
            if (!$this->indexHash) {
                // Try to get index hash from cache
                $this->indexHash = Cache::get('product_index_hash');
            }

            if ($this->indexHash) {
                $response = $this->client->post('cat', [
                    'query' => ['arg' => $this->indexHash]
                ]);
                return json_decode($response->getBody()->getContents(), true);
            }

            // If no index exists, create new one
            return ['products' => []];
        } catch (\Exception $e) {
            return ['products' => []];
        }
    }

    private function updateProductIndex($index)
    {
        // Store updated index in IPFS
        $response = $this->client->post('add', [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => json_encode($index),
                    'filename' => 'product_index.json'
                ]
            ]
        ]);

        $result = json_decode($response->getBody()->getContents());
        $this->indexHash = $result->Hash;

        // Cache the index hash
        Cache::put('product_index_hash', $this->indexHash, now()->addDay());

        return $index;
    }

    public function findMany($type, $createdById, $category, $gem, $material, $gender, $min_price, $max_price, $city, $search, $tags, $stamp, $weight, $customization_available, $isPaidAdv = null)
    {
        $index = $this->getProductIndex();
        $products = collect($index['products']);

        // Apply filters
        $filtered = $products->filter(function($product) use ($type, $createdById, $category, $min_price, $max_price, $search) {
            $matches = true;

            if ($type && is_array($type)) {
                $matches = $matches && in_array($product['created_by']['type'], $type);
            }
            if ($createdById) {
                $matches = $matches && $product['created_by']['id'] === $createdById;
            }
            if ($category) {
                $matches = $matches && in_array($product['category'], (array)$category);
            }
            if ($min_price) {
                $matches = $matches && $product['price'] >= (float)$min_price;
            }
            if ($max_price) {
                $matches = $matches && $product['price'] <= (float)$max_price;
            }
            if ($search) {
                $matches = $matches && (
                        str_contains(strtolower($product['title']), strtolower($search)) ||
                        str_contains(strtolower($product['description']), strtolower($search))
                    );
            }

            return $matches;
        });

        // Sort
        $sorted = $filtered->sortByDesc('update_date');

        // Paginate
        return new CursorPaginator($sorted->values(), 12);
    }

    public function findOneById($id)
    {
        $index = $this->getProductIndex();
        $product = collect($index['products'])->firstWhere('_id', $id);

        if ($product) {
            // Get full product data from IPFS
            try {
                $response = $this->client->post('cat', [
                    'query' => ['arg' => $product['content_hash']]
                ]);
                return json_decode($response->getBody()->getContents(), true);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    public function create($createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $gender, $phoneNumber, $description, $customization, $city, $price, $tags, $imageUrls, $passportUrls)
    {
        $productId = (string) \Str::uuid();

        $product = [
            '_id' => $productId,
            'product_sku' => str_pad(random_int(0, 9999999), 7, '0', STR_PAD_LEFT),
            'created_by' => $createdBy,
            'representative' => [
                'user_id' => $user->id,
                'name' => $user->information['first_name'] . ' ' . $user->information['last_name']
            ],
            'title' => $title,
            'category' => $category,
            'material' => $material,
            'stamp' => $stamp,
            'weight' => $weight,
            'gem' => $gem,
            'size' => $size,
            'gender' => $gender,
            'phone_number' => $phoneNumber,
            'description' => $description,
            'customization' => $customization ?? ['available' => false, 'details' => ''],
            'city' => $city,
            'price' => (float)$price,
            'views_count' => 0,
            'tags' => $tags,
            'image_urls' => $imageUrls,
            'passport_urls' => $passportUrls,
            'update_date' => Carbon::now()->toDateTime(),
            'is_paid_adv' => 0
        ];

        // Store full product data in IPFS
        $response = $this->client->post('add', [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => json_encode($product),
                    'filename' => "product_{$productId}.json"
                ]
            ]
        ]);
        $result = json_decode($response->getBody()->getContents());

        // Update index
        $index = $this->getProductIndex();
        $index['products'][] = [
            '_id' => $productId,
            'title' => $title,
            'price' => (float)$price,
            'update_date' => Carbon::now()->toDateTime(),
            'content_hash' => $result->Hash
        ];
        $this->updateProductIndex($index);

        return $product;
    }

    public function update($id, $createdBy, $user, $title, $category, $material, $stamp, $weight, $gem, $size, $gender, $phoneNumber, $description, $customization, $city, $price, $tags, $imageUrls, $passportUrls)
    {
        $currentProduct = $this->findOneById($id);
        if (!$currentProduct) {
            return null;
        }

        $updatedProduct = array_merge($currentProduct, [
            'created_by' => $createdBy,
            'representative' => [
                'user_id' => $user->id,
                'name' => $user->information['first_name'] . ' ' . $user->information['last_name']
            ],
            'title' => $title,
            'category' => $category,
            'material' => $material,
            'stamp' => $stamp,
            'weight' => $weight,
            'gem' => $gem,
            'size' => $size,
            'gender' => $gender,
            'phone_number' => $phoneNumber,
            'description' => $description,
            'customization' => $customization,
            'city' => $city,
            'price' => (float)$price,
            'tags' => $tags,
            'image_urls' => $imageUrls,
            'passport_urls' => $passportUrls,
            'update_date' => Carbon::now()->toDateTime()
        ]);

        // Store updated product in IPFS
        $response = $this->client->post('add', [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => json_encode($updatedProduct),
                    'filename' => "product_{$id}.json"
                ]
            ]
        ]);
        $result = json_decode($response->getBody()->getContents());

        // Update index
        $index = $this->getProductIndex();
        $index['products'] = collect($index['products'])->map(function($product) use ($id, $title, $price, $result) {
            if ($product['_id'] === $id) {
                return [
                    '_id' => $id,
                    'title' => $title,
                    'price' => (float)$price,
                    'update_date' => Carbon::now()->toDateTime(),
                    'content_hash' => $result->Hash
                ];
            }
            return $product;
        })->toArray();

        $this->updateProductIndex($index);

        return $updatedProduct;
    }

    public function delete($id)
    {
        $index = $this->getProductIndex();
        $index['products'] = collect($index['products'])
            ->filter(function($product) use ($id) {
                return $product['_id'] !== $id;
            })
            ->values()
            ->toArray();

        $this->updateProductIndex($index);
        return true;
    }
}
