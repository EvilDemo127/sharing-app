<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionTag;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */

    public function run(): void
    {
        // User::factory(4)->create();
        Question::factory(20)->create();
        QuestionTag::factory(20)->create();
        // User::factory()->create([
        //     'name' => 'user',
        //     'email' => 'user@gmail.com',
        //     'password'=> Hash::make('password'),
        //     'image'=>'default.png'
        // ]);

        // Tag::create([
        //     'name'=>'Web Dev',
        //     'slug'=>Str::slug('Web Dev'),
        // ]);

        // Tag::create([
        //     'name'=>'Web Desig',
        //     'slug'=>Str::slug('Web Desig'),
        // ]);

        // Tag::create([
        //     'name'=>'Mobile Dev',
        //     'slug'=>Str::slug('Mobile Dev'),
        // ]);

  

        // QuestionTag::create([
        //     'question_id'=>1,
        //     'tag_id'=>1
        // ]);

        // QuestionTag::create([
        //     'question_id'=>1,
        //     'tag_id'=>2
        // ]);
       
    }
}
