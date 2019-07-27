<?php 
include('vendor/autoload.php');
require('Reader.php');
use TheReader\Reader;

$bookfile = sys_get_temp_dir().'/'.$_GET['book'];
$page = $_GET['page'];
$totalpage = $_GET['pages']; //totalpages

$extension = explode('.', $_GET['book']);
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

//echo $text;

$sentences = Reader::texttoSentence200char($text);

$response = implode(".\n",$sentences);

echo $response;
