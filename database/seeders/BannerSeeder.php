<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BannerSeeder extends Seeder
{
    public const HERO_GRADIENT_PATH = 'banners/tejada-hero-gradient.svg';

    public function run(): void
    {
        DB::transaction(function () {

            $languages = Language::where('active', 1)->get();

            $this->ensureHeroGradientAsset();

            $banners = [
                [
                    'type' => 'promotion',
                    'status' => 1,
                    'translations' => [
                        'title' => 'Welcome Home',
                        'description' => 'Quality products for every room.',
                        'image' => self::HERO_GRADIENT_PATH,
                    ],
                ],
            ];

            foreach ($banners as $item) {

                $banner = Banner::create([
                    'type' => $item['type'],
                    'status' => $item['status'],
                ]);

                foreach ($languages as $lang) {
                    $translatedTitle = match ($lang->code) {
                        'es' => 'Listo para comprar',
                        'de' => 'Bereit für Ihr Zuhause',
                        default => $item['translations']['title'],
                    };

                    $translatedDescription = match ($lang->code) {
                        'es' => 'Todo lo que necesitás para equipar tu hogar con calidad Tejada Hogar.',
                        'de' => 'Alles, was Sie brauchen, um Ihr Zuhause einzurichten.',
                        default => $item['translations']['description'],
                    };

                    $banner->translations()->create([
                        'language_code' => $lang->code,
                        'title' => $translatedTitle,
                        'description' => $translatedDescription,
                        'image_url' => $item['translations']['image'],
                    ]);
                }
            }
        });
    }

    public function ensureHeroGradientAsset(): void
    {
        if (Storage::disk('public')->exists(self::HERO_GRADIENT_PATH)) {
            return;
        }

        Storage::disk('public')->put(self::HERO_GRADIENT_PATH, $this->tejadaHeroGradientSvg());
    }

    protected function tejadaHeroGradientSvg(): string
    {
        return <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="800" height="800" viewBox="0 0 800 800" role="img" aria-label="Tejada Hogar">
  <defs>
    <linearGradient id="th-base" x1="8%" y1="92%" x2="92%" y2="8%">
      <stop offset="0%" stop-color="#0072BC"/>
      <stop offset="48%" stop-color="#00AEEF"/>
      <stop offset="100%" stop-color="#29ABE2"/>
    </linearGradient>
    <radialGradient id="th-shine" cx="72%" cy="28%" r="58%">
      <stop offset="0%" stop-color="#7ad4f7" stop-opacity="0.85"/>
      <stop offset="55%" stop-color="#29ABE2" stop-opacity="0.35"/>
      <stop offset="100%" stop-color="#0072BC" stop-opacity="0"/>
    </radialGradient>
    <radialGradient id="th-depth" cx="22%" cy="78%" r="50%">
      <stop offset="0%" stop-color="#004a8f" stop-opacity="0.55"/>
      <stop offset="100%" stop-color="#0072BC" stop-opacity="0"/>
    </radialGradient>
  </defs>
  <circle cx="400" cy="400" r="400" fill="url(#th-base)"/>
  <circle cx="400" cy="400" r="400" fill="url(#th-depth)"/>
  <circle cx="400" cy="400" r="400" fill="url(#th-shine)"/>
</svg>
SVG;
    }
}
