<?php

namespace App\Services\OriginConnector\Providers\Lush\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLushPlayerRequest extends FormRequest
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
            'first_name' => 'required',
            'middle_initial' => 'sometimes|nullable|string',
            'last_name' => 'required',
            'birthday' => 'required|date',
            'gender' => 'required',
            'lush_rank_id' => 'required',
            'is_excluded' => 'sometimes|boolean',
            'register_at_date' => 'required_with:register_at_time',
            'register_at_time' => 'required_with:register_at_date',
            'card_swipe_data' => 'sometimes|nullable|string',
            'card_pin' => 'sometimes|nullable|numeric|digits:4',
            'card_pin_attempts' => 'sometimes|nullable|numeric',
            'id_type' => 'required',
            'id_number' => 'required|string',
            'id_expiration_date' => 'required',
            'email' => 'sometimes|nullable|email',
            'email_opt_in' => 'sometimes|boolean',
            'phone_opt_in' => 'sometimes|boolean',
            'phone' => 'sometimes|nullable|string',
            'address' => 'sometimes|nullable|string',
            'address_2' => 'sometimes|nullable|string',
            'country' => 'sometimes|nullable|string',
            'city' => 'sometimes|nullable|string',
            'state' => 'sometimes|nullable|string',
            'zip' => 'sometimes|nullable|string',
        ];
    }
}
