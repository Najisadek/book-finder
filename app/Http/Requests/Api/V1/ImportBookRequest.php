<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ApiResponse;

final class ImportBookRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return $this->user() && $this->user()->is_admin;
    }

    public function rules(): array
    {
        return [
            'google_books_id' => ['required', 'string'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbidden('Unauthorized. Admin access required.')
        );
    }
}