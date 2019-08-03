<?php
set_time_limit(180);

include('vendor/autoload.php');
require('Reader.php');

//Run Cleaner to get Disk space
cleaner('audio');
cleaner('book');

use TheReader\Reader;

$uuid = $_COOKIE['uuid'];
$lang = $_GET['lang'];
$bookfile = 'book/' . filter_filename($_GET['book']);
$page = $_GET['page'];
$totalpage = $_GET['pages']; //totalpages

$extension = explode('.', filter_filename($_GET['book']));
$extension = $extension[(count($extension) - 1)]; 

switch ($extension) {
  case 'epub':
    $text = Reader::epubtoText($bookfile, $page);
    break;

  case 'pdf':
    $text = Reader::pdftoText($bookfile, $page);
    break;

  default:
    die('WRONG EXTENSION');
    break;
}

$sentences = Reader::texttoSentence200char($text);

//Get TTS file
$id = 0;
foreach ($sentences as $sentence) {

  $id++;
  for ($i = 0; $i < 3; $i++) {

    $url = 'http://translate.google.com/translate_tts?ie=UTF-8&q=' . urlencode($sentence) . '&tl=' . $lang . '&total=1&idx=0&textlen=1000&client=tw-ob'; //&ttsspeed=1';

    $filetts = 'audio/' . $uuid . '_' . $id . '.mp3';

    $response = curl_get($url);

    file_put_contents($filetts, $response);

    if (@filesize($filetts) > 500) { // if file size not too small Break loop and do next thing, or continue loop for retry
      break;
    }
  }
}

//Merge TTS Files to one File
$filefinal = 'audio/' . $uuid . '.mp3';

$ffmpeg_merge_command = './ffmpeg -y -i "concat:';
for ($id = 1; $id <= sizeof($sentences); $id++) {
  $ffmpeg_merge_command .= 'audio/' . $uuid . '_' . $id . '.mp3';
  if ($id != sizeof($sentences)) {
    $ffmpeg_merge_command .= '|silence.mp3|'; //add silence file between tts files
    //$ffmpeg_merge_command .= '|'; //add silence file between tts files
  } else {
    $ffmpeg_merge_command .= '"';
  }
}
$ffmpeg_merge_command .= ' -acodec copy ' . $filefinal;

//echo $ffmpeg_merge_command;
exec($ffmpeg_merge_command);

//delete all file parts
for ($id = 1; $id <= sizeof($sentences); $id++) {
  $filetts = 'audio/' . $uuid . '_' . $id . '.mp3';
  @unlink($filetts);
}

//---Convert tempo if safari---
$fileconverted = 'audio/' . $uuid . '_converted.mp3';

//transform speed
if ($_GET['safari'] == 'true') { //isSafari
    exec('./ffmpeg -y -loglevel quiet -i ' . $filefinal . ' -filter:a "atempo=' . $_GET['speed'] . '" ' . $fileconverted);
    @unlink($filefinal); //clear file final for save space
} else {
  $fileconverted = $filefinal;
}


/* header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename='.basename($fileconverted));
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($fileconverted));
ob_clean();
flush();
readfile($fileconverted); */


//smartReadFile($fileconverted, basename($fileconverted), 'audio/mpeg');

//@unlink($fileconverted);
//@unlink($filefinal);

//Redirect directly to outputed audio file
header('Location: '.$fileconverted.'?random='.chr(mt_rand(97, 122)).substr(md5(time()), 1));

exit;

function curl_get($url)
{

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

//Send file with HTTPRange support (partial download)
//ex: smartReadFile("/tmp/filename","myfile.mp3","audio/mpeg")
function smartReadFile($location, $filename, $mimeType = 'application/octet-stream')
{
  if (!file_exists($location)) {
    header("HTTP/1.1 404 Not Found");
    return;
  }

  $size = filesize($location);
  $time = date('r', filemtime($location));

  $fm = @fopen($location, 'rb');
  if (!$fm) {
    header("HTTP/1.1 505 Internal server error");
    return;
  }

  $begin = 0;
  $end = $size;

  if (isset($_SERVER['HTTP_RANGE'])) {
    if (preg_match('/bytes=\h*(\d+)-(\d*)[\D.*]?/i', $_SERVER['HTTP_RANGE'], $matches)) {
      $begin = intval($matches[0]);
      if (!empty($matches[1]))
        $end = intval($matches[1]);
    }
  }

  if ($begin > 0 || $end < $size)
    header('HTTP/1.1 206 Partial Content');
  else
    header('HTTP/1.1 200 OK');

  header("Content-Type: $mimeType");
  header('Cache-Control: public, must-revalidate, max-age=0');
  header('Pragma: no-cache');
  header('Accept-Ranges: bytes');
  header('Content-Length:' . ($end - $begin));
  header("Content-Range: bytes $begin-$end/$size");
  header("Content-Disposition: inline; filename=$filename");
  header("Content-Transfer-Encoding: binary\n");
  header("Last-Modified: $time");
  header('Connection: close');

  $cur = $begin;
  fseek($fm, $begin, 0);

  while (!feof($fm) && $cur < $end && (connection_status() == 0)) {
    print fread($fm, min(1024 * 16, $end - $cur));
    $cur += 1024 * 16;
  }
}

function filter_filename($filename, $beautify = false)
{
  // sanitize filename
  $filename = preg_replace(
    '~
        [<>:"/\\|?*]|            # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
        [#\[\]@!$&\'()+,;=]|     # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
        ~x',
    '-',
    $filename
  );
  // avoids ".", ".." or ".hiddenFiles"
  $filename = ltrim($filename, '.-');
  // optional beautification
  if ($beautify) $filename = beautify_filename($filename);
  // maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
  $ext = pathinfo($filename, PATHINFO_EXTENSION);
  $filename = mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)) . ($ext ? '.' . $ext : '');
  return $filename;
}

function beautify_filename($filename)
{
  // reduce consecutive characters
  $filename = preg_replace(array(
    // "file   name.zip" becomes "file-name.zip"
    '/ +/',
    // "file___name.zip" becomes "file-name.zip"
    '/_+/',
    // "file---name.zip" becomes "file-name.zip"
    '/-+/'
  ), '-', $filename);
  $filename = preg_replace(array(
    // "file--.--.-.--name.zip" becomes "file.name.zip"
    '/-*\.-*/',
    // "file...name..zip" becomes "file.name.zip"
    '/\.{2,}/'
  ), '.', $filename);
  // lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
  $filename = mb_strtolower($filename, mb_detect_encoding($filename));
  // ".file-name.-" becomes "file-name"
  $filename = trim($filename, '.-');
  return $filename;
}

function cleaner($folderpath){ //DONT HAVE Slash / at END

  $fileSystemIterator = new FilesystemIterator($folderpath);
  $now = time();
  foreach ($fileSystemIterator as $file) {
    if ($now - $file->getCTime() >= 60 * 60 * 24 * 1){ // 1 days 
      if($file->getFilename() != '.gitkeep'){ // 1 days 
        unlink($folderpath.'/' . $file->getFilename());
      }
    }
  }

}

?>
