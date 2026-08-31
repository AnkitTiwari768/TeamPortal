<?php

namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class PHPMailerService
{
    public function sendEmail($to, $subject, $body , $fromName = 'Your Name')
    {
		$mail = new PHPMailer();
		$mail->Encoding = "base64";
		$mail->SMTPAuth = true;
		$mail->Host = "smtp.zeptomail.in";
		$mail->Port = 587;
		$mail->Username = "emailapikey";
		$mail->Password = 'PHtE6r0FS+69gjV5+hUE5/a9FpWjMdh9rOs2LglEuIYTCaQBGk1c+oh6kTa3qxt8UqVLR/KSyopqtO7KuuPRJ27uMWhNXWqyqK3sx/VYSPOZsbq6x00VtVQTd0fbUoHvetdr3CXev9fcNA==';
		$mail->SMTPSecure = 'TLS';
		$mail->isSMTP();
		$mail->IsHTML(true);
		$mail->CharSet = "UTF-8";
		$mail->From = "noreply@team.msmemart.com";
		$mail->addAddress($to);
		$mail->Body=$body;
		$mail->Subject=$subject;
		//$mail->SMTPDebug = 1;
		/*$mail->Debugoutput = function($str, $level) {
			echo "debug level $level; message: $str"; echo "<br>";
		};*/
		if(!$mail->Send()) {
			return false;
			//echo "Mail sending failed";
		} else {
			return true;
			//echo "Successfully sent";
		}
		
		
        /*$mail = new PHPMailer(true);
			try{
			$mail->isSMTP();
			$mail->Host       = 'otprelay.nic.in';
			$mail->SMTPAuth   = true;
			$mail->Username   = 'officer2-csghyd@nic.in';
			$mail->Password   = 'Efxs*123wer#';
			//$mail->Password   = 'uxulrjmlyroywhpi';
			//$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;//working with mobile network
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			//$mail->Port       = 25;//working with mobile network ,465
			$mail->Port       = 465;
				//$mail->SMTPDebug = SMTP::DEBUG_SERVER;
				$mail->SMTPOptions = array(
						 'ssl' => array(
								 'verify_peer' => false,
								 'verify_peer_name' => false,
								 'allow_self_signed' => true
						 )
				);

			$mail->setFrom('officer2-csghyd@nic.in', 'Uneecops Test mail');

			$mail->addAddress('sujay.php@gmail.com');


			$mail->isHTML(true);
			$mail->Subject = 'Mail is working'.time();
			$mail->Body    = 'This is the HTML message body <b>in bold!</b>';
			$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

			$mail->send();
			echo 'Message has been sent';
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}*/

    }

}


?>
