<?php

namespace App\Services\Billz;

use App\Models\IntegrationSetting;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\QrToken;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\SyncLog;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Throwable;

class BillzSyncService
{
    public function __construct(protected BillzClient $client) {}

    public function syncShops(): SyncLog
    {
        return $this->run('sync-shops', function () {
            $payload = $this->client->get('/v1/shop', ['limit' => 1000]);
            $items = $payload['shops'] ?? $payload['data']['shops'] ?? $payload['data'] ?? [];
            if (! is_array($items)) {
                $items = [];
            }

            $count = 0;
            foreach ($items as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $id = $row['id'] ?? $row['shop_id'] ?? null;
                $name = $row['name'] ?? $row['shop_name'] ?? null;
                if (! $id || ! $name) {
                    continue;
                }
                Shop::query()->updateOrCreate(
                    ['billz_id' => (string) $id],
                    ['name' => $name, 'is_active' => true]
                );
                if (! empty($row['company_id'])) {
                    IntegrationSetting::setValue('billz_company_id', (string) $row['company_id']);
                }
                $count++;
            }

            IntegrationSetting::setValue('billz_last_shops_sync', now()->toIso8601String());

            return $count;
        });
    }

    public function syncProducts(?int $pages = 20): SyncLog
    {
        return $this->run('sync-products', function () use ($pages) {
            $count = 0;
            for ($page = 1; $page <= $pages; $page++) {
                $payload = $this->client->get('/v2/products', [
                    'limit' => 100,
                    'page' => $page,
                ]);
                $items = $payload['products']
                    ?? $payload['data']['products']
                    ?? $payload['data']
                    ?? [];

                if (! is_array($items) || count($items) === 0) {
                    break;
                }

                foreach ($items as $row) {
                    if (! is_array($row)) {
                        continue;
                    }
                    $id = (string) ($row['id'] ?? $row['product_id'] ?? '');
                    if ($id === '') {
                        continue;
                    }
                    $category = $row['category_name']
                        ?? data_get($row, 'categories.0.name')
                        ?? data_get($row, 'category.name');
                    $warranty = config('billz.default_warranty_months');
                    foreach ($row['custom_fields'] ?? [] as $cf) {
                        $cfName = mb_strtolower((string) ($cf['cusom_field_name'] ?? $cf['custom_field_name'] ?? $cf['name'] ?? ''));
                        if (str_contains($cfName, 'kafolat') || str_contains($cfName, 'warranty')) {
                            $warranty = (int) preg_replace('/\D+/', '', (string) ($cf['value'] ?? $cf['cusom_field_value'] ?? $warranty)) ?: $warranty;
                        }
                    }
                    Product::query()->updateOrCreate(
                        ['billz_id' => $id],
                        [
                            'sku' => $row['sku'] ?? $row['barcode'] ?? null,
                            'name' => $row['name'] ?? $row['product_name'] ?? 'Mahsulot',
                            'brand' => $row['brand_name'] ?? data_get($row, 'brand.name') ?? null,
                            'category' => $category,
                            'supplier' => $row['supplier_name'] ?? data_get($row, 'supplier.name') ?? null,
                            'warranty_months' => (int) ($row['warranty_months'] ?? $warranty),
                            'supply_price' => $row['supply_price'] ?? $row['purchase_price'] ?? null,
                            'retail_price' => $row['retail_price'] ?? data_get($row, 'shop_prices.0.retail_price') ?? null,
                        ]
                    );
                    $count++;
                }

                if (count($items) < 100) {
                    break;
                }
            }

            IntegrationSetting::setValue('billz_last_products_sync', now()->toIso8601String());

            return $count;
        });
    }

    /**
     * Mijozlar faqat bot orqali — Billz /v1/client endi import qilinmaydi.
     */
    public function syncClients(?int $pages = 20): SyncLog
    {
        return $this->run('sync-clients', function () {
            IntegrationSetting::setValue('billz_last_clients_sync', now()->toIso8601String());

            return 0;
        });
    }

    public function syncSales(?string $from = null, ?string $to = null, ?int $pages = 50): SyncLog
    {
        $from = $from ?: now()->subDays(7)->toDateString();
        $to = $to ?: now()->toDateString();

        return $this->run('sync-sales', function () use ($from, $to, $pages) {
            $count = 0;
            $shopIds = Shop::query()->whereNotNull('billz_id')->pluck('billz_id')->filter()->implode(',');

            for ($page = 1; $page <= $pages; $page++) {
                $query = [
                    'start_date' => $from,
                    'end_date' => $to,
                    'limit' => 50,
                    'page' => $page,
                ];
                if ($shopIds !== '') {
                    $query['shop_ids'] = $shopIds;
                }
                $companyId = IntegrationSetting::getValue('billz_company_id');
                if ($companyId) {
                    $query['company_id'] = $companyId;
                }

                $payload = $this->client->get('/v3/order-search', $query);
                $groups = $payload['data']['orders_sorted_by_date_list']
                    ?? $payload['orders_sorted_by_date_list']
                    ?? $payload['data']
                    ?? [];

                $orders = [];
                if (is_array($groups)) {
                    foreach ($groups as $group) {
                        if (isset($group['orders']) && is_array($group['orders'])) {
                            foreach ($group['orders'] as $order) {
                                $orders[] = $order;
                            }
                        } elseif (isset($group['id']) || isset($group['order_number'])) {
                            $orders[] = $group;
                        }
                    }
                }

                if (count($orders) === 0) {
                    break;
                }

                foreach ($orders as $row) {
                    try {
                        $this->upsertOrder($row);
                        $count++;
                    } catch (Throwable $e) {
                        Order::query()->updateOrCreate(
                            ['billz_id' => (string) ($row['id'] ?? Str::uuid())],
                            [
                                'order_number' => $row['order_number'] ?? null,
                                'sync_error' => true,
                                'sync_error_message' => $e->getMessage(),
                                'raw' => $row,
                            ]
                        );
                    }
                }

                if (count($orders) < 50) {
                    break;
                }
            }

            // Enrich recent orders missing line items (list API often omits them)
            $this->enrichOrderDetails(80);

            IntegrationSetting::setValue('billz_last_sales_sync', now()->toIso8601String());
            IntegrationSetting::setValue('billz_sync_status', 'ok');
            IntegrationSetting::setValue('billz_sync_error', null);

            return $count;
        });
    }

    public function enrichOrderDetails(int $limit = 80): int
    {
        $orders = Order::query()
            ->whereDoesntHave('items')
            ->whereNotNull('billz_id')
            ->latest('sold_at')
            ->limit($limit)
            ->get();

        $n = 0;
        foreach ($orders as $order) {
            try {
                $full = $this->client->get('/v2/order/'.$order->billz_id);
                $fullData = $full['data'] ?? $full;
                if (! is_array($fullData)) {
                    continue;
                }
                $this->upsertOrder($fullData);
                $n++;
            } catch (Throwable) {
                continue;
            }
        }

        return $n;
    }

    protected function upsertOrder(array $row): Order
    {
        $orderId = (string) ($row['id'] ?? '');
        $detail = $row['order_detail'] ?? [];
        $lineItems = $detail['order_items'] ?? $detail['products'] ?? $detail['order_products'] ?? $row['products'] ?? [];

        $shopBillz = $detail['shop_id'] ?? data_get($detail, 'shop.id') ?? null;
        $shop = $shopBillz
            ? Shop::query()->firstOrCreate(
                ['billz_id' => (string) $shopBillz],
                ['name' => data_get($detail, 'shop.name') ?? 'Do\'kon']
            )
            : null;

        $sellerBillz = data_get($detail, 'user.id') ?? $detail['seller_id'] ?? null;
        $seller = $sellerBillz
            ? Seller::query()->updateOrCreate(
                ['billz_id' => (string) $sellerBillz],
                [
                    'name' => data_get($detail, 'user.name') ?? $detail['seller_name'] ?? 'Sotuvchi',
                    'shop_id' => $shop?->id,
                ]
            )
            : null;

        $soldAt = $row['created_at'] ?? $detail['closed_at'] ?? $detail['created_at'] ?? null;
        if (! empty($row['company_id'])) {
            IntegrationSetting::setValue('billz_company_id', (string) $row['company_id']);
        }

        // customer_id Billzdan yozilmaydi — faqat bot aktivatsiyasi bogʻlaydi
        $order = Order::query()->updateOrCreate(
            ['billz_id' => $orderId],
            [
                'order_number' => $row['order_number'] ?? null,
                'shop_id' => $shop?->id,
                'seller_id' => $seller?->id,
                'status' => $row['order_status'] ?? null,
                'total_price' => $detail['total_price'] ?? $row['total_price'] ?? 0,
                'sold_at' => $soldAt ? Carbon::parse($soldAt) : now(),
                'sync_error' => false,
                'sync_error_message' => null,
                'raw' => $row,
            ]
        );

        if (is_array($lineItems)) {
            foreach ($lineItems as $p) {
                if (! is_array($p)) {
                    continue;
                }
                $prod = $p['product'] ?? $p;
                $productBillz = (string) ($prod['id'] ?? $p['product_id'] ?? $p['id'] ?? '');
                $productName = $prod['name'] ?? $prod['base_name'] ?? $p['name'] ?? $p['product_name'] ?? 'Mahsulot';
                $product = $productBillz !== ''
                    ? Product::query()->firstOrCreate(
                        ['billz_id' => $productBillz],
                        [
                            'name' => $productName,
                            'sku' => $prod['sku'] ?? $prod['barcode'] ?? null,
                            'brand' => $prod['brand_name'] ?? null,
                            'category' => $prod['category_name'] ?? null,
                            'warranty_months' => (int) ($prod['warranty_months'] ?? config('billz.default_warranty_months')),
                            'retail_price' => $prod['retail_price'] ?? null,
                            'supply_price' => $prod['supply_price'] ?? null,
                        ]
                    )
                    : null;

                // Prefer item seller if present
                $itemSellerId = data_get($p, 'sellers.0.seller_id') ?? data_get($p, 'sellers.0.seller.id');
                if ($itemSellerId) {
                    $seller = Seller::query()->updateOrCreate(
                        ['billz_id' => (string) $itemSellerId],
                        [
                            'name' => data_get($p, 'sellers.0.seller.name') ?? data_get($p, 'sellers.0.seller.first_name') ?? 'Sotuvchi',
                            'shop_id' => $shop?->id,
                        ]
                    );
                    if ($order->seller_id !== $seller->id) {
                        $order->update(['seller_id' => $seller->id]);
                    }
                }

                $item = OrderItem::query()->updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'billz_product_id' => $productBillz !== '' ? $productBillz : null,
                        'serial_number' => $p['serial_number'] ?? $p['imei'] ?? $prod['serial_number'] ?? null,
                    ],
                    [
                        'product_id' => $product?->id,
                        'name' => $productName,
                        'sku' => $prod['sku'] ?? $product?->sku,
                        'brand' => $prod['brand_name'] ?? $product?->brand,
                        'category' => $prod['category_name'] ?? $product?->category,
                        'supplier' => $prod['supplier_name'] ?? $product?->supplier,
                        'price' => $p['price'] ?? $p['retail_price'] ?? $prod['retail_price'] ?? 0,
                        'supply_price' => $p['supply_price'] ?? $prod['supply_price'] ?? null,
                        'quantity' => $p['quantity'] ?? $p['measurement_value'] ?? 1,
                        'warranty_months' => (int) ($p['warranty_months'] ?? $product?->warranty_months ?? config('billz.default_warranty_months')),
                    ]
                );

                QrToken::query()->firstOrCreate(
                    [
                        'order_id' => $order->id,
                        'order_item_id' => $item->id,
                    ],
                    [
                        'token' => Str::lower(Str::random(24)),
                        'printed' => true,
                    ]
                );
            }
        }

        if ($order->qrTokens()->count() === 0) {
            QrToken::query()->create([
                'token' => Str::lower(Str::random(24)),
                'order_id' => $order->id,
                'printed' => true,
            ]);
        }

        return $order;
    }

    protected function run(string $job, callable $callback): SyncLog
    {
        $log = SyncLog::query()->create([
            'source' => 'billz',
            'job' => $job,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $records = (int) $callback();
            $log->update([
                'status' => 'success',
                'records' => $records,
                'finished_at' => now(),
                'message' => 'OK',
            ]);
        } catch (Throwable $e) {
            IntegrationSetting::setValue('billz_sync_status', 'failed');
            IntegrationSetting::setValue('billz_sync_error', $e->getMessage());
            $log->update([
                'status' => 'failed',
                'finished_at' => now(),
                'message' => $e->getMessage(),
            ]);
        }

        return $log->fresh();
    }
}
