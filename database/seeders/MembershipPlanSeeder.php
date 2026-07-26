<?php

namespace Database\Seeders;

use App\Models\MembershipPlan;
use Illuminate\Database\Seeder;

class MembershipPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Monthly',
                'duration_in_months' => 1,
                'is_lifetime' => false,
                'price' => 1500,
                'admission_fee' => 2500,
                'admission_discount' => 0,
                'admission_free' => false,
            ],
            [
                'name' => '3 Month',
                'duration_in_months' => 3,
                'is_lifetime' => false,
                'price' => 4000,
                'admission_fee' => 2500,
                'admission_discount' => 0,
                'admission_free' => false,
            ],
            [
                'name' => '6 Month',
                'duration_in_months' => 6,
                'is_lifetime' => false,
                'price' => 7000,
                'admission_fee' => 2500,
                'admission_discount' => 500,
                'admission_free' => false,
            ],
            [
                'name' => '12 Month',
                'duration_in_months' => 12,
                'is_lifetime' => false,
                'price' => 13000,
                'admission_fee' => 2500,
                'admission_discount' => 2500,
                'admission_free' => true,
            ],
            [
                'name' => 'Lifetime',
                'duration_in_months' => null,
                'is_lifetime' => true,
                'price' => 80000,
                'admission_fee' => 2500,
                'admission_discount' => 2500,
                'admission_free' => true,
            ],
        ];

        foreach ($plans as $plan) {
            MembershipPlan::updateOrCreate(['name' => $plan['name']], $plan + ['is_active' => true]);
        }
    }
}
