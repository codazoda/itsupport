<?php

// Turn on all error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Setup some variables
$validAppId = 'amzn1.ask.skill.535f89e1-f247-4ecf-9853-07a0414ea8b1';

// Get raw POST data
$postData = file_get_contents('php://input');

// Write debug info
file_put_contents('debug.log', $postData);

// Decode the JSON
$alexaRequest = json_decode($postData);
$response = '';

// Verify the application ID
if ($alexaRequest->session->application->applicationId === $validAppId) {

	// Look at the session.request.intent.name
	switch($alexaRequest->request->intent->name) {
		case 'howto':
			$response = itHowto();
			break;
		default:
			$response = itUnknown();
	}

	// Setup a JSON response header and send the json response
	header('Content-Type: application/json');
	echo json_encode($response);

}

function itUnknown() {

	// Setup a response
	$response = [
		"response" => [
			"outputSpeech" => [
				"type" => "SSML",
				"ssml" => "<speak>What</speak>"
			]
		]
	];

	return $response;

}

function itHowto() {

	global $alexaRequest;

	// Grab the question
	$question = $alexaRequest->request->intent->slots->question->value;

	// Handle special words
	$specialWords = checkForSpecialWords($question);

	if ($specialWords) {

		switch($specialWords) {
			case 'password':
				$response = [
					"response" => [
						"outputSpeech" => [
							"type" => "SSML",
							"ssml" => "<speak>You can easily reset your password by visiting password dot deseret digital dot com.</speak>"
						]
					]
				];
				break;
			case 'printer':
				$response = [
					"response" => [
						"outputSpeech" => [
							"type" => "SSML",
							"ssml" => "<speak>There are two printers on the 4th floor. On 4th South it's called blah and the IP address is 192.168.1.1. On 4th North it's called blah and the IP address is 192.168.1.2</speak>"
						]
					]
				];
				break;
		}


	} else {

		// Email it to IT Support
		mail('jdare@ksl.com', 'Support Request from Alexa', $question . "\n\n" . json_encode($alexaRequest));

		// Setup a response
		$response = [
			"response" => [
				"outputSpeech" => [
					"type" => "SSML",
					"ssml" => "<speak>Okay, I've forwarded your request to I.T. Support.</speak>"
				]
			]
		];

	}

	return $response;

}

function checkForSpecialWords($wordString) {

	// If it contains password and reset
	if (strpos($wordString, 'password') !== false &&
	    strpos($wordString, 'reset') !== false) {
		$trigger = 'password';
	}

	// If it contains printer and setup or install
	if (strpos($wordString, 'printer') !== false &&
	   (strpos($wordString, 'set up') !== false || strpos($wordString, 'install') !== false)) {
		$trigger = 'printer';
	}

	return $trigger;

}