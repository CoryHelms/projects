<?php
require '../googleLogin/dbh-inc.php';

$name = $_SESSION[$userName];

if(isset($name)){
    $text = $_POST['text'];
     
    $text_message = "<div class='msgln'><span class='chat-time'>".date("g:i A")."</span> <b class='user-name'>".$name."</b><br><p class='chat'>".stripslashes(htmlspecialchars($text))."</p><br></div>";
    file_put_contents("log.html", $text_message, FILE_APPEND | LOCK_EX);
}
?>
