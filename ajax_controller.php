<?php
include('class.scraper.php');

if (isset($_POST["email"])) {
	$email = $_POST["email"];
}

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

$scraper = new Scraper($email);

if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
	echo json_encode(
		array(
			"data" => array(
				"have_i_been_pawned" => call_have_i_been_pawned($email)
			),	
			"scrapers" => $scraper->scrape()
		)
	);
}else {
	echo json_encode("bad email");
}