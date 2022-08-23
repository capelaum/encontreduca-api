<?php

namespace Tests\Feature\Api\V1;

use App\Http\Resources\V1\Review\ReviewResource;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use  RefreshDatabase;

    private array $reviewResourceKeys = [
        'id',
        'userId',
        'resourceId',
        'author',
        'authorAvatar',
        'authorReviewCount',
        'rating',
        'comment',
        'updatedAt'
    ];

    public function test_list_reviews()
    {
        $this->createReviews(10);

        $this->getJson(route('reviews.index'))
            ->assertOk()
            ->assertJsonStructure(['*' => $this->reviewResourceKeys])->json();
    }

    public function test_show_review()
    {
        $review = $this->createReviews()->first();

        $response = $this->getJson(route('reviews.show', $review->id))
            ->assertOk()
            ->json();

        $reviewResponse = (new ReviewResource($review))->toArray($review);

        $this->assertEquals($response, $reviewResponse);
    }

    public function test_store_review()
    {
        $this->authUser();
        $resource = $this->createResource();
        $review = Review::factory()->make();

        $data = [
            'resourceId' => $resource->id,
            'rating' => $review->rating,
            'comment' => $review->comment
        ];

        $this->postJson(route('reviews.store'), $data)
            ->assertCreated()
            ->assertJsonStructure($this->reviewResourceKeys);
    }

    public function test_user_cannot_create_two_reviews_on_same_resource()
    {
        $this->authUser();

        $resource = $this->createResource();

        $review = $this->createReviews(args: [
            'user_id' => Auth::user()->id,
            'resource_id' => $resource->id
        ])->first();

        $this->postJson(route('reviews.store'), [
            'resourceId' => $resource->id,
            'rating' => $review->rating,
            'comment' => $review->comment
        ])->assertStatus(409)
            ->assertJson([
                'message' => 'Você já avaliou este recurso.'
            ]);

    }

    public function test_update_review()
    {
        $this->authUser();
        $review = $this->createReviews(args: ['user_id' => Auth::id()])->first();

        $this->patchJson(route('reviews.update', $review->id), [
            'rating' => 5,
            'comment' => 'New comment'
        ])->assertOk()
            ->assertJsonStructure($this->reviewResourceKeys);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => 'New comment'
        ]);
    }

    public function test_user_cannot_update_review_of_another_user()
    {
        $this->withExceptionHandling();
        $this->authUser();
        $review = $this->createReviews()->first();

        $this->patchJson(route('reviews.update', $review->id), [
            'rating' => 5,
            'comment' => 'New comment'
        ])->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }

    public function test_delete_review()
    {
        $this->authUser();
        $review = $this->createReviews(args: ['user_id' => Auth::id()])->first();

        $this->deleteJson(route('reviews.destroy', $review->id))
            ->assertNoContent();

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id
        ]);
    }

    public function test_user_cannot_delete_review_of_another_user()
    {
        $this->withExceptionHandling();
        $this->authUser();
        $review = $this->createReviews()->first();

        $this->deleteJson(route('reviews.destroy', $review->id))
            ->assertStatus(401)
            ->assertJsonStructure([
                'message'
            ]);
    }
}
