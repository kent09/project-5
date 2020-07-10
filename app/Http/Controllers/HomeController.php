<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Transformer\UserForSupportTransformer;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
     
     
    public function __construct()
    {
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('toolshome');
    }

    public function supportView(UserForSupportTransformer $transformer)
    {
        $user = \Auth::user();
        $user = $transformer->transform($user);
        return view('support', compact('user'));
    }
    
    public function support(Request $request)
    {
        $this->validate($request, [
              "name" => "required",
              "email" => "required|email|max:255",
              "type" => "required",
              "message" => "required",
        ]);
        $request = $request->all();
        
        $response = "<h3>Hi,</h3>";
        $response .= "<p>We have got support request.</p>";
        $response .= "<p><strong>Name</strong> : ".$request['name']."</p>";
        $response .= "<p><strong>Email</strong> : ".$request['email']."</p>";
        $response .= "<p><strong>Inquiry Type</strong> : ".$request['type']."</p>";
        $response .= "<p><strong>Contact Number</strong> : ".$request['phone']."</p>";
        $response .= "<p><strong>Message</strong> : ".$request['message']."</p>";
        
        \Mail::send('emails.sendMail', ['content' => $response, 'name' => $request['name'] ], function ($message) use($request) {
            $message->from($request['email'], $request['name']);
            $message->to('help@fusedtools.com')->subject('Fuesdtools support.');
        });
        
        return back()->with('success', 'Your message was sent successfully.');
    }

    public function homeredirect()
    {
        return redirect('/dashboard');
    }
}
