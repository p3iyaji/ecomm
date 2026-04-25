<?php

namespace App\Jobs;

use App\Models\Product;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class ProcessBulkProductImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $products;
    protected string $batchId;
    protected int $totalProducts;
    protected int $processed = 0;

    public $timeout = 600;
    public $tries = 1;

    public function __construct(array $products, string $batchId)
    {
        $this->products = $products;
        $this->batchId = $batchId;
        $this->totalProducts = count($products);
    }

    public function handle(): void
    {
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        foreach ($this->products as $index => $productData) {
            try {
                // Validate required fields
                if (empty($productData['name']) || empty($productData['price'])) {
                    throw new \Exception('Name and price are required');
                }

                // Generate SKU if not provided
                if (empty($productData['sku'])) {
                    $productData['sku'] = 'SKU-' . strtoupper(Str::random(8));
                }

                // Create or update product
                Product::updateOrCreate(
                    ['sku' => $productData['sku']],
                    array_merge($productData, [
                        'slug' => Str::slug($productData['name'] . '-' . uniqid()),
                        'is_active' => $productData['is_active'] ?? true,
                    ])
                );

                $successCount++;
                $this->processed++;

                // Update progress in Redis
                $progress = ($this->processed / $this->totalProducts) * 100;
                Redis::setex(
                    "imports:{$this->batchId}:progress",
                    3600,
                    json_encode([
                        'progress' => round($progress, 2),
                        'processed' => $this->processed,
                        'total' => $this->totalProducts,
                        'success' => $successCount,
                        'failed' => $failedCount
                    ])
                );
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = [
                    'row' => $index + 1,
                    'product' => $productData['name'] ?? 'Unknown',
                    'error' => $e->getMessage()
                ];

                Log::warning('Product import failed', [
                    'batch_id' => $this->batchId,
                    'product' => $productData,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Store final results
        Redis::setex(
            "imports:{$this->batchId}:results",
            3600,
            json_encode([
                'completed_at' => now()->toDateTimeString(),
                'total' => $this->totalProducts,
                'success' => $successCount,
                'failed' => $failedCount,
                'errors' => $errors
            ])
        );

        Log::info('Bulk product import completed', [
            'batch_id' => $this->batchId,
            'total' => $this->totalProducts,
            'success' => $successCount,
            'failed' => $failedCount
        ]);
    }
}
