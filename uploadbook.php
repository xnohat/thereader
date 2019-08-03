<?php

require('Reader.php');
use TheReader\Reader;

header('Content-Type: application/json');

if (isset($_POST) && !empty($_FILES['file'])) {

    if(isset($_FILES['file'])) {
      if($_FILES['file']['size'] > file_upload_max_size()) {
      // File too big
        $response = [
          'status' => false,
          'message' => 'File size too big, only allow file size below '. isa_convert_bytes_to_specified(file_upload_max_size(),'M') . ' MB',
        ];
        die(json_encode($response));
      }
    }

    $extension = explode('.', $_FILES['file']['name']); 
    $extension = $extension[(count($extension) - 1)]; 

    if ($extension === 'pdf' || $extension === 'epub') {

        $booktmp = "book_".$_COOKIE['uuid'].'.'.$extension; //temp name for uploaded file persistent save in /tmp
        if (move_uploaded_file($_FILES['file']['tmp_name'],'book/'.$booktmp)) {
            $filefullpath = 'book/' . $booktmp;
            $filename = $_FILES['file']['name'];
            switch ($extension) {
                case 'epub':
                    $totalpage = Reader::getEpubPage('book/' . $booktmp);
                    break;

                case 'pdf':
                    $totalpage = Reader::getPdfPage('book/' . $booktmp);
                    break;

                default:
                    die('WRONG EXTENSION');
                    break;
            }

            //success
            $response = [
                'status' => true,
                'message' => 'success',
                'bookname' => $_FILES['file']['name'],
                'booktmp' => $booktmp,
                'pages' => $totalpage,
            ];
            die(json_encode($response));
        } else { // fail
            $response = [
                'status' => false,
                'message' => 'fail with unknown error',
            ];
            die(json_encode($response)); 
        }
        
    } else { // not allow file extension
        $response = [
            'status' => false,
            'message' => 'file type not allowed',
        ];
        die(json_encode($response)); 
    }
} else {
    $response = [
        'status' => false,
        'message' => 'Not support GET Method or Empty file',
    ];
    die(json_encode($response));
}

// Returns a file size limit in bytes based on the PHP upload_max_filesize
// and post_max_size
function file_upload_max_size() {
  static $max_size = -1;

  if ($max_size < 0) {
    // Start with post_max_size.
    $post_max_size = parse_size(ini_get('post_max_size'));
    if ($post_max_size > 0) {
      $max_size = $post_max_size;
    }

    // If upload_max_size is less, then reduce. Except if upload_max_size is
    // zero, which indicates no limit.
    $upload_max = parse_size(ini_get('upload_max_filesize'));
    if ($upload_max > 0 && $upload_max < $max_size) {
      $max_size = $upload_max;
    }
  }
  return $max_size;
}

function parse_size($size) {
  $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
  $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
  if ($unit) {
    // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
    return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
  }
  else {
    return round($size);
  }
}

function isa_convert_bytes_to_specified($bytes, $to, $decimal_places = 1)
{
  $formulas = array(
    'K' => number_format($bytes / 1024, $decimal_places),
    'M' => number_format($bytes / 1048576, $decimal_places),
    'G' => number_format($bytes / 1073741824, $decimal_places)
  );
  return isset($formulas[$to]) ? $formulas[$to] : 0;
}

?>