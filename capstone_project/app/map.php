<?php
//Start session
session_start();

//Add database info
require '../server/googleLogin/dbh-inc.php';

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

        <style type="text/css">
            /* body { font: normal 14px Verdana; } */
            h1 {padding: 15px; }
            /* h2 { font-size: 18px; } */
            #sidebar { float: right; width: 30%; }
            #main { padding: 15px; }
            .infoWindow { width: 220px; }
        </style>

        <!-- Javascript -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyByl056S3O9LHpda09_mjzbjTubQFCuo0Q&callback=init&sensor=false"></script>
        <script>
            function makeRequest(url, callback) {
                var request;

                if (window.XMLHttpRequest) {
                request = new XMLHttpRequest(); // IE7+, Firefox, Chrome, Opera, Safari
                } else {
                request = new ActiveXObject("Microsoft.XMLHTTP"); // IE6, IE5
                }

                request.onreadystatechange = function() {
                if (request.readyState == 4 && request.status == 200) {
                callback(request);
                }
                }

                request.open("GET", url, true);
                request.send();
            }
        </script>
        <script>
            function displayLocation(location) {

                var content =   '<div class="infoWindow"><strong>'  + location.name + '</strong>'
                + '<br/>'     + location.address
                + '<br/>'     + location.type + '</div>';

                if (parseInt(location.lat) == 0) {
                geocoder.geocode( { 'address': location.address }, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {

                var marker = new google.maps.Marker({
                map: map,
                position: results[0].geometry.location,
                title: location.name
                });

                google.maps.event.addListener(marker, 'click', function() {
                infowindow.setContent(content);
                infowindow.open(map,marker);
                });
                }
                });
                } else {
                var position = new google.maps.LatLng(parseFloat(location.lat), parseFloat(location.lon));
                var marker = new google.maps.Marker({
                map: map,
                position: position,
                title: location.name
                });

                google.maps.event.addListener(marker, 'click', function() {
                infowindow.setContent(content);
                infowindow.open(map,marker);
                });
                }
                }
        </script>
        <script type="text/javascript">
            //<![CDATA[
            var map;

            // Bloomington, Indiana Location
            var center = new google.maps.LatLng(39.167080, -86.534241);

            var geocoder = new google.maps.Geocoder();
            var infowindow = new google.maps.InfoWindow();

            function init() {

            var mapOptions = {
            zoom: 13,
            center: center,
            mapTypeId: google.maps.MapTypeId.ROADMAP
            }

            map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);

            // ***might need to change this to match folder path setup***!!!
            makeRequest('get_locations.php', function(data) {

            var data = JSON.parse(data.responseText);

            for (var i = 0; i < data.length; i++) {
            displayLocation(data[i]);
            }
            });
            // var marker = new google.maps.Marker({
            // map: map,
            // position: center,
            // });
            }
            //]]>
        </script>
        <script>
            function makeRequest(url, callback) {
                var request;

                if (window.XMLHttpRequest) {
                request = new XMLHttpRequest(); // IE7+, Firefox, Chrome, Opera, Safari
                } else {
                request = new ActiveXObject("Microsoft.XMLHTTP"); // IE6, IE5
                }

                request.onreadystatechange = function() {
                if (request.readyState == 4 && request.status == 200) {
                callback(request);
                }
                }

                request.open("GET", url, true);
                request.send();
            }
        </script>
        <script>
            // Save geocoding result to the Database
            // ***might need to change this to match folder path setup***!!!
            var url =   'set_coords.php?id=' + location.id
            + '&amp;lat=' + results[0].geometry.location.lat()
            + '&amp;lon=' + results[0].geometry.location.lng();

            makeRequest(url, function(data) {
            if (data.responseText == 'OK') {
            // Success
            }
            });
        </script>
    </head>

    <body onload="init();">
        <header>
            <!-- Navigation -->
            <?php
                //Add database info
                require '../server/googleLogin/dbh-inc.php';

                // If user is not logged in, redirect
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

            <h2 class="center_h2">Apartments in Bloomington!</h2>
            <h3 class="map_h3">Click on a marker to view more information about a location!</h3>

            <section id="sidebar">
                <div id="directions_panel"></div>
            </section>

            <section id="main">
                <div id="map_canvas" style="width: 100%; height: 500px;"></div>
            </section>
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

        <!-- Javascript for Nav -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
        <script src="//code.jquery.com/jquery-1.10.2.js"></script>
        <script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
        <script src="../server/js/nav.js"></script>

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

    </body>
</html>

<?php endif; ?>
