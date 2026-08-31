<?php
declare(strict_types=1);

namespace App\Web\Mail;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ClientController;
use App\Web\Mail\TestMail;
use App\Web\Notification\SendNotificationEvent;
use Illuminate\Support\Facades\Auth;
use App\Domain\EmailTemplate\EmailTemplateService;

class MailController extends ClientController
{
    public function testMail()
    {

      app(EmailTemplateService::class)->send(
					templateKey: 'claim-batch-ondc-verification',
					toEmail: 'ankit.tiwari@uneecops.in',
					data: [
						'name' => 'Ankit Tiwari',
						'batch_number' => '21323223'
					]
				);

    
    app(EmailTemplateService::class)->send(
					templateKey: 'claim-batch-received-final-verification',
					toEmail: 'ankit.tiwari@uneecops.in',
					data: [
						'name' => 'Ankit Tiwari',
						'batch_number' => '21323223'
					]
				);
       app(EmailTemplateService::class)->send(
					templateKey: 'claim-batch-received-financial-verification',
					toEmail: 'ankit.tiwari@uneecops.in',
					data: [
						'name' => 'Ankit Tiwari',
						'batch_number' => '21323223'
					]
				);


    app(EmailTemplateService::class)->send(
					templateKey: 'new-claim-batch-received-for-verification',
					toEmail: 'ankit.tiwari@uneecops.in',
					data: [
						'name' => 'Ankit Tiwari',
						'batch_number' => '21323223'
					]
				);

                 return "✅ Email send  Successfully";
        // $roleId= getRoleIdBySnp('snp');
        // event(new SendNotificationEvent(templateKey: 'batch-approved-by-nsic-finance',fromUserId: AuthId(),toUserId: AuthId(),formRole: $roleId,toRole: null, message: [
        //  'BATCH_NUMBER' => '21323223'
        // ]));
    //    message: [
    //         'SUCCESS_COUNT' => 120,
    //         'TOTAL_COUNT'   => 150
    //     ]
//     message: [
//     'CLAIM_ID' => 12345,
//     'BATCH_NUMBER' => 789
// ]
    //  message: [
    //     'BATCH_NUMBER' => '21323223'
    // ]
      // ));

         return "✅ Notification Event Dispatched Successfully";


        try {

            Mail::to('ankit.tiwari@uneecops.in')->send(new TestMail());

            return "✅ Mail Sent Successfully";

        } catch (\Exception $e) {
            return "❌ Error: " . $e->getMessage();
        }
    
    }
}