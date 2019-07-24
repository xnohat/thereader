<?php 
include('vendor/autoload.php');
require('Reader.php');
use TheReader\Reader;

$pdffile = sys_get_temp_dir().'/'.$_GET['book'];
$page = $_GET['page'];

$totalpage = Reader::getPdfPage($pdffile);
$text = Reader::pdftoText($pdffile,$page,$page);

//echo $text;

$sentences = Reader::texttoSentence200char($text);

$response = implode(".\n",$sentences);

echo $response;
