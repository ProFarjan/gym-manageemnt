<?php

namespace Database\Seeders;

use App\Models\PaymentAccount;
use Illuminate\Database\Seeder;

class PaymentAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            ['name' => 'Cash', 'type' => 'cash'],
            ['name' => 'Bank', 'type' => 'bank'],
            ['name' => 'bKash', 'type' => 'bkash'],
            ['name' => 'Nagad', 'type' => 'nagad'],
            ['name' => 'Online Wallet', 'type' => 'wallet'],
        ];

        foreach ($accounts as $account) {
            PaymentAccount::updateOrCreate(['name' => $account['name']], $account + ['is_active' => true]);
        }
    }
}
