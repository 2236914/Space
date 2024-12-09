<?php
require_once 'HTTP/Request2.php';
$request = new HTTP_Request2();
$request->setUrl('https://51xlkj.api.infobip.com/sms/2/text/advanced');
$request->setMethod(HTTP_Request2::METHOD_POST);
$request->setConfig(array(
    'follow_redirects' => TRUE
));
$request->setHeader(array(
    'Authorization' => 'App aac8d9570753ad1481fdd92e68f74db7-12573a69-b8b6-4954-ae29-6245f68cd555',
    'Content-Type' => 'application/json',
    'Accept' => 'application/json'
));

// Let's make the message more relevant to your system
$message = "SPACE: New Booking Request\n\n" .
          "Date: " . date('F j, Y') . "\n" .
          "Time: 2:30 PM\n" .
          "Student ID: 2020-00123\n" .
          "Status: PENDING\n\n" .
          "Please wait for confirmation.";

$request->setBody(json_encode([
    'messages' => [[
        'destinations' => [['to' => '639267075697']],
        'from' => '447491163443',
        'text' => $message
    ]]
]));

try {
    $response = $request->send();
    if ($response->getStatus() == 200) {
        echo $response->getBody();
    }
    else {
        echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
        $response->getReasonPhrase();
    }
}
catch(HTTP_Request2_Exception $e) {
    echo 'Error: ' . $e->getMessage();
}