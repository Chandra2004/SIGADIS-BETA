<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnswerScreeningQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'screening_question_id' => ['required', 'integer', Rule::exists('screening_questions', 'id')],
            'answer' => ['required', Rule::in(['ya', 'tidak'])],
            'used_text_to_speech' => ['sometimes', 'boolean'],
        ];
    }
}
