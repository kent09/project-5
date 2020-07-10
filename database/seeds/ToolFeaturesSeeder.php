<?php

use Illuminate\Database\Seeder;
use App\ToolFeatures;

class ToolFeaturesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tool_features = $this->tool_features();

        foreach ($tool_features as $tool_feature) {
            ToolFeatures::updateOrCreate($tool_feature);
        }
    }

    private function tool_features() {
        return [
            [
                'tool' => 'docs', 
                'enabled' => true, 
                'description' => 'Document Sending', 
                'token_cost' => 5,
                'amount_per' => 1,
                'amount_unit' => 'document' 
            ],
            [
                'tool' => 'tools', 
                'enabled' => true, 
                'description' => 'Script Tasks', 
                'token_cost' => 1,
                'amount_per' => 1,
                'amount_unit' => 'script' 
            ],
            [
                'tool' => 'tools', 
                'enabled' => true, 
                'description' => 'CSV Import', 
                'token_cost' => 1,
                'amount_per' => 100,
                'amount_unit' => 'records' 
            ],
            [
                'tool' => 'tools', 
                'enabled' => true, 
                'description' => 'Geo Tools', 
                'token_cost' => 1,
                'amount_per' => 100,
                'amount_unit' => 'records' 
            ],
            [
                'tool' => 'invoices', 
                'enabled' => true, 
                'description' => 'Xero Tools', 
                'token_cost' => 1,
                'amount_per' => 1,
                'amount_unit' => 'invoice' 
            ],
        ];
    }
}
