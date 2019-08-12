<?php 

namespace TheReader;

class Reader{
    public function __construct()
    {
    
    }

    public static function getPdfPage($file){
        //REQUIRED BINARY: yum install poppler-utils or apt-get install poppler-utils
        //pdfinfo document.pdf | grep Pages: | awk '{print $2}'
        return intval(trim(shell_exec("./pdfinfo $file | grep Pages: | awk '{print $2}'")));
    }

    public static function pdftoText($file, $page){
        //REQUIRED BINARY: yum install poppler-utils or apt-get install poppler-utils
        //pdftotext -q -raw -nopgbrk -f 10 -l 10 document3.pdf -
        return shell_exec("./pdftotext -q -nopgbrk -f $page -l $page $file -");
    }

    public static function getEpub2TxtPage($file){
        $charperpage = 3000;
        $charcount = intval(trim(shell_exec("./epub2txt -r $file | wc -m $file")));
        $totalpage = intval(round($charcount / $charperpage));
        return $totalpage;
    }

    public static function getEpubPage($file){
        //mutool draw -s -q -F text -o /dev/null book/book_a29dd235146e773d3594b98be5f4bfb4.epub 2>&1 | tail -n -1 | awk '{print $3}'
        return intval(trim(shell_exec("./mutool draw -s -q -W 530 -H 530 -F text -o /dev/null $file 2>&1 | tail -n -1 | awk '{print $3}'")));
    }

    public static function epub2txt($file, $page){
        //Require binary: https://github.com/kevinboone/epub2txt2
        //epub2txt -r document.epub
        $charperpage = 3000;
        $safetycutcharmargin = 10; //this margin for keep last word of page will continue with first word of next page and prevent lost word, this should be longest char of word in language (example VN is 5, english is 6-7)

        if($page == 1){
            $startpos = 0;
        }else{
            $startpos = ($page - 1)*$charperpage;
        }
        $endpos = $charperpage + $safetycutcharmargin;

        $textfull = shell_exec("./epub2txt -r $file");

        $textofpage = mb_substr($textfull, $startpos, $endpos);
        $firstBlankPos = mb_strpos($textofpage, " ");
        $lastBlankPos = mb_strrpos($textofpage," ");
        $textofpage = mb_substr($textofpage, $firstBlankPos, $lastBlankPos-$firstBlankPos); //safety cut to not break word position

        return $textofpage;
    }

    public static function epubtoText($file, $page){
        //mutool draw -q -W 550 -H 550 -F text book/book_a29dd235146e773d3594b98be5f4bfb4.epub 10
        $text = shell_exec("./mutool draw -q -W 530 -H 530 -F text $file $page");
        $text = preg_replace('/[\x00-\x1F\x7F]/u', ' ', $text); //replace non-printable characters by space
        return $text;
    }

    public static function texttoSentence200char($text){

            $text = str_replace("\t", "", $text);
            $text = preg_replace("/\n\s+\n/", "\n\n", $text);
            $text = preg_replace("/[\n]{3,}/", "\n\n", $text);

            //clear number decimal or thousand delimiter
            /* preg_match_all('#(\d+(.|,))+(\d)+#', $text, $arr_unclear_numbers);
            foreach ($arr_unclear_numbers[0] as $unclear_number) {
                if((strpos($unclear_number,'.') != false) or (strpos($unclear_number, ',') != false)){
                    $cleared_number = str_replace(".", "", $unclear_number); //clear dot
                    $text = str_replace($unclear_number, $cleared_number, $text);
                }
            } */

            $sentences = array();
            $a = explode("\n\n", $text);
            foreach ($a as $b) {
                $b = preg_replace("/http:\/\/(.*?)[\s\)]/", "", $b);
                $b = preg_replace("/http:\/\/([^\s]*?)$/", "", $b);
                $b = preg_replace("/\[\s*[0-9]*\s*\]/", "", $b);
                foreach (array_filter(self::multiexplode(array('. ',', ',';','?','!'), $b)) as $sent){ //array_filter to remove all empty row . Notes: space after delimiter char is very important, it will skip broken thousand and decimal number
                    
                    if (strlen(trim($sent)) > 3) {
                        $sent = preg_replace("/\n/", " ", $sent);
                        $sent = trim(str_replace("  ", " ", $sent));
                        //$sent[0] = strtoupper($sent[0]);
                        //echo "String len (". mb_strlen($sent,'utf8').") : ".$sent."\n";
                        //array_push($sentences, $sent);
                        if(mb_strlen($sent, 'utf8') < 200){
                            array_push($sentences, $sent);
                        }else{
                            $mainpart = self::truncate($sent,200,'',false);
                            array_push($sentences, $mainpart);
                            $remainpart = trim(str_replace($mainpart,'',$sent));
                            array_push($sentences, $remainpart);
                        }
                    }
                }
            }

            return $sentences;
    }

    public static function read($text){
        

    }

    /** 
     * Truncates text.
     *
     * Cuts a string to the length of $length and replaces the last characters
     * with the ending if the text is longer than length.
     *
     * @param string $text String to truncate.
     * @param integer $length Length of returned string, including ellipsis.
     * @param string $ending Ending to be appended to the trimmed string.
     * @param boolean $exact If false, $text will not be cut mid-word
     * @param boolean $considerHtml If true, HTML tags would be handled correctly
     * @return string Trimmed string.
     */
    public function truncate($text, $length = 100, $ending = '...', $exact = true, $considerHtml = false)
    {
        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }

            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

            $total_length = strlen($ending);
            $open_tags = array();
            $truncate = '';

            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it’s an “empty element” with or without xhtml-conform closing slash (f.e.)
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                        // if tag is a closing tag (f.e.)
                    } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                        // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                        // if tag is an opening tag (f.e. )
                    } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                        // add tag to the beginning of $open_tags list
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                    // add html-tag to $truncate’d text
                    $truncate .= $line_matchings[1];
                }

                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length + $content_length > $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                    // maximum lenght is reached, so get off the loop
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }

                // if the maximum length is reached, get off the loop
                if ($total_length >= $length) {
                    break;
                }
            }
        } else {
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = substr($text, 0, $length - strlen($ending));
            }
        }

        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = substr($truncate, 0, $spacepos);
            }
        }

        // add the defined ending to the text
        $truncate .= $ending;

        if ($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '';
            }
        }

        return $truncate;
    }

    public function multiexplode($delimiters, $string)
    {

        $ready = str_replace($delimiters, $delimiters[0], $string);
        $launch = explode($delimiters[0], $ready);
        return  $launch;
    }


}

?>