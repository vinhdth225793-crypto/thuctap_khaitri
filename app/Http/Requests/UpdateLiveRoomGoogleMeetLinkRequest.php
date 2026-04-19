<?php

namespace App\Http\Requests;

use App\Support\OnlineMeetingUrl;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLiveRoomGoogleMeetLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isGiangVien();
    }

    public function rules(): array
    {
        return [
            'google_meet_url' => [
                'required',
                'url',
                'max:500',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! OnlineMeetingUrl::isGoogleMeetUrl((string) $value)) {
                        $fail('Link phai thuoc Google Meet, vi du https://meet.google.com/abc-defg-hij.');
                    }
                },
            ],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
