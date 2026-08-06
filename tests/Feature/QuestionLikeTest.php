<?php

namespace Tests\Feature;

use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event as FacadesEvent;
use App\Events\QuestionLike;
use Tests\TestCase;

class QuestionLikeTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    public function test_question_like_event(): void
    {
        FacadesEvent::fake();
        $user = User::factory()->create();
        $question =Question::factory()->create([
            'user_id'=>User::factory()->create()->id
        ]);

        $response =$this->actingAs($user)->postJson('question/like/'.$question->id);
        $response->assertOk();

        FacadesEvent::assertDispatched(
            QuestionLike::class,
            function ($event) use ($question){
                return $event->question->id === $question->id;
            }
        );
    }
}
