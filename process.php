<?php 
include('vendor/autoload.php');
require('Reader.php');
use TheReader\Reader;

$epubfile = 'tamly.epub';

$totalpage = Reader::getEpubPage($epubfile);
echo $totalpage."\n";
var_dump($totalpage);
/* echo Reader::epubtoText($epubfile,1);
echo "\n---------\n";
echo Reader::epubtoText($epubfile,2);
echo "\n---------\n";
echo Reader::epubtoText($epubfile,3);
echo "\n---------\n";
echo Reader::epubtoText($epubfile, 4);
echo "\n---------\n";
echo Reader::epubtoText($epubfile, 5); */

/* $text = Reader::pdftoText($epubfile,30,30);

echo $text;

$sentences = Reader::texttoSentence200char($text);

print_r($sentences);
 */
?>