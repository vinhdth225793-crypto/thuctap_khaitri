<?php

namespace App\Support\Imports;

use InvalidArgumentException;

class ImportTemplateRegistry
{
    public const QUESTION_BANK_MCQ = 'question_bank_mcq';
    public const QUESTION_BANK_MCQ_LEGACY_CSV = 'question_bank_mcq_csv';

    public function get(string $key): array
    {
        $template = config("import_templates.templates.{$key}");

        if (!is_array($template)) {
            throw new InvalidArgumentException("Import template [{$key}] is not configured.");
        }

        $disk = (string) ($template['disk'] ?? 'local');
        $path = ltrim((string) ($template['path'] ?? ''), '/');

        return array_merge($template, [
            'key' => $key,
            'disk' => $disk,
            'path' => $path,
            'absolute_path' => storage_path('app/' . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path)),
        ]);
    }

    public function questionBankMcq(): array
    {
        return $this->get(self::QUESTION_BANK_MCQ);
    }

    public function legacyProfile(string $key): array
    {
        $profile = config("import_templates.legacy_profiles.{$key}");

        if (!is_array($profile)) {
            throw new InvalidArgumentException("Import legacy profile [{$key}] is not configured.");
        }

        return array_merge($profile, [
            'key' => $key,
        ]);
    }

    public function questionBankMcqLegacyCsv(): array
    {
        return $this->legacyProfile(self::QUESTION_BANK_MCQ_LEGACY_CSV);
    }

    public function exists(string $key): bool
    {
        $template = $this->get($key);

        return is_file($template['absolute_path']);
    }
}