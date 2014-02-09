<?php
session_start();
// theming time
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>RemoteWAKE 1.0</title>
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />
	<link rel="stylesheet" type="text/css" href="bluebliss.css" />
</head>
<body>
<div id="mainContentArea">
	<div id="contentBox">
        <div id="title">RemoteWAKE</div>
        
        <div id="linkGroup">
            <div class="link"><a href="index.php?action=login">Login</a></div>
            <div class="link"><a href="index.php?action=logout">Logout</a></div>
            <div class="link"><a href="index.php?action=dashboard">Dashboard</a></div>
        </div>
        
        <div id="blueBox"> 
          <div id="header"></div>
          <div class="contentTitle"><?php echo ucfirst($_REQUEST['action']) ?></div>
            <div class="pageContent"><br>

<?php
// Configuration Settings
$users_file = "/file_database/users.json";
$sounds_file = "/file_database/sounds.json";
// Universal Functions
function generate_csrf_token() {
 $token= md5(uniqid());
 $_SESSION['delete_customer_token']= $token;
 return $token;
}
function login_error($messageID) {
  header('Location: /index.php?action=login&error='.$messageID);
}
function auth_user($username, $password, $user_file) {
  $json_file = file_get_contents($user_file);
  $json = json_decode($json_file);
  $authed = false;
  foreach($json as $key, $value) {
    if($key == $username && $value == md5($password))
    {
      $_SESSION['username'] = $username;
      $authed = true;
    }
  }
  return $authed;
}
function send_to_alarm_server($data) {
  // $data is an array. We need to turn it into a specialized variable stringset
  $stringset = "";
  $data_count = count($data);
  $i = 0;
  foreach($data as $key, $value) {
    if ($i == $data_count - 1) {
      // last
      $stringset += $key."==".$value;
    }
    else {
      // not last .. normal output
      $stringset += $key."==".$value."||";
    }
  }
  // stringset is complete (data to be sent to the RemoteWAKE server)
  $server_ip_file = "server_settings.php";
  include($server_ip_file);
  // $server['host'] and $server['port'] should be defined
  // tcp socket time
  if(isset($server) && isset($server['host']) && isset($server['port'])) {
    $fp = fsockopen("tcp://".$server['host'], $server['port'], $errno, $errstr, 10);
    if (!$fp) {
      return "ERROR: $errno - $errstr";
    } else {
       // this is going to timeout IF IT WORKS
       fwrite($fp, $stringset); // send stringset
       // no need to wait for a response... If it doesn't work, there is nothing we can do anyways.
       fclose($fp);
       return true;
    }
  }
}
$action = $_REQUEST('action');
$method = $_SERVER['REQUEST_METHOD'];
$logged_in = isset($_SESSION['username']);
if(empty($action)) {
  header('Location: /index.php?action=login');
}
if($action == "dashboard") {
  // dashboard
  if(!($logged_in)) {
    header('Location: /index.php?action=login');
  }
  else
  {
    if($method == "GET") {
      // show alarm form
      $csrf_token = generate_csrf_token();
      session_write_close();
      $error = $_GET['error'];
      if(isset($error)) {
        // could use switch, but this is easier to understand for just two values
        if($error == "InputError") {
          echo '<div class="error">You must fill all form fields!</div><br>';
        }
      }
      ?>
        <form id="alarm_form" method="POST" action="index.php">
          <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>" />
          <input type="hidden" name="action" value="login" />
          <!-- Form Elements -->
          <p>Alarm Action&nbsp;
            <select name="alarm_action">
              <option value="startAlarm">Start the Alarm NOW!</option>
              <option value="setAlarm">Set the Alarm in a certain amount of seconds or minutes</option>
            </select>
          </p>
          <p>Alarm Time (if you set the above to "Set the Alarm in a certain amount of seconds or minutes") - Set this in seconds (10 minutes is 600 seconds) - Leave blank if alarm is set to "Start the Alarm NOW!"&nbsp;
            <input type="text" name="time" value="600" />
          </p>
          <p>Alarm Type (<a href="/sounds.php" target="_blank">See Sounds</a>):&nbsp;
            <select name="alarm_type">
              <option value="1" selected="selected">One</option>
              <option value="2">Two</option>
              <option value="3">Three</option>
              <option value="4">Four</option>
              <option value="5">Five</option>
            </select>
          </p>
          <p>How Long (in seconds, 120 seconds by default):&nbsp;
            <input type="text" name="how_long" value="120" />
          </p>
          <p>From Name (your name, username shown after):&nbsp;
            <input type="text" name="from_name" value="<?php echo $_SESSION['username']; ?>" />
          </p>
          <p>From Message:&nbsp;
            <textarea name="from_message">Insert a message or any comments here for the person to see when they wake up from the alarm.</textarea>
          </p>
      <?php
    }
    if($method == "POST") {
      // submit alarm
      $token = $_SESSION['delete_customer_token'];
      unset($_SESSION['delete_customer_token']);
      session_write_close();
      if ($token && $_POST['token']==$token) {
        // parsing data into an array
        $data = array();
        $data['alarm_action'] = $_POST['alarm_action'];
        $data['time'] = null;
        if($data['alarm_action'] == "setAlarm" && isset($_POST['time']) && ctype_digit($_POST['time'])) {
          $data['time'] = $_POST['time'];
        }
        $data['alarm_type'] = 1; // default 1
        if(isset($_POST['alarm_type'] && ctype_digit($_POST['alarm_type']))
        {
          $data['alarm_type'] = $_POST['alarm_type'];
        }
        $data['how_long'] = 120; // default 120
        if(isset($_POST['how_long'] && ctype_digit($_POST['how_long']))
        {
          $data['how_long'] = $_POST['how_long'];
        }
        // below requires safety trimming and stripping out html characters and special characters
        $from_name = $_POST['from_name'];
        $from_name_trimmed = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($from_name))))));
        $data['from_name'] = $from_name_trimmed . + " with the username " + $_SESSION['username'];
        $from_message = $_POST['from_message'];
        $data['from_message'] = trim(preg_replace('/ +/', ' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', urldecode(html_entity_decode(strip_tags($from_message))))));
        $response = send_to_alarm_server($data);
        if($response==true) {
          ?>
            <div class="success">Sent request to Alarm Server. As long as the Alarm server is up and running, and no errors were returned, the alarm shall go off.</div>
          <?php
        } else {
          ?>
            <div class="error"><?php echo $response; ?></div>
          <?php
        }
      }
      else {
          // csrf failed
          // echo error
          ?>
            <div class="error">Suspected CSRF Attack/Exploit Attempt. Login Access Denied.</div><br>
          <?php
      }
  }
}
if($action == "logout") {
  session_destroy();
  unset($_SESSION['username']);
  header('Location: index.php?action=login');
}
if($action == "login") {
  // login
  if($logged_in) {
    header('Location: /index.php?action=dashboard');
  }
  else
  {
    if($method == "GET") {
      $csrf_token = generate_csrf_token();
      session_write_close();
      $error = $_GET['error'];
      if(isset($error)) {
        // could use switch, but this is easier to understand for just two values
        if($error == "AuthenticationFailed") {
          echo '<div class="error">Authentication Failed. There was no user found with that username/password combo.</div><br>';
        }
        if($error == "InputError") {
          echo '<div class="error">You must fill both the username and password fields!</div><br>';
        }
      }
      ?>
        <form id="login" method="post" action="index.php">
          <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?>" />
          <input type="hidden" name="action" value="login" />
          <!-- Form Elements -->
          <p>Username:&nbsp;&nbsp;<input type="text" name="username" /></p>
          <p>Password:&nbsp;&nbsp;<input type="password" name="password" /></p>
          <p>Submit:&nbsp;&nbsp;<input type="submit" name="submit" /></p>
        </form>
      <?php
    }
    if($method == "POST") {
      $username = $_POST['username'];
      $password = $_POST['password'];
      if (isset($Username) && isset($password))
      {
        $token = $_SESSION['delete_customer_token'];
        unset($_SESSION['delete_customer_token']);
        session_write_close();
        if ($token && $_POST['token']==$token) {
           // csrf fine
          if(!(auth_user($username, $password, $users_file)))
          {
            // Failed
            // Return to login with error
            login_error("AuthenticationFailed");
          }
          else
          {
            // auth success
            // redirect
            header('Location: index.php?action=dashboard&loggedin=true')
          }
        }
        else
        {
          // csrf failed
          // echo error
          ?>
            <div class="error">Suspected CSRF Attack/Exploit Attempt. Login Access Denied.</div><br>
          <?php
        }
      }
      else
      {
        login_error("InputError");
      }
    }
  }
}
?>
<!-- Footer / Ending HTML -->
</div>
            <div id="footer"><a href="http://www.aszx.net">Theme</a> by <a href="http://www.bryantsmith.com">bryant smith</a></div>
        </div>
	</div>
</div>
</body>
</html>

