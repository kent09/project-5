<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\PostcTags;
use Carbon\Carbon;
use App\Services\InfusionSoftService;

class ApplyTagOnCron extends Command
{
    protected $infusionSoftService;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:applyTagOnCron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Apply tag via cron job';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(InfusionSoftService $infusionSoftService)
    {
        parent::__construct();
        $this->infusionSoftService = $infusionSoftService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $postcode_data = PostcTags::where('status', 2)->first();

        if (!$postcode_data) {
            return;
        }
        $postcode_data->update(['status'=>3]);

        if ($postcode_data->postc_type == 1 || $postcode_data->postc_type == 0) {
            $tagname =  $postcode_data->postc_code.' + '.$postcode_data->postc_radius.$postcode_data->postc_units;
        } elseif ($postcode_data->postc_type == 2) {
            $tagname =  Carbon::parse($postcode_data->created_at)->format('d-m-Y H:i').' List';
        }

        $obj = new \App\Services\PostcodeTaggingService($this->infusionSoftService);
        $obj->deleteAreaRmvTag($postcode_data->id, 1, $postcode_data->user_id);
        $tagCount = $obj->tagBasedOnRadius($postcode_data->id, $tagname, $postcode_data->user_id);

        $postcode_data->update([ 'tag_count' => $tagCount, 'status' => 1 ]);

        $user_data = $postcode_data->user;
        $email = $user_data->email;

        $content = "The contacts inside your specific infusionsoft account have now been tagged based on the following criteria:<br><br>";
        
        if ($postcode_data->postc_type == 1 || $postcode_data->postc_type == 0) {
            $content .= "<b>Postcode: </b>".$postcode_data->postc_code."<br>";
            $content .= "<b>Radius: </b>".$postcode_data->postc_radius." ".$postcode_data->postc_units."<br>";
        } elseif ($postcode_data->postc_type == 2) {
            $content .= "<b>Postcode List: </b>".$postcode_data->postc_list."<br>";
        }
        $content .= "<b>The tag was: </b> ".$tagname." (ID: ".$postcode_data->tag_id.")<br>";
        
        #send an email
        # catch if there is an exception and will do the re-attempt for 3x
        $this->infusionSoftService->handler(function () use ($content, $email, $user_data) {
            \Mail::send('emails.tagEmail', [ 'content' => $content ], function ($message) use ($email,$user_data) {
                $message->from('help@fusedtools.com', 'FusedTools');
                $message->to(\CommanHelper::notifyEmails($user_data->id))->bcc("help@fusedtools.com")->subject('Postcode Tagging Complete');
            });
        });
    }
}
