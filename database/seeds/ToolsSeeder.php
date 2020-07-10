<?php

use App\Tools;
use Illuminate\Database\Seeder;

class ToolsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $tools = $this->tools();

        foreach($tools as $tool) {
            Tools::updateOrCreate($tool);
        }

    }

    private function tools() {
        return [
            ['code' => 'tools','tool_name' => 'Fused Tools', 'tool_url' => 'https://tools.fusedsuite.com'],
            ['code' => 'docs','tool_name' => 'Fused Docs', 'tool_url' => 'https://docs.fusedsuite.com'],
            ['code' => 'invoices','tool_name' => 'Fused Invoices', 'tool_url' => 'https://invoices.fusedsuite.com']
        ];
    }

}
