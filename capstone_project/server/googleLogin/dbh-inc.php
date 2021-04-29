<?php
session_start();
//Create variables for server + database
$serverName = "db.luddy.indiana.edu";
$dbUserName = "i494f20_team55";
$dbPassword = "my+sql=i494f20_team55";
$dBName = "i494f20_team55";

//Connect to the database
$conn = mysqli_connect($serverName, $dbUserName, $dbPassword, $dBName);

//Check if connection fails
if (!$conn) {
  die("Connection Failed: " . mysqli_connect_error());
}