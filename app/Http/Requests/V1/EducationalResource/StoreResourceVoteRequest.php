<?php

namespace App\Http\Requests\V1\EducationalResource;

use Illuminate\Foundation\Http\FormRequest;

class StoreResourceVoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            "resourceId" => "required|integer|exists:resources,id",
            "vote" => "required|boolean",
            "justification" => "required|string|min:3"
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'resource_id' => $this->resourceId,
        ]);
    }
}
