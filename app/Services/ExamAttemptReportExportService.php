<?php

namespace App\Services;

use App\Models\BaiKiemTra;
use App\Models\BaiLamBaiKiemTra;
use App\Models\KetQuaHocTap;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

class ExamAttemptReportExportService
{
    private const NS_MAIN = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
    private const NS_REL_OFFICE = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
    private const NS_REL_PACKAGE = 'http://schemas.openxmlformats.org/package/2006/relationships';
    private const TEMPLATE_RELATIVE_PATH = 'templates/exports/bao-cao-hoc-vien-lam-bai/mau_xuat_bao_cao_hoc_vien_lam_bai_day_du_co_nhan_tieng_viet.xlsx';
    private const REPORT_SHEET_NAME = 'BaoCao';
    private const DATA_START_ROW = 3;
    private const EXPORT_COLUMNS = [
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
        'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
    ];

    /**
     * @return array{path:string, download_name:string}
     */
    public function export(BaiKiemTra $baiKiemTra): array
    {
        $templatePath = storage_path('app/' . self::TEMPLATE_RELATIVE_PATH);

        if (!is_file($templatePath)) {
            throw new InvalidArgumentException('Không tìm thấy file mẫu xuất báo cáo học viên làm bài.');
        }

        $filePath = tempnam(sys_get_temp_dir(), 'exam-attempt-report-');
        if ($filePath === false) {
            throw new RuntimeException('Không thể tạo file tạm để xuất báo cáo.');
        }

        $xlsxPath = $filePath . '.xlsx';
        if (!@rename($filePath, $xlsxPath)) {
            @unlink($filePath);

            throw new RuntimeException('Không thể tạo file Excel tạm để xuất báo cáo.');
        }

        @unlink($xlsxPath);

        if (!@copy($templatePath, $xlsxPath)) {
            throw new RuntimeException('Không thể sao chép file mẫu để xuất báo cáo.');
        }

        $this->fillTemplateWorkbook($xlsxPath, $this->buildRows($baiKiemTra));

        return [
            'path' => $xlsxPath,
            'download_name' => $this->buildDownloadName($baiKiemTra),
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    private function buildRows(BaiKiemTra $baiKiemTra): array
    {
        $baiKiemTra->loadMissing([
            'khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc',
            'moduleHoc:id,ma_module,ten_module,so_buoi',
            'lichHoc:id,khoa_hoc_id,module_hoc_id,buoi_so,buoi_hoc,ngay_hoc,gio_bat_dau,gio_ket_thuc,hinh_thuc,link_online',
            'lichHoc.khoaHoc:id,ma_khoa_hoc,ten_khoa_hoc',
            'lichHoc.moduleHoc:id,ma_module,ten_module,so_buoi',
        ]);

        $attempts = BaiLamBaiKiemTra::query()
            ->with([
                'hocVien:ma_nguoi_dung,ho_ten,email,so_dien_thoai',
                'nguoiCham:ma_nguoi_dung,ho_ten,email',
                'chiTietTraLois.cauHoi:id,noi_dung,loai_cau_hoi',
                'chiTietTraLois.chiTietBaiKiemTra:id,diem_so,thu_tu',
            ])
            ->where('bai_kiem_tra_id', $baiKiemTra->id)
            ->whereIn('trang_thai', ['da_nop', 'cho_cham', 'da_cham'])
            ->whereNotNull('nop_luc')
            ->orderBy('hoc_vien_id')
            ->orderBy('lan_lam_thu')
            ->get()
            ->values();
        $officialResults = KetQuaHocTap::query()
            ->where('bai_kiem_tra_id', $baiKiemTra->id)
            ->whereIn('hoc_vien_id', $attempts->pluck('hoc_vien_id')->unique()->values()->all())
            ->get()
            ->keyBy('hoc_vien_id');

        return $attempts
            ->map(fn (BaiLamBaiKiemTra $baiLam) => $this->buildRow($baiKiemTra, $baiLam, $officialResults->get($baiLam->hoc_vien_id)))
            ->values()
            ->all();
    }

    /**
     * @return array<int, string>
     */
    private function buildRow(BaiKiemTra $baiKiemTra, BaiLamBaiKiemTra $baiLam, ?KetQuaHocTap $officialResult = null): array
    {
        $student = $baiLam->hocVien;
        $schedule = $baiKiemTra->lichHoc;
        $course = $baiKiemTra->khoaHoc ?: $schedule?->khoaHoc;
        $module = $baiKiemTra->moduleHoc ?: $schedule?->moduleHoc;

        return [
            (string) ($student?->ma_nguoi_dung ?? $baiLam->hoc_vien_id),
            (string) ($student?->ho_ten ?? 'Không rõ học viên'),
            (string) ($student?->email ?? ''),
            (string) ($student?->so_dien_thoai ?? ''),
            (string) ($course?->ma_khoa_hoc ?? ''),
            (string) ($course?->ten_khoa_hoc ?? ''),
            (string) ($module?->ma_module ?? ''),
            (string) ($module?->ten_module ?? ''),
            $module?->so_buoi !== null ? (string) $module->so_buoi : '',
            (string) $baiKiemTra->id,
            (string) $baiKiemTra->tieu_de,
            (string) $baiKiemTra->pham_vi_label,
            (string) $baiKiemTra->content_mode_label,
            $this->formatDateTime($baiKiemTra->ngay_mo ?: $schedule?->starts_at),
            $this->formatDateTime($baiKiemTra->ngay_dong ?: $schedule?->ends_at),
            $this->formatScheduleLabel($schedule),
            (string) ($schedule?->hinh_thuc_label ?? ''),
            (string) ($schedule?->link_online ?? ''),
            (string) ($baiLam->lan_lam_thu ?? ''),
            $this->formatDateTime($baiLam->bat_dau_luc),
            $this->formatDateTime($baiLam->nop_luc),
            $this->formatActualMinutes($baiLam),
            $this->formatDecimal($baiLam->tong_diem_trac_nghiem),
            $this->formatDecimal($baiLam->tong_diem_tu_luan),
            (string) ($baiLam->tong_so_vi_pham ?? 0),
            $this->buildArchiveNote($baiLam, $officialResult),
        ];
    }

    private function formatScheduleLabel(mixed $schedule): string
    {
        if (!$schedule) {
            return '';
        }

        $parts = [];
        if ($schedule->buoi_so !== null) {
            $parts[] = 'Buổi ' . $schedule->buoi_so;
        } elseif (filled($schedule->buoi_hoc)) {
            $parts[] = (string) $schedule->buoi_hoc;
        }

        if ($schedule->ngay_hoc) {
            $parts[] = $schedule->ngay_hoc->format('d/m/Y');
        }

        return implode(' - ', $parts);
    }

    private function formatDateTime(mixed $value): string
    {
        if (!$value) {
            return '';
        }

        return $value instanceof Carbon
            ? $value->format('d/m/Y H:i')
            : Carbon::parse($value)->format('d/m/Y H:i');
    }

    private function formatActualMinutes(BaiLamBaiKiemTra $baiLam): string
    {
        if (!$baiLam->bat_dau_luc || !$baiLam->nop_luc) {
            return '';
        }

        $minutes = max(0, $baiLam->bat_dau_luc->diffInSeconds($baiLam->nop_luc, false) / 60);

        return $this->formatDecimal($minutes);
    }

    private function formatDecimal(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $formatted = number_format((float) $value, 2, '.', '');

        return rtrim(rtrim($formatted, '0'), '.');
    }

    private function buildArchiveNote(BaiLamBaiKiemTra $baiLam, ?KetQuaHocTap $officialResult = null): string
    {
        return collect([
            $this->buildOfficialResultNote($baiLam, $officialResult),
            $baiLam->nhan_xet,
            $baiLam->ghi_chu_giam_sat,
            $this->buildQuestionDetailArchiveNote($baiLam),
            $baiLam->da_tu_dong_nop ? 'Tự động nộp khi hết giờ/vi phạm' : null,
            $baiLam->nguoiCham ? 'Người chấm: ' . $baiLam->nguoiCham->ho_ten : null,
        ])
            ->filter(fn ($note) => filled($note))
            ->map(fn ($note) => trim((string) $note))
            ->implode(' | ');
    }

    private function buildOfficialResultNote(BaiLamBaiKiemTra $baiLam, ?KetQuaHocTap $officialResult): ?string
    {
        if (!$officialResult) {
            return null;
        }

        $sourceAttemptIds = collect($officialResult->source_attempt_ids ?: []);
        if ($officialResult->source_attempt_id) {
            $sourceAttemptIds->push((int) $officialResult->source_attempt_id);
        }

        if (isset($officialResult->chi_tiet['bai_lam_id'])) {
            $sourceAttemptIds->push((int) $officialResult->chi_tiet['bai_lam_id']);
        }

        $isOfficialAttempt = $sourceAttemptIds
            ->map(fn ($id) => (int) $id)
            ->contains((int) $baiLam->id);

        return 'Diem chinh thuc: ' . $this->formatDecimal($officialResult->diem_kiem_tra)
            . '; strategy: ' . ($officialResult->attempt_strategy_used ?: 'highest_score')
            . '; attempt nay: ' . ($isOfficialAttempt ? 'co' : 'khong');
    }

    private function buildQuestionDetailArchiveNote(BaiLamBaiKiemTra $baiLam): ?string
    {
        if (!$baiLam->relationLoaded('chiTietTraLois') || $baiLam->chiTietTraLois->isEmpty()) {
            return null;
        }

        $items = $baiLam->chiTietTraLois
            ->sortBy(fn ($detail) => (int) ($detail->chiTietBaiKiemTra?->thu_tu ?? 0))
            ->values()
            ->map(function ($detail, int $index) {
                $maxScore = $this->formatDecimal($detail->chiTietBaiKiemTra?->diem_so);
                $score = $detail->cauHoi?->loai_cau_hoi === 'trac_nghiem'
                    ? $this->formatDecimal($detail->diem_tu_dong)
                    : $this->formatDecimal($detail->diem_tu_luan);
                $scoreText = $score !== '' || $maxScore !== ''
                    ? trim(($score !== '' ? $score : '0') . '/' . ($maxScore !== '' ? $maxScore : '?'))
                    : '';
                $typeText = $detail->cauHoi?->loai_cau_hoi === 'trac_nghiem' ? 'TN' : 'TL';
                $comment = filled($detail->nhan_xet) ? ' - ' . trim((string) $detail->nhan_xet) : '';

                return trim('C' . ($index + 1) . " {$typeText} {$scoreText}{$comment}");
            })
            ->filter(fn (string $item) => $item !== '')
            ->implode('; ');

        return $items !== '' ? 'Chi tiet cau hoi: ' . $items : null;
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function fillTemplateWorkbook(string $xlsxPath, array $rows): void
    {
        $zip = new ZipArchive();
        if ($zip->open($xlsxPath) !== true) {
            throw new RuntimeException('Không thể mở file Excel mẫu để xuất báo cáo.');
        }

        try {
            $worksheetPath = $this->resolveWorksheetPath($zip, self::REPORT_SHEET_NAME);
            $worksheetContent = $zip->getFromName($worksheetPath);

            if ($worksheetContent === false) {
                throw new RuntimeException('Không thể đọc sheet BaoCao trong file mẫu.');
            }

            $document = $this->loadDocument($worksheetContent, 'worksheet');
            $xpath = new DOMXPath($document);
            $xpath->registerNamespace('main', self::NS_MAIN);

            $sheetData = $xpath->query('//main:sheetData')->item(0);
            if (!$sheetData instanceof DOMElement) {
                throw new RuntimeException('File mẫu Excel không hợp lệ: thiếu sheetData.');
            }

            $templateRow = null;
            $rowsToReplace = [];

            foreach ($xpath->query('./main:row', $sheetData) as $rowNode) {
                if (!$rowNode instanceof DOMElement) {
                    continue;
                }

                $rowNumber = (int) $rowNode->getAttribute('r');
                if ($rowNumber === self::DATA_START_ROW) {
                    $templateRow = $rowNode->cloneNode(true);
                }

                if ($rowNumber >= self::DATA_START_ROW) {
                    $rowsToReplace[] = $rowNode;
                }
            }

            if (!$templateRow instanceof DOMElement) {
                throw new RuntimeException('File mẫu Excel không có dòng mẫu dữ liệu để xuất báo cáo.');
            }

            foreach ($rowsToReplace as $rowNode) {
                $sheetData->removeChild($rowNode);
            }

            $this->removeDataMergeRanges($xpath, self::DATA_START_ROW);

            if ($rows === []) {
                $rows[] = array_fill(0, count(self::EXPORT_COLUMNS), '');
            }

            foreach (array_values($rows) as $offset => $rowValues) {
                $rowNumber = self::DATA_START_ROW + $offset;
                $newRow = $templateRow->cloneNode(true);

                if (!$newRow instanceof DOMElement) {
                    throw new RuntimeException('Không thể sao chép dòng mẫu trong file Excel.');
                }

                $this->fillWorksheetRow($document, $newRow, $rowNumber, $rowValues);
                $sheetData->appendChild($newRow);
            }

            $this->updateDimension($xpath, self::DATA_START_ROW + count($rows) - 1);

            $updatedWorksheetContent = $document->saveXML();
            if ($updatedWorksheetContent === false) {
                throw new RuntimeException('Không thể lưu sheet báo cáo đã điền dữ liệu.');
            }

            $zip->deleteName($worksheetPath);
            if (!$zip->addFromString($worksheetPath, $updatedWorksheetContent)) {
                throw new RuntimeException('Không thể cập nhật dữ liệu vào file báo cáo.');
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * @param  array<int, string>  $values
     */
    private function fillWorksheetRow(DOMDocument $document, DOMElement $rowNode, int $rowNumber, array $values): void
    {
        $values = array_pad(array_slice(array_values($values), 0, count(self::EXPORT_COLUMNS)), count(self::EXPORT_COLUMNS), '');
        $rowNode->setAttribute('r', (string) $rowNumber);

        $cellByColumn = [];
        foreach ($rowNode->childNodes as $childNode) {
            if (!$childNode instanceof DOMElement || $childNode->localName !== 'c') {
                continue;
            }

            $columnName = $this->columnNameFromCellRef($childNode->getAttribute('r'));
            if ($columnName !== '') {
                $cellByColumn[$columnName] = $childNode;
            }
        }

        foreach (self::EXPORT_COLUMNS as $index => $columnName) {
            $cellNode = $cellByColumn[$columnName] ?? null;

            if (!$cellNode instanceof DOMElement) {
                $cellNode = $document->createElementNS(self::NS_MAIN, 'x:c');
                $rowNode->appendChild($cellNode);
            }

            $cellNode->setAttribute('r', $columnName . $rowNumber);
            $this->fillWorksheetCell($document, $cellNode, (string) ($values[$index] ?? ''));
        }
    }

    private function fillWorksheetCell(DOMDocument $document, DOMElement $cellNode, string $value): void
    {
        while ($cellNode->firstChild !== null) {
            $cellNode->removeChild($cellNode->firstChild);
        }

        if ($value === '') {
            $cellNode->removeAttribute('t');

            return;
        }

        $cellNode->setAttribute('t', 'inlineStr');

        $inlineString = $document->createElementNS(self::NS_MAIN, 'x:is');
        $textNode = $document->createElementNS(self::NS_MAIN, 'x:t');

        if ($this->shouldPreserveXmlWhitespace($value)) {
            $textNode->setAttributeNS('http://www.w3.org/XML/1998/namespace', 'xml:space', 'preserve');
        }

        $textNode->appendChild($document->createTextNode($value));
        $inlineString->appendChild($textNode);
        $cellNode->appendChild($inlineString);
    }

    private function removeDataMergeRanges(DOMXPath $xpath, int $startRow): void
    {
        foreach ($xpath->query('//main:mergeCells') as $mergeCellsNode) {
            if (!$mergeCellsNode instanceof DOMElement) {
                continue;
            }

            $removed = 0;
            foreach (iterator_to_array($xpath->query('./main:mergeCell', $mergeCellsNode)) as $mergeCellNode) {
                if (!$mergeCellNode instanceof DOMElement) {
                    continue;
                }

                if ($this->firstRowFromRange($mergeCellNode->getAttribute('ref')) >= $startRow) {
                    $mergeCellsNode->removeChild($mergeCellNode);
                    $removed++;
                }
            }

            $remaining = $xpath->query('./main:mergeCell', $mergeCellsNode)->length;
            if ($remaining === 0 && $mergeCellsNode->parentNode) {
                $mergeCellsNode->parentNode->removeChild($mergeCellsNode);
            } elseif ($removed > 0) {
                $mergeCellsNode->setAttribute('count', (string) $remaining);
            }
        }
    }

    private function updateDimension(DOMXPath $xpath, int $lastRow): void
    {
        $dimensionNode = $xpath->query('//main:dimension')->item(0);
        if ($dimensionNode instanceof DOMElement) {
            $dimensionNode->setAttribute('ref', 'A1:Z' . max(self::DATA_START_ROW, $lastRow));
        }
    }

    private function resolveWorksheetPath(ZipArchive $zip, string $sheetName): string
    {
        $workbookContent = $zip->getFromName('xl/workbook.xml');
        $relationshipsContent = $zip->getFromName('xl/_rels/workbook.xml.rels');

        if ($workbookContent === false || $relationshipsContent === false) {
            throw new RuntimeException('File Excel mẫu không hợp lệ: thiếu workbook metadata.');
        }

        $workbookDocument = $this->loadDocument($workbookContent, 'workbook');
        $workbookXPath = new DOMXPath($workbookDocument);
        $workbookXPath->registerNamespace('main', self::NS_MAIN);
        $workbookXPath->registerNamespace('r', self::NS_REL_OFFICE);

        $relationshipsDocument = $this->loadDocument($relationshipsContent, 'workbook relationships');
        $relationshipsXPath = new DOMXPath($relationshipsDocument);
        $relationshipsXPath->registerNamespace('rel', self::NS_REL_PACKAGE);

        $targets = [];
        foreach ($relationshipsXPath->query('//rel:Relationship') as $relationshipNode) {
            if (!$relationshipNode instanceof DOMElement) {
                continue;
            }

            $target = ltrim($relationshipNode->getAttribute('Target'), '/');
            if (!str_starts_with($target, 'xl/')) {
                $target = 'xl/' . $target;
            }

            $targets[$relationshipNode->getAttribute('Id')] = $target;
        }

        foreach ($workbookXPath->query('//main:sheets/main:sheet') as $sheetNode) {
            if (!$sheetNode instanceof DOMElement || $sheetNode->getAttribute('name') !== $sheetName) {
                continue;
            }

            $relationId = $sheetNode->getAttributeNS(self::NS_REL_OFFICE, 'id') ?: $sheetNode->getAttribute('r:id');
            $worksheetPath = $targets[$relationId] ?? null;

            if ($worksheetPath !== null) {
                return $worksheetPath;
            }
        }

        throw new RuntimeException("Không tìm thấy sheet {$sheetName} trong file Excel mẫu.");
    }

    private function loadDocument(string $xmlContent, string $label): DOMDocument
    {
        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;

        if (!@$document->loadXML($xmlContent)) {
            throw new RuntimeException("Không thể phân tích {$label} trong file Excel mẫu.");
        }

        return $document;
    }

    private function columnNameFromCellRef(string $cellRef): string
    {
        return preg_match('/^([A-Z]+)/', $cellRef, $matches) === 1 ? $matches[1] : '';
    }

    private function firstRowFromRange(string $range): int
    {
        return preg_match('/^[A-Z]+(\d+)/', $range, $matches) === 1 ? (int) $matches[1] : 0;
    }

    private function shouldPreserveXmlWhitespace(string $value): bool
    {
        return preg_match('/^\s|\s$|\R| {2,}|\t/', $value) === 1;
    }

    private function buildDownloadName(BaiKiemTra $baiKiemTra): string
    {
        $slug = Str::slug($baiKiemTra->tieu_de ?: ('bai-kiem-tra-' . $baiKiemTra->id));
        $slug = $slug !== '' ? $slug : ('bai-kiem-tra-' . $baiKiemTra->id);

        return 'bao-cao-hoc-vien-lam-bai-' . $slug . '-' . now()->format('YmdHis') . '.xlsx';
    }
}
