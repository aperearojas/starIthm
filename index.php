<?php
    session_start();

    @ini_set('display_errors', 1);
    @ini_set('track_errors', 0);

    $_SESSION['login-message'] ='';
    $_SESSION['message'] ='';
    $_SESSION['search-message'] ='';

    #change localhost and parameters when changed database
    $conn = mysqli_connect('localhost', 'root', 'starithm1234', 'stproj');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['submit']))){
        $known = $conn->real_escape_string($_POST['known']);
        $sql = "SELECT * FROM `user` WHERE (username='$known' OR email='$known')";
        $res = mysqli_query($conn, $sql);
        $count = mysqli_num_rows($res);
        if($count == 1){
            $r = mysqli_fetch_assoc($res);
            $password = $r['password'];
            $email = $r['email'];
            $name = $r['first_name'];
            $username = $r['username'];

            $body = '<body style="font-size:14px">';
            $body .= 'Dear '.$name.', <br /><br />';
            $body .= 'There is a request for the reset or recovery of the password from this account. <br /><br />';
            $body .= 'Username: <b>'.$username.'</b><br />';
            $body .= 'Password: <b>'.$password.'</b><br /><br />';
            $body .= 'If you would like to reset your password, ';
            $body .= 'please login and change your password manually.<br /><br />';
            $body .= 'Thank you, <br /> Team Starithm';
            $body .= '</body>';

            $from_usr = 'alecita9perea@gmail.com';
            $from_psw = 'highschool19';
            $subject = 'Password Reset or Recovery - Starithm';

            require_once('PHPMailer/PHPMailerAutoload.php');
            $mail = new PHPMAiler();
            $mail -> isSMTP();
            $mail -> SMTPAuth = true;
            $mail -> SMTPSecure = 'ssl';
            $mail -> Host = 'smtp.gmail.com';
            $mail -> Port = '465';
            $mail -> isHTML();
            $mail -> Username = $from_usr;
            $mail -> Password = $from_psw;
            $mail -> SetFrom('no-reply');
            $mail -> Subject = $subject;
            $mail -> AddAddress($email);
            $mail -> Body = $body;

            if($mail -> Send()) {
                echo '<script type="text/javascript">',
                     'alert("Your Password has been sent to your account email.")',
                     '</script>';
            } else {
                echo '<script type="text/javascript">',
                     'alert("Failed to recover your password. Mailer Error: '. $mail->ErrorInfo . '")',
                     '</script>';
            }
        }else{
            echo '<script type="text/javascript">',
                 'alert("Account does not exist")',
                 '</script>';
        }
      }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['login']))){
        $top = $conn->real_escape_string($_POST['top']);
        $password = md5($_POST['password']);
        $sqllog = "SELECT id FROM user WHERE (username = '$top' OR email = '$top') and password = '$password'";
        $result = mysqli_query($conn,$sqllog);
        $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
        $count = mysqli_num_rows($result);
        $sql = "SELECT * FROM `user` WHERE (username = '$top' OR email = '$top')";
        $res = mysqli_query($conn,$sql);
        if($count == 1) {
            $r = mysqli_fetch_assoc($res);
            $name = $r['first_name'];
            $lastname = $r['last_name'];
            $username = $r['username'];
            $email = $r['email'];
            $_SESSION['name'] = $name;
            $_SESSION['lastname'] = $lastname;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['message'] = "Login successful $name!";
            header("location: user.php");
        }
        else {
            $_SESSION['login-message'] = "Invalid username or password" . $conn->error;
        }
    }

    if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['register']))){

        if ($_POST['password'] == $_POST['confirmpassword']) {
            $name =$conn->real_escape_string($_POST['first_name']);
            $lastname = $conn->real_escape_string($_POST['last_name']);
            $username = $conn->real_escape_string($_POST['username']);
            $email = $conn->real_escape_string($_POST['email']);
            $password = md5($_POST['password']);//
            $sql = "INSERT INTO user (first_name, last_name, username, email, password)
            VALUES ('$name','$lastname','$username', '$email', '$password')";

            //if the query is successful, redirect to user.php page, done!
            if ($conn->query($sql) === TRUE) {
                $name = $_SESSION['first_name'];
                $lastname = $_SESSION['last_name'];
                $username = $_SESSION['username'];
                $email = $_SESSION['email'];

                header("location: user.php");

                $_SESSION['message'] = "Your registration was successful";
                $_SESSION['name'] = $name;
                $_SESSION['lastname'] = $lastname;
                $_SESSION['username'] = $username;
                $_SESSION['email'] = $email;
            } else{
                $_SESSION['message'] = $conn->error. ".";
            }
                //else{$_SESSION['message'] = "File upload failed";
            //else{$_SESSION['message'] = "Please only upload GIf, JPG, or PNG images";
        }
        else{
            $_SESSION['message'] = "Passwords don't match.";
        }
    }

    ###########SEARCH CATALOG#################
    $dir = 'stars/';
    $stars = scandir($dir, 1);
    if (($_SERVER['REQUEST_METHOD'] == 'POST') && (isset($_POST['search']))){
        $value = $_POST['search_input'];
        $id = "star";
        if(in_array($value,$stars)) {
            //$result = '<br />We found <a href="stars/'.$value.'" target="_blank" class="cat_a">'.$value.'</a>.';
            $_SESSION['onclick'] = "toggle_visibility('star')";
            $result = '<a href="#" class="cat_c" onclick='.$_SESSION['onclick'].'>'.$value.'</a>';
            $_SESSION['star'] = $value;
        }else{
            $result = "<br />We could not find ".$value.".";
        }
        $_SESSION['search-message'] = $result;
    }

?>

<!DOCTYPE html>
<html lang="en">
  <head>

    <!-- meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link rel="icon" href="images/arrow.ico">
    <link rel="stylesheet" href="style.css">
    <title>The only interface in stellar parameters prediction - Starithm</title>

    <!-- external stylesheet -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,700">

    <!--credit: http://jqueryui.com/autocomplete/-->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

  </head>

  <body>

    <div style="background-image: url('images/background.png'); background-size:100%; padding-bottom:0px; ">

      <div class="searchi">
        <div style="float:right;margin-right:8%;margin-left:10%;margin-top:4px">
        <form class="form" action="#search" method="post" enctype="multipart/form-data" autocomplete="off">
          <div class="ui-widget">
            <input type="text" name="search_input" placeholder="Search a star" id="tags" style="width:150px;height:32px;font-size:15px;" required/>
            <input type="submit" value="Search" name="search" class="btn"/>
            <label><?= $_SESSION['search-message'] ?></label>
          </div>
        </form>
      </div>
    </div>
      <div class="intro-project">

        <div>
          <div class="left">
            <h1 class="index">Starithm</h1><br />
            <p class="intro-project-description" style="float:left">
              Starithm is a web-based interface that predicts the physical properties
              of solar-like stars with the use of machine learning. With inverse stellar modelling,
              the algorithm is able to determine the unknown characteristics of oscillating stars based on their frequency spectra.
            </p>

          </div>

              <div class="login-wrapper">

                <h1 class="index" style="color:white; font-size:20px;">Log in</h1><br />
                <form class="form" action="#login" method="post" enctype="multipart/form-data" autocomplete="off">
                  <div class="alert alert-error"><?= $_SESSION['login-message'] ?></div>
                  <input type="text" name="top" placeholder="username or email" required/>
                  <input type="password" name="password" id="passwrd" placeholder="password" required/>
                  <p style="background-color: rgba(0, 0, 0, .9);color:white; padding: 6px 5px;width:"><input type="checkbox" onclick="myPassword()"> Show Password</p><br />
                  <a href="#" onclick="toggle_visibility('forgot_psw')" class="btn" style="background-color: rgba(0, 0, 0, .9);
                  color: white;">Forgot Password</a><br /><br />
                  <input type="submit" value="Login" name="login" class="btn btn-block btn-primary" />
                </form>

              </div>
          </div>
      </div>
    </div>

    <a href="#" onclick="toggle_visibility('catalog')"><div class="start-now">Catalog</div></a>
    <br /><br /><br /><br /><br /><br /><br /><br />

    <div style="margin-left:5%;margin-right:5%;margin-bottom:10%;overflow:auto;">
        <h2 class="index" style="color:rgb(35,35,35); font-size:50px;text-align:center;">Research</h2>
          <div style="margin-top:5%;margin-left:25%;width:50%">
            <ul reversed>
              <li>Grupp, Frank, and Lyudmila I. Mashonkina.
                <a target="_blank" href="#">
                  How Accurately Can We Determine Stellar Parameters. The Case of Teff in Cool Stars."</a>
                <i>ASPC..384..221G.</i> SAO/NASA Astrophysics Data System (ADS), 2008.</li><br />
              <li>Handler G (2013)
                <a target="_blank" href="#">Asteroseismology.</a>
                In: Oswalt TD Barstow MA (eds) Planets, Stars and Stellar Systems.
                Volume 4: Stellar Structure and Evolution, Springer, p 207</li><br />
              <li>Di Mauro, M. P. (2017).
                <a target="_blank" href="#">A review on Asteroseismology.</a>
                arXiv preprint arXiv:1703.07604.</li><br />
              <li>Bellinger, E. P., Basu, S., Hekker, S., Ball, W. (2017).
                <a href="http://adsabs.harvard.edu/abs/2017ApJ...851...80B" target="_blank">
                  Model-independent Measurement of Internal Stellar Structure in 16 Cygni A and B</a>.
                  <i>The Astrophysical Journal</i>, 851 (2), 80.</li><br />
              <li>Bellinger, E. P., Angelou, G. C., Hekker, S., Basu, S., Ball, W., Guggenberger, E. (2016).
                <a href="http://adsabs.harvard.edu/abs/2016ApJ...830...31B" target="_blank">
                  Fundamental Parameters of Main-Sequence Stars in an Instant with Machine Learning</a>.
                  <i>The Astrophysical Journal</i>, 830 (1), 20.</li><br />
              <li>Guggenberger, E., Hekker, S., Basu, S., Angelou, G. C., Bellinger, E. P. (2017).
                <a href="http://adsabs.harvard.edu/abs/2017MNRAS.470.2069G" target="_blank">
                  Mitigating the mass dependence in the Δν scaling relation of red-giant stars</a>.
                  <i>Monthly Notices of the Royal Astronomical Society</i>,  470 (2), doi: 10.1093/mnras/stx1253.</li><br />
            </ul></div>
          </div>
    </div>

    <div class="signup-content">
      <div class="module">
        <h1 class="index" style="color:rgb(35,35,35); text-align:center;">Sign Up</h1><br /><br />
        <form class="form" action="#signup" method="post" enctype="multipart/form-data" autocomplete="off">
          <div class="alert alert-error"><?= $_SESSION['message'] ?></div>
          <input type="text" placeholder="First Name" name="first_name" required />
          <input type="text" placeholder="Last Name" name="last_name" required />
          <input type="text" placeholder="User Name" name="username" required />
          <input type="email" placeholder="Email" name="email" required />
          <input type="password" id="myInput" name="password" placeholder="Password" autocomplete="new-password" required/>
          <input type="password" id="myInput" placeholder="Confirm Password" name="confirmpassword" autocomplete="new-password" required />
          <p style="color:rgb(70, 81, 97)"><input type="checkbox" onclick="myPassword()"> Show Password</p><br />
          <input type="submit" value="Register" name="register" class="btn btn-block btn-primary" />
        </form>
      </div>
    </div>

    <div style="padding-bottom:10%;padding-top:7%;background-color:rgb(235,235,235)">
      <div style="margin-left:10%;margin-right:10%;">
        <h2 style="float:right;color:rgb(35,35,35); font-size:35px;">Contact Us</h2>
      </div>
    </div>
    <div class="start-now-no-hover">
      <p style="margin-right:9%;color:white;font-size:20px;float:right;vertical-align:middle" href="#">Starithm</p>
      <a class="menu-home-home" style="color:white;font-size:12px;vertical-align:middle" target="_blank" href="terms.php"><p>Terms of Use</p></a>
      <a class="menu-home-home" style="color:white;font-size:12px;vertical-align:middle" target="_blank" href="privacy.php"><p>Privacy Policy</p></a>
    </div>

  </body>

    <script src="functions.js"></script>
    <script type="text/javascript">
      function toggle_visibility(id){
        var toggling = document.getElementById(id);
        if (toggling.style.display == 'block')
          toggling.style.display = 'none';
        else
          toggling.style.display = 'block';
      }
      $(function() {
        var availableTags = <?php echo json_encode($stars) ?>;
        $( "#tags" ).autocomplete({
          source: availableTags
        });
      } );
    </script>

    <div id="forgot_psw" class="popup-container">
      <a href="#" onclick="toggle_visibility('forgot_psw')" class="popup-wrap-large"></a>
      <div class="popup-position">
          <p>Reset or Recover Password</p><br />
          <form class="form" method="post" action"#recover_password" enctype="multipart/form-data" autocomplete="off">
              <input type="text" name="known" placeholder="Insert Email or Username" required/>
              <input type="submit" value="Submit" name="submit" class="btn btn-block btn-primary"/>
          </form>
      </div>
    </div>

    <div id="catalog" class="popup-container">
      <a href="#" onclick="toggle_visibility('catalog')" class="popup-wrap-large"></a>
      <div class="popup-pos-long">
        <h1>Catalog</h1><br />
        <div style="margin-left:2%;">
          <?php
          foreach ($stars as $star){
            if (($star != '.') && ($star != '..')) {
              print '<a href="stars/'.$star.'" target="_blank" class="cat_a">'.$star.'</a><br />';
            }
          }
          ?>
        </div>
      </div>
    </div>

    <div id="star" class="popup-container">
      <a href="#" onclick="toggle_visibility('star')" class="popup-wrap-large"></a>
      <div class="popup-position">
          <p><?=$_SESSION['star']?></p><br />
          <?php
          $files = 'stars/'.$value;
          $files = scandir($files, 1);
          foreach ($files as $file){
            if (($file != '.') && ($file != '..')) {
              print '<a href="stars/'.$value.'/'.$file.'" target="_blank" class="cat_a">'.$file.'</a><br />';
            }
          }
          ?>
      </div>
    </div>


</html>
