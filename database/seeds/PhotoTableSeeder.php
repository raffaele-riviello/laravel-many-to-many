<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

use App\Photo;
use App\User;

class PhotoTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $user =  User::inRandomOrder()->first();
        // $photo = new Photo;
        // $photo->user_id = $user->id;
        // $photo->name = 'Lorem Ipsum';

        // // takes the files in our folder
        // $files = Storage::disk('public')->files('images');
        // $photo->path = $files[rand(0, count($files) - 1)];;

        // $photo->description = 'Lorem ipsum';
        // $photo->save();

        // takes the files in our folder
        $files = Storage::disk('public')->files('images');
        foreach ($files as $file) {
            $user =  User::inRandomOrder()->first();
            $photo = new Photo;
            $photo->user_id = $user->id;
            $photo->name = 'Lorem Ipsum';
            $photo->path = $file;
            $photo->description = 'Lorem ipsum';
            $photo->save();
        }
    }
}
