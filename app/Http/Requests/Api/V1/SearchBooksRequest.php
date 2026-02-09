<?php 

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Foundation\Http\FormRequest;
use App\Traits\ApiResponse;

final class SearchBooksRequest extends FormRequest
{
    use ApiResponse;

    public function authorize(): bool
    {
        return $this->user() && $this->user()->is_admin;
    }   

    public function rules(): array
    {
        return [
            'query' => ['required', 'string', 'min:2', 'max:255'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:40'],
        ];
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            $this->forbidden('Unauthorized. Admin access required.')
        );
    }
}