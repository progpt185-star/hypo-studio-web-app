<?php
$url = 'http://127.0.0.1:8100/login';
$data = http_build_query(['username' => 'admin', 'password' => 'password']);
$opts = [
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
        'content' => $data,
        'ignore_errors' => true,
    ]
];
$context = stream_context_create($opts);
$result = @file_get_contents($url, false, $context);
if ($result === false) {
    echo "REQUEST ERROR\n";
    exit(1);
}
echo "STATUS-LINE:\n";
if (isset($http_response_header[0])) echo $http_response_header[0] . PHP_EOL;
echo "BODY-FIRST-1200:\n" . substr($result, 0, 1200) . PHP_EOL;
