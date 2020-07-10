<?php

use App\PostcCodesAu;
use App\PostcCountries;
use Illuminate\Database\Seeder;

class PostcCodesAuSeeder extends Seeder
{

    protected $country = 'Australia';

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $states = $this->stateCode();
        $country = PostcCountries::where('country_name', $this->country)
            ->first();
            
        foreach($states as $state) {
            PostcCodesAu::updateOrCreate(array_merge($state, [
                'country_id' => $country->id
            ]));
        }
    }

    /**
     * List of all state code
     * 
     * @return array
     */
    private function stateCode()
    {
        return [];
    }
}
