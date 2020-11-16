<?php
include('class.scraper.php');

// function verify_recapcha_v2() {
// 	$curlx = curl_init();

// 	curl_setopt($curlx, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
// 	curl_setopt($curlx, CURLOPT_HEADER, 0);
// 	curl_setopt($curlx, CURLOPT_RETURNTRANSFER, 1); 
// 	curl_setopt($curlx, CURLOPT_POST, 1);

// 	$post_data = [
// 	    "secret" => "6LdmtcAZAAAAAC73DOUkWIK0zAo4wHaK7gJknjMp",
// 	    "response" => $_POST["googleCapcha"]
// 	];

// 	curl_setopt($curlx, CURLOPT_POSTFIELDS, $post_data);

// 	$resp = json_decode(curl_exec($curlx));

// 	curl_close($curlx);

// 	return $resp->success;
// }

// function get_email_status($email) {

// 	$vmail = new verifyEmail();
// 	$vmail->setStreamTimeoutWait(20);
// 	$vmail->Debug= TRUE;
// 	$vmail->Debugoutput= 'html';

// 	$vmail->setEmailFrom('localhost@admin.com');

// 	if ($vmail->check($email)) {
// 		$email_exists = true;
// 	}else {
// 		$email_exists = false;
// 	}

// 	return $email_exists;
// }

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

function call_have_i_been_pawned($email) {
	$headers = array(
    	"hibp-api-key: 356caf020183406f88b0a67e4c445867",
    	"user-agent: email_investigator",
	);

	$breaches = curl_response("https://haveibeenpwned.com/api/v3/breachedaccount/", $email . "?truncateResponse=false", $headers);
	
	if (!empty($breaches)) {
		$breaches = json_decode($breaches);
	}else {
		$breaches = $breaches;
	}

	return $breaches;
}

if (isset($_POST["morePawndInfo"])) {
	$get_more_pawned_info = $_POST["morePawndInfo"];
}
if (isset($_POST["email"])) {
	$email = $_POST["email"];
}

$scraper = new Scraper($email);

echo json_encode(
	array(
		"data" => array(
			"have_i_been_pawned" => call_have_i_been_pawned($email)
		),	
		"scrapers" => $scraper->scrape()
	)
);
