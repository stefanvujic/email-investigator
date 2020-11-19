<?php
define("CAPTCHA_SECRET", "6LdmtcAZAAAAAC73DOUkWIK0zAo4wHaK7gJknjMp");
define("DOMAIN", "email-investigator.local");

include('classes/class.scraper.php');
include('classes/class.pawnd.php');
include('recapcha/src/autoload.php');

if (isset($_POST["email"])) {
	$email = $_POST["email"];
}
if (isset($_POST["googleCapcha"])) {
	$capcha_response = $_POST["googleCapcha"];
}

$pawnd = new Pawnd($email);

$scraper = new Scraper($email);

$recaptcha = new \ReCaptcha\ReCaptcha(CAPTCHA_SECRET);
$captcha_resp = $recaptcha->setExpectedHostname(DOMAIN)
				  ->verify($capcha_response, $_SERVER['SERVER_ADDR']);


if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
	if ($captcha_resp->isSuccess()) {
		echo json_encode(
			array(
				"data" => array(
					"have_i_been_pawned" => $pawnd->get_breaches()
				),	
				"scrapers" => $scraper->scrape()
			)
		);
	}else {
	    echo json_encode("bad captcha");
	}	
}else {
	echo json_encode("bad email");
}