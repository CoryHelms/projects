<?php
//Start session
session_start();

//Unset session variables
$_SESSION = array();

//Kill the session and cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

//Remove all session variables, destroy the session, and redirect to login
session_unset();
session_destroy();
session_write_close();
setcookie(session_name(),'',time()-7000000,'/');
setcookie('login_id','',time()-7000000,'/');
session_regenerate_id(true);
header("Location: login.php");
exit;
?>