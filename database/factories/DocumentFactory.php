<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Document;
use App\Models\LedgerAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'file_path' => 'documents/' . $this->faker->uuid() . '.pdf',
            'original_filename' => $this->faker->word() . '.pdf',
            'status' => $this->faker->randomElement(['pending', 'ocr_processing', 'review_required', 'approved']),
            'document_type' => $this->faker->randomElement(['purchase_invoice', 'receipt', 'bank_statement', 'sales_invoice', 'other']),
            'upload_source' => $this->faker->randomElement(['web', 'mobile_camera']),
            'amount_excl' => $this->faker->randomFloat(2, 10, 1000),
            'amount_vat' => null,
            'amount_incl' => null,
            'vat_rate' => $this->faker->randomElement(['21', '9', '0', 'verlegd']),
            'ledger_account_id' => null,
            'confidence_score' => $this->faker->randomFloat(2, 0, 100),
            'ocr_data' => [
                'supplier' => [
                    'name' => $this->faker->company(),
                    'vat_number' => null,
                    'iban' => null,
                ],
                'invoice' => [
                    'number' => $this->faker->numerify('INV-####'),
                    'date' => $this->faker->date(),
                ],
                'amounts' => [
                    'excl' => 100.00,
                    'vat' => 21.00,
                    'incl' => 121.00,
                ],
                'raw_text' => $this->faker->text(200),
            ],
            'document_date' => $this->faker->date(),
            'supplier_name' => $this->faker->company(),
            'supplier_vat' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    public function withAmounts(float $excl, float $vat, float $incl): static
    {
        return $this->state(fn (array $attributes) => [
            'amount_excl' => $excl,
            'amount_vat' => $vat,
            'amount_incl' => $incl,
        ]);
    }
}
