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

// Get user score from student score table
$sql = "SELECT studentID FROM Student WHERE ((studentID = '$userID') && (googleID = '$stuGogID'))";
$sql_result = mysqli_query($conn, $sql);
if (mysqli_num_rows($sql_result)>0) {
    $row = mysqli_fetch_array($sql_result);
    $stID = $row[0];
    
    $sql_student_score = "SELECT score FROM StudentScore WHERE studentID = '$stID'";
    $result_student_score = mysqli_query($conn, $sql_student_score);

    if (mysqli_num_rows($result_student_score) > 0) {
        $row = mysqli_fetch_array($result_student_score);
        $score = $row[0];
    } else {
        $score = 'No score';
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

            <h2 class="center_h2"> Roommate Matches </h2>
            <?php echo '<h3 class="matching_h3"> Your score: ' . $score . '</h3>'; ?>

            <?php
                if ($score != 'No score') {
                    $lowerRange = ($score - 10);
                    $higherRange = ($score + 10);

                    // Add first match from student score table in database
                    $sql = "SELECT s.studentID, s.profileIMG, s.name, ss.score from Student as s JOIN StudentScore as ss on ss.studentID = s.studentID WHERE (s.studentID != '$stID') AND (ss.score between '$lowerRange' AND '$higherRange') ORDER BY ss.score DESC, s.name ASC LIMIT 1";
                    $result_sql = mysqli_query($conn, $sql);
                    echo '<div class="matching_container">';
                    echo "<div class='tab'>";

                    if (mysqli_num_rows($result_sql) > 0) {
                        $row = mysqli_fetch_array($result_sql);
                        echo '<button class="tablinks" onclick="openName(event, \'student'.$row[0].'\')" id="defaultOpen"><img src="'.$row[1].'">'."\t".$row[2]."\t".'Score: '.$row[3].'</button>';
                    } else {
                        echo 'Cannot access first result';
                    }

                    // Add remaining matches from student score table in database
                    $sql = "SELECT s.studentID, s.profileIMG, s.name, ss.score from Student as s JOIN StudentScore as ss on ss.studentID = s.studentID WHERE (s.studentID != '$stID') AND (ss.score between '$lowerRange' AND '$higherRange') ORDER BY ss.score DESC, s.name ASC LIMIT 1000000000000 OFFSET 1";
                    $result_sql = mysqli_query($conn, $sql);

                    if (mysqli_num_rows($result_sql) > 0) {
                        while($row = mysqli_fetch_array($result_sql)) {
                            echo '<button class="tablinks" onclick="openName(event, \'student'.$row[0].'\')" ><img src="'.$row[1].'">'."\t".$row[2]."\t".'Score: '.$row[3].'</button>';
                        }
                    } else {
                        echo 'Cannot access remaining results';
                    }
                    echo '</div>';

                    // add content on the right
                    $sql2 = "SELECT s.studentID, s.name, s.profileIMG, s.email, s.preferredName, s.bio, s.gender, ss.score FROM Student as s JOIN StudentScore as ss on ss.studentID = s.studentID WHERE (s.studentID != '$stID') AND (ss.score between '$lowerRange' AND '$higherRange') ORDER BY ss.score DESC, s.name ASC";
                    $result_sql2 = mysqli_query($conn, $sql2);

                            
                    if (mysqli_num_rows($result_sql2) > 0) {
                        while ($row = mysqli_fetch_array($result_sql2)) {
                            $studentid = $row[0];
                            $student_name = $row[1];
                            $student_profileIMG = $row[2];
                            $student_email = $row[3];
                            $student_preferredName = $row[4];
                            $student_bio = $row[5];
                            $student_gender = $row[6];
                            $matchScore = $row[7];

                            echo '<div id="student'.$studentid.'" class="tabcontent">';
                            echo '<!-- Student profile -->
                            <div class="profileIMG">
                                <img src="'.$student_profileIMG.'">
                            </div>
            
                            <h2 class="profile_name">'.$student_name.'</h2>
            
                            <div class="profile_bio">
                                <p class="bio_info">'.$student_bio.'</p>
                            </div>
            
                            <h3 class="viewprofile_h3"> Basic Info </h3>
                            <div class="basic_info_small">
                                <div class="basic_info">
            
                                    <p class="profile_label"> QUESTIONNAIRE SCORE </p>
                                    <p class="profile_info">'.$matchScore.'</p>
            
                                    <p class="profile_label"> PREFERRED NAME </p>
                                    <p class="profile_info">'.$student_preferredName.'</p>
            
                                    <p class="profile_label"> PREFERRED GENDER </p>
                                    <p class="profile_info">'.$student_gender.'</p>
            
                                    <p class="profile_label"> EMAIL </p>
                                    <p class="profile_info">'.$student_email.'</p>
                                </div>
                            </div>
            
                            <div class="basic_info_large">
                                <div class="basic_info">
                                    <table>
                                        <tr>
                                            <th class="basic_table"> QUESTIONNAIRE SCORE </th>
                                            <td>'.$matchScore.'</td>
                                        </tr>
                                        <tr>
                                            <th class="basic_table"> PREFERRED NAME </th>
                                            <td>'.$student_preferredName.'</td>
                                        </tr>
                                        <tr>
                                            <th class="basic_table"> PREFERRED GENDER </th>
                                            <td>'.$student_gender.'</td>
                                        </tr>
                                        <tr>
                                            <th class="basic_table"> EMAIL </th>
                                            <td>'.$student_email.'</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>';
                        }
                        echo '</div>';
                    } else {
                        echo '<p> Cannot find match profile info </p>';
                    }
                } else {
                    echo "<p>0 results</p>";
                }
            ?>
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

        <!-- Match script -->
        <script>
        function openName(evt, matchName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablinks");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(matchName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        // Get the element with id="defaultOpen" and click on it
        document.getElementById("defaultOpen").click();
        </script>

	</body>
</html>
