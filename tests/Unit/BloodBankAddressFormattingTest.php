<?php

namespace Tests\Unit;

use App\Support\BloodBank;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BloodBankAddressFormattingTest extends TestCase
{
    #[Test]
    public function it_removes_duplicate_and_abbreviated_location_segments(): void
    {
        $this->assertSame('P-3, Payag, Bacuag, Surigao del Norte', BloodBank::cleanAddressString('P-3, PAYAG, BACUAG, SDN'));
        $this->assertSame('Surigao City, Surigao del Norte', BloodBank::cleanAddressString('SC, Surigao City, Surigao del Norte'));
        $this->assertSame('Surigao City', BloodBank::cleanAddressString('SURIGAO CITY, Surigao City'));
        $this->assertSame('Barangay Washington, Surigao City, Surigao del Norte', BloodBank::cleanAddressString('Barangay Washington, Surigao City, SC, Surigao del Norte'));
    }

    #[Test]
    public function it_summarizes_caraga_addresses_to_municipality_and_province(): void
    {
        $this->assertSame('Bacuag, Surigao del Norte', BloodBank::summarizeCaragaAddress('P-3, PAYAG, BACUAG, SDN'));
        $this->assertSame('Bacuag, Surigao del Norte', BloodBank::summarizeCaragaAddress('BACUAG, SDN'));
        $this->assertSame('Surigao City, Surigao del Norte', BloodBank::summarizeCaragaAddress('Barangay Washington, SC, SDN'));
        $this->assertSame('Hinatuan, Surigao del Sur', BloodBank::summarizeCaragaAddress('Sitio Uno, Hinatuan, SDS'));
        $this->assertSame('Basilisa, Dinagat Islands', BloodBank::summarizeCaragaAddress('Purok 2, Basilisa, PDI'));
        $this->assertSame('Butuan City, Agusan del Norte', BloodBank::summarizeCaragaAddress('Barangay 1, Butuan City, ADN'));
        $this->assertSame('Bayugan City, Agusan del Sur', BloodBank::summarizeCaragaAddress('Purok 4, Bayugan City, ADS'));
        $this->assertSame('', BloodBank::summarizeCaragaAddress('Unknown Barangay, Unknown Municipality'));
    }

    #[Test]
    public function it_resolves_provinces_and_municipalities_from_abbreviated_addresses(): void
    {
        $this->assertSame(
            ['province' => 'Surigao del Norte', 'municipality' => 'Surigao City'],
            BloodBank::resolveCaragaLocation('Barangay Washington, SC, SDN')
        );
        $this->assertSame(
            ['province' => 'Surigao del Sur', 'municipality' => 'Hinatuan'],
            BloodBank::resolveCaragaLocation('Sitio Uno, Hinatuan, SDS')
        );
        $this->assertSame(
            ['province' => 'Dinagat Islands', 'municipality' => 'Basilisa'],
            BloodBank::resolveCaragaLocation('Purok 2, Basilisa, PDI')
        );
        $this->assertSame(
            ['province' => 'Agusan del Norte', 'municipality' => 'Butuan City'],
            BloodBank::resolveCaragaLocation('Barangay 1, Butuan City, ADN')
        );
        $this->assertSame(
            ['province' => 'Agusan del Sur', 'municipality' => 'Bayugan City'],
            BloodBank::resolveCaragaLocation('Purok 4, Bayugan City, ADS')
        );
    }
}
