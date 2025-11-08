<?php
$headers = @get_headers('http://127.0.0.1:8200', 1);
if (isset($headers) && isset($headers[0])) {
    echo $headers[0] . PHP_EOL;
} else {
    echo "NO-HEADERS\n";
}
$body = @file_get_contents('http://127.0.0.1:8200');
if ($body === false) {
    echo "REQUEST ERROR\n";
    exit(1);
}
echo "BODY-FIRST-1200:\n" . substr($body, 0, 1200) . PHP_EOL;
