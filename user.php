<?php
    session_start();

    // make directory for user named $_SESSION['username']
    if (!file_exists('user/'.$_SESSION['username'])) {
        mkdir('user/'.$_SESSION['username'], 0777, true);
    }
    //make data directory (where the files uploaded will be stored)
    if (!file_exists('user/'.$_SESSION['username'].'/data')) {
        mkdir('user/'.$_SESSION['username'].'/data', 0777, true);
    }
    //make the filetool directory (where other files from the algorithm will be copied to)
    if (!file_exists('user/'.$_SESSION['username'].'/filetool')) {
        mkdir('user/'.$_SESSION['username'].'/filetool', 0777, true);
    }

    // copy files to user's directoy //
    // exec.py
    $mainexec = 'user/'.$_SESSION['username'].'/exec.py';
    copy('user/exec.py', $mainexec);
    // learn.py
    $newlearn = 'user/'.$_SESSION['username'].'/learn.py';
    copy('user/learn.py', $newlearn);
    // perturb.R
    $newperturb = 'user/'.$_SESSION['username'].'/perturb.R';
    copy('user/perturb.R', $newperturb);
    // upload.php
    $newupload = 'user/'.$_SESSION['username'].'/upload.php';
    copy('user/upload.php',$newupload );
    // exec.php
    $exec = 'user/'.$_SESSION['username'].'/execute.py';
    copy('user/execute.py', $exec );

    // logout exec
       if (isset($_POST['logout'])){
           session_unset();
           session_destroy();
           header("Location: index.php");
           exit();
       }
    // Settings
?>

<!DOCTYPE html>

<html lang="en" style="background-image: url('images/bg.png');background-size:100%;" >
   <div style="background-image: url('images/bg.png');background-size:100%;" >
     <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="images/arrow.ico">
        <link rel="stylesheet" href="style.css" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,700" rel="stylesheet">
        <title>starithm</title>
        <style>
          .loader {
            margin:auto;
            border: 8px solid #f3f3f3;
            border-radius: 50%;
            border-top: 8px solid #515E69;
            width: 60px;
            height: 60px;
            -webkit-animation: spin 2s linear infinite; /* Safari */
            animation: spin 2s linear infinite;
          }

          /* Safari */
          @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
          }

          @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
          }
        </style>
        <script>
          //////// UPLOAD ////////
          function dropupload(){
            var dropzone = document.getElementById('dropzone');

            var displayUploads = function(data) {
                var uploads = document.getElementById('uploads'),
                    anchor,
                    x;
                for(x=0; x < data.length; x = x+ 1) {
                    anchor = document.createElement('a');
                    anchor.innerText = data[x].name;
                    uploads.appendChild(anchor);
                }
            }

            var upload  = function(files) { //upload variable as function for files to get data?
                var formData = new FormData(),
                    xhr = new XMLHttpRequest(),
                    x;
                for(x = 0; x < files.length; x = x + 1 ) { //append data //list in order (drag many at atime)
                    formData.append('file[]',files[x]);
                }
                xhr.onload = function() { //save data loading it
                    var data = JSON.parse(this.responseText);
                    displayUploads(data);
                }
                xhr.open('POST', 'user/<?php echo $_SESSION['username'];?>/upload.php'); ///so now it can open this php document
                xhr.send(formData) ///send it to the location
            }

            dropzone.ondrop = function(e) {
                e.preventDefault(); //to prevent from the pic taking over
                this.className = 'dropzone';
                upload(e.dataTransfer.files);  //uploading it as sending arg to console
            }

            dropzone.ondragover = function() {
                this.className = 'dropzone dragover';
                return false;
            }
            dropzone.ondragleave = function() {
                this.className = 'dropzone';
                return false;
            }
          }
        </script>
    </head>

<div style="width:100%;background-color:rgba(255,255,255,.3)">
    <!--HEADER AND LOGOUT PHP-->
    <div class='welcome' style='padding-top:40px;'><div class='alert alert-success'>"<?=$_SESSION['message']?>"</div><br />

      <div class="column">
        <div class="row">
          <form action="index.php" method="POST" style="float:right; margin-right:1%;">
              <button class="logout" name="logout" type="logout" style="vertical-align:middle"><span>Logout</span></button></form>
        </div>
        <div class="row">
          <button class='execbtn' href="#" onclick="toggle_visibility('account_settings')">Account Settings</button><br /></div>
        <div class="row">
          <button class='execbtn' href="#" onclick="toggle_visibility('past_results')">Past Results</button><br />
        </div>
      </div>

      <!-- DROPZONE -->
      <div id="uploads"></div><div class="dropzone" id="dropzone">
          Drag and Drop Files<br />
          <!-- upload button-->
          <form style="margin-top:20px;" enctype="multipart/form-data" action="#upload_reload" method="POST">
            <input type="file" name="file" multiple><br /><br />
            <button class="refresh" type="submit" name="upreload">Upload</button></form></div>

      <script>dropupload();</script>

      <!--Check if files exist,parameters for data, asynchronous execution, loading, display results-->
      <?php
          $dir = "user/".$_SESSION['username']."/data/";
          $user_files = @scandir($dir);

          if(count($user_files) <= 3 ){
              echo "<br /> <br /> <br /><br />No files uploaded yet<br /> <br />";
              if(isset($_POST['upreload'])){
                  //upload individual files
                  $fileName = $_FILES['file']['name'];
                  $fileTmpName = $_FILES['file']['tmp_name'];
                  $uploaddir = 'user/'.$_SESSION['username'].'/data/';
                  $uploadfile = $uploaddir . basename($fileName);

                  if (move_uploaded_file($fileTmpName, $uploadfile)) {
                  } else {
                  }
              } else { }
          }
          if(count($user_files) >= 3) {
              if(isset($_POST['upreload'])){
                  //upload individual files
                  $fileName = $_FILES['file']['name'];
                  $fileTmpName = $_FILES['file']['tmp_name'];
                  $uploaddir = 'user/'.$_SESSION['username'].'/data/';
                  $uploadfile = $uploaddir . basename($fileName);

                  if (move_uploaded_file($fileTmpName, $uploadfile)) {
                  } else {
                  }
              } else { }

              // if there are files; show them:
                  //check for size, type
              $files = array();
              echo "<br />";
              foreach (glob("user/".$_SESSION['username']."/data/*") as $file) {
                  $files[] = $file;
                  $allowed = array('dat');
                  $file_ext = explode('.',$file);
                  $file_ext = strtolower(end($file_ext));
                  $file_name = explode('/',$file);
                  $file_name = strtolower(end($file_name));

                  //parameters for uploads
                  //datafile
                  if(in_array($file_ext,$allowed)){
                    //size
                      if(filesize($file)<10000){
                          echo "Upload success for ".$file_name."<br />";
                      } else{
                          unlink($file);
                          echo "<br />File must be <10 KB. <br />";
                          echo "<p style='display:inline;color:blue'>". $file_name. "</p> File was deleted.";
                      }
                  }else{
                      unlink($file);
                      echo "<br />Only data files are accepted. <br />";
                      echo "<p style='display:inline;color:blue'>". $file_name. "</p> File was deleted.";
                  }
              }
              echo "  <!-- EXECUTE BUTTON -->
                      <br /><form action='#results' method='POST'>
                      <button class='execbtn' type='execute' name='execute'>Execute</button><br /></form>";
          }

        // ASYNC EXECUTION //
          if(isset($_POST['execute'])){
            #options
                #async and send through email
                #iframe not asynchronous or async and send to print
            #execution
            passthru("cd user/".$_SESSION['username']."/ && python execute.py ".$_SESSION['email']." ".$_SESSION['name']." > /dev/null 2>/dev/null &");
            echo '<br />Your results will be sent to '.$_SESSION['email'].".";

          }else{ }
      ?>
  </div>

  <!-- FOOTER -->
  <div class="bottom" style="font-size:15px;margin-top:40px;">
      <script> document.write("This page was last modified on: " + document.lastModified + "");</script>
  </div>

</div>
</div>

<script src="functions.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/iframe-resizer/3.5.16/iframeResizer.min.js"></script>

</html>

<div id="past_results" class="popup-container">
  <a onclick="toggle_visibility('past_results')" class="popup-wrap-large"></a>
  <div class="popup-pos-large">
      <?php
      echo '<iframe style="border:0; width:100%;height:100%;overflow:auto;" src="user/'.$_SESSION['username'].'/output.php"></iframe>';
      ?>
  </div>
</div>

<div id="account_settings" class="popup-container">
  <a onclick="toggle_visibility('account_settings')" class="popup-wrap-large"></a>
  <div class="popup-pos-large">
      <h1>Account Settings</h1>
      <h2>Change Password</h2>
      <!--<form><input></input><button>Check</button></form>
      if password is correct... say its correct and allow for change of Password
      else echo its not ... sorry try again...-->
  </div>
</div>

<script type="text/javascript">
  function toggle_visibility(id){
    var toggling = document.getElementById(id);
    if (toggling.style.display == 'block')
      toggling.style.display = 'none';
    else
      toggling.style.display = 'block';
  }
</script>
