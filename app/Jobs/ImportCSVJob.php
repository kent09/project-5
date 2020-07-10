<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\CsvImports;
use FusedSoftware\Contracts\InfusionSoftContract;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ImportCSVJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $csvImport;

    protected $infsAccount;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($csvImport, $infsAccount)
    {
        $this->csvImport = $csvImport;
        $this->infsAccount = $infsAccount;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $csvImport = CsvImports::where('status', 0)->get();

        foreach($csvImport as $csv) {
            $this->create($csv);
            CsvImports::find($csv->id)
                 ->update('status', 3);
        }
    }

    /**
     * Create contact, companies, order, products in infusionsoft
     * 
     * @param object
     * @return void
     */
    private function create($csv)
    {
        $infusion = $this->infusionSoft($csv->account_id);
        $toImport = $csv->import_results;

        CsvImports::find($csv->id)
                 ->update('status', 2);

        if (isset($toImport['contacts'])) {

            $infusion->contacts()
                ->create($toImport['contacts']);
        }

        if (isset($toImport['companies'])) {
            $infusion->contacts()
                ->create($toImport['companies']);
        }

        if (isset($toImport['orders'])) {
            $infusion->orders()
                ->create($toImport['orders']);
        }

        if (isset($toImport['products'])) {
            $infusion->orders()
                ->create($toImport['products']);
        }
    }

    /**
     * Instanciate infusionsoft
     * 
     * @param id
     * @return Infusionsoft
     */
    private function infusionSoft($id)
    {
        return app(InfusionSoftContract::class)
             ->client($id);
    }

}
