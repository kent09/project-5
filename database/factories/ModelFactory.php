<?php
use App\User;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(User::class, function (Faker\Generator $faker) {
    return [
        'invitation_token' => str_random(15),
        'email' => $faker->safeEmail,
        'password' => bcrypt(str_random(10)),
        'active' => 1,
        'infusionsoft_contact_id' => rand(1, 6),
        'infusion_soft_contact_id' => rand(1, 6),
        'free_docs' => 10
    ];
});
