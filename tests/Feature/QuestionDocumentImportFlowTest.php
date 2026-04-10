<?php

namespace Tests\Feature;

use App\Models\KhoaHoc;
use App\Models\NganHangCauHoi;
use App\Models\NguoiDung;
use App\Models\NhomNganh;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use ZipArchive;

class QuestionDocumentImportFlowTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    private int $sequence = 5000;

    public function test_can_preview_and_confirm_docx_import_detecting_bold_answer_as_correct(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);

        $docxPath = $this->createDocxQuestionFile([
            ['text' => '1. Thu do cua Viet Nam la gi?'],
            ['text' => 'A. Hue'],
            [
                'runs' => [
                    ['text' => 'B. '],
                    ['text' => 'Ha Noi', 'bold' => true],
                ],
            ],
            ['text' => 'C. Da Nang'],
            ['text' => 'D. Can Tho'],
        ]);

        $upload = new UploadedFile(
            $docxPath,
            'cau-hoi-docx.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
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

        $this->assertSame('question_document_docx', $preview['profile']);
        $this->assertSame('docx', $preview['source_format']);
        $this->assertSame(1, $preview['summary']['valid']);
        $this->assertSame('hop_le', $preview['data'][0]['status']);
        $this->assertSame('hop_le', $preview['data'][0]['validation_status']);
        $this->assertSame('Ha Noi', $preview['data'][0]['dap_an_dung']);

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.confirm-import'))
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.index'));

        $question = NganHangCauHoi::query()
            ->where('khoa_hoc_id', $course->id)
            ->where('noi_dung', 'Thu do cua Viet Nam la gi?')
            ->with('dapAns')
            ->firstOrFail();

        $this->assertCount(4, $question->dapAns);
        $this->assertSame('Ha Noi', $question->dapAns->firstWhere('is_dap_an_dung', true)?->noi_dung);
    }

    public function test_can_preview_docx_question_using_word_numbering_without_visible_numeric_prefix(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);

        $docxPath = $this->createDocxQuestionFile([
            [
                'text' => 'Quan ly du an la gi',
                'num_id' => 1,
                'ilvl' => 0,
                'runs' => [
                    ['text' => 'Quan ly du an la gi', 'bold' => true],
                ],
            ],
            ['text' => '1/1'],
            ['text' => 'A. Phuong an sai'],
            ['text' => 'B. Phuong an dung', 'runs' => [['text' => 'B. Phuong an dung', 'highlight' => true]]],
            ['text' => 'C. Phuong an sai 2'],
            ['text' => 'D. Phuong an sai 3'],
        ]);

        $upload = new UploadedFile(
            $docxPath,
            'word-numbering.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
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
        $this->assertSame('hop_le', $preview['data'][0]['status']);
        $this->assertSame('Quan ly du an la gi', $preview['data'][0]['noi_dung_cau_hoi']);
        $this->assertSame('Phuong an dung', $preview['data'][0]['dap_an_dung']);
    }

    public function test_can_preview_docx_question_using_standalone_correct_answer_line(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);

        $docxPath = $this->createDocxQuestionFile([
            ['text' => '1. Laravel duoc viet bang ngon ngu nao?'],
            ['text' => 'A. Ruby'],
            ['text' => 'B. PHP'],
            ['text' => 'C. Python'],
            ['text' => 'D. Java'],
            ['text' => 'Dap an dung: B'],
        ]);

        $upload = new UploadedFile(
            $docxPath,
            'docx-with-answer-line.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
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
        $this->assertSame('hop_le', $preview['data'][0]['validation_status']);
        $this->assertSame('PHP', $preview['data'][0]['dap_an_dung']);
    }

    public function test_pdf_text_preview_marks_question_for_manual_review_when_correct_answer_is_unknown(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);

        $pdfPath = $this->createPdfTextQuestionFile([
            '1. Thu do cua Viet Nam la gi?',
            'A. Hue',
            'B. Ha Noi',
            'C. Da Nang',
            'D. Can Tho',
        ]);

        $upload = new UploadedFile($pdfPath, 'manual-review.pdf', 'application/pdf', null, true);

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.import'), [
                'khoa_hoc_id' => $course->id,
                'file_import' => $upload,
            ])
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.preview'));

        $preview = session('import_preview');

        $this->assertSame('question_document_pdf_text', $preview['profile']);
        $this->assertSame('pdf', $preview['source_format']);
        $this->assertSame(0, $preview['summary']['valid']);
        $this->assertSame(1, $preview['summary']['needs_review']);
        $this->assertSame('loi_du_lieu', $preview['data'][0]['status']);
        $this->assertSame('khong_xac_dinh_dap_an_dung', $preview['data'][0]['validation_status']);
    }

    public function test_can_preview_and_confirm_pdf_text_import_when_star_marker_identifies_correct_answer(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);

        $pdfPath = $this->createPdfTextQuestionFile([
            '1. Framework nao dung trong project nay?',
            'A. *Laravel',
            'B. Django',
            'C. Rails',
            'D. Spring',
        ]);

        $upload = new UploadedFile($pdfPath, 'pdf-text-based.pdf', 'application/pdf', null, true);

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.import'), [
                'khoa_hoc_id' => $course->id,
                'file_import' => $upload,
            ])
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.preview'));

        $preview = session('import_preview');

        $this->assertSame(1, $preview['summary']['valid']);
        $this->assertSame('hop_le', $preview['data'][0]['status']);
        $this->assertSame('Laravel', $preview['data'][0]['dap_an_dung']);

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.confirm-import'))
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.index'));

        $question = NganHangCauHoi::query()
            ->where('khoa_hoc_id', $course->id)
            ->where('noi_dung', 'Framework nao dung trong project nay?')
            ->with('dapAns')
            ->firstOrFail();

        $this->assertSame('Laravel', $question->dapAns->firstWhere('is_dap_an_dung', true)?->noi_dung);
    }

    public function test_can_preview_pdf_text_import_when_standalone_answer_reference_is_present(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);

        $pdfPath = $this->createPdfTextQuestionFile([
            '1. Laravel su dung he quan tri CSDL nao pho bien?',
            'A. Photoshop',
            'B. MySQL',
            'C. Figma',
            'D. Word',
            'Dap an: B',
        ]);

        $upload = new UploadedFile($pdfPath, 'pdf-answer-line.pdf', 'application/pdf', null, true);

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.import'), [
                'khoa_hoc_id' => $course->id,
                'file_import' => $upload,
            ])
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.preview'));

        $preview = session('import_preview');

        $this->assertSame(1, $preview['summary']['valid']);
        $this->assertSame('hop_le', $preview['data'][0]['validation_status']);
        $this->assertSame('MySQL', $preview['data'][0]['dap_an_dung']);
    }

    public function test_can_preview_and_confirm_docx_essay_import_with_marker_and_suggestion(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);

        $docxPath = $this->createDocxQuestionFile([
            ['text' => '1. [Tu luan] Trinh bay vai tro cua migration trong Laravel.'],
            ['text' => 'Goi y: Neu hoc vien nhac den version control schema thi duoc cong diem.'],
        ]);

        $upload = new UploadedFile(
            $docxPath,
            'essay-docx.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
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
        $this->assertSame('tu_luan', $preview['data'][0]['loai_cau_hoi']);
        $this->assertSame('Giảng viên chấm tay', $preview['data'][0]['dap_an_dung']);
        $this->assertSame('Neu hoc vien nhac den version control schema thi duoc cong diem.', $preview['data'][0]['goi_y_tra_loi']);

        $this->actingAs($admin)
            ->post(route('admin.kiem-tra-online.cau-hoi.confirm-import'))
            ->assertRedirect(route('admin.kiem-tra-online.cau-hoi.index'));

        $question = NganHangCauHoi::query()
            ->where('khoa_hoc_id', $course->id)
            ->where('noi_dung', 'Trinh bay vai tro cua migration trong Laravel.')
            ->with('dapAns')
            ->firstOrFail();

        $this->assertSame('tu_luan', $question->loai_cau_hoi);
        $this->assertNull($question->kieu_dap_an);
        $this->assertSame('Neu hoc vien nhac den version control schema thi duoc cong diem.', $question->goi_y_tra_loi);
        $this->assertCount(0, $question->dapAns);
    }

    public function test_docx_preview_marks_question_invalid_when_answer_count_is_not_four(): void
    {
        $admin = $this->createUser('admin');
        $course = $this->createCourse($admin);

        $docxPath = $this->createDocxQuestionFile([
            ['text' => '1. Cau hoi chi co ba dap an?'],
            ['text' => 'A. Lua chon 1'],
            ['text' => 'B. Lua chon 2'],
            ['text' => 'C. Lua chon 3'],
            ['text' => 'Dap an: B'],
        ]);

        $upload = new UploadedFile(
            $docxPath,
            'docx-three-answers.docx',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
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
        $this->assertSame('loi_du_lieu', $preview['data'][0]['status']);
        $this->assertSame('khong_du_4_dap_an', $preview['data'][0]['validation_status']);
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
     * @param  array<int, array<string, mixed>>  $paragraphs
     */
    private function createDocxQuestionFile(array $paragraphs): string
    {
        $filePath = tempnam(sys_get_temp_dir(), 'docx-question-import-');
        if ($filePath === false) {
            throw new \RuntimeException('Unable to create temp docx file.');
        }

        $docxPath = $filePath . '.docx';
        @rename($filePath, $docxPath);

        $paragraphXml = [];
        $usesNumbering = false;
        foreach ($paragraphs as $paragraph) {
            $runs = $paragraph['runs'] ?? [
                ['text' => (string) ($paragraph['text'] ?? '')],
            ];

            $runXml = [];
            foreach ($runs as $run) {
                $properties = '';
                if (!empty($run['bold'])) {
                    $properties .= '<w:b/>';
                }
                if (!empty($run['highlight'])) {
                    $properties .= '<w:highlight w:val="yellow"/>';
                }

                $propertyXml = $properties !== '' ? '<w:rPr>' . $properties . '</w:rPr>' : '';
                $runXml[] = '<w:r>' . $propertyXml . '<w:t xml:space="preserve">' . $this->escapeXml((string) ($run['text'] ?? '')) . '</w:t></w:r>';
            }

            $paragraphProperties = [];
            if (isset($paragraph['num_id'])) {
                $usesNumbering = true;
                $paragraphProperties[] = '<w:pStyle w:val="ListParagraph"/>';
                $paragraphProperties[] = '<w:numPr><w:ilvl w:val="' . (int) ($paragraph['ilvl'] ?? 0) . '"/><w:numId w:val="' . (int) $paragraph['num_id'] . '"/></w:numPr>';
            }

            $paragraphPropertyXml = $paragraphProperties !== [] ? '<w:pPr>' . implode('', $paragraphProperties) . '</w:pPr>' : '';
            $paragraphXml[] = '<w:p>' . $paragraphPropertyXml . implode('', $runXml) . '</w:p>';
        }

        $documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body>' . implode('', $paragraphXml) . '</w:body>'
            . '</w:document>';

        $contentTypesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '</Types>';

        $rootRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            . '</Relationships>';

        $documentRelsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';

        if ($usesNumbering) {
            $documentRelsXml .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/>';
        }

        $documentRelsXml .= '</Relationships>';

        $numberingXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:abstractNum w:abstractNumId="0">'
            . '<w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="decimal"/><w:lvlText w:val="%1."/></w:lvl>'
            . '<w:lvl w:ilvl="1"><w:start w:val="1"/><w:numFmt w:val="upperLetter"/><w:lvlText w:val="%2."/></w:lvl>'
            . '</w:abstractNum>'
            . '<w:num w:numId="1"><w:abstractNumId w:val="0"/></w:num>'
            . '</w:numbering>';

        $zip = new ZipArchive();
        if ($zip->open($docxPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Unable to create docx fixture.');
        }

        $zip->addFromString('[Content_Types].xml', $contentTypesXml);
        $zip->addFromString('_rels/.rels', $rootRelsXml);
        $zip->addFromString('word/document.xml', $documentXml);
        $zip->addFromString('word/_rels/document.xml.rels', $documentRelsXml);
        if ($usesNumbering) {
            $zip->addFromString('word/numbering.xml', $numberingXml);
        }
        $zip->close();

        return $docxPath;
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function createPdfTextQuestionFile(array $lines): string
    {
        $filePath = tempnam(sys_get_temp_dir(), 'pdf-question-import-');
        if ($filePath === false) {
            throw new \RuntimeException('Unable to create temp pdf file.');
        }

        $pdfPath = $filePath . '.pdf';
        @rename($filePath, $pdfPath);

        $streamLines = array_map(function (string $line) {
            $escaped = strtr($line, [
                '\\' => '\\\\',
                '(' => '\\(',
                ')' => '\\)',
            ]);

            return '(' . $escaped . ') Tj';
        }, $lines);

        $stream = "BT\n" . implode("\n", $streamLines) . "\nET";
        $content = "%PDF-1.4\n"
            . "1 0 obj\n"
            . "<< /Length " . strlen($stream) . " >>\n"
            . "stream\n"
            . $stream . "\n"
            . "endstream\n"
            . "endobj\n"
            . "%%EOF";

        file_put_contents($pdfPath, $content);

        return $pdfPath;
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
