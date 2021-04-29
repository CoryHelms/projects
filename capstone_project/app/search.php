<?php

require '../server/googleLogin/dbh-inc.php';

// If user is not logged in, redirect
$userID = $_SESSION['login_id'];
$stuGogID = $_SESSION['studentGoogleID'];
$ownGogID = $_SESSION['ownerGoogleID'];

if (!isset($_SESSION['login_id'])) {
    header('Location: ../server/googleLogin/login.php');
    exit;
}

// property owner profile vs student profile
$propowner = FALSE;
$student = FALSE;

// Check if user is in Property Owner table or student table, save check in variable
$checkUserOwner = mysqli_query($conn, "SELECT ownerID FROM PropertyOwner WHERE ((ownerID = '$userID') && (googleID = '$ownGogID'))");
$checkUserStudent = mysqli_query($conn, "SELECT studentID FROM Student WHERE ((studentID = '$userID') && (googleID = '$stuGogID'))");

// If they are in a table, change status
if(mysqli_num_rows($checkUserOwner) > 0){
    $propowner = TRUE;
    $row = mysqli_fetch_array($checkUserOwner);
    $ownID = $row[0];
} elseif (mysqli_num_rows($checkUserStudent) > 0) {
    $student = TRUE;
    $row = mysqli_fetch_array($checkUserStudent);
    $stID = $row[0];
} else {
    echo '<p> Something went wrong! No profile info found </p>';
}

// If they are in Property Owner table, limit searches
if(mysqli_num_rows($checkUserOwner) > 0){
  $propowner = TRUE;

  if(isset($_POST['search'])){
    $search = mysqli_real_escape_string($conn,$_POST['search']);

    $query = "SELECT companyName FROM PropertyOwner WHERE (companyName LIKE '%".$search."%') UNION SELECT name FROM PropertyOwner WHERE (name LIKE '%".$search."%') LIMIT 20";
    $result = mysqli_query($conn,$query);

    while($row = mysqli_fetch_array($result) ){
        $response[] = array("label"=>$row[0]);
    }

    echo json_encode($response);
    exit;
  }
} 

// If they are in Student table, all searches
elseif (mysqli_num_rows($checkUserStudent) > 0) {
  $student = TRUE;

  if(isset($_POST['search'])){
    $search = mysqli_real_escape_string($conn,$_POST['search']);

    $query = "SELECT name FROM PropertyOwner WHERE (name LIKE '%".$search."%') UNION SELECT companyName FROM PropertyOwner WHERE (companyName LIKE '%".$search."%') UNION SELECT name FROM Student WHERE ((name LIKE '%".$search."%') OR (preferredName LIKE '%".$search."%')) LIMIT 20";
    $result = mysqli_query($conn,$query);

    while($row = mysqli_fetch_array($result) ){
        $response[] = array("label"=>$row[0]);
    }

    echo json_encode($response);
    exit;
  }
} else {
  echo '<p> Something went wrong! No profile found </p>';
}
?>