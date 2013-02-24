<?php defined('C5_EXECUTE') or die(_("Access Denied."));

class ShootQContactFormBlockController extends BlockController {

	protected $btDescription = "ShootQ Contact Form";
	protected $btName = "ShootQ Contact Form";
	protected $btTable = 'btShootqContactForm'; //Change db.xml table name to match this
	protected $btInterfaceWidth = "500";
	protected $btInterfaceHeight = "450";
	
	protected $btCacheBlockRecord = true;
	protected $btCacheBlockOutput = true;
	protected $btCacheBlockOutputOnPost = false;
	protected $btCacheBlockOutputForRegisteredUsers = true;
	protected $btCacheBlockOutputLifetime = 300;
	
	public function view() {
		$this->set('showThanks', $this->get('thanks'));
	}
	
	public function action_submit_form() {
		$error = $this->validate_form($this->post());
		
		if ($error->has()) {
			//Fail -- re-display the form (C5 form helpers will repopulate user's entered data for us)
			$this->set('errors', $error->getList());
		} else {
			//Success -- send notification email and reload/redirect page to avoid browser warnings about re-posting content if user reloads page 
			$this->submitToShootQ($this->post());
			$redirect_to_path = Page::getCurrentPage()->getCollectionPath() . '?thanks=1';
			$this->redirect($redirect_to_path);
		}
	}
	
	public function validate_form($post) { //Note: this function can't just be called "validate" because then C5 automatically calls it to validate the add/edit dialog!
		$val = Loader::helper('validation/form');
		$val->setData($post);
		$val->addRequired('firstname', 'You must enter your first name.');
		$val->addRequired('lastname', 'You must enter your last name.');
		$val->addRequiredEmail('email', 'You must provide a valid email address');
		$val->test();
		$error = $val->getError();
		
		//Perform manual checks (anything that the validation helper doesn't have rules for)
		$iph = Loader::helper('validation/ip');
		if (!$iph->check()) {
			$error->add($iph->getErrorMessage());
		}
		
		//Note that we don't have to validate CSRF tokens ourselves
		// because C5 handles it for us via the $this->action() function.
		
		return $error;
	}

    private function submitToShootQ($post) {
        $data = array(
            "api_key"   => $this->apiKey,
            "contact"   => array(
                "first_name"    => $post['firstname'],
                "last_name"     => $post['lastname'],
                "emails"        => array(
                    array(
                        "type"  => "Home",
                        "email" => $post['email'],
                    ),
                ),
            ),
            "event"     => array(
                "type"      => $this->eventType,
                "remarks"   =>  nl2br($post['message']),
            ),
        );

        $data = json_encode($data);
        $url = "https://app.shootq.com/api/" . $this->brandAbbreviation . "/leads";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/json"));
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        /* get the response from the ShootQ API */
        $response_json = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if($httpcode != 200) {
            $this->send_notification_email($data, $response_json, $httpcode);
        }
    }
	
	private function send_notification_email($data, $response, $httpcode) {
		$subject = '['.SITE.'] New Contact Form Submission';
		$body = <<<EOB
A new submission has been made to the ShootQ contact form, and something went wrong with sending it to ShootQ:

Name: {$data['firstname']} {$data['lastname']}
Email: {$data['email']}

Message:
{$data['message']}

For debugging purposes, the following is the response from ShootQ:
Response Code: {$httpcode}
Response Data: {$response}

EOB;
//Dev Note: The "EOB;" above must be at the far-left of the page (no whitespace before it),
//          and cannot have anything after it (not even comments).
//			See http://www.php.net/manual/en/language.types.string.php#language.types.string.syntax.heredoc
		
		//Send email
		$mh = Loader::helper('mail');
		$mh->from(UserInfo::getByID(USER_SUPER_ID)->getUserEmail());
		$mh->to($this->notifyEmail);
		$mh->setSubject($subject);
		$mh->setBody($body); //Use $mh->setBodyHTML() if you want an HTML email instead of (or in addition to) plain-text
		$mh->sendMail(); 
	}
}
