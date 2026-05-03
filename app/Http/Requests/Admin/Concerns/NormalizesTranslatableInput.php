<?php

namespace App\Http\Requests\Admin\Concerns;

trait NormalizesTranslatableInput
{
    /**
     * @param array<int, string> $fields
     */
    protected function normalizeTranslatableInput(array $fields): void
    {
        $payload = [];

        foreach ($fields as $field) {
            $value = $this->input($field);

            if (is_array($value)) {
                $arabic = $value['ar'] ?? null;
                $english = $value['en'] ?? null;

                if ($arabic !== null || $english !== null) {
                    $payload[$field] = [
                        'ar' => $arabic,
                        'en' => $english ?? $arabic ?? '',
                    ];
                }

                continue;
            }

            if ($value === null) {
                continue;
            }

            $legacyEnglish = $this->input($field . '_en');
            $payload[$field] = [
                'ar' => $value,
                'en' => $legacyEnglish !== null && $legacyEnglish !== '' ? $legacyEnglish : $value,
            ];
        }

        if ($payload !== []) {
            $this->merge($payload);
        }
    }
}
