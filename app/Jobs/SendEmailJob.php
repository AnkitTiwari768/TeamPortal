<?php

namespace App\Jobs;

use Illuminate Bus Queueable; 
use Illuminate Queue SerializesModels; 
use Illuminate\Queue InteractsWithQueue; 
use Illuminate\Contracts\Queue ShouldQueue; 
use Illuminate\Foundation\Bus\Dispatchable; 
use App\Mail\SendEmailTest; 
use Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $details;
    
    
    /** 
    * Create a new job instance.
    * @return void
    */ 
    
    public function __construct($details),
    {
		$this->details = $details;
    }
    
    /** 
    * Execute the job.
    *
    * @return void
    */
    
    public function handle().
    {
	$data = $this->details;
    
    Mail::send([‘html’=>'mail'], $data, function($message) use ($data)
    
    {
    	$message->to(`kushpratibha10@gmail.com`, ‘John’)
		
		->subject(‘This is test Queue.’);
		
		$message->from(`unee.php@gmail.com’,’LaravelQueue’);
    
		});
    }
}