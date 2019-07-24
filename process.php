<?php 
include('vendor/autoload.php');
require('Reader.php');
use TheReader\Reader;

$pdffile = 'bookupload/sotaymohinhnenNhat.pdf';

$totalpage = Reader::getPdfPage($pdffile);
$text = Reader::pdftoText($pdffile,30,30);

echo $text;

$sentences = Reader::texttoSentence200char($text);

print_r($sentences);

?>