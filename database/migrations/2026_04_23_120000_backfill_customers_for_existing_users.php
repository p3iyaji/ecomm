<?php

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        User::query()->whereDoesntHave('customer')->each(function (User $user) {
            Customer::query()->create([
                'user_id' => $user->id,
                'total_spent' => 0,
                'total_orders' => 0,
            ]);
        });
    }

    public function down(): void
    {
        //
    }
};
