<?php

namespace App\Http\Requests\V1\EducationalResource;

use Illuminate\Foundation\Http\FormRequest;

class StoreResourceRequest extends FormRequest
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
        $rules = [
            "userId" => "required|exists:users,id",
            "categoryId" => "required|exists:categories,id",
            "name" => "required|string|min:3|max:255",
            "position.lat" => "required|numeric|between:-90,90",
            "position.lng" => "required|numeric|between:-180,180",
            "address" => "required|string|min:3|max:255",
            "website" => "nullable|string|min:7|max:255",
            "phone" => "nullable|string|min:14|max:15",
            "cover" => "required|string|max:1000",
            "approved" => "nullable|boolean"
        ];

        return $rules;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'user_id' => $this->userId,
            'category_id' => $this->categoryId,
            'latitude' => $this->position['lat'],
            'longitude' => $this->position['lng'],
        ]);
    }
}