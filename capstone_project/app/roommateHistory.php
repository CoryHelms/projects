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

// save form data once saved
if (isset($_POST['submit'])) {

	// Get POST data from form
	// escape variables for security sql injection
	$sanroommateName = mysqli_real_escape_string($conn, $_POST['rhRoommate']);
	$sanownerName = mysqli_real_escape_string($conn, $_POST['rhOwner']);
	$sanstartDate = mysqli_real_escape_string($conn, $_POST['rhStartDate']);
	$sanendDate = mysqli_real_escape_string($conn, $_POST['rhEndDate']);
	$sanstr = mysqli_real_escape_string($conn, $_POST['studentStreet']);
	$sanaddType = mysqli_real_escape_string($conn, $_POST["studentAddType"]);
	$sancity = mysqli_real_escape_string($conn, $_POST['studentCity']);
	$sanstate = mysqli_real_escape_string($conn, $_POST['studentState']);
	$sanzip = mysqli_real_escape_string($conn, $_POST['studentZip']);

	// Find the id of the roommate for insert into RoommateValidation table
	$sql_roommate = "SELECT studentID from Student WHERE name = '$sanroommateName'";
    $roommate_result = mysqli_query($conn, $sql_roommate);

    if (mysqli_num_rows($roommate_result) > 0) {
        $row = mysqli_fetch_array($roommate_result);
        $roommateID = $row[0];
        // Find the id of the owner for insert into RoommateValidation table
        $sql_owner = "SELECT ownerID from PropertyOwner WHERE name = '$sanownerName' OR companyName = '$sanownerName'";
        $owner_result = mysqli_query($conn, $sql_owner);

        if (mysqli_num_rows($owner_result) > 0) {
            $row = mysqli_fetch_array($owner_result);
            $ownerID = $row[0];

            $sql = "SELECT * FROM RoommateValidation WHERE ((studentID = '$stID') && (roommateID = '$roommateID') && (ownerID = '$ownerID') && (startDate = '$sanstartDate') && (endDate = '$sanendDate') && (str = '$sanstr') && (addType = '$sanaddType') && (city = '$sancity') && (state = '$sanstate') && (zip = '$sanzip'))";
            $result = mysqli_query($conn, $sql);

            if (mysqli_num_rows($result) > 0) {
                echo 'You have already submitted a roommate request for this roommate, property, location, and dates!';
            } else {
                // Add living info to roommate validation table in database
                $sql = "INSERT INTO RoommateValidation(studentID, roommateID, ownerID, startDate, endDate, str, city, state, zip, addType) VALUES ('$userID', '$roommateID', '$ownerID', '$sanstartDate', '$sanendDate', '$sanstr', '$sancity','$sanstate','$sanzip', '$sanaddType')";
                $query = mysqli_query($conn, $sql);

                if($query){
                    // check to see if both students have submitted the form into the roommate validation table
                    $sql_validate1 = "SELECT * FROM RoommateValidation WHERE ((studentID = '$stID') && (roommateID = '$roommateID') && (ownerID = '$ownerID') && (startDate = '$sanstartDate') && (endDate = '$sanendDate') && (str = '$sanstr') && (addType = '$sanaddType') && (city = '$sancity') && (state = '$sanstate') && (zip = '$sanzip'))";
                    $validate_result1 = mysqli_query($conn, $sql_validate1);

                    $sql_validate2 = "SELECT * FROM RoommateValidation WHERE ((studentID = '$roommateID') && (roommateID = '$stID') && (ownerID = '$ownerID') && (startDate = '$sanstartDate') && (endDate = '$sanendDate') && (str = '$sanstr') && (addType = '$sanaddType') && (city = '$sancity') && (state = '$sanstate') && (zip = '$sanzip'))";
                    $validate_result2 = mysqli_query($conn, $sql_validate2);

                    if ((mysqli_num_rows($validate_result1) > 0) && (mysqli_num_rows($validate_result2) > 0)) {
                        // Add living info to roommate history table in database after validation
                        $sql = "INSERT INTO RoommateHistory (studentID, roommateID, ownerID, startDate, endDate, str, addType, city, state, zip) VALUES ('$stID', '$roommateID', '$ownerID', '$sanstartDate', '$sanendDate', '$sanstr', '$sanaddType','$sancity','$sanstate','$sanzip')";
                        $query = mysqli_query($conn, $sql);

                        if($query) {
                            $_SESSION['rhSuccess'] = TRUE;

                            //produce notification for successful validation
                            $student_notif_sql1 = "INSERT INTO Notification (senderID, studentRecipientID, notifText) VALUES ('$stID', '$stID', 'Your prior roommate history has been validated!')";
                            $student_notif_query1 = mysqli_query($conn, $student_notif_sql1);

                            $student_notif_sql2 = "INSERT INTO Notification (senderID, studentRecipientID, notifText) VALUES ('$stID', '$roommateID', 'Your prior roommate history has been validated!')";
                            $student_notif_query2 = mysqli_query($conn, $student_notif_sql2);

                            $student_notif_sql3 = "INSERT INTO Notification (senderID, ownerRecipientID, notifText) VALUES ('$stID', '$ownerID', 'Your location for two roommates has been validated!')";
                            $student_notif_query3 = mysqli_query($conn, $student_notif_sql3);

                            header("Location: roommateValidation.php");
                            exit;
                        } else {
                            echo "Data insert failed! Something went wrong.";
                        }
                    } else {
                        $_SESSION['rhSuccess'] = FALSE;

                        //produce notification for roommate request
                        $student_notif_sql4 = "INSERT INTO Notification (senderID, studentRecipientID, notifText) VALUES ('$stID', '$roommateID', 'You have a roommate request! Fill out your Roommate Request form now!')";
                        $student_notif_query4 = mysqli_query($conn, $student_notif_sql4);

                        $student_notif_sql5 = "INSERT INTO Notification (senderID, ownerRecipientID, notifText) VALUES ('$stID', '$ownerID', 'Your location has been requested!')";
                        $student_notif_query5 = mysqli_query($conn, $student_notif_sql5);

                        header("Location: roommateValidation.php");
                        exit;
                    }
                } else {
                    echo "Data insert failed! Something went wrong.";
                }
            }
        } else {
            echo 'No property owner or company with this name was found.';
        }
    } else {
        echo 'No student with this name was found.';
    }
}
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
            <div class="search1">
                <form action="search_result.php" method="post" class="search-form">
                    <input type="text" id="autocomplete" name="autocomplete">
                    <label class="search-label" for="autocomplete"><i class="fa fa-search"></i></label>
                </form>
            </div>
            <!-- Navigation -->
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
                </nav>

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

			<div class="notif">
                <!-- circle button for notification dropdown -->
                <div id="notif_button">
                    <i class="fas fa-bell fa-2x"></i>
                </div>
            </div>

            <!--show notification count-->
            <div id="notif_counter"></div>
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

			<div class="center_no_flex">

				<h2 class="rh_h2"> Your Housing Plans </h2>

				<form action='roommateHistory.php' method='post'>
                    <!-- basic info -->
                    <h3 class="editprofile_h3"> Basic Info </h3>

                    <div class="basic_info_edit">
					    <label for="rhRoommate"> Roommate: </label>
						<input type='text' id="rhRoommate" name='rhRoommate' placeholder="John Smith" required><br>

						<label for="rhOwner"> Property Owner: </label>
						<input type='text' id="rhOwner" name='rhOwner' placeholder="Cool Rentals" required><br>

						<label for="rhStartDate"> Start Date: </label>
						<input type='date' id="rhStartDate" name='rhStartDate' required><br>

						<label for="rhEndDate"> End Date: </label>
						<input type='date' id="rhEndDate" name='rhEndDate' required><br>
					</div>

                    <!-- address -->
                    <h3 class="editprofile_h3"> Address </h3>

                    <div class="address_edit">
                        <label for="studentStreet">Street:</label>
                        <input type="text" id="studentStreet" name="studentStreet" placeholder="123 Cool Str" required><br>

                        <label for="studentAddType">APT / STE / Other:</label>
                        <input type="text" id="studentAddType" name="studentAddType" placeholder="Apt 0"><br>

                        <label for="studentCity">City:</label>
                        <input type="text" id="studentCity" name="studentCity" placeholder="Cool City" required><br>

                        <label for="studentState">State:</label>
                        <select id="studentState" name="studentState" required>
                            <option value="" disabled selected>Select your state</option>
                            <option value="AL">Alabama</option>
                            <option value="AK">Alaska</option>
                            <option value="AZ">Arizona</option>
                            <option value="AR">Arkansas</option>
                            <option value="CA">California</option>
                            <option value="CO">Colorado</option>
                            <option value="CT">Connecticut</option>
                            <option value="DE">Delaware</option>
                            <option value="DC">District Of Columbia</option>
                            <option value="FL">Florida</option>
                            <option value="GA">Georgia</option>
                            <option value="HI">Hawaii</option>
                            <option value="ID">Idaho</option>
                            <option value="IL">Illinois</option>
                            <option value="IN">Indiana</option>
                            <option value="IA">Iowa</option>
                            <option value="KS">Kansas</option>
                            <option value="KY">Kentucky</option>
                            <option value="LA">Louisiana</option>
                            <option value="ME">Maine</option>
                            <option value="MD">Maryland</option>
                            <option value="MA">Massachusetts</option>
                            <option value="MI">Michigan</option>
                            <option value="MN">Minnesota</option>
                            <option value="MS">Mississippi</option>
                            <option value="MO">Missouri</option>
                            <option value="MT">Montana</option>
                            <option value="NE">Nebraska</option>
                            <option value="NV">Nevada</option>
                            <option value="NH">New Hampshire</option>
                            <option value="NJ">New Jersey</option>
                            <option value="NM">New Mexico</option>
                            <option value="NY">New York</option>
                            <option value="NC">North Carolina</option>
                            <option value="ND">North Dakota</option>
                            <option value="OH">Ohio</option>
                            <option value="OK">Oklahoma</option>
                            <option value="OR">Oregon</option>
                            <option value="PA">Pennsylvania</option>
                            <option value="RI">Rhode Island</option>
                            <option value="SC">South Carolina</option>
                            <option value="SD">South Dakota</option>
                            <option value="TN">Tennessee</option>
                            <option value="TX">Texas</option>
                            <option value="UT">Utah</option>
                            <option value="VT">Vermont</option>
                            <option value="VA">Virginia</option>
                            <option value="WA">Washington</option>
                            <option value="WV">West Virginia</option>
                            <option value="WI">Wisconsin</option>
                            <option value="WY">Wyoming</option>
                        </select><br>

                        <label for="studentZip">Zip:</label>
                        <input type="text" id="studentZip" name="studentZip" pattern="[0-9]{5}" maxlength="5" placeholder="00000"
                        oninput="javascript: if (numberInput.validity.valueMissing && !numberInput.validity.badInput) {errorMessage.textContent = 'Field must consist of exactly 5 numbers';}"
                        oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required><br><br>

					<div class="button">
						<button type="submit" name="submit"> Submit </button>
					</div>
				</form>
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
