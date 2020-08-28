<?php
include('simple_html_dom.php');
include('scrapers/google_scraper.php');
include('scrapers/bing_scraper.php');
include_once('class.verifyEmail.php');

function curl_response($url, $email, $headers=false) {

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, $url . $email);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec($curl);
	curl_close($curl);

	return $result;
}

function verify_recapcha_v2($secret) {
	$curlx = curl_init();

	curl_setopt($curlx, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
	curl_setopt($curlx, CURLOPT_HEADER, 0);
	curl_setopt($curlx, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($curlx, CURLOPT_POST, 1);

	$post_data = [
	    "secret" => $secret,
	    "response" => $_POST["googleCapcha"]
	];

	curl_setopt($curlx, CURLOPT_POSTFIELDS, $post_data);

	$resp = json_decode(curl_exec($curlx));

	curl_close($curlx);

	return $resp->success;
}

function get_email_status($email) {

	$vmail = new verifyEmail();
	$vmail->setStreamTimeoutWait(20);
	$vmail->Debug= TRUE;
	$vmail->Debugoutput= 'html';

	$vmail->setEmailFrom('localhost@admin.com');

	if ($vmail->check($email)) {
		$email_exists = true;
	}else {
		$email_exists = false;
	}

	return $email_exists;
}

function call_have_i_been_pawned($email) {
	$headers = array(
    	"hibp-api-key: 356caf020183406f88b0a67e4c445867",
    	"user-agent: email_investigator",
	);

	$breaches = curl_response("https://haveibeenpwned.com/api/v3/breachedaccount/", $email, $headers);
	$breaches = json_decode($breaches);

	$breach_list = array();
	foreach ($breaches as $breach) {
		foreach ($breach as $breach_name) {
			$breach_list[] = $breach_name;
		}
	}

	return $breach_list;
}

$email = $_POST["email"];

if ($email) {
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$capcha_response = verify_recapcha_v2("6LdmtcAZAAAAAC73DOUkWIK0zAo4wHaK7gJknjMp");

		// if ($capcha_response) {
			echo json_encode(
				array(
					"data" => array(
						"have_i_been_pawned" => call_have_i_been_pawned($email)
					),	
					"scrapers" => array(
						"google" => scrape_google($email, 2),
						"bing" => scrape_bing($email)
					)
				)
			);
		// }
	}else {
		echo json_encode("bad email");
	}
}
