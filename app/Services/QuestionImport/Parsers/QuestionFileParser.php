<?php

namespace App\Services\QuestionImport\Parsers;

use Illuminate\Http\UploadedFile;

interface QuestionFileParser
{
    public function supports(string $extension): bool;

    /**
     * @return array{
     *     profile:string,
     *     source_format:string,
     *     original_name:string,
     *     questions:array<int, array<string, mixed>>
     * }
     */
    public function parse(UploadedFile $file): array;
}
