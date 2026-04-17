<?php

namespace App\Services;

use App\Models\TaiNguyenBuoiHoc;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use ZipArchive;

class StudentResourcePreviewService
{
    public function build(TaiNguyenBuoiHoc $resource): array
    {
        $url = $this->resourceUrl($resource);
        $extension = $this->extension($resource, $url);
        $mimeType = strtolower((string) $resource->mime_type);
        $localPath = $this->localPath($resource);

        $base = [
            'kind' => 'empty',
            'url' => $url,
            'extension' => $extension,
            'mime_type' => $mimeType,
            'message' => null,
            'content' => null,
        ];

        if (blank($url)) {
            return $base + [
                'message' => 'Tài nguyên này chưa có tệp hoặc liên kết để hiển thị.',
            ];
        }

        if ($this->isImage($extension, $mimeType)) {
            return array_merge($base, ['kind' => 'image']);
        }

        if ($this->isVideo($extension, $mimeType)) {
            return array_merge($base, ['kind' => 'video']);
        }

        if ($this->isAudio($extension, $mimeType)) {
            return array_merge($base, ['kind' => 'audio']);
        }

        if ($this->isPdf($extension, $mimeType)) {
            return array_merge($base, ['kind' => 'frame']);
        }

        if ($localPath && $this->isText($extension, $mimeType)) {
            return array_merge($base, [
                'kind' => 'text',
                'content' => $this->readText($localPath),
            ]);
        }

        if ($localPath && in_array($extension, ['docx', 'xlsx', 'pptx'], true)) {
            try {
                return match ($extension) {
                    'docx' => array_merge($base, [
                        'kind' => 'docx',
                        'content' => $this->readDocx($localPath),
                    ]),
                    'xlsx' => array_merge($base, [
                        'kind' => 'xlsx',
                        'content' => $this->readXlsx($localPath),
                    ]),
                    'pptx' => array_merge($base, [
                        'kind' => 'pptx',
                        'content' => $this->readPptx($localPath),
                    ]),
                };
            } catch (Throwable) {
                return array_merge($base, [
                    'kind' => 'frame',
                    'message' => 'Không thể đọc nội dung tệp Office này, hệ thống sẽ thử nhúng tệp trực tiếp.',
                ]);
            }
        }

        if ($localPath && in_array($extension, ['doc', 'ppt', 'xls'], true)) {
            return array_merge($base, [
                'kind' => 'text',
                'content' => $this->readLegacyOfficeText($localPath),
                'message' => 'Tệp Office định dạng cũ được trích xuất văn bản tự động nên có thể không giữ nguyên bố cục.',
            ]);
        }

        if ($resource->is_external || in_array($extension, ['doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx'], true)) {
            return array_merge($base, [
                'kind' => 'frame',
                'message' => 'Nếu trình duyệt không hiển thị được nội dung, hãy mở tài liệu ở tab mới.',
            ]);
        }

        return array_merge($base, [
            'kind' => 'unsupported',
            'message' => 'Định dạng này chưa hỗ trợ xem trực tiếp trong trang.',
        ]);
    }

    private function resourceUrl(TaiNguyenBuoiHoc $resource): ?string
    {
        if ($resource->is_external) {
            return $resource->file_url;
        }

        $path = ltrim((string) $resource->duong_dan_file, '/');

        if ($path !== '' && is_file(public_path($path))) {
            return asset($path);
        }

        if ($path !== '' && str_starts_with($path, 'storage/') && is_file(public_path($path))) {
            return asset($path);
        }

        return $resource->file_url;
    }

    private function extension(TaiNguyenBuoiHoc $resource, ?string $url): string
    {
        $extension = strtolower((string) $resource->file_extension);

        if ($extension !== '') {
            return $extension;
        }

        $path = parse_url((string) $url, PHP_URL_PATH) ?: '';

        return strtolower(pathinfo($path, PATHINFO_EXTENSION));
    }

    private function localPath(TaiNguyenBuoiHoc $resource): ?string
    {
        if ($resource->is_external || blank($resource->duong_dan_file)) {
            return null;
        }

        $path = ltrim((string) $resource->duong_dan_file, '/');
        $candidates = [
            public_path($path),
        ];

        if (! str_starts_with($path, 'storage/')) {
            $candidates[] = public_path('storage/' . $path);
        }

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        $storagePath = str_starts_with($path, 'storage/')
            ? Str::after($path, 'storage/')
            : $path;

        if (Storage::disk('public')->exists($storagePath)) {
            return Storage::disk('public')->path($storagePath);
        }

        return null;
    }

    private function isPdf(string $extension, string $mimeType): bool
    {
        return $extension === 'pdf' || str_contains($mimeType, 'pdf');
    }

    private function isImage(string $extension, string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/')
            || in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
    }

    private function isVideo(string $extension, string $mimeType): bool
    {
        return str_starts_with($mimeType, 'video/')
            || in_array($extension, ['mp4', 'webm', 'mov', 'm4v'], true);
    }

    private function isAudio(string $extension, string $mimeType): bool
    {
        return str_starts_with($mimeType, 'audio/')
            || in_array($extension, ['mp3', 'wav', 'ogg', 'm4a'], true);
    }

    private function isText(string $extension, string $mimeType): bool
    {
        return str_starts_with($mimeType, 'text/')
            || in_array($extension, ['txt', 'csv', 'md', 'json', 'xml', 'log'], true);
    }

    private function readText(string $path): string
    {
        $content = file_get_contents($path, false, null, 0, 250000);

        return trim((string) $content);
    }

    private function readLegacyOfficeText(string $path): string
    {
        $binary = (string) file_get_contents($path, false, null, 0, 1500000);
        $candidates = [$binary];

        if (function_exists('mb_convert_encoding')) {
            $candidates[] = @mb_convert_encoding($binary, 'UTF-8', 'UTF-16LE') ?: '';
            $candidates[] = @mb_convert_encoding($binary, 'UTF-8', 'Windows-1258') ?: '';
        }

        if (function_exists('iconv')) {
            $candidates[] = @iconv('UTF-16LE', 'UTF-8//IGNORE', $binary) ?: '';
            $candidates[] = @iconv('Windows-1258', 'UTF-8//IGNORE', $binary) ?: '';
        }

        $best = '';
        $bestScore = 0;

        foreach ($candidates as $candidate) {
            $text = $this->cleanExtractedText($candidate);
            $score = preg_match_all('/[\p{L}\p{N}]/u', $text) ?: 0;

            if ($score > $bestScore) {
                $best = $text;
                $bestScore = $score;
            }
        }

        return $best !== ''
            ? $best
            : 'Không trích xuất được nội dung văn bản từ tệp này. Vui lòng mở tài liệu ở tab mới.';
    }

    private function cleanExtractedText(string $text): string
    {
        $text = str_replace("\0", ' ', $text);

        if (function_exists('mb_convert_encoding')) {
            $text = @mb_convert_encoding($text, 'UTF-8', 'UTF-8') ?: $text;
        }

        $text = preg_replace('/[^\P{C}\r\n\t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/[ \t]{2,}/', ' ', $text) ?? $text;

        $lines = preg_split('/\R+/', $text) ?: [];
        $lines = array_values(array_filter(array_map('trim', $lines), function (string $line): bool {
            return mb_strlen($line) >= 3 && preg_match('/[\p{L}\p{N}]/u', $line);
        }));

        return trim(implode("\n", array_slice($lines, 0, 300)));
    }

    /**
     * @return array<int, string>
     */
    private function readDocx(string $path): array
    {
        $zip = $this->openZip($path);

        try {
            $content = $zip->getFromName('word/document.xml');

            if ($content === false) {
                return [];
            }

            $document = $this->xmlDocument($content);
            $xpath = new DOMXPath($document);
            $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

            $paragraphs = [];

            foreach ($xpath->query('//w:body/w:p') as $paragraphNode) {
                if (! $paragraphNode instanceof DOMElement) {
                    continue;
                }

                $text = $this->flattenOfficeText($xpath, $paragraphNode, 'w');

                if ($text !== '') {
                    $paragraphs[] = $text;
                }
            }

            return $paragraphs;
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<int, array{title:string, lines:array<int, string>}>
     */
    private function readPptx(string $path): array
    {
        $zip = $this->openZip($path);

        try {
            $slidePaths = [];

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $name = $zip->getNameIndex($index);

                if (is_string($name) && preg_match('#^ppt/slides/slide\d+\.xml$#', $name)) {
                    $slidePaths[] = $name;
                }
            }

            natsort($slidePaths);
            $slides = [];

            foreach (array_values($slidePaths) as $slideIndex => $slidePath) {
                $content = $zip->getFromName($slidePath);

                if ($content === false) {
                    continue;
                }

                $document = $this->xmlDocument($content);
                $xpath = new DOMXPath($document);
                $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');

                $lines = [];

                foreach ($xpath->query('//a:t') as $textNode) {
                    $line = trim($textNode->textContent);

                    if ($line !== '') {
                        $lines[] = $line;
                    }
                }

                $slides[] = [
                    'title' => 'Slide ' . ($slideIndex + 1),
                    'lines' => $lines,
                ];
            }

            return $slides;
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<int, array{title:string, rows:array<int, array<int, string>>}>
     */
    private function readXlsx(string $path): array
    {
        $zip = $this->openZip($path);

        try {
            $sharedStrings = $this->xlsxSharedStrings($zip);
            $sheetPaths = [];

            for ($index = 0; $index < $zip->numFiles; $index++) {
                $name = $zip->getNameIndex($index);

                if (is_string($name) && preg_match('#^xl/worksheets/sheet\d+\.xml$#', $name)) {
                    $sheetPaths[] = $name;
                }
            }

            natsort($sheetPaths);
            $sheets = [];

            foreach (array_values($sheetPaths) as $sheetIndex => $sheetPath) {
                $content = $zip->getFromName($sheetPath);

                if ($content === false) {
                    continue;
                }

                $sheets[] = [
                    'title' => 'Sheet ' . ($sheetIndex + 1),
                    'rows' => $this->xlsxRows($content, $sharedStrings),
                ];
            }

            return $sheets;
        } finally {
            $zip->close();
        }
    }

    /**
     * @return array<int, string>
     */
    private function xlsxSharedStrings(ZipArchive $zip): array
    {
        $content = $zip->getFromName('xl/sharedStrings.xml');

        if ($content === false) {
            return [];
        }

        $document = $this->xmlDocument($content);
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $strings = [];

        foreach ($xpath->query('//main:sst/main:si') as $item) {
            if ($item instanceof DOMElement) {
                $strings[] = $this->flattenOfficeText($xpath, $item, 'main');
            }
        }

        return $strings;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     * @return array<int, array<int, string>>
     */
    private function xlsxRows(string $content, array $sharedStrings): array
    {
        $document = $this->xmlDocument($content);
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

        $rows = [];

        foreach ($xpath->query('//main:sheetData/main:row') as $rowNode) {
            if (! $rowNode instanceof DOMElement) {
                continue;
            }

            $cells = [];

            foreach ($xpath->query('./main:c', $rowNode) as $cellNode) {
                if (! $cellNode instanceof DOMElement) {
                    continue;
                }

                $reference = $cellNode->getAttribute('r');
                $column = preg_replace('/\d+/', '', $reference) ?: 'A';
                $cells[$this->columnToIndex($column)] = $this->xlsxCellValue($xpath, $cellNode, $sharedStrings);
            }

            if ($cells === []) {
                continue;
            }

            ksort($cells);
            $max = min(max(array_keys($cells)), 24);
            $row = [];

            for ($index = 0; $index <= $max; $index++) {
                $row[] = trim($cells[$index] ?? '');
            }

            $rows[] = $row;

            if (count($rows) >= 100) {
                break;
            }
        }

        return $rows;
    }

    /**
     * @param  array<int, string>  $sharedStrings
     */
    private function xlsxCellValue(DOMXPath $xpath, DOMElement $cellNode, array $sharedStrings): string
    {
        $type = $cellNode->getAttribute('t');

        if ($type === 'inlineStr') {
            return $this->flattenOfficeText($xpath, $cellNode, 'main');
        }

        $value = trim($xpath->query('./main:v', $cellNode)->item(0)?->textContent ?? '');

        return match ($type) {
            's' => $sharedStrings[(int) $value] ?? '',
            'b' => $value === '1' ? 'TRUE' : 'FALSE',
            default => $value,
        };
    }

    private function flattenOfficeText(DOMXPath $xpath, DOMElement $contextNode, string $namespace): string
    {
        $parts = [];

        foreach ($xpath->query(".//{$namespace}:t|.//{$namespace}:tab|.//{$namespace}:br", $contextNode) as $node) {
            $parts[] = match ($node->localName) {
                'tab' => "\t",
                'br' => "\n",
                default => $node->textContent,
            };
        }

        return trim(implode('', $parts));
    }

    private function xmlDocument(string $content): DOMDocument
    {
        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;
        @$document->loadXML($content);

        return $document;
    }

    private function openZip(string $path): ZipArchive
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Cannot open archive.');
        }

        return $zip;
    }

    private function columnToIndex(string $column): int
    {
        $column = strtoupper($column);
        $index = 0;

        for ($pointer = 0; $pointer < strlen($column); $pointer++) {
            $index = ($index * 26) + (ord($column[$pointer]) - 64);
        }

        return $index - 1;
    }
}
