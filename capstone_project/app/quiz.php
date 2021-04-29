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

// Get post data from Genex Form
if (isset($_POST['submit'])) {
	$sql = "SELECT name FROM GenEx";
	$result = mysqli_query($conn, $sql);
	if (mysqli_num_rows($result) > 0) {
		$total = 0;
		while ($row = mysqli_fetch_array($result)) {
			$value = $_POST[$row[0]];
			$total += $value;
		}

		// Add genex score info to student score table in database after validation
		$sql = "SELECT * FROM StudentScore WHERE studentID = '$userID'";
		$result_sql = mysqli_query($conn, $sql);

		// if student already has score
		if (mysqli_num_rows($result_sql) > 0) {
			$row = mysqli_fetch_array($result_sql);
			$completeScore = $row[1];
			$genex = $row[2];
			$pref = $row[3];
			$needs = $row[4];

			//update genex score
			$sql2 = "UPDATE StudentScore SET genex = '$total' WHERE studentID='$userID'";
			$query2 = mysqli_query($conn, $sql2);

			if ($query2) {
				//update complete score
				$completeScore = $total + $pref + $needs;
				$sql3 = "UPDATE StudentScore SET score = '$completeScore' WHERE studentID='$userID'";
				$query3 = mysqli_query($conn, $sql3);

				if ($query3) {
					header("Location: matching.php");
					exit;
				} else {
				echo "Data update failed! Something went wrong.";
				}
			}
		} else {
			// if student somehow doesn't already have a score
			$sql = "INSERT INTO StudentScore (studentID, score, genex) VALUES ('$userID', '$total', '$total')";
			$query = mysqli_query($conn, $sql);

			if ($query) {
				header("Location: matching.php");
				exit;
			} else {
				echo "Data insert failed! Something went wrong.";
			}
		}
	} else {
		echo 'No score';
	}
}

// Get post data from Pref Form
if (isset($_POST['submit2'])) {
	$sql = "SELECT name FROM Preferences";
	$result = mysqli_query($conn, $sql);
	if (mysqli_num_rows($result) > 0) {
		$total = 0;
		while ($row = mysqli_fetch_array($result)) {
			$value = $_POST[$row[0]];
			$total += $value;
		}

		// Add pref score info to student score table in database after validation
		$sql = "SELECT * FROM StudentScore WHERE studentID = '$userID'";
		$result_sql = mysqli_query($conn, $sql);

		// if student already has score
		if (mysqli_num_rows($result_sql) > 0) {
			$row = mysqli_fetch_array($result_sql);
			$completeScore = $row[1];
			$genex = $row[2];
			$pref = $row[3];
			$needs = $row[4];

			//update genex score
			$sql2 = "UPDATE StudentScore SET pref = '$total' WHERE studentID='$userID'";
			$query2 = mysqli_query($conn, $sql2);

			if ($query2) {
				//update complete score
				$completeScore = $genex + $total + $needs;
				$sql3 = "UPDATE StudentScore SET score = '$completeScore' WHERE studentID='$userID'";
				$query3 = mysqli_query($conn, $sql3);

				if ($query3) {
					header("Location: matching.php");
					exit;
				} else {
				echo "Data update failed! Something went wrong.";
				}
			}
		} else {
			// if student somehow doesn't already have a score
			$sql = "INSERT INTO StudentScore (studentID, score, pref) VALUES ('$userID', '$total', '$total')";
			$query = mysqli_query($conn, $sql);

			if ($query) {
				header("Location: matching.php");
				exit;
			} else {
				echo "Data insert failed! Something went wrong.";
			}
		}
	} else {
		echo 'No score';
	}
}

// Get post data from Needs Form
if (isset($_POST['submit3'])) {
	$sql = "SELECT name FROM Needs";
	$result = mysqli_query($conn, $sql);
	if (mysqli_num_rows($result) > 0) {
		$total = 0;
		while ($row = mysqli_fetch_array($result)) {
			$value = $_POST[$row[0]];
			$total += $value;
		}

		// Add needs score info to student score table in database after validation
		$sql = "SELECT * FROM StudentScore WHERE studentID = '$userID'";
		$result_sql = mysqli_query($conn, $sql);

		// if student already has score
		if (mysqli_num_rows($result_sql) > 0) {
			$row = mysqli_fetch_array($result_sql);
			$completeScore = $row[1];
			$genex = $row[2];
			$pref = $row[3];
			$needs = $row[4];

			//update genex score
			$sql2 = "UPDATE StudentScore SET needs = '$total' WHERE studentID='$userID'";
			$query2 = mysqli_query($conn, $sql2);

			if ($query2) {
				//update complete score
				$completeScore = $genex + $pref + $total;
				$sql3 = "UPDATE StudentScore SET score = '$completeScore' WHERE studentID='$userID'";
				$query3 = mysqli_query($conn, $sql3);

				if ($query3) {
					header("Location: matching.php");
					exit;
				} else {
				echo "Data update failed! Something went wrong.";
				}
			}
		} else {
			// if student somehow doesn't already have a score
			$sql = "INSERT INTO StudentScore (studentID, score, needs) VALUES ('$userID', '$total', '$total')";
			$query = mysqli_query($conn, $sql);

			if ($query) {
				header("Location: matching.php");
				exit;
			} else {
				echo "Data insert failed! Something went wrong.";
			}
		}
	} else {
		echo 'No score';
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

			<div class="layout">
				<!-- General Expectations -->
				<input name="quiz-navigation" type="radio" class="no-show" id="genex" checked="checked" />
				<div class="page genex-page">
					<div class="page-contents">
						<h2 class="quiz-form"> General Expectations </h2>
						<div id="form-wrapper">
							<form action="quiz.php" method="post">
								<?php
									$sql = "SELECT name, question, value1, value2, value3, value4, value5 FROM GenEx";
									$result = mysqli_query($conn, $sql);
									if (mysqli_num_rows($result) > 0) {
										while ($row = mysqli_fetch_array($result)) {
											echo '<h4 id="question">' . $row[1] . '</h4>';
											echo '<div class="section">';
											echo '<div id="slider">';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-1" value="1" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-1" data="' . $row[2] . '"></label>';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-2" value="2" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-2" data="' . $row[3] . '"></label>';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-3" value="3" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-3" data="' . $row[4] . '"></label>';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-4" value="4" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-4" data="' . $row[5] . '"></label>';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-5" value="5" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-5" data="' . $row[6] . '"></label>';
											echo '<div id="pos"></div>';
											echo '</div>';
											echo '</div>';
										}
									} else {
										echo "no questions found";
									}
								?>
								<div class="button">
									<button type="submit" name="submit">Save</button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<label class="quiznav" for="genex">
					<span class="quiz-span">
						<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
						General Expectations
					</span>
				</label>

				<!-- Preferences -->
				<input name="quiz-navigation" type="radio" class="no-show" id="pref" />
				<div class="page pref-page">
					<div class="page-contents">
						<h2 class="quiz-form"> Personal Preferences </h2>
						<div id="form-wrapper">
							<form action="quiz.php" method="post">
								<?php
									$sql = "SELECT name, question, value1, value2, value3, value4, value5 FROM Preferences";
									$result = mysqli_query($conn, $sql);
									if (mysqli_num_rows($result) > 0) {
										while ($row = mysqli_fetch_array($result)) {
											echo '<h4 id="question">' . $row[1] . '</h4>';
											echo '<div class="section">';
											echo '<div id="slider">';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-1" value="1" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-1" data="' . $row[2] . '"></label>';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-2" value="2" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-2" data="' . $row[3] . '"></label>';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-3" value="3" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-3" data="' . $row[4] . '"></label>';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-4" value="4" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-4" data="' . $row[5] . '"></label>';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-5" value="5" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-5" data="' . $row[6] . '"></label>';
											echo '<div id="pos"></div>';
											echo '</div>';
											echo '</div>';
										}
									} else {
										echo "no questions found";
									}
								?>
								<div class="button">
									<button type="submit" name="submit2">Save</button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<label class="quiznav" for="pref">
					<span class="quiz-span">
						<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12" y2="17"></line></svg>
						Preferences
					</span>
				</label>

				<!-- Needs -->
				<input name="quiz-navigation" type="radio" class="no-show" id="needs" />
				<div class="page needs-page">
					<div class="page-contents">
						<h2 class="quiz-form"> Non-Negotiable Needs </h2>
						<div id="form-wrapper">
							<form action="quiz.php" method="post">
								<?php
									$sql = "SELECT name, question, value1, value2, value3, value4, value5 FROM Needs";
									$result = mysqli_query($conn, $sql);
									if (mysqli_num_rows($result) > 0) {
										while ($row = mysqli_fetch_array($result)) {
											echo '<h4 id="question">' . $row[1] . '</h4>';
											echo '<div class="section">';
											echo '<div id="slider">';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-1" value="1" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-1" data="' . $row[2] . '"></label>';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-2" value="2" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-2" data="' . $row[3] . '"></label>';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-3" value="3" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-3" data="' . $row[4] . '"></label>';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-4" value="4" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-4" data="' . $row[5] . '"></label>';
											echo '<input type="radio" name="' . $row[0] . '" id="' . $row[0] . '-5" value="5" class="quiz" required>';
											echo '<label class="quiz" for="' . $row[0] . '-5" data="' . $row[6] . '"></label>';
											echo '<div id="pos"></div>';
											echo '</div>';
											echo '</div>';
										}
									} else {
										echo "no questions found";
									}
								?>
								<div class="button">
									<button type="submit" name="submit3">Save</button>
								</div>
							</form>
						</div>
					</div>
				</div>
				<label class="quiznav" for="needs">
					<span class="quiz-span">
						<svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
						Needs
					</span>
				</label>
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
