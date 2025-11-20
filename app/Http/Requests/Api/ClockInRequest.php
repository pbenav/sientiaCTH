<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ClockInRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'work_center_code' => 'sometimes|string|max:50',
            'manual_work_center_code' => 'sometimes|string|max:50',
            'user_code' => 'required|string|max:50',
            'action' => 'sometimes|string|in:pause,resume_workday,clock_out,confirm_exceptional_clock_in,exceptional_clock_in',
            'pause_event_id' => 'sometimes|integer',
            'location' => 'sometimes|array',
            'location.latitude' => 'sometimes|numeric|between:-90,90',
            'location.longitude' => 'sometimes|numeric|between:-180,180',
            'observations' => 'sometimes|string|max:255',
        ];
    }
}
