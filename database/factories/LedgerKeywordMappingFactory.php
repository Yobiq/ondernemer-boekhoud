<?php

namespace Database\Factories;

use App\Models\LedgerAccount;
use App\Models\LedgerKeywordMapping;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LedgerKeywordMapping>
 */
class LedgerKeywordMappingFactory extends Factory
{
    protected $model = LedgerKeywordMapping::class;

    public function definition(): array
    {
        return [
            'keyword' => $this->faker->word(),
            'ledger_account_id' => LedgerAccount::factory(),
            'priority' => $this->faker->numberBetween(1, 10),
        ];
    }
}
