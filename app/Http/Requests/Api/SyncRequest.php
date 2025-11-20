<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SyncRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'work_center_code' => 'sometimes|string',
            'user_code' => 'required|string',
            'offline_events' => 'array',
            'offline_events.*.action' => 'required|string',
            'offline_events.*.datetime' => 'required|date',
        ];
    }
}
