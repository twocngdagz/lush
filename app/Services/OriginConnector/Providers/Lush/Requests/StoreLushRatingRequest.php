<?php

namespace App\Services\OriginConnector\Providers\Lush\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLushRatingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'player_id' => 'required',
            'play_type' => 'required',
            'points_earned' => 'required',
            'play_start_at_date' => 'required',
            'play_start_at_time' => 'required',
            'play_end_at_date' => 'required',
            'play_end_at_time' => 'required'
        ];
    }
}
