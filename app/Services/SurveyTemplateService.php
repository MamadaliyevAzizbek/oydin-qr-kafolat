<?php

namespace App\Services;

use App\Models\IntegrationSetting;

class SurveyTemplateService
{
    /** @return list<string> */
    public static function stages(): array
    {
        return ['T1', 'T2', 'T3', 'T4', 'SELLER'];
    }

    /** @return array<string, array{id:string,label:string,when:string,hint:string,default:string,placeholders:list<string>}> */
    public static function meta(): array
    {
        return [
            'T1' => [
                'id' => 'T1',
                'label' => 'T1 — 7 kun',
                'when' => 'Xariddan 7 kun keyin',
                'hint' => 'Yaxshi / Muammo bor tugmalari',
                'default' => "So'rovnoma (7 kun): mahsulotdan qoniqdingizmi?",
                'placeholders' => ['{stage_label}', '{chek}', '{shop}', '{product}'],
            ],
            'T2' => [
                'id' => 'T2',
                'label' => 'T2 — 30 kun',
                'when' => 'Xariddan 30 kun keyin',
                'hint' => 'Yaxshi / Muammo bor tugmalari',
                'default' => "So'rovnoma (30 kun): mahsulotdan qoniqdingizmi?",
                'placeholders' => ['{stage_label}', '{chek}', '{shop}', '{product}'],
            ],
            'T3' => [
                'id' => 'T3',
                'label' => 'T3 — 90 kun',
                'when' => 'Xariddan 90 kun keyin',
                'hint' => 'Yaxshi / Muammo bor tugmalari',
                'default' => "So'rovnoma (90 kun): mahsulotdan qoniqdingizmi?",
                'placeholders' => ['{stage_label}', '{chek}', '{shop}', '{product}'],
            ],
            'T4' => [
                'id' => 'T4',
                'label' => 'T4 — kafolat tugashi',
                'when' => 'Kafolat muddati tugashiga yaqin',
                'hint' => 'Yaxshi / Muammo bor tugmalari',
                'default' => "So'rovnoma (kafolat tugashi): mahsulotdan qoniqdingizmi?",
                'placeholders' => ['{stage_label}', '{chek}', '{shop}', '{product}'],
            ],
            'SELLER' => [
                'id' => 'SELLER',
                'label' => 'Sotuvchi baholash',
                'when' => 'Chek tasdiqlangach (kechikish sozlamada)',
                'hint' => '1–5⭐ tugmalari',
                'default' => "⭐ <b>Sotuvchi xizmatini baholang</b>\n\n"
                    ."Chek: <b>{chek}</b>\n"
                    ."Do‘kon: {shop}\n"
                    ."Sotuvchi: <b>{seller}</b>\n"
                    ."\nXizmat sifatini 1–5 ball bilan belgilang.",
                'placeholders' => ['{chek}', '{shop}', '{seller}', '{product}'],
            ],
        ];
    }

    public static function settingKey(string $stage): string
    {
        return 'survey_tpl_'.$stage;
    }

    public function getRaw(string $stage): string
    {
        $meta = self::meta()[$stage] ?? null;
        if (! $meta) {
            return '';
        }
        $stored = IntegrationSetting::getValue(self::settingKey($stage));
        $text = is_string($stored) ? trim($stored) : '';

        return $text !== '' ? $text : $meta['default'];
    }

    /** @return list<array<string, mixed>> */
    public function allForPanel(): array
    {
        $out = [];
        foreach (self::meta() as $stage => $m) {
            $stored = IntegrationSetting::getValue(self::settingKey($stage));
            $custom = is_string($stored) && trim($stored) !== '';
            $out[] = [
                'id' => $stage,
                'label' => $m['label'],
                'when' => $m['when'],
                'hint' => $m['hint'],
                'placeholders' => $m['placeholders'],
                'default' => $m['default'],
                'text' => $this->getRaw($stage),
                'is_custom' => $custom,
            ];
        }

        return $out;
    }

    /**
     * @param  array<string, string>  $texts  stage => text
     */
    public function saveMany(array $texts): array
    {
        foreach ($texts as $stage => $text) {
            $stage = strtoupper((string) $stage);
            if (! isset(self::meta()[$stage])) {
                continue;
            }
            $text = trim((string) $text);
            if ($text === '' || $text === self::meta()[$stage]['default']) {
                IntegrationSetting::setValue(self::settingKey($stage), null);
            } else {
                IntegrationSetting::setValue(self::settingKey($stage), mb_substr($text, 0, 4000));
            }
        }

        return $this->allForPanel();
    }

    /**
     * @param  array<string, string|null>  $vars
     */
    public function render(string $stage, array $vars = []): string
    {
        $tpl = $this->getRaw($stage);
        $stageLabel = match ($stage) {
            'T1' => '7 kun',
            'T2' => '30 kun',
            'T3' => '90 kun',
            'T4' => 'kafolat tugashi',
            'SELLER' => 'sotuvchi',
            default => $stage,
        };
        $map = array_merge([
            '{stage_label}' => $stageLabel,
            '{chek}' => '—',
            '{shop}' => '—',
            '{seller}' => '—',
            '{product}' => '—',
        ], array_filter([
            '{stage_label}' => $vars['stage_label'] ?? $stageLabel,
            '{chek}' => $vars['chek'] ?? null,
            '{shop}' => $vars['shop'] ?? null,
            '{seller}' => $vars['seller'] ?? null,
            '{product}' => $vars['product'] ?? null,
        ], fn ($v) => $v !== null && $v !== ''));

        // Sotuvchi bo‘sh bo‘lsa — qatorni yumshoq tozalash
        $text = strtr($tpl, $map);
        $text = preg_replace("/Sotuvchi:\\s*<b>—<\\/b>\\n?/u", '', $text) ?? $text;
        $text = preg_replace("/Sotuvchi:\\s*—\\n?/u", '', $text) ?? $text;

        return trim($text);
    }
}
