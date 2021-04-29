<?php
// add database info
require 'googleLogin/dbh-inc.php';

// If user is not logged in, redirect
$userID = $_SESSION['login_id'];
$gogID = $_SESSION['googleID'];

if (!isset($_SESSION['login_id'])) {
	header('Location: googleLogin/login.php');
	exit;
}

//If quiz is already completed, redirect
$query1 = "SELECT IFNULL(score, 0) FROM StudentScore WHERE studentID = ?";
$stmt1 = $conn->prepare($query1);
$stmt1->bind_param("i", $userID);
$stmt1->execute();
$result1 = $stmt1->get_result();
$value1 = $result1->fetch_row()[0];
if ($value1 != 0) {
    header("Location: ../app/home.php");
    exit;
}

// Get post data from form
if (isset($_POST['submit'])) {
    $q1 = mysqli_real_escape_string($conn, $_POST['distancing']);
    $q2 = mysqli_real_escape_string($conn, $_POST['clean']);
    $q3 = mysqli_real_escape_string($conn, $_POST['hygiene']);
    $q4 = mysqli_real_escape_string($conn, $_POST['pda']);
    $q5 = mysqli_real_escape_string($conn, $_POST['sharing']);
    $q6 = mysqli_real_escape_string($conn, $_POST['acceptable']);
    $genexTotal = $q1 + $q2 + $q3 + $q4 + $q5 + $q6;

    $q7 = mysqli_real_escape_string($conn, $_POST['politics']);
    $q8 = mysqli_real_escape_string($conn, $_POST['animals']);
    $q9 = mysqli_real_escape_string($conn, $_POST['involvement']);
    $q10 = mysqli_real_escape_string($conn, $_POST['diet']);
    $q11 = mysqli_real_escape_string($conn, $_POST['entertainment']);
    $prefTotal = $q7 + $q8 + $q9 + $q10 + $q11;

    $q12 = mysqli_real_escape_string($conn, $_POST['allergy']);
    $q13 = mysqli_real_escape_string($conn, $_POST['recreational']);
    $q14 = mysqli_real_escape_string($conn, $_POST['budgeting']);
    $needsTotal = $q12 + $q13 + $q14;

    $total = $genexTotal + $prefTotal + $needsTotal;

    //insert data
    $sql = "SELECT * FROM StudentScore WHERE studentID = '$userID'";
	$result_sql = mysqli_query($conn, $sql);

	// if somehow student already has score
    if (mysqli_num_rows($result_sql) > 0) {

        //update genex score
        $sql2 = "UPDATE StudentScore SET genex = '$genexTotal' WHERE studentID='$userID'";
        $query2 = mysqli_query($conn, $sql2);

        if ($query2) {
            //update pref score
            $sql3 = "UPDATE StudentScore SET pref = '$prefTotal' WHERE studentID='$userID'";
            $query3 = mysqli_query($conn, $sql3);

            if ($query3) {
                //update needs score
                $sql4 = "UPDATE StudentScore SET needs = '$needsTotal' WHERE studentID='$userID'";
                $query4 = mysqli_query($conn, $sql4);

                if ($query4) {
                    //update total score
                    $sql5 = "UPDATE StudentScore SET score = '$total' WHERE studentID='$userID'";
                    $query5 = mysqli_query($conn, $sql5);

                    if ($query5) {
                        header("Location: ../app/matching.php");
                        exit;
                    } else {
                        echo "Data update failed! Something went wrong.";
                    }
                }
            }
        }
    } else {
        // if student doesn't already have a score
        $sql = "INSERT INTO StudentScore (studentID, score, genex, pref, needs) VALUES ('$userID', '$total', '$genexTotal', '$prefTotal', '$needsTotal')";
        $query = mysqli_query($conn, $sql);

        if ($query) {
            header("Location: ../app/matching.php");
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
        <link rel="stylesheet" type="text/css" href="css/normalize.css">
        <!-- custom styles -->
        <link rel="stylesheet" type="text/css" href="css/styles.css">

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
        <link rel="stylesheet" type="text/css" href="js/jquery-ui-1.12.1/jquery-ui.min.css">
        <script type="text/javascript" src="js/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.12.1/jquery-ui.min.js"></script>
        
        <!-- Script -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <!-- jQuery UI -->
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
	</head>

	<body>
		<header>
			<!-- Header Text -->
			<div class="header-text">
				<h1>Room@IU</h1>
			</div>
		</header>

		<main>
			<div>
				<!-- General Expectations -->
                <div>
                    <h2 class="quiz-form"> General Expectations </h2>
                    <div id="form-wrapper">
                        <form action="newquiz.php" method="post">
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
                                    echo "no genex questions found";
                                }

                                echo '<h2 class="quiz-form">Personal Preferences </h2>';
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
                                    echo "no pref questions found";
                                }

                                echo '<h2 class="quiz-form">Non-Negotiable Needs </h2>';
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
                                    echo "no needs questions found";
                                }
                            ?>

                            <div class="button">
                                <button type="submit" name="submit">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
			</div>
		</main>

		<footer>
            <div class="container">
                <div class="footer1">
                    <div class="links">
                        <a href="../app/aboutUs.php"> About Us <strong>|</strong></a> <a href="../app/contact_us.php">Contact Us</a>
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
        <script src="js/nav.js"></script>

		<!-- jquery for notifications -->
        <script>
            $(document).ready(function () {

                // animation for notification counter
                $('#notif_counter')
                    .text(
						<?php
                            $notif_count = "SELECT COUNT(notifText)
                            FROM Notification
                            WHERE notifStatus = '0' AND recipientID = '$userID'";

                            $count_result = mysqli_query($conn, $notif_count);
                                if (mysqli_num_rows($count_result) > 0) {
                                    while($row = mysqli_fetch_array($count_result)) {
                                        echo ($row[0]);
                                    }
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
                        $notifStatus_update = "UPDATE Notification
                        SET notifStatus = '1'
                        WHERE recipientID = '$userID' AND notifStatus = '0'";

                        $status_query = mysqli_query($conn, $notifStatus_update);
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
