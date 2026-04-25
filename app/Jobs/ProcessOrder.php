<?php

namespace App\Jobs;

use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ProcessOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;

    public $timeout = 120;
    public $tries = 3;
    public $backoff = [5, 10, 30];
    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        //eloquent relations are not serialized when jobs are serialized and pushed to queue
        //so it is good to re-fetch the order inside the job with eager loading
        $order = Order::with('items')->findOrFail($this->order->id);

        //if order is already confirmed, do nothing to prevent double processing
        if ($order->status === Order::STATUS_CONFIRMED) {
            return;
        }

        DB::transaction(function () use ($order) {
            $products = Product::whereIn('id', $order->items->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($order->items as $item) {
                $product = $products[$item->product_id];

                if (!$product->hasStock($item->quantity)) {
                    throw new \Exception("Insufficient stock for product: {$product->name}");
                }
            }

            foreach ($order->items as $item) {
                $products[$item->product_id]->decrementStock($item->quantity);
            }

            $order->update([
                'status' => Order::STATUS_CONFIRMED,
                'metadata' => array_merge($order->metadata ?? [], [
                    'processed_at' => now(),
                    'processing_timestamp' => now()->timestamp,
                ]),
            ]);

            cache()->forget('dashboard:recent_orders');
            cache()->forget('dashboard:order_statistics');

            // Redis::del('dashboard:recent_orders');
            // Redis::del('dashboard:statistics');
        });

        SendOrderConfirmation::dispatch($order)
            ->delay(now()->addSeconds(10))
            ->afterCommit();

        Log::info('Order processed successfully', [
            'order_number' => $order->order_number,
            'order_id' => $order->id,
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Order processing failed', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage()
        ]);

        $this->order->update([
            'status' => Order::STATUS_PENDING,
            'metadata' => array_merge(
                $this->order->metadata ?? [],
                [
                    'processing_error' => $exception->getMessage(),
                    'failed_at' => now()->toDateTimeString(),
                ]
            ),
        ]);

    }
}
