<?php

namespace App\Http\Middleware;

use Closure;
use App\Repositories\CsvImportRepository;

class CheckState
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $csv_import = new CsvImportRepository();
        $csv_import = $csv_import->find($request->id);

        switch ($request->segment(3)) {
            case 'step2':
                $step = 2;
                break;

            case 'step3':
                $step = 3;
                break;

            case 'step4':
                $step = 4;
                break;
            
            case 'step5':
                $step = 5;
                break;

            default:
                $step = 0;
                break;
        }

        if($csv_import->step < $step && $csv_import->step != null) {

            return redirect()->route('step'.$csv_import->step , ['id' => $csv_import->id]);

        }

        return $next($request);
    }
}
