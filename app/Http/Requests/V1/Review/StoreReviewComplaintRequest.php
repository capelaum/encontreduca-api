<?php

namespace App\Http\Requests\V1\Review;

use Illuminate\Foundation\Http\FormRequest;

class StoreReviewComplaintRequest extends FormRequest
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
            'reviewId' => 'required|integer|exists:reviews,id',
            'motiveId' => 'required|integer|exists:motives,id',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'review_id' => $this->reviewId,
            'motive_id' => $this->motiveId,
        ]);
    }
}
