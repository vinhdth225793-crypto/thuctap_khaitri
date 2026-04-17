<?php

namespace Tests\Unit;

use App\Support\OnlineMeetingUrl;
use PHPUnit\Framework\TestCase;

class OnlineMeetingUrlTest extends TestCase
{
    public function test_it_removes_google_meet_query_parameters(): void
    {
        $this->assertSame(
            'https://meet.google.com/rqp-oyke-xsr',
            OnlineMeetingUrl::normalize('https://meet.google.com/rqp-oyke-xsr?pli=1')
        );
    }

    public function test_it_keeps_non_google_meet_urls_unchanged(): void
    {
        $this->assertSame(
            'https://example.com/room?id=123',
            OnlineMeetingUrl::normalize('https://example.com/room?id=123')
        );
    }

    public function test_it_extracts_google_meet_code_from_normalized_url(): void
    {
        $this->assertSame(
            'rqp-oyke-xsr',
            OnlineMeetingUrl::meetingCode('https://meet.google.com/rqp-oyke-xsr?pli=1')
        );
    }
}
