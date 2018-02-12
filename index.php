<?php

// Turn on all error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Setup some variables
$validAppId = 'amzn1.ask.skill.535f89e1-f247-4ecf-9853-07a0414ea8b1';

// Get raw POST data
$postData = file_get_contents('php://input');

// Decode the JSON
$alexaRequest = json_decode($postData);
$response = '';

// Verify the application ID
//if ($alexaRequest->session->application->applicationId === $validAppId) {

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

//}

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

	// TODO: Email IT Support

	// TODO: Grab the question
	$question = $alexaRequest->request->intent->slots->question;

	// Setup a response
	$response = [
		"response" => [
			"outputSpeech" => [
				"type" => "SSML",
				"ssml" => "<speak>{$question}</speak>"
			]
		]
	];

	return $response;

}