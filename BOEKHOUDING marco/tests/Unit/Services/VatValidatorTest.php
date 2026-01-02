<?php

namespace Tests\Unit\Services;

use App\Services\VatValidator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VatValidatorTest extends TestCase
{
    protected VatValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new VatValidator();
    }

    #[Test]
    public function it_validates_correct_21_percent_vat(): void
    {
        $result = $this->validator->validate(100.00, 21.00, '21');

        $this->assertTrue($result['valid']);
        $this->assertEquals(21.00, $result['expected_vat']);
        $this->assertStringContainsString('Correct', $result['message']);
    }

    #[Test]
    public function it_validates_correct_9_percent_vat(): void
    {
        $result = $this->validator->validate(100.00, 9.00, '9');

        $this->assertTrue($result['valid']);
        $this->assertEquals(9.00, $result['expected_vat']);
    }

    #[Test]
    public function it_allows_2_cent_tolerance(): void
    {
        // Valid: 21.00 expected, 21.02 provided (within tolerance)
        $result = $this->validator->validate(100.00, 21.02, '21');

        $this->assertTrue($result['valid']);
    }

    #[Test]
    public function it_rejects_vat_exceeding_tolerance(): void
    {
        // Invalid: 21.00 expected, 21.03 provided (exceeds tolerance)
        $result = $this->validator->validate(100.00, 21.03, '21');

        $this->assertFalse($result['valid']);
        $this->assertEquals(21.00, $result['expected_vat']);
    }

    #[Test]
    public function it_validates_zero_percent_vat(): void
    {
        $result = $this->validator->validate(100.00, 0.00, '0');

        $this->assertTrue($result['valid']);
        $this->assertEquals(0.00, $result['expected_vat']);
    }

    #[Test]
    public function it_allows_small_deviation_for_zero_vat(): void
    {
        // Should allow up to 2 cents deviation
        $result = $this->validator->validate(100.00, 0.01, '0');

        $this->assertTrue($result['valid']);
    }

    #[Test]
    public function it_validates_verlegd_vat(): void
    {
        $result = $this->validator->validate(100.00, 0.00, 'verlegd');

        $this->assertTrue($result['valid']);
        $this->assertEquals(0.00, $result['expected_vat']);
    }

    #[Test]
    public function it_rejects_invalid_vat_rate(): void
    {
        $result = $this->validator->validate(100.00, 15.00, '15');

        $this->assertFalse($result['valid']);
        $this->assertNull($result['expected_vat']);
        $this->assertStringContainsString('Ongeldig BTW tarief', $result['message']);
    }

    #[Test]
    public function it_rejects_null_values(): void
    {
        $result = $this->validator->validate(null, 21.00, '21');

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('Ontbrekende BTW gegevens', $result['message']);
    }

    #[Test]
    public function it_calculates_from_total_amount(): void
    {
        $result = $this->validator->calculateFromTotal(121.00, '21');

        $this->assertTrue($result['valid']);
        $this->assertEquals(100.00, $result['excl']);
        $this->assertEquals(21.00, $result['vat']);
    }

    #[Test]
    public function it_calculates_from_total_with_9_percent(): void
    {
        $result = $this->validator->calculateFromTotal(109.00, '9');

        $this->assertTrue($result['valid']);
        // Excl should be approximately 100
        $this->assertGreaterThanOrEqual(99.99, $result['excl']);
        $this->assertLessThanOrEqual(100.01, $result['excl']);
    }

    #[Test]
    public function it_handles_zero_percent_in_calculate_from_total(): void
    {
        $result = $this->validator->calculateFromTotal(100.00, '0');

        $this->assertTrue($result['valid']);
        $this->assertEquals(100.00, $result['excl']);
        $this->assertEquals(0.00, $result['vat']);
    }

    #[Test]
    public function it_normalizes_rate_from_string(): void
    {
        $this->assertEquals('21', $this->validator->normalizeRate('21'));
        $this->assertEquals('9', $this->validator->normalizeRate('9'));
        $this->assertEquals('0', $this->validator->normalizeRate('0'));
        $this->assertEquals('verlegd', $this->validator->normalizeRate('verlegd'));
    }

    #[Test]
    public function it_returns_valid_rates(): void
    {
        $rates = $this->validator->getValidRates();

        $this->assertContains('21', $rates);
        $this->assertContains('9', $rates);
        $this->assertContains('0', $rates);
        $this->assertContains('verlegd', $rates);
    }

    #[Test]
    public function it_returns_rate_description(): void
    {
        $this->assertStringContainsString('21%', $this->validator->getRateDescription('21'));
        $this->assertStringContainsString('9%', $this->validator->getRateDescription('9'));
        $this->assertStringContainsString('0%', $this->validator->getRateDescription('0'));
        $this->assertStringContainsString('Verlegd', $this->validator->getRateDescription('verlegd'));
    }

    #[Test]
    public function it_handles_rounded_amounts_correctly(): void
    {
        // Test with amounts that require rounding
        $result = $this->validator->validate(33.33, 7.00, '21');

        // Expected VAT: 33.33 * 0.21 = 6.9993 ≈ 7.00
        $this->assertTrue($result['valid']);
    }
}

