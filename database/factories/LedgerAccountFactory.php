<?php

namespace Database\Factories;

use App\Models\LedgerAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LedgerAccount>
 */
class LedgerAccountFactory extends Factory
{
    protected $model = LedgerAccount::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->numerify('####'),
            'description' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(['balans', 'winst_verlies']),
            'vat_default' => $this->faker->randomElement(['21', '9', '0', 'verlegd']),
            'active' => true,
        ];
    }
}
