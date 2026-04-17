<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $resource->tieu_de }}</title>
    <style>
        :root {
            --ink: #0f172a;
            --muted: #64748b;
            --line: #dbeafe;
            --blue: #2563eb;
            --soft: #eff6ff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #ffffff;
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }

        a {
            color: var(--blue);
            font-weight: 700;
        }

        .preview-page {
            min-height: 100vh;
            padding: 16px;
        }

        .preview-header {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
            padding-bottom: 12px;
            margin-bottom: 14px;
            border-bottom: 1px solid var(--line);
        }

        .preview-title {
            margin: 0 0 4px;
            font-size: 18px;
            font-weight: 800;
        }

        .preview-meta {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .preview-action {
            flex: 0 0 auto;
            border: 1px solid var(--blue);
            border-radius: 8px;
            padding: 8px 10px;
            text-decoration: none;
            white-space: nowrap;
        }

        .preview-alert {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 12px;
            background: var(--soft);
            color: #1e3a8a;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .preview-frame {
            width: 100%;
            height: 680px;
            border: 1px solid var(--line);
            border-radius: 8px;
            background: #f8fafc;
        }

        .preview-image {
            display: block;
            max-width: 100%;
            max-height: 720px;
            margin: 0 auto;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #f8fafc;
        }

        .preview-media {
            width: 100%;
            border-radius: 8px;
            border: 1px solid var(--line);
            background: #020617;
        }

        .preview-text {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 14px;
            background: #f8fafc;
            color: var(--ink);
            min-height: 260px;
        }

        .doc-paragraph {
            margin: 0 0 10px;
        }

        .slide-card,
        .sheet-card {
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 14px;
            background: #ffffff;
        }

        .slide-card__title,
        .sheet-card__title {
            padding: 10px 12px;
            background: var(--soft);
            color: #1e3a8a;
            font-weight: 800;
            border-bottom: 1px solid var(--line);
        }

        .slide-card__body {
            padding: 12px;
        }

        .slide-card__body p {
            margin: 0 0 8px;
        }

        .sheet-scroll {
            overflow: auto;
        }

        table {
            width: 100%;
            min-width: 620px;
            border-collapse: collapse;
        }

        td {
            border: 1px solid #e2e8f0;
            padding: 8px;
            vertical-align: top;
        }

        tr:first-child td {
            background: #f8fafc;
            font-weight: 700;
        }

        .empty-box {
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 24px;
            background: #f8fafc;
            color: var(--muted);
            text-align: center;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <main class="preview-page">
        <div class="preview-header">
            <div>
                <h1 class="preview-title">{{ $resource->tieu_de }}</h1>
                <div class="preview-meta">{{ $resource->loai_label }} · {{ strtoupper($preview['extension'] ?: 'LINK') }}</div>
            </div>
            @if($preview['url'])
                <a class="preview-action" href="{{ $preview['url'] }}" target="_blank" rel="noopener noreferrer">Mở tab mới</a>
            @endif
        </div>

        @if($preview['message'])
            <div class="preview-alert">{{ $preview['message'] }}</div>
        @endif

        @switch($preview['kind'])
            @case('image')
                <img class="preview-image" src="{{ $preview['url'] }}" alt="{{ $resource->tieu_de }}">
                @break

            @case('video')
                <video class="preview-media" controls playsinline preload="metadata">
                    <source src="{{ $preview['url'] }}" type="{{ $preview['mime_type'] }}">
                    Trình duyệt của bạn không hỗ trợ xem video này.
                </video>
                @break

            @case('audio')
                <audio class="preview-media" controls preload="metadata">
                    <source src="{{ $preview['url'] }}" type="{{ $preview['mime_type'] }}">
                    Trình duyệt của bạn không hỗ trợ nghe âm thanh này.
                </audio>
                @break

            @case('text')
                <pre class="preview-text">{{ $preview['content'] ?: 'Tệp không có nội dung để hiển thị.' }}</pre>
                @break

            @case('docx')
                @forelse($preview['content'] as $paragraph)
                    <p class="doc-paragraph">{{ $paragraph }}</p>
                @empty
                    <div class="empty-box">Không đọc được nội dung trong tài liệu Word này.</div>
                @endforelse
                @break

            @case('xlsx')
                @forelse($preview['content'] as $sheet)
                    <section class="sheet-card">
                        <div class="sheet-card__title">{{ $sheet['title'] }}</div>
                        <div class="sheet-scroll">
                            @if(count($sheet['rows']) > 0)
                                <table>
                                    <tbody>
                                        @foreach($sheet['rows'] as $row)
                                            <tr>
                                                @foreach($row as $cell)
                                                    <td>{{ $cell }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="empty-box">Sheet này chưa có dữ liệu để hiển thị.</div>
                            @endif
                        </div>
                    </section>
                @empty
                    <div class="empty-box">Không đọc được nội dung trong bảng tính này.</div>
                @endforelse
                @break

            @case('pptx')
                @forelse($preview['content'] as $slide)
                    <section class="slide-card">
                        <div class="slide-card__title">{{ $slide['title'] }}</div>
                        <div class="slide-card__body">
                            @forelse($slide['lines'] as $line)
                                <p>{{ $line }}</p>
                            @empty
                                <p class="empty-box">Slide này không có văn bản để hiển thị.</p>
                            @endforelse
                        </div>
                    </section>
                @empty
                    <div class="empty-box">Không đọc được nội dung trong bài thuyết trình này.</div>
                @endforelse
                @break

            @case('frame')
                <iframe class="preview-frame" src="{{ $preview['url'] }}" title="{{ $resource->tieu_de }}"></iframe>
                <div class="preview-alert" style="margin-top: 14px;">
                    Nếu khung xem bị trống do trình duyệt hoặc website nguồn chặn nhúng, hãy dùng nút “Mở tab mới”.
                </div>
                @break

            @case('unsupported')
                <div class="empty-box">{{ $preview['message'] }}</div>
                @break

            @default
                <div class="empty-box">{{ $preview['message'] ?: 'Chưa có nội dung để hiển thị.' }}</div>
        @endswitch
    </main>
</body>
</html>
