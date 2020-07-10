<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::enableQueryLog();
        $this->call(RoleSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(UserPlansTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(InfsCountrySeed::class);
        $this->call(InfusionsoftAccount::class);
        // $this->call(PostcCountriesSeeder::class);
        // $this->call(PostcCodesAuSeeder::class);
        // $this->call(PostcCodesNzSeeder::class);
        // $this->call(PostcCodesUsSeeder::class);
        // $this->call(PostcCodesCaSeeder::class);
        $this->call(ToolsSeeder::class);
        $this->call(ToolFeaturesSeeder::class);
        $this->call(PlansSeeder::class);
    }
}
