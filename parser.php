<?php

$mecuryapiurl = 'https://mercury.postlight.com/parser?url='.$_GET['u'];

$response = curl_get($mecuryapiurl);

echo $response;

function curl_get($url) {

        $ch = curl_init();

        $headers = array(
		    'Content-Type: application/json', 
		    'x-api-key: 0LodF6lth6xSBhQS9yplfJyPmuaZBmlp28a4XD4d',
		);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // set URL and other appropriate options
        curl_setopt($ch, CURLOPT_URL, $url); // use Random to generate unique URL every connect
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; rv:17.0) Gecko/20100101 Firefox/17.0');
        //curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
        //curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); // follow 302 header
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, TRUE); //Don't use cache version, "Cache-Control: no-cache"
        //curl_setopt($ch, CURLOPT_VERBOSE, 1); //for get header
        //curl_setopt($ch, CURLOPT_HEADER, 1); //for get header
        // grab URL and pass it to the browser
        $response = curl_exec($ch);

        // Then, after your curl_exec call:
        //$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        //$header = substr($response, 0, $header_size);
        //$body = substr($response, $header_size);

        //Log::info($header);

        // close cURL resource, and free up system resources
        curl_close($ch);

        //return (string) $body;
        return $response;
}

?>