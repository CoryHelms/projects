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

// save form data once saved - OWNER
if (isset($_POST['ownersubmit'])) {

	// Get POST data from form
	// escape variables for security sql injection
	$sanowner = mysqli_real_escape_string($conn, $_POST['form_owner']);
	$sanownerrating = mysqli_real_escape_string($conn, $_POST['form_owner_rating']);
	$sanownerreview = mysqli_real_escape_string($conn, $_POST['form_owner_review']);

	// Make sure all inputs are filled in
	function emptyInputInsert($sanowner, $sanownerrating, $sanownerreview) {
		$answer;
		if (empty($sanowner) || empty($sanownerrating) || empty($sanownerreview)) {
			$answer = true;
		} else {
			$answer = false;
		}
		return $answer;
	}

	// If any inputs are empty, redirect
	if (emptyInputInsert($sanowner, $sanownerrating, $sanownerreview) !== false) {
		header("Location: rating.php?error=emptyinput");
		exit();
	}

	$owner_sql_check = "SELECT * FROM OwnerRatings WHERE (raterID = '$userID') and (rateeID = '$sanowner')";
	$owner_result_sql = mysqli_query($conn, $owner_sql_check);

	if (mysqli_num_rows($owner_result_sql) > 0) {
		$owner_sql = "UPDATE OwnerRatings SET raterID='$userID', rateeID='$sanowner', date=CURRENT_DATE(), rating='$sanownerrating', review='$sanownerreview' WHERE (raterID='$userID') AND (rateeID='$sanowner')";
		$owner_query = mysqli_query($conn, $owner_sql);

        //Add notification into database
		$owner_notif_sql = "INSERT INTO Notification (senderID, ownerRecipientID, notifText) VALUES ('$stID', '$sanowner', 'You have an updated rating!')";
		$owner_notif_query = mysqli_query($conn, $owner_notif_sql);

		if($owner_query) {
			header("Location: viewProfile.php");
			exit;
		} else {
			echo "Data insert failed! Something went wrong.";
		}

	} else {
		// Add rating info to owner ratings table in database after validation
		$owner_sql = "INSERT INTO OwnerRatings (raterID, rateeID, date, rating, review) VALUES ('$userID', '$sanowner',CURRENT_DATE(),'$sanownerrating','$sanownerreview')";
		$owner_query = mysqli_query($conn, $owner_sql);

        //Add notification into database
		$owner_notif_sql2 = "INSERT INTO Notification (senderID, ownerRecipientID, notifText) VALUES ('$stID', '$sanowner', 'You have a new rating!')";
		$owner_notif_query2 = mysqli_query($conn, $owner_notif_sql2);

		if($owner_query) {
			header("Location: viewProfile.php");
			exit;
		} else {
			echo "Data insert failed! Something went wrong.";
		}
	}
}

// save form data once saved - ROOMMATE
if (isset($_POST['roommatesubmit'])) {

	// Get POST data from form
	// escape variables for security sql injection
	$sanroommate = mysqli_real_escape_string($conn, $_POST['form_roommate']);
	$sanroommaterating = mysqli_real_escape_string($conn, $_POST['form_roommate_rating']);
	$sanroommatereview = mysqli_real_escape_string($conn, $_POST['form_roommate_review']);

	// Make sure all inputs are filled in
	function emptyInputInsert($sanroommate, $sanroommaterating, $sanroommatereview) {
		$answer;
		if (empty($sanroommate) || empty($sanroommaterating) || empty($sanroommatereview)) {
			$answer = true;
		} else {
			$answer = false;
		}
		return $answer;
	}

	// If any inputs are empty, redirect
	if (emptyInputInsert($sanroommate, $sanroommaterating, $sanroommatereview) !== false) {
		header("Location: rating.php?error=emptyinput");
		exit();
	}

	$roommate_sql_check = "SELECT * FROM RoommateRatings WHERE (raterID='$userID') AND (rateeID='$sanroommate')";
	$roommate_result_sql = mysqli_query($conn, $roommate_sql_check);

	if (mysqli_num_rows($roommate_result_sql) > 0) {
		$roommate_sql = "UPDATE RoommateRatings SET raterID='$userID', rateeID='$sanroommate', date=CURRENT_DATE(), rating='$sanroommaterating', review='$sanroommatereview' WHERE (raterID='$userID') AND (rateeID='$sanroommate')";
		$roommate_query = mysqli_query($conn, $roommate_sql);

        //Add notification into database
		$student_notif_sql = "INSERT INTO Notification (senderID, studentRecipientID, notifText) VALUES ('$stID', '$sanroommate', 'You have an updated rating!')";
		$student_notif_query = mysqli_query($conn, $student_notif_sql);

		if($roommate_query) {
			header("Location: viewProfile.php");
			exit;
		} else {
			echo "Data insert failed! Something went wrong.";
		}
	} else {
		// Add rating info to roommate ratings table in database after validation
		$roommate_sql = "INSERT INTO RoommateRatings (raterID, rateeID, date, rating, review) VALUES ('$userID', '$sanroommate',CURRENT_DATE(),'$sanroommaterating','$sanroommatereview')";
		$roommate_query = mysqli_query($conn, $roommate_sql);

        //Add notification into database
		$student_notif_sql2 = "INSERT INTO Notification (senderID, studentRecipientID, notifText) VALUES ('$stID','$sanroommate', 'You have a new rating!')";
		$student_notif_query2 = mysqli_query($conn, $student_notif_sql2);

		if($roommate_query) {
			header("Location: viewProfile.php");
			exit;
		} else {
			echo "Data insert failed! Something went wrong.";
		}
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

        <main class="rating_main">
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

            <!-- OWNER -->
			<div class="rating_div">

				<h2 class="center_h2"> Property Owner Rating </h2>

				<form action='rating.php' method='post'>
					<div class="select_user">
						<label class="rating" for="form_owner"> Property: </label>
						<div>
							<select name="form_owner" required>
								<option value="" disabled selected> Select a previous property (required) </option>
								<?php

								$owner_sql = "SELECT p.ownerID AS id, p.companyName AS name, r.str AS street, r.addType as addType, r.city AS city, r.state AS state, r.zip AS zip FROM PropertyOwner as p JOIN RoommateHistory as r on p.ownerID=r.ownerID WHERE (r.studentID='$userID') OR (r.roommateID='$userID')";
								$owner_result = mysqli_query($conn, $owner_sql);

								if (mysqli_num_rows($owner_result) > 0) {
									while($owner_row = mysqli_fetch_assoc($owner_result)) {
										if ($owner_row['addType'] == " ") {
											$owner_row['addType'] = "";
										}
										$owner_address = $owner_row['street'] . " " . $owner_row['addType'] . " " . $owner_row['city'] . " " . $owner_row['state'] . " " . $owner_row['zip'];
										echo '<option value="'.$owner_row['id'].'"> ' . $owner_row['name'] . " - " . $owner_address . '</option>';
									}
								} else {
									echo 'No previous properties';
								}
								?>
							</select>
						</div>
					</div>

					<div class="rate_user">
						<label class="rating" for="form_owner_rating"> Rating: </label>
						<input type='number' name='form_owner_rating' placeholder="1-5 (required)" min="1" max="5" maxlength="1"
						oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required>
					</div>

					<div class="review_user">
						<label class="rating" for="form_owner_review"> Review: </label>
						<textarea id="form_owner_review" name="form_owner_review" placeholder="Explain your rating (required)" class="textarea" required></textarea>
					</div>

					<div class="button">
						<button type="submit" name="ownersubmit"> Submit Rating </button>
					</div>
				</form>
			</div>

            <!-- ROOMMATE -->
            <div class="rating_div">

				<h2 class="center_h2"> Roommate Rating </h2>

				<form action='rating.php' method='post'>
					<div class="select_user">
						<label class="rating" for="form_roommate"> Roommate: </label>
						<div>
							<select name="form_roommate" required>
								<option value="" disabled selected> Select a previous roommate (required) </option>
								<?php

								$roommate_result = mysqli_query($conn, "SELECT rs.roommateID AS id, s.name AS name FROM RoommateHistory AS rs JOIN Student AS s ON s.studentID = rs.roommateID WHERE rs.studentID = '$userID' UNION SELECT rs.studentID AS id, s.name AS name FROM RoommateHistory AS rs JOIN Student AS s ON s.studentID = rs.studentID WHERE rs.roommateID = '$userID'");
								while($roommate_row = mysqli_fetch_assoc($roommate_result)) {
									unset($id, $name);
									$id = $roommate_row['id'];
									$name = $roommate_row['name'];
									echo '<option value="'.$id.'"> '.$name.'</option>';
								}
								?>
							</select>
						</div>
					</div>

					<div class="rate_user">
						<label class="rating" for="form_roommate_rating"> Rating: </label>
						<input type='number' name='form_roommate_rating' placeholder="1-5 (required)" min="1" max="5" maxlength="1"
						oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required> <br>
					</div>

					<div class="review_user">
						<label class="rating" for="form_roommate_review"> Review: </label>
						<textarea id="form_roommate_review" name="form_roommate_review" placeholder="Explain your rating (required)" class="textarea" required></textarea> <br>
					</div>

					<div class="button">
						<button type="submit" name="roommatesubmit"> Submit Rating </button>
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
