<?php

require '../googleLogin/dbh-inc.php';

// If user is not logged in, redirect
$userID = $_SESSION['login_id'];
$stuGogID = $_SESSION['studentGoogleID'];
$ownGogID = $_SESSION['ownerGoogleID'];

if (!isset($_SESSION['login_id'])) {
    header('Location: ../googleLogin/login.php');
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

$sql_userOwner = "SELECT companyName FROM PropertyOwner WHERE ((ownerID = '$userID') && (googleID = '$ownGogID'))";
$sql_userStudent = "SELECT name FROM Student WHERE ((studentID = '$userID') && (googleID = '$stuGogID'))";

$resultUserOwner = mysqli_query($conn, $sql_userOwner);
$resultUserStudent = mysqli_query($conn, $sql_userStudent);

if (mysqli_num_rows($resultUserOwner) > 0) {
    $row = mysqli_fetch_array($resultUserOwner);
    $name = $row[0];
    $_SESSION[$userName] = $name;

} elseif (mysqli_num_rows($resultUserStudent) > 0) {
    $row = mysqli_fetch_array($resultUserStudent);
    $name = $row[0];
    $_SESSION[$userName] = $name;
} else {
    echo 'Cannot find name';
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
        <link rel="stylesheet" type="text/css" href="../css/normalize.css">
        <!-- custom styles -->
        <link rel="stylesheet" type="text/css" href="../css/styles.css">

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
        <link rel="stylesheet" type="text/css" href="../js/jquery-ui-1.12.1/jquery-ui.min.css">
        <script type="text/javascript" src="../js/jquery-3.6.0.min.js"></script>
        <script type="text/javascript" src="../js/jquery-ui-1.12.1/jquery-ui.min.js"></script>
        
        <!-- Script -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <!-- jQuery UI -->
        <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
	</head>
<?php if ($propowner == TRUE): ?>
    <body>
        <header>
            <div class="search1">
                <form action="../../app/search_result.php" method="post" class="search-form">
                    <input type="text" id="autocomplete" name="autocomplete">
                    <label class="search-label" for="autocomplete"><i class="fa fa-search"></i></label>
                </form>
            </div>
            <!-- Navigation -->
            <div class="nav">
                <nav id="mySidenav" class="sidenav">
                    <a class="closebtn">&times;</a>
                    <div class="search2">
                        <form action="../../app/search_result.php" method="post" class="search-form">
                            <input type="text" id="autocomplete" name="autocomplete">
                            <label class="search-label" for="autocomplete"><i class="fa fa-search"></i></label>
                        </form>
                    </div>
                    <a href="../../app/home.php">Home</a>
                    <a href="instant_messaging.php">Chat Room</a>
                    <a href="../../app/aboutUs.php">About Us</a>
                    <a href="../../app/contact_us.php">Contact Us</a>
                    <a href="../../app/map.php"> Map </a>
                    <a href="../../app/viewProfile.php"> Profile </a>
                    <a href="../googleLogin/logout.php">Log Out</a>
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
<?php elseif ($student == TRUE): ?>
    <body>
        <header>
            <div class="search1">
                <form action="../../app/search_result.php" method="post" class="search-form">
                    <input type="text" id="autocomplete" name="autocomplete">
                    <label class="search-label" for="autocomplete"><i class="fa fa-search"></i></label>
                </form>
            </div>
            <!-- Navigation -->
            <div class="nav">
                <nav id="mySidenav" class="sidenav">
                    <a class="closebtn">&times;</a>
                    <div class="search2">
                        <form action="../../app/search_result.php" method="post" class="search-form">
                            <input type="text" id="autocomplete" name="autocomplete">
                            <label class="search-label" for="autocomplete"><i class="fa fa-search"></i></label>
                        </form>
                    </div>
                    <a href="../../app/home.php">Home</a>
                    <a href="../../app/aboutUs.php">About Us</a>
                    <a href="instant_messaging.php">Chat Room</a>
                    <a href="../../app/quiz.php">Roommate Compatibility Questionnaire</a>
                    <a href="../../app/matching.php">Matches</a>
                    <a href="../../app/roommateHistory.php">Roommate Request Form</a>
                    <a href="../../app/rating.php"> Ratings </a>
                    <a href="../../app/contact_us.php">Contact Us</a>
                    <a href="../../app/map.php"> Map </a>
                    <a href="../../app/viewProfile.php"> Profile </a>
                    <a href="../googleLogin/logout.php">Log Out</a>
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
<?php else: ?>
    <!-- Error of some sort -->
    <p> Profile not found </p>
<?php endif; ?>
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

                <div id="wrapper">
                    <div id="menu">
                        <p class="welcome">Welcome, <b><?php echo $name; ?></b></p>
                    </div>

                    <div id="chatbox">
                        <?php
                            if(file_exists("log.html") && filesize("log.html") > 0){
                                $contents = file_get_contents("log.html");
                                echo $contents;
                        }
                        ?>
                    </div>

                    <form name="message" class="message" action="#">
                        <textarea id="usermsg" name="usermsg" placeholder="Hello world!" class="textarea"></textarea>
                        <input name="submitmsg" type="submit" id="submitmsg" value="Send" />


                    </form>
                </div>
            </div>
        </main>

        <footer>
            <div class="container">
                <div class="footer1">
                    <div class="links">
                        <a href="../../app/aboutUs.php"> About Us <strong>|</strong></a> <a href="../../app/contact_us.php">Contact Us</a>
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
        <script src="../js/nav.js"></script>

        
        <!--<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>-->
        
        <script type="text/javascript">
            // jQuery Document
            $(document).ready(function () {
                $("#submitmsg").click(function () {
                    var clientmsg = $("#usermsg").val();
                    $.post("post.php", { text: clientmsg });
                    $("#usermsg").val("");
                    return false;
                });

                function loadLog() {
                    var oldscrollHeight = $("#chatbox")[0].scrollHeight - 20; //Scroll height before the request

                    $.ajax({
                        url: "log.html",
                        cache: false,
                        success: function (html) {
                            $("#chatbox").html(html); //Insert chat log into the #chatbox div

                            //Auto-scroll
                            var newscrollHeight = $("#chatbox")[0].scrollHeight - 20; //Scroll height after the request
                            if(newscrollHeight > oldscrollHeight){
                                $("#chatbox").animate({ scrollTop: newscrollHeight }, 'normal'); //Autoscroll to bottom of div
                            }
                        }
                    });
                }

                setInterval (loadLog, 2500);
            });
        </script>

        <!-- jquery 
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
        -->

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
                        url: "../../app/search.php",
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
