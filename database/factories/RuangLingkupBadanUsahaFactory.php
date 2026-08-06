<?php

namespace Database\Factories;

use App\Models\BadanUsaha;
use App\Models\RuangLingkup;
use App\Models\RuangLingkupBadanUsaha;
use Illuminate\Database\Eloquent\Factories\Factory;

class RuangLingkupBadanUsahaFactory extends Factory
{
    protected $model = RuangLingkupBadanUsaha::class;

    public function definition(): array
    {
        return [
            'ruang_lingkup_id' => RuangLingkup::factory(),
            'badan_usaha_id' => BadanUsaha::factory(),
            'benefit' => [fake()->word()],
            'cakupan' => [fake()->word()],
            'jenis_layanan_ids' => [],
            'obat_ids' => [],
        ];
    }
}
