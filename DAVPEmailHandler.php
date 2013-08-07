<?php

class MailHandler{

	private $_config = false;
	private $_mailer = false;
	private $akismet = false;

	function __construct(){
		include(dirname(__FILE__).'/config.php');
		require(dirname(__FILE__).'/includes/akismet/akismet.php');
		require(dirname(__FILE__).'/includes/smtp/class.phpmailer.php');
		$_config['smtp'] = (object)$smtpConfig;
		$_config['honeypot'] = (object)$honeypotConfig;
		$_akismet = new Akismet($akismetConfig);
	}

	function verifyEmailFields($emailData, $json=false){
		$is_valid = true;
		if($this->akismet->verify()){
			$email_name = $emailData['name'] . $emailData['lastname'] ? " ".$emailData['lastname'] : "";
			if($this->akismet->check($email_name, $emailData['email'], $emailData['body'], $url = FALSE)){
				$is_valid = false;
			}
		}
		if($_config['honeypot']->enabled){
			if($emailData && count($emailData) > 0){
				foreach($emailData as $key => $value){
					if(strpos($key, "required_") !== false && !empty($emailData[$key])){
						$is_valid = false;
						break;
					}
				}
			}
		}

		if($is_valid){
			$value = array("is_valid" => true, "message" => "Email data is valid");
			if($json){
				header('Content-Type: application/json');
				echo json_encode($value);
			}
			else{
				return true;
			}
		}
		else{
			$value = array("is_valid" => false, "message" => "Email data is not valid");
			if($json){
				header('Content-Type: application/json');
				echo json_encode($value);
			}
			else{
				return false;
			}
		}
	}

	function sendEmail($emailData, $request=false, $json=false){

		if(!verifyEmailFields($emailData)){
			$value = array("is_valid" => false, "message" => "Email data is not valid");
			if($json){
				header('Content-Type: application/json');
				echo json_encode($value);
			}
			else{
				return false;
			}
		}

		if($request) {
			$emailData = $_REQUEST;
		}

		if(!$emailData){
			$value = array("is_empty" => true, "message" => "Email data is empty");
			if($json){
				header('Content-Type: application/json');
				echo json_encode($value);
			}
			else{
				return false;
			}
		}

		$_mailer->From = $emailData['email'];
		$_mailer->FromName = $emailData['name'];

		if($emailData['replyto']){
			$_mailer->AddReplyTo($emailData['replyto']['email'], $emailData['email']['name']);
		}
		if($emailData['cc']){
			$_mailer->AddCC($emailData['cc']['email'], $emailData['cc']['name']);
		}
		if($emailData['bcc']){
			$_mailer->AddBCC($emailData['bcc']['email'], $emailData['bcc']['name']);
		}

		$_mailer->Subject = $emailData['subject'];
		$_mailer->Body    = $emailData['body'];
		$_mailer->AltBody = $emailData['altbody'];

		if(!$_mailer->Send()) {
			$value = array("sent" => false, "message" => "Email was not sent");
			if($json){
				header('Content-Type: application/json');
				echo json_encode($value);
			}
			else{
				return false;
			}
		}
		else{
			$value = array("sent" => true, "message" => "Email was sent successfully");
			if($json){
				header('Content-Type: application/json');
				echo json_encode($value);
			}
			else{
				return true;
			}
		}

	}

	function Mailer() { return $_mailer; }

	private function initSMTPData(){
		$_mailer = new PHPMailer;
		$_mailer->IsSMTP();
		$_mailer->Host = $_config['smtp']->host;
		$_mailer->SMTPAuth = true;
		$_mailer->Username = $_config['smtp']->username;
		$_mailer->Password = $_config['smtp']->password;
		$_mailer->SMTPSecure = $_config['smtp']->encription;
		$_mailer->WordWrap = $_config['smtp']->wordwrap;
		$_mailer->IsHTML = $_config['smtp']->ishtml;
	}

	function generateHoneypotField(){
		if(!$_config['honeypot']) return "";
		$rand_keys = array_rand($_config['honeypot']->fieldnames, 1);
		$fieldname = "required_". !empty($_config["honeypot"]->default_fieldname) ? $_config["honeypot"]->default_fieldname : $_config['honeypot']->fieldnames[$rand_keys[0]];
		$input = '<div class="r_input_div"><input type="text" name='.$fieldname.' id="r_input" value="" /></div>';
		echo $input;
	}

}


?>
