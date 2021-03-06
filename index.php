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

	// TODO: Deal with request.type (IntentRequest, LaunchRequest)
	if ($alexaRequest->request->type == 'IntentRequest') {

		// Look at the session.request.intent.name
		switch($alexaRequest->request->intent->name) {
			case 'howto':
				$response = itHowto();
				break;
			case 'AMAZON.HelpIntent':
				$response = itHelp();
				break;
			case 'AMAZON.StopIntent':
				$response = itCancel();
				break;
			case 'AMAZON.CancelIntent':
				$response = itCancel();
				break;
			default:
				$response = itCancel();
		}

		// Setup a JSON response header and send the json response
		header('Content-Type: application/json');
		echo json_encode($response);

	} elseif ($alexaRequest->request->type == 'LaunchRequest') {

		voiceResponse();

	}

}

function itHowto() {

	global $alexaRequest;

	// Grab the question
	$question = $alexaRequest->request->intent->slots->question->value;

	// Handle special words
	$specialWords = checkForSpecialWords($question);

	switch($specialWords) {
		case 'password':
			$response = [
				"response" => [
					"outputSpeech" => [
						"type" => "SSML",
						"ssml" => "<speak>You can reset your password by visiting password dot deseret digital dot com.</speak>"
					],
				]
			];
			break;
		case 'printer':
			$response = [
				"response" => [
					"outputSpeech" => [
						"type" => "SSML",
						"ssml" => "<speak>There are two printers on the 4th floor. On 4th South there's  an HP LaserJet 500 and its IP address is 10.250.6.25. On 4th North there's another printer, but I don't know anything about that one.</speak>"
					]
				]
			];
			break;
		default:
			// Email it to IT Support
			mail('jdare@ksl.com', 'Support Request from Alexa', $question);

			// Setup a response
			$response = [
				"response" => [
					"outputSpeech" => [
						"type" => "SSML",
						"ssml" => "<speak>Okay, I've forwarded your request to I.T. Support.</speak>"
					]
				]
			];
			break;
	}

	return $response;

}

function itHelp() {

	$response = [
		"response" => [
			"outputSpeech" => [
				"type" => "SSML",
				"ssml" => '<speak>The support skill can help with a few common questions and it can forward other questions on to I.T. Support. You can say things like "Alexa, ask support to reset my password". Or, "Alexa, ask support how to setup a printer". You can also make more general comments that will get forwarded by email, for example you can say, "Alexa, tell support that the coke is out on 4th north."</speak>'
			]
		]
	];

	return $response;

}

function itCancel() {

	$response = [
		"response" => [
			"outputSpeech" => [
				"type" => "SSML",
				"ssml" => '<speak>Okay, bleep, blop, bleep, cancelled."</speak>'
			]
		]
	];

	return $response;

}

function voiceResponse() {

	$response = [
		"response" => [
			"reprompt" => [
		     	"outputSpeech" => [
		    		"type" => "PlainText",
		    		"text" => 'Okay, what can I.T. Support do for you today?'
		    	],
		    	"shouldEndSession" => false,
		    	"sessionAttributes" => []
		    ]
		]
	];

	// Setup a JSON response header and send the json response
	header('Content-Type: application/json');
	echo json_encode($response);

}

function checkForSpecialWords($wordString) {

	$trigger = '';

	// If it contains password and reset
	if (stripos($wordString, 'password')) {
		$trigger = 'password';
	}

	// If it contains printer and setup or install
	if (stripos($wordString, 'printer')) {
		$trigger = 'printer';
	}

	return $trigger;

}