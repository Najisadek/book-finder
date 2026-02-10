<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;
    
    public function definition(): array
    {
        return [
            'google_books_id' => $this->faker->uuid,
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name,
            'isbn' => $this->faker->isbn13,
            'cover_url' => $this->faker->imageUrl(),
        ];
    }
}
