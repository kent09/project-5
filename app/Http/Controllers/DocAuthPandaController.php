<?php

namespace App\Http\Controllers;

use App\Services\PandaDocService;
use App\InfsAccount;
use App\DocsAccountsPanda;
use App\Http\Requests;
use Illuminate\Http\Request;

class DocAuthPandaController extends Controller
{
    //

    protected $pandaDocService;

    public function __construct(PandaDocService $pandaDocService)
    {
        $this->pandaDocService = $pandaDocService;
    }
    
    public function index()
    {
        $pandadoc_connect = false;
        if (DocsAccountsPanda::where('user_id', \Auth::id())->first()) {
            $pandadoc_connect = true;
        }
        return view('docs.pandadoc-auth', compact('pandadoc_connect'));
    }
    
    public function connectPandaDoc()
    {
        return redirect($this->pandaDocService->getAuthorizationUrl());
    }

    public function redirect(Request $request)
    {
        if (DocsAccountsPanda::where('user_id', \Auth::id())->first()) {
            $this->pandaDocService->requestAndStoreAccessTokens($request);
            return redirect('docs/manageaccounts')->with('success', 'You have configured your account with fusedtools.');
        }
        $this->pandaDocService->requestAndStoreAccessTokens($request);
        $pandadoc_connect = false;
        if (DocsAccountsPanda::where('user_id', \Auth::id())->first()) {
            $pandadoc_connect = true;
        }
        
        return redirect('docs/manage-panda-account');
    }

    public function deletePandadoc(Request $request)
    {
        DocsAccountsPanda::where('user_id', \Auth::id())->first()->delete();
        return redirect('docs/manageaccounts')->with('success', 'Pandadoc integration removed.');
    }
}
