<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Language::query()->update(['active' => false]);

        Language::updateOrCreate(['code' => 'es'], [
            'name' => 'Spanish',
            'translated_text' => 'Español',
            'active' => true,
        ]);
    }
}
