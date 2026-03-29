<?php

namespace Tests\Feature;

use App\Models\KhoaHoc;
use App\Models\NganHangCauHoi;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use App\Support\Imports\SimpleXlsxReader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use ZipArchive;

class QuestionBankImportFlowTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    private int $sequence = 3000;

    public function test_template_download_uses_file_stored_in_storage(): void
    {
        $admin = $this->createUser('admin');
        $templatePath = storage_path('app/' . config('import_templates.templates.question_bank_mcq.path'));

        $this->assertFileExists($templatePath);

        $response = $this->actingAs($admin)
            ->get(route('admin.kiem-tra-online.cau-hoi.template'));

        $response
            ->assertOk()
            ->assertDownload('mau-import-cau-hoi-trac-nghiem.xlsx')
            ->assertHeader('Pragma', 'no-cache')
            ->assertHeader('Expires', '0');

        $cacheControl = (string) $response->baseResponse->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
        $this->assertStringContainsString('max-age=0', $cacheControl);

        ob_start();
        $response->baseResponse->sendContent();
        $downloadedContent = ob_get_clean();

        $this->assertSame(file_get_contents($templatePath), $downloadedContent);
    }

    public function test_template_download_shows_clear_error_when_stored_file_is_missing(): void
    {
        $admin = $this->createUser('admin');

        config()->set('import_templates.templates.question_bank_mcq.path', 'templates/imports/cau-hoi/khong-ton-tai.xlsx');

        $this->actingAs($admin)
            ->get(route('admin.kiem-tra-online.cau-hoi.template'))
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.index'))
            ->assertSessionHas('error', fn (string $message) => str_contains($message, 'Không tìm thấy file mẫu import câu hỏi'));
    }

    public function test_stored_template_keeps_form_layout_and_reserves_row_seven_for_input(): void
    {
        $templatePath = storage_path('app/' . config('import_templates.templates.question_bank_mcq.path'));
        $rows = app(SimpleXlsxReader::class)->readSheetRows($templatePath, 'Mau_Import');

        $rowTwo = collect($rows)->firstWhere('row', 2);
        $rowSeven = collect($rows)->firstWhere('row', 7);

        $this->assertNotNull($rowTwo);
        $this->assertSame([
            'cau_hoi',
            'dap_an_1',
            'dap_an_2',
            'dap_an_3',
            'dap_an_4',
            'dap_an_dung',
        ], $rowTwo['values']);
        $this->assertNotNull($rowSeven);
        $this->assertSame(['', '', '', '', '', ''], $rowSeven['values']);
    }

    public function test_can_preview_and_confirm_xlsx_import_using_row_seven_as_first_data_row(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);

        $xlsxPath = $this->createQuestionImportXlsx([
            ['Mau import ngan hang cau hoi'],
            ['cau_hoi', 'dap_an_1', 'dap_an_2', 'dap_an_3', 'dap_an_4', 'dap_an_dung'],
            ['Thu do Viet Nam la gi?', 'Ha Noi', 'Hue', 'Da Nang', 'Can Tho', 'Ha Noi'],
            ['2 + 2 bang may?', '3', '4', '5', '6', '4'],
            ['Luu y:', 'Nhap cot F bang noi dung dap an dung'],
            ['Vi du:', "Neu dap an dung la 'Ha Noi' thi cot F phai ghi 'Ha Noi'"],
            ['Thu do cua Viet Nam la gi?', 'Hue', 'Ha Noi', 'Da Nang', 'Can Tho', 'Ha Noi'],
        ]);

        $upload = new UploadedFile(
            $xlsxPath,
            'cau-hoi-moi.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.import'), [
                'khoa_hoc_id' => $course->id,
                'file_import' => $upload,
            ])
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.preview'));

        $preview = session('import_preview');

        $this->assertSame(1, $preview['summary']['valid']);
        $this->assertSame(0, $preview['summary']['error']);
        $this->assertSame('hop_le', $preview['data'][0]['status']);
        $this->assertSame(7, $preview['data'][0]['line']);
        $this->assertSame('Ha Noi', $preview['data'][0]['dap_an_dung']);
        $this->assertSame('question_bank_mcq', $preview['profile']);

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.confirm-import'))
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.index'))
            ->assertSessionHas('success', fn (string $message) => str_contains($message, 'Đã import thành công 1 câu hỏi.'));

        $question = NganHangCauHoi::query()
            ->where('khoa_hoc_id', $course->id)
            ->where('noi_dung', 'Thu do cua Viet Nam la gi?')
            ->with('dapAns')
            ->firstOrFail();

        $this->assertCount(4, $question->dapAns);
        $this->assertSame('Ha Noi', $question->dapAns->firstWhere('is_dap_an_dung', true)?->noi_dung);
        $this->assertSame(NganHangCauHoi::KIEU_MOT_DAP_AN, $question->kieu_dap_an);
        $this->assertSame(NganHangCauHoi::TRANG_THAI_SAN_SANG, $question->trang_thai);
        $this->assertTrue($question->supports_current_exam_builder);
    }

    public function test_preview_flags_invalid_correct_answer_text_in_xlsx_file_when_data_begins_on_row_seven(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);

        $xlsxPath = $this->createQuestionImportXlsx([
            ['Mau import ngan hang cau hoi'],
            ['cau_hoi', 'dap_an_1', 'dap_an_2', 'dap_an_3', 'dap_an_4', 'dap_an_dung'],
            ['Thu do Viet Nam la gi?', 'Ha Noi', 'Hue', 'Da Nang', 'Can Tho', 'Ha Noi'],
            ['2 + 2 bang may?', '3', '4', '5', '6', '4'],
            ['Luu y:', 'Nhap cot F bang noi dung dap an dung'],
            ['Vi du:', "Neu dap an dung la 'Ha Noi' thi cot F phai ghi 'Ha Noi'"],
            ['2 + 2 = ?', '1', '2', '3', '4', '5'],
        ]);

        $upload = new UploadedFile(
            $xlsxPath,
            'cau-hoi-sai.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.import'), [
                'khoa_hoc_id' => $course->id,
                'file_import' => $upload,
            ])
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.preview'));

        $preview = session('import_preview');

        $this->assertSame(0, $preview['summary']['valid']);
        $this->assertSame(1, $preview['summary']['error']);
        $this->assertSame('loi_du_lieu', $preview['data'][0]['status']);
        $this->assertSame(7, $preview['data'][0]['line']);
        $this->assertStringContainsString('khop', $preview['data'][0]['note']);
    }

    public function test_import_rejects_file_when_header_row_is_not_supported(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);

        $xlsxPath = $this->createQuestionImportXlsx([
            ['Mau import ngan hang cau hoi'],
            ['cau_hoi', 'dap_an_a', 'dap_an_b', 'dap_an_c', 'dap_an_d', 'dap_an_dung'],
            ['Luu y:', 'Sai header nen file nay phai bi tu choi'],
            ['Vi du:', 'Header khong hop le'],
            [''],
            [''],
            ['Thu do cua Viet Nam la gi?', 'Hue', 'Ha Noi', 'Da Nang', 'Can Tho', 'Ha Noi'],
        ]);

        $upload = new UploadedFile(
            $xlsxPath,
            'header-khong-hop-le.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );

        $this->actingAs($admin)
            ->from(route('admin.kiem-tra-online.cau-hoi.index'))
            ->post(route('admin.kiem-tra-online.cau-hoi.import'), [
                'khoa_hoc_id' => $course->id,
                'file_import' => $upload,
            ])
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.index'))
            ->assertSessionHasErrors('file_import');
    }

    public function test_preview_requires_session_owned_by_current_user(): void
    {
        $owner = $this->createUser('admin');
        $anotherAdmin = $this->createUser('admin');

        $this->actingAs($anotherAdmin)
            ->withSession([
                'import_preview' => [
                    'khoa_hoc_id' => 1,
                    'khoa_hoc_ten' => 'Demo course',
                    'source_format' => 'xlsx',
                    'profile' => 'question_bank_mcq',
                    'original_name' => 'demo.xlsx',
                    'data' => [],
                    'summary' => [
                        'total' => 0,
                        'valid' => 0,
                        'duplicate_file' => 0,
                        'duplicate_db' => 0,
                        'error' => 0,
                    ],
                    'user_id' => $owner->id,
                ],
            ])
            ->get(route('admin.kiem-tra-online.cau-hoi.preview'))
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.index'))
            ->assertSessionHas('error', fn (string $message) => str_contains($message, 'Phiên import không còn hợp lệ'))
            ->assertSessionMissing('import_preview');
    }

    public function test_confirm_import_skips_questions_that_become_duplicates_after_preview(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);

        $xlsxPath = $this->createQuestionImportXlsx([
            ['Mau import ngan hang cau hoi'],
            ['cau_hoi', 'dap_an_1', 'dap_an_2', 'dap_an_3', 'dap_an_4', 'dap_an_dung'],
            ['Thu do Viet Nam la gi?', 'Ha Noi', 'Hue', 'Da Nang', 'Can Tho', 'Ha Noi'],
            ['2 + 2 bang may?', '3', '4', '5', '6', '4'],
            ['Luu y:', 'Nhap cot F bang noi dung dap an dung'],
            ['Vi du:', "Neu dap an dung la 'Ha Noi' thi cot F phai ghi 'Ha Noi'"],
            ['Cau hoi duoc tao sau preview', 'Dap an 1', 'Dap an 2', 'Dap an 3', 'Dap an 4', 'Dap an 1'],
        ]);

        $upload = new UploadedFile(
            $xlsxPath,
            'duplicate-after-preview.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.import'), [
                'khoa_hoc_id' => $course->id,
                'file_import' => $upload,
            ])
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.preview'));

        NganHangCauHoi::create([
            'khoa_hoc_id' => $course->id,
            'nguoi_tao_id' => $admin->ma_nguoi_dung,
            'ma_cau_hoi' => 'CH-DUPLICATE-TEST',
            'noi_dung' => 'Cau hoi duoc tao sau preview',
            'loai_cau_hoi' => NganHangCauHoi::LOAI_TRAC_NGHIEM,
            'kieu_dap_an' => NganHangCauHoi::KIEU_MOT_DAP_AN,
            'muc_do' => 'trung_binh',
            'diem_mac_dinh' => 1,
            'trang_thai' => NganHangCauHoi::TRANG_THAI_SAN_SANG,
            'co_the_tai_su_dung' => true,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.confirm-import'))
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.index'))
            ->assertSessionHas('success', function (string $message) {
                return str_contains($message, 'Đã import thành công 0 câu hỏi.')
                    && str_contains($message, 'bị bỏ qua');
            });

        $this->assertSame(1, NganHangCauHoi::query()
            ->where('khoa_hoc_id', $course->id)
            ->where('noi_dung', 'Cau hoi duoc tao sau preview')
            ->count());
    }

    public function test_legacy_csv_import_is_still_supported(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);

        $csvPath = $this->createLegacyCsvImportFile([
            ['cau_hoi', 'dap_an_sai_1', 'dap_an_sai_2', 'dap_an_sai_3', 'dap_an_dung'],
            ['Laravel la gi?', 'He dieu hanh', 'Ngon ngu lap trinh', 'Phan mem do hoa', 'PHP Framework'],
        ]);

        $upload = new UploadedFile($csvPath, 'legacy.csv', 'text/csv', null, true);

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.import'), [
                'khoa_hoc_id' => $course->id,
                'file_import' => $upload,
            ])
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.preview'));

        $preview = session('import_preview');
        $this->assertSame(1, $preview['summary']['valid']);
        $this->assertSame('hop_le', $preview['data'][0]['status']);
        $this->assertSame('question_bank_mcq_csv', $preview['profile']);

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.confirm-import'))
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.index'));

        $question = NganHangCauHoi::query()
            ->where('khoa_hoc_id', $course->id)
            ->where('noi_dung', 'Laravel la gi?')
            ->with('dapAns')
            ->firstOrFail();

        $this->assertSame('PHP Framework', $question->dapAns->firstWhere('is_dap_an_dung', true)?->noi_dung);
    }

    private function createUser(string $role, array $overrides = []): NguoiDung
    {
        $index = $this->sequence++;

        return NguoiDung::create(array_merge([
            'ho_ten' => strtoupper($role) . ' ' . $index,
            'email' => $role . $index . '@example.com',
            'mat_khau' => bcrypt('password'),
            'vai_tro' => $role,
            'trang_thai' => true,
        ], $overrides));
    }

    private function createCourse(NguoiDung $creator): KhoaHoc
    {
        $index = $this->sequence++;
        $group = NhomNganh::create([
            'ma_nhom_nganh' => 'NN' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            'ten_nhom_nganh' => 'Group ' . $index,
            'trang_thai' => true,
        ]);

        return KhoaHoc::create([
            'nhom_nganh_id' => $group->id,
            'ma_khoa_hoc' => 'KH-' . str_pad((string) $index, 3, '0', STR_PAD_LEFT),
            'ten_khoa_hoc' => 'Course ' . $index,
            'cap_do' => 'co_ban',
            'tong_so_module' => 1,
            'phuong_thuc_danh_gia' => 'cuoi_khoa',
            'ty_trong_diem_danh' => 20,
            'ty_trong_kiem_tra' => 80,
            'trang_thai' => true,
            'loai' => 'hoat_dong',
            'trang_thai_van_hanh' => 'dang_day',
            'created_by' => $creator->ma_nguoi_dung,
        ]);
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function createQuestionImportXlsx(array $rows): string
    {
        $filePath = tempnam(sys_get_temp_dir(), 'question-import-');
        if ($filePath === false) {
            throw new \RuntimeException('Unable to create temp file for xlsx import test.');
        }

        $xlsxPath = $filePath . '.xlsx';
        @rename($filePath, $xlsxPath);

        $guidanceRows = [
            ['HUONG_DAN_IMPORT_CAU_HOI'],
            ['1. Su dung sheet Mau_Import de nhap du lieu.'],
            ['2. Co the giu nguyen cac dong mau phia tren.'],
            ['3. Bat dau nhap cau hoi tu dong 7, moi dong la 1 cau hoi.'],
            ['4. Cot dap_an_dung phai ghi dung noi dung dap an dung.'],
        ];

        $worksheets = [
            ['name' => 'Mau_Import', 'rows' => $rows],
            ['name' => 'Huong_Dan', 'rows' => $guidanceRows],
        ];

        $sharedStrings = [];
        $stringMap = [];

        $addSharedString = function (string $text) use (&$sharedStrings, &$stringMap): int {
            if (array_key_exists($text, $stringMap)) {
                return $stringMap[$text];
            }

            $index = count($sharedStrings);
            $stringMap[$text] = $index;
            $sharedStrings[] = $text;

            return $index;
        };

        $columnName = function (int $index): string {
            $index++;
            $name = '';

            while ($index > 0) {
                $remainder = ($index - 1) % 26;
                $name = chr(65 + $remainder) . $name;
                $index = intdiv($index - 1, 26);
            }

            return $name;
        };

        $escape = fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $sheetXml = [];

        foreach ($worksheets as $sheetIndex => $sheet) {
            $rowsXml = [];

            foreach ($sheet['rows'] as $rowIndex => $rowValues) {
                $cellXml = [];

                foreach ($rowValues as $cellIndex => $value) {
                    if ($value === '') {
                        continue;
                    }

                    $sharedIndex = $addSharedString((string) $value);
                    $reference = $columnName($cellIndex) . ($rowIndex + 1);
                    $cellXml[] = '<c r="' . $reference . '" t="s"><v>' . $sharedIndex . '</v></c>';
                }

                $rowsXml[] = '<row r="' . ($rowIndex + 1) . '">' . implode('', $cellXml) . '</row>';
            }

            $sheetXml[$sheetIndex + 1] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<sheetData>' . implode('', $rowsXml) . '</sheetData>'
                . '</worksheet>';
        }

        $sharedStringItems = [];
        foreach ($sharedStrings as $text) {
            $sharedStringItems[] = '<si><t>' . $escape($text) . '</t></si>';
        }

        $sharedStringsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($sharedStrings) . '" uniqueCount="' . count($sharedStrings) . '">'
            . implode('', $sharedStringItems)
            . '</sst>';

        $workbookSheetsXml = [];
        $workbookRelsXml = [];
        foreach ($worksheets as $index => $sheet) {
            $sheetNumber = $index + 1;
            $workbookSheetsXml[] = '<sheet name="' . $escape($sheet['name']) . '" sheetId="' . $sheetNumber . '" r:id="rId' . $sheetNumber . '"/>';
            $workbookRelsXml[] = '<Relationship Id="rId' . $sheetNumber . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="/xl/worksheets/sheet' . $sheetNumber . '.xml"/>';
        }

        $sharedStringRelId = count($worksheets) + 1;
        $stylesRelId = $sharedStringRelId + 1;
        $workbookRelsXml[] = '<Relationship Id="rId' . $sharedStringRelId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="/xl/sharedStrings.xml"/>';
        $workbookRelsXml[] = '<Relationship Id="rId' . $stylesRelId . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="/xl/styles.xml"/>';

        $contentTypeOverrides = [
            '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>',
            '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>',
            '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>',
        ];

        foreach ($worksheets as $index => $sheet) {
            $contentTypeOverrides[] = '<Override PartName="/xl/worksheets/sheet' . ($index + 1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets>' . implode('', $workbookSheetsXml) . '</sheets>'
            . '</workbook>';

        $workbookRelsXmlString = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . implode('', $workbookRelsXml)
            . '</Relationships>';

        $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . implode('', $contentTypeOverrides)
            . '</Types>';

        $rootRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="1"><fill><patternFill patternType="none"/></fill></fills>'
            . '<borders count="1"><border/></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';

        $zip = new ZipArchive();
        if ($zip->open($xlsxPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create xlsx import fixture.');
        }

        $zip->addFromString('[Content_Types].xml', $contentTypesXml);
        $zip->addFromString('_rels/.rels', $rootRelsXml);
        $zip->addFromString('xl/workbook.xml', $workbookXml);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRelsXmlString);
        $zip->addFromString('xl/sharedStrings.xml', $sharedStringsXml);
        $zip->addFromString('xl/styles.xml', $stylesXml);

        foreach ($sheetXml as $index => $xml) {
            $zip->addFromString('xl/worksheets/sheet' . $index . '.xml', $xml);
        }

        $zip->close();

        return $xlsxPath;
    }

    /**
     * @param  array<int, array<int, string>>  $rows
     */
    private function createLegacyCsvImportFile(array $rows): string
    {
        $filePath = tempnam(sys_get_temp_dir(), 'legacy-question-import-');
        if ($filePath === false) {
            throw new \RuntimeException('Unable to create temp file for csv import test.');
        }

        $csvPath = $filePath . '.csv';
        @rename($filePath, $csvPath);

        $handle = fopen($csvPath, 'wb');
        if ($handle === false) {
            throw new \RuntimeException('Unable to open temp csv file.');
        }

        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $csvPath;
    }
}
