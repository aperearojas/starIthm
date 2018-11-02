<?php
session_start(); 
header('Content-Type: application/json');
$uploaded = array();

// must add other parameters for security
if(!empty($_FILES['file']['name'][0])) {
    foreach($_FILES['file']['name'] as $position => $name){
        if(move_uploaded_file($_FILES['file']['tmp_name'][$position], 'data/'.$name)){
            $uploaded[] = array(
                'name' => $name,
                'file' => 'data/'.$name
            );
        }
    }
}


echo json_encode($uploaded);

?>
