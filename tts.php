<?php
set_time_limit(30);

$url = 'http://translate.google.com/translate_tts?ie=UTF-8&q='. urlencode($_GET['q']) .'&tl='. $_GET['lang'] .'&total=1&idx=0&textlen=1000&client=tw-ob';//&ttsspeed=1';

$file = sys_get_temp_dir() . '/' . $_COOKIE['uuid'] . '.mp3';
$fileconverted = sys_get_temp_dir() . '/' . $_COOKIE['uuid'] . '_converted.mp3';

$response = curl_get($url);

file_put_contents($file, $response);

//transform speed
if($_GET['safari'] == 'true'){ //isSafari
  if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    //windows
    exec('ffmpeg.exe -y -loglevel quiet -i '. $file .' -filter:a "atempo='. $_GET['speed'] .'" '. $fileconverted);
  } else {
    //linux
    exec('./ffmpeg -y -loglevel quiet -i '. $file .' -filter:a "atempo='. $_GET['speed'] .'" '. $fileconverted);
  }
}else{
  $fileconverted = $file;
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename($fileconverted));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($fileconverted));
ob_clean();
flush();
readfile($fileconverted);
@unlink($file);
@unlink($fileconverted);
exit;

function curl_get($url) {

        $ch = curl_init();

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