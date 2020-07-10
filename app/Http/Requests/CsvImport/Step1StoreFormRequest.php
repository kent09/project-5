<?php

namespace App\Http\Requests\CsvImport;

use App\CsvImports;
use App\Http\Requests\Request;
use Illuminate\Support\Facades\Gate;

class Step1StoreFormRequest extends Request
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        $csv_import_id = $this->route('id');

        return Gate::allows('owns-csv-import', CsvImports::findOrFail($csv_import_id));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'import_title' => 'required',
            'account_id' => 'required',
            'id' => 'required',
            'csv_file'=> 'sometimes|file'
        ];
    }

    /**
     * Override failed authorization
     */
    protected function failedAuthorization()
    {
        return abort(404);
    }
}
