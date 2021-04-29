<?php
//Start session
session_start();

//Add database info
require '../server/googleLogin/dbh-inc.php';

if(isset($_POST["submit"])) {
    //Get POST data from form
    $fname = mysqli_real_escape_string($conn, $_POST["fname"]);
    $lname = mysqli_real_escape_string($conn, $_POST["lname"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $phone = mysqli_real_escape_string($conn, $_POST["phone"]);
    $subject = mysqli_real_escape_string($conn, $_POST["subject"]);
    $message = mysqli_real_escape_string($conn, $_POST["message"]);

    $sql = "INSERT INTO Feedback (fname, lname, email, phone, subject, message) VALUES ('$fname', '$lname', '$email', '$phone', '$subject', '$message')";
    $query = mysqli_query($conn, $sql);

    if($query){
        header("Location: contact_received.php");
        exit;
    }
    else{
        echo "Data insert failed! Something went wrong.";
    }
}

//Add google-api folder
require '../server/googleLogin/google-api/vendor/autoload.php';

//Add Google API Keys
$clientConnection = new Google_Client();
$clientConnection->setClientId('261718937372-8cqr2ufaliv8adv4ce7j3r44cb20psgo.apps.googleusercontent.com');
$clientConnection->setClientSecret('jgzmIiW_RE2SRWAKbwhsl2MD');

//Set redirect Uri
$clientConnection->setRedirectUri('https://cgi.luddy.indiana.edu/~team55/server/googleLogin/login.php');

//Get email and profile from Google
$clientConnection->addScope("email");
$clientConnection->addScope("profile");


//Check if 'code' is in URL
if(isset($_GET['code'])):

    $userToken = $clientConnection->fetchAccessTokenWithAuthCode($_GET['code']);

    //Ensure error is not in URL
    if(!isset($userToken["error"])){

        $clientConnection->setAccessToken($userToken['access_token']);

        $google_oauth = new Google_Service_Oauth2($clientConnection);
        $google_account_info = $google_oauth->userinfo->get();

        //Get user info from Google
        $gogId = mysqli_real_escape_string($conn, $google_account_info->id);
        $full_name = mysqli_real_escape_string($conn, trim($google_account_info->name));
        $email = mysqli_real_escape_string($conn, $google_account_info->email);
        $profile_pic = mysqli_real_escape_string($conn, $google_account_info->picture);

        // Check if user exists in Property Owner Table
        $checkUserOwner = mysqli_query($conn, "SELECT googleID FROM PropertyOwner WHERE googleID = '$gogId'");

        //If so, set session and redirect to owner info
        if(mysqli_num_rows($checkUserOwner) > 0){
            $query = "SELECT ownerID FROM PropertyOwner WHERE googleID=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $gogId);
            $stmt->execute();
            $result = $stmt->get_result();
            $value = $result->fetch_row()[0];
            $_SESSION['login_id'] = $value;
            header('Location: ../server/owner-info.php');
            exit;
        }

        //If they don't exist in property owner table:
        else {
            //Check if user exists in Student Table
            $checkUserStudent = mysqli_query($conn, "SELECT googleID FROM Student WHERE googleID = '$gogId'");

            //If so, set session and redirect to student info
            if(mysqli_num_rows($checkUserStudent) > 0){
                $query1 = "SELECT studentID FROM Student WHERE googleID=?";
                $stmt1 = $conn->prepare($query1);
                $stmt1->bind_param("s", $gogId);
                $stmt1->execute();
                $result1 = $stmt1->get_result();
                $value1 = $result1->fetch_row()[0];
                $_SESSION['login_id'] = $value1;
                header('Location: ../server/student-info.php');
                exit;
            }

            // if they don't exist in property owner or student tables:
            else {
                // Check if email ends in "@iu.edu"
                $parts = explode('@', $email);
                $domain = "@" . $parts[1];

                // If the email does end in "@iu.edu" does, add to student table
                if (($domain == "@iu.edu") || ($domain == "@indiana.edu")) {
                    $studentInsert = mysqli_query($conn, "INSERT INTO Student (googleID,name,profileIMG,email) VALUES ('$gogId','$full_name','$profile_pic','$email')");

                    // If user is added correctly, set session and redirect to student info
                    if($studentInsert){
                        $query2 = "SELECT studentID FROM Student WHERE googleID=?";
                        $stmt2 = $conn->prepare($query2);
                        $stmt2->bind_param("s", $gogId);
                        $stmt2->execute();
                        $result2 = $stmt2->get_result();
                        $value2 = $result2->fetch_row()[0];
                        $_SESSION['login_id'] = $value2;
                        header('Location: ../server/student-info.php');
                        exit;
                    }
                    else{
                        echo "Sign up failed! Something went wrong.";
                    }
                }

                //If email doesn't end in "@iu.edu", add to Property Owner table
                else {
                    $ownerInsert = mysqli_query($conn, "INSERT INTO PropertyOwner (googleID,name,profileIMG,email) VALUES ('$gogId','$full_name','$profile_pic','$email')");

                    // If user is added correctly, set session and redirect
                    if($ownerInsert){
                        $query3 = "SELECT ownerID FROM PropertyOwner WHERE googleID=?";
                        $stmt3 = $conn->prepare($query3);
                        $stmt3->bind_param("s", $gogId);
                        $stmt3->execute();
                        $result3 = $stmt3->get_result();
                        $value3 = $result3->fetch_row()[0];
                        $_SESSION['login_id'] = $value3;
                        header('Location: ../server/owner-info.php');
                        exit;
                    }
                    else{
                        echo "Sign up failed! Something went wrong.";
                    }
                }
            }
        }

    }
    else{
        header('Location: ../server/googleLogin/login.php');
        exit;
    }

else:

?>
<!DOCTYPE html>
<html lang="en">

	<!--
		Julia Turner
        Olivia Nash
        Sarmanya Bhiwaniwala
        Cory Helms
    	I495 Informatics Capstone
		Academic Year 2020 - 2021
	-->

    <head>
		<meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Room@IU</title>

        <!-- Leaflet: Map -->
		<link rel="stylesheet" href="https://unpkg.com/leaflet@1.6.0/dist/leaflet.css"
		integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ=="
		crossorigin=""/>

        <!-- resets browser defaults -->
        <link rel="stylesheet" type="text/css" href="../server/css/normalize.css">
        <!-- custom styles -->
        <link rel="stylesheet" type="text/css" href="../server/css/styles.css">

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.gstatic.com">

        <link href="https://fonts.googleapis.com/css2?family=Montserrat&family=Mulish&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Ubuntu&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@600&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Mulish:wght@800&display=swap" rel="stylesheet">

        <!-- Font Awesome -->
        <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet" >
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" >
        <link href="https://netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
        <link href="https://use.fontawesome.com/releases/v5.15.3/css/all.css" integrity="sha384-SZXxX4whJ79/gErwcOYf+zWLeJdY/qpuqC4cAa9rOGUstPomtqpuNWT9wdPEn2fk" rel="stylesheet" crossorigin="anonymous">
        <script src="https://use.fontawesome.com/71c2495e48.js"></script>

		<!-- search -->
        <link rel="stylesheet" type="text/css" href="../server/js/jquery-ui-1.12.1/jquery-ui.min.css">
        <script type="text/javascript" src="../server/js/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="../server/js/jquery-ui-1.12.1/jquery-ui.min.js"></script>
        
        <!-- Script -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <!-- jQuery UI -->
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
	</head>

    <body>
        <header>
            <!-- Navigation -->
            <?php
                // If user is not logged in
                $userID = $_SESSION['login_id'];
                if (!isset($_SESSION['login_id'])) {
                    echo '<div class="nav">
                        <nav id="mySidenav" class="sidenav">
                            <a class="closebtn">&times;</a>
                            <a href="../server/googleLogin/login.php">Home</a>
                            <a href="aboutUs.php">About Us</a>
                            <a href="'. $clientConnection->createAuthUrl().'">Student Login</a>
                            <a href="'. $clientConnection->createAuthUrl().'">Property Owner Login</a>
                            <a href="map.php">Map</a>
                            <a href="contact_us.php">Contact Us</a>
                        </nav>';
                } else {
                    $userID = $_SESSION['login_id'];
                    $stuGogID = $_SESSION['studentGoogleID'];
                    $ownGogID = $_SESSION['ownerGoogleID'];
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

                    if(mysqli_num_rows($checkUserOwner) > 0){
                        echo '<div class="search1">
                            <form action="search_result.php" method="post" class="search-form">
                                <input type="text" id="autocomplete" name="autocomplete">
                                <label class="search-label" for="autocomplete"><i class="fa fa-search"></i></label>
                            </form>
                        </div>
                        <div class="nav">
                            <nav id="mySidenav" class="sidenav">
                                <a class="closebtn">&times;</a>
                                <div class="search2">
                                    <form action="search_result.php" method="post" class="search-form">
                                        <input type="text" id="autocomplete" name="autocomplete">
                                        <label class="search-label" for="autocomplete"><i class="fa fa-search"></i></label>
                                    </form>
                                </div>
                                <a href="home.php">Home</a>
                                <a href="../server/instant_messaging/instant_messaging.php">Chat Room</a>
                                <a href="aboutUs.php">About Us</a>
                                <a href="contact_us.php">Contact Us</a>
                                <a href="map.php"> Map </a>
                                <a href="viewProfile.php"> Profile </a>
                                <a href="../server/googleLogin/logout.php">Log Out</a>
                            </nav>';
                    } elseif (mysqli_num_rows($checkUserStudent) > 0) {
                        echo '<div class="search1">
                            <form action="search_result.php" method="post" class="search-form">
                                <input type="text" id="autocomplete" name="autocomplete">
                                <label class="search-label" for="autocomplete"><i class="fa fa-search"></i></label>
                            </form>
                        </div>
                        <div class="nav">
                            <nav id="mySidenav" class="sidenav">
                                <a class="closebtn">&times;</a>
                                <div class="search2">
                                    <form action="search_result.php" method="post" class="search-form">
                                        <input type="text" id="autocomplete" name="autocomplete">
                                        <label class="search-label" for="autocomplete"><i class="fa fa-search"></i></label>
                                    </form>
                                </div>
                                <a href="home.php">Home</a>
                                <a href="aboutUs.php">About Us</a>
                                <a href="../server/instant_messaging/instant_messaging.php">Chat Room</a>
                                <a href="quiz.php">Roommate Compatibility Questionnaire</a>
                                <a href="matching.php">Matches</a>
                                <a href="roommateHistory.php">Roommate Request Form</a>
                                <a href="rating.php"> Ratings </a>
                                <a href="contact_us.php">Contact Us</a>
                                <a href="map.php"> Map </a>
                                <a href="viewProfile.php"> Profile </a>
                                <a href="../server/googleLogin/logout.php">Log Out</a>
                            </nav>';
                    } else {
                        echo '<p> Something went wrong! No user is found</p>';
                    }
                }
            ?>
                <!-- Open Sidebar Nav -->
                <div class="openbtn">
                    <i class="fa fa-bars"></i>
                    <span class="menu-text">menu</span>
                </div>

                <div class="all-over-bkg"></div>
            </div>

            <!-- Header Text -->
            <div class="header-text">
                <h1>Room@IU</h1>
            </div>
      
            <?php
                if (isset($_SESSION['login_id'])) {
                    echo '
            <div class="notif">     
                <!-- circle button for notification dropdown -->
                <div id="notif_button">
                    <i class="fas fa-bell fa-2x"></i>
                </div>
            </div>

            <!--show notification count-->
            <div id="notif_counter"></div>';
                } elseif (!isset($_SESSION['login_id'])) {
                    echo '';
                } else {
                    echo 'Something went wrong!';
                }
            ?>
        </header>
        <main>
            <!--notification dropdown box-->
            <div id="notifications">
                <h3 class="seeAll">Notifications</h3>
                <div class="notif_contents">
                    <?php
                        if ($propowner == TRUE) {
                            // prop unseen notifs
                            $notif_select = "SELECT notifText FROM Notification WHERE ownerRecipientID = '$ownID' and notifStatus = 0";
                            $notif_result = mysqli_query($conn, $notif_select);

                            // prop seen notifs
                            $notif_select2 = "SELECT notifText FROM Notification WHERE ownerRecipientID = '$ownID' and notifStatus = 1";
                            $notif_result2 = mysqli_query($conn, $notif_select2);

                            if (mysqli_num_rows($notif_result) > 0) {
                                echo '<h5> New Notifications </h5>';
                                while ($row = mysqli_fetch_array($notif_result)) {
                                    echo '<strong>'.$row[0].'</strong><br>';
                                }
                            } else {
                                echo '<style type="text/css">
                                #notif_counter {
                                    display: none;
                                }
                                </style>';
                            }

                            if (mysqli_num_rows($notif_result2) > 0) {
                                echo '<h5> Old Notifications </h5>';
                                while ($row2 = mysqli_fetch_array($notif_result2)) {
                                    echo $row2[0].'<br>';
                                }
                            }
                        } elseif ($student == TRUE) {
                            // student unseen notifs
                            $notif_select = "SELECT notifText FROM Notification WHERE studentRecipientID = '$stID' and notifStatus = 0";
                            $notif_result = mysqli_query($conn, $notif_select);

                            // prop seen notifs
                            $notif_select2 = "SELECT notifText FROM Notification WHERE studentRecipientID = '$stID' and notifStatus = 1";
                            $notif_result2 = mysqli_query($conn, $notif_select2);

                            if (mysqli_num_rows($notif_result) > 0) {
                                echo '<h5> New Notifications </h5>';
                                while ($row = mysqli_fetch_array($notif_result)) {
                                    echo '<strong>'.$row[0].'</strong><br>';
                                }
                            } else {
                                echo '<style type="text/css">
                                #notif_counter {
                                    display: none;
                                }
                                </style>';
                            }

                            if (mysqli_num_rows($notif_result2) > 0) {
                                echo '<h5> Old Notifications </h5>';
                                while ($row2 = mysqli_fetch_array($notif_result2)) {
                                    echo $row2[0].'<br>';
                                }
                            }
                        } else {
                            echo '<p>Something went wrong</p>';
                        }
                    ?>
                </div>
            </div>

            <div class="contact-container">
                <div class="panel panel-left">
                    <div class="panel-content">
                        <h2>Leave A Comment!</h2>
                        <form action="contact_us.php" method="post" class="contact-form">
                            <div class="group">
                                <input type="text" class="contact-input" id="fname" name="fname" required>
                                <span class="highlight"></span>
                                <label class="contact-label" for="fname"> <span class="ccform-addon"><i class="fas fa-user fa-2x"></i></span> First name</label>
                            </div>
                            <div class="group">
                                <input type="text" class="contact-input" id="lname" name="lname" required>
                                <span class="highlight"></span>
                                <label class="contact-label" for="lname"> <span class="ccform-addon"><i class="far fa-user fa-2x"></i></span> Last name</label>
                            </div>
                            <div class="group">
                                <input type="text" class="contact-input" id="email" name="email" pattern="^[a-zA-Z0-9.!#$%&’*+\/\\=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$" placeholder="you@example.com" required>
                                <span class="highlight"></span>
                                <label class="contact-label" for="email"> <span class="ccform-addon"><i class="fa fa-envelope fa-2x"></i></span> Email</label>
                            </div>
                            <div class="group">
                                <input type="text" class="contact-input" id="phone" name="phone" pattern="\(\d{3}\) (\d{3})[-](\d{4})" placeholder="(000) 000-0000" required>
                                <span class="highlight"></span>
                                <label class="contact-label" for="phone"> <span class="ccform-addon"><i class="fa fa-mobile-phone fa-2x"></i></span> Phone Number</label>
                            </div>
                            <div class="group">
                                <input type="text" class="contact-input" id="subject" name="subject" required>
                                <span class="highlight"></span>
                                <label class="contact-label" for="subject"> <span class="ccform-addon"><i class="fa fa-info fa-2x"></i></span> Subject</label>
                            </div>
                            <div class="group">
                                <textarea class="contact-textarea" id="message" name="message" required></textarea>
                                <span class="highlight"></span>
                                <label class="contact-label" for="message"> <span class="ccform-addon"><i class="fa fa-comment fa-2x"></i></span> Message </label>
                            </div>
                            <div class="contact_button">
                                <button type="submit" name="submit"> Send </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="panel panel-right">
                    <div class="panel-content">
                        <h2> Stay In Touch! </h2>
                        <div class="contact-section">
                            <div id="mapid"></div>
                            <a href="http://maps.google.com/?q=Indiana University Bloomington" target="_blank">
                                <h4> Indiana University Bloomington </h4>
                                <p class="contact-p contact-address">107 S Indiana Ave <br> Bloomington, IN 47405</p>
                            </a>
                        </div>
                        <div class="contact-xs">
                            <div class="contact-section">
                                <table class="contact-table">
                                    <tr>
                                        <th> Email us at: </th>
                                        <td><a href="mailto:room.iu.team55@gmail.com">room.iu.team55@gmail.com</a></td>
                                    </tr>
                                    <tr>
                                        <th> Call us at: </th>
                                        <td><a href="tel:317-800-2768">(317)-800-2768</a></td>
                                    </tr>

                                </table>
                            </div>
                        </div>

                        <div class="contact-s-plus">
                            <div class="contact-section">
                                <h4> Email us at: </h4>
                                <p class="contact-p-email"><a href="mailto:room.iu.team55@gmail.com">room.iu.team55@gmail.com</a></p>
                            </div>
                            <div class="contact-section">
                                <h4> Call us at: </h4>
                                <p class="contact-p"><a href="tel:317-800-2768">(317)-800-2768</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <div class="container">
                <div class="footer1">
                    <div class="links">
                        <a href="aboutUs.php"> About Us <strong>|</strong></a> <a href="contact_us.php">Contact Us</a>
                    </div>
                    <p class="p1"> Team 55 <strong>|</strong> 2020 - 2021 Capstone </p>
                    <p class="p2">IU Luddy School of Informatics, Computing, and Engineering</p>
                </div>
                <div class="footer2">
                    <p class="p3">Team 55 <strong>|</strong> 2020 - 2021 Capstone <strong>|</strong> IU Luddy School of Informatics, Computing, and Engineering</p>
                </div>
            </div>
        </footer>

        <!-- Javascript -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <script src="../server/js/nav.js"></script>

        <script>
            $('#message').on('keyup', function() {
                var textarea = $(this);
                if(textarea.val().length === 0) {
                    textarea.removeClass('success');
                } else {
                    textarea.addClass('success');
                }
            });
        </script>

        <script>
            $('#phone').on('keyup', function() {
                var input = $(this);
                if(input.val().length === 0) {
                    input.removeClass('success');
                } else {
                    input.addClass('success');
                }
            });
        </script>

        <script>
            $('#email').on('keyup', function() {
                var input = $(this);
                if(input.val().length === 0) {
                    input.removeClass('success');
                } else {
                    input.addClass('success');
                }
            });
        </script>

        <!-- Script -->
        <script type='text/javascript' >
        $( function() {
    
            $( "#autocomplete" ).autocomplete({
                source: function( request, response ) {
                    
                    $.ajax({
                        url: "search.php",
                        type: 'post',
                        dataType: "json",
                        data: {
                            search: request.term
                        },
                        success: function( data ) {
                            response( data );
                        }
                    });
                },
                select: function (event, ui) {
                    $('#autocomplete').val(ui.item.label); // display the selected text
                    return false;
                },
                focus: function(event, ui){
                    $( "#autocomplete" ).val( ui.item.label );
                    return false;
                },
            });
        });

        function split( val ) {
        return val.split( /,\s*/ );
        }
        function extractLast( term ) {
        return split( term ).pop();
        }

        </script>

        <!-- Map -->
		<script src="https://unpkg.com/leaflet@1.6.0/dist/leaflet.js"
		integrity="sha512-gZwIG9x3wUXg2hdXF6+rVkLF/0Vi9U8D2Ntg4Ga5I5BZpVkVxlJWbSQtXPSiUTtC0TjtGOmxa1AJPuV0CPthew=="
		crossorigin=""></script>
		<script>
			var mymap = L.map('mapid').setView([39.160160, -86.512962], 12);
			L.tileLayer('https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token={accessToken}', {attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
			maxZoom: 18,
			id: 'mapbox/streets-v11',
			tileSize: 512,
			zoomOffset: -1,
			accessToken: 'pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw'}).addTo(mymap);
			var marker = L.marker([39.160160, -86.512962]).addTo(mymap);
			marker.bindPopup("<b>Room @ IU</b><br>IU Bloomington").openPopup();
		</script>

        <!-- jquery for notifications -->
        <script>
            $(document).ready(function () {

                // animation for notification counter
                $('#notif_counter')
                    .text(
                        <?php
                            if ($propowner == TRUE) {
                                $notif_count = "SELECT COUNT(notifText) FROM Notification WHERE notifStatus = 0 AND ownerRecipientID = '$ownID'";

                                $count_result = mysqli_query($conn, $notif_count);
                                if (mysqli_num_rows($count_result) > 0) {
                                    $row = mysqli_fetch_array($count_result);
                                    echo $row[0];
                                } 
                            } elseif ($student == TRUE) {
                                $notif_count = "SELECT COUNT(notifText) FROM Notification WHERE notifStatus = 0 AND studentRecipientID = '$stID'";

                                $count_result = mysqli_query($conn, $notif_count);
                                if (mysqli_num_rows($count_result) > 0) {
                                    $row = mysqli_fetch_array($count_result);
                                    echo $row[0];
                                } 
                            } else {
                                echo 'There is a problem';
                            }
                        ?>
                    )

                $('#notif_button').click(function () {

                    // toggle notification window
                    $('#notifications').fadeToggle('fast', 'linear', function () {
                        if ($('#notifications').is(':hidden')) {
                            $('#notif_button').css('background-color', 'transparent');
                        }
                        // button background color
                        else $('#notif_button').css('background-color', 'transparent');
                    });

                    $('#notif_counter').fadeOut('slow');     // hide counter once button is clicked

                    //updates notification table to mark notification as seen so they won't show again
                    <?php
                        if ($propowner == TRUE) {
                            $notifStatus_update = "UPDATE Notification
                            SET notifStatus = 1
                            WHERE ownerRecipientID = '$ownID' AND notifStatus = 0";

                            $status_query = mysqli_query($conn, $notifStatus_update);
                        } elseif ($student == TRUE) {
                            $notifStatus_update = "UPDATE Notification
                            SET notifStatus = 1
                            WHERE studentRecipientID = '$stID' AND notifStatus = 0";

                            $status_query = mysqli_query($conn, $notifStatus_update);
                        } else {
                            echo 'There is a problem';
                        }
                    ?>

                    return false;
                });

                // hide notifications when clicking anywhere else on page
                $(document).click(function () {
                    $('#notifications').hide();

                    // check to see if notification counter is hidden
                    if ($('#notif_counter').is(':hidden')) {
                        // change background color for button
                        $('#notif_button').css('background-color', 'transparent');
                    }
                });

                $('#notifications').click(function () {
                    return false;       // do nothing when container is clicked
                });
            });
        </script>

    </body>
</html>

<?php endif; ?>
