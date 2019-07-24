<?php

require('Reader.php');
use TheReader\Reader;

header('Content-Type: application/json');

if (isset($_POST) && !empty($_FILES['file'])) {
    $extension = explode('.', $_FILES['file']['name']); 
    $extension = $extension[(count($extension) - 1)]; 

    if ($extension === 'pdf' || $extension === 'epub') {

        $booktmp = "book_".$_COOKIE['uuid'].'.'.$extension; //temp name for uploaded file persistent save in /tmp
        if (move_uploaded_file($_FILES['file']['tmp_name'],sys_get_temp_dir().'/'.$booktmp)) {
            $filefullpath = sys_get_temp_dir().'/' . $booktmp;
            $filename = $_FILES['file']['name'];

            //success
            $response = [
                'status' => true,
                'message' => 'success',
                'bookname' => $_FILES['file']['name'],
                'booktmp' => $booktmp,
                'pages' => Reader::getPdfPage($filefullpath),
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
?>