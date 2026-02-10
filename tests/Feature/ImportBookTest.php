<?php

use App\Enums\UserRole;
use App\Models\{User, Book};
use App\Services\GoogleBooksService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\{actingAs, postJson, assertDatabaseCount, assertDatabaseHas};

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'role' => UserRole::Admin->value,
    ]);
    $this->endpoint = '/api/v1/books/import';
});

test('it requires authentication', function () {
    postJson($this->endpoint, [
        'google_books_id' => 'test123',
    ])->assertStatus(401);
});

test('it requires google books id', function () {
    actingAs($this->user)
        ->postJson($this->endpoint, [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['google_books_id']);
});

test('it returns existing book if already imported', function () {
    $book = Book::factory()->create([
        'google_books_id' => 'existing123',
    ]);

    actingAs($this->user)
        ->postJson($this->endpoint, [
            'google_books_id' => 'existing123',
        ])
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Book already exists in database',
        ])
        ->assertJsonPath('data.book.id', $book->id);

    assertDatabaseCount('books', 1);
});

test('it returns 404 when book not found in google api', function () {
    $googleService = $this->mock(GoogleBooksService::class);
    $googleService->shouldReceive('getBook')
        ->with('notfound123')
        ->once()
        ->andReturn(null);

    actingAs($this->user)
        ->postJson($this->endpoint, [
            'google_books_id' => 'notfound123',
        ])
        ->assertStatus(404)
        ->assertJson([
            'success' => false,
            'message' => 'Book not found in Google Books API',
        ]);

    assertDatabaseCount('books', 0);
});

test('it successfully imports new book from google api', function () {
    $googleBookData = [
        'id' => 'newbook123',
        'volumeInfo' => [
            'title' => 'Test Book',
            'authors' => ['Test Author'],
            'industryIdentifiers' => [
                ['type' => 'ISBN_13', 'identifier' => '9781234567890'],
            ],
            'imageLinks' => [
                'thumbnail' => 'http://example.com/cover.jpg',
            ],
        ],
    ];

    $transformedData = [
        'google_books_id' => 'newbook123',
        'title' => 'Test Book',
        'author' => 'Test Author',
        'isbn' => '9781234567890',
        'cover_url' => 'http://example.com/cover.jpg',
    ];

    $googleService = $this->mock(GoogleBooksService::class);
    $googleService->shouldReceive('getBook')
        ->with('newbook123')
        ->once()
        ->andReturn($googleBookData);
    
    $googleService->shouldReceive('transformToSimplified')
        ->with([$googleBookData])
        ->once()
        ->andReturn([$transformedData]);

    actingAs($this->user)
        ->postJson($this->endpoint, [
            'google_books_id' => 'newbook123',
        ])
        ->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Book imported successfully from Google Books',
        ]);

    assertDatabaseHas('books', [
        'google_books_id' => 'newbook123',
        'title' => 'Test Book',
        'author' => 'Test Author',
        'isbn' => '9781234567890',
    ]);
});

test('it handles database errors gracefully', function () {
    $googleBookData = ['id' => 'error123'];
    
    $googleService = $this->mock(GoogleBooksService::class);
    $googleService->shouldReceive('getBook')
        ->with('error123')
        ->once()
        ->andReturn($googleBookData);
    
    $googleService->shouldReceive('transformToSimplified')
        ->andThrow(new \Exception('Database error'));

    actingAs($this->user)
        ->postJson($this->endpoint, [
            'google_books_id' => 'error123',
        ])
        ->assertStatus(500)
        ->assertJson([
            'success' => false,
            'message' => 'Failed to import book',
        ]);
});