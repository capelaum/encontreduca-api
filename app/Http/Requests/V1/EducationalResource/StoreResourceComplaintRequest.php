<?php

namespace App\Http\Requests\V1\EducationalResource;

use Illuminate\Foundation\Http\FormRequest;

class StoreResourceComplaintRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules()
    {
        $rules =  [
            "userId" => "required|exists:users,id",
            "resourceId" => "required|exists:resources,id",
            "motiveId" => "required|exists:motives,id"
        ];

        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => $this->userId,
            'resource_id' => $this->resourceId,
            'motive_id' => $this->motiveId
        ]);
    }
}