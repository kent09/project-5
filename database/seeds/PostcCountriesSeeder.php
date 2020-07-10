<?php

use App\PostcCountries;
use Illuminate\Database\Seeder;

class PostcCountriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = $this->countries();

        foreach($countries as $country) {
            PostcCountries::updateOrCreate($country);
        }
    }

    /**
     * List of all countries
     * 
     * @return array
     */
    private function countries()
    {
        return [];
    }
}
