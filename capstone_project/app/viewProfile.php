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

// If they are in Property Owner table, get profile info
if(mysqli_num_rows($checkUserOwner) > 0){
    $propowner = TRUE;
    // Get user info from property owner table
    $sql_owner_profile = "SELECT name, profileIMG, email, str, addType, city, state, zip, companyName, websiteAddress, bio FROM PropertyOwner WHERE ownerID = '$userID'";
    $result_owner_profile = mysqli_query($conn, $sql_owner_profile);
    if (mysqli_num_rows($result_owner_profile) > 0) {
        $row = mysqli_fetch_array($result_owner_profile);
        $owner_name = $row[0];
        $owner_profileIMG = $row[1];
        $owner_email = $row[2];
        $owner_str = $row[3];
        $owner_addType = $row[4];
        $owner_city = $row[5];
        $owner_state = $row[6];
        $owner_zip = $row[7];
        $owner_companyName = $row[8];
        $owner_websiteAddress = $row[9];
        $owner_bio = $row[10];
    }
}

// If they are in Student table, get profile info
elseif (mysqli_num_rows($checkUserStudent) > 0) {
    $student = TRUE;

    // Get user profile info from student table
    $sql_student_profile = "SELECT name, profileIMG, email, preferredName, str, addType, city, state, zip, bio, gender FROM Student WHERE studentID = '$userID'";
    $result_student_profile = mysqli_query($conn, $sql_student_profile);
    if ($result_student_profile->num_rows > 0) {
        $row = mysqli_fetch_array($result_student_profile);
        $student_name = $row[0];
        $student_profileIMG = $row[1];
        $student_email = $row[2];
        $student_preferredName = $row[3];
        $student_str = $row[4];
        $student_addType = $row[5];
        $student_city = $row[6];
        $student_state = $row[7];
        $student_zip = $row[8];
        $student_bio = $row[9];
        $student_gender = $row[10];
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
} else {
    echo '<p> Something went wrong! No profile info found </p>';
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

<?php if ($propowner == TRUE): ?>
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
                    <a href="../server/instant_messaging/instant_messaging.php">Chat Room</a>
                    <a href="aboutUs.php">About Us</a>
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
            <!-- Property Owner profile -->
                <div class="profileIMG">
                    <img src="<?php echo $owner_profileIMG; ?>">
                </div>

                <div class="profile_name">
                    <h2><?php echo $owner_name; ?></h2>
                </div>

                <div class="profile_bio">
                    <p class="bio_info"><?php echo $owner_bio; ?></p>
                    <!-- &#9; &nbsp; &emsp; -->
                </div>

                <h3 class="viewprofile_h3"> Basic Info </h3>
                <div class="basic_info_small">
                    <div class="basic_info">

                        <p class="profile_label"> EMAIL </p>
                        <p class="profile_info"><?php echo $owner_email; ?></p>

                        <p class="profile_label"> COMPANY NAME </p>
                        <p class="profile_info"><?php echo $owner_companyName; ?></p>

                        <p class="profile_label"> WEBSITE ADDRESS </p>
                        <p class="profile_info"><?php echo $owner_websiteAddress; ?></p>
                    </div>
                </div>

                <div class="basic_info_large">
                    <div class="basic_info">
                        <table>
                            <tr>
                                <th class="basic_table"> EMAIL </th>
                                <td> <?php echo $owner_email; ?> </td>
                            </tr>
                            <tr>
                                <th class="basic_table"> COMPANY NAME </th>
                                <td> <?php echo $owner_companyName; ?> </td>
                            </tr>
                            <tr>
                                <th class="basic_table"> WEBSITE ADDRESS </th>
                                <td> <?php echo $owner_websiteAddress; ?> </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h3 class="viewprofile_h3"> Address </h3>
                <div class="address_small">
                    <div class="address">

                        <p class="profile_label"> STREET </p>
                        <p class="profile_info"><?php echo $owner_str; ?></p>

                        <p class="profile_label"> APT / STE / OTHER </p>
                        <p class="profile_info"><?php echo $owner_addType; ?></p>

                        <p class="profile_label"> CITY </p>
                        <p class="profile_info"><?php echo $owner_city; ?></p>

                        <p class="profile_label"> STATE </p>
                        <p class="profile_info"><?php echo $owner_state; ?></p>

                        <p class="profile_label"> ZIP CODE </p>
                        <p class="profile_info"><?php echo $owner_zip; ?></p>
                    </div>
                </div>

                <div class="address_large">
                    <div class="address">
                        <table>
                            <tr>
                                <th class="address_table"> STREET </th>
                                <td> <?php echo $owner_str; ?> </td>
                            </tr>
                            <tr>
                                <th class="address_table"> APT / STE / OTHER </th>
                                <td> <?php echo $owner_addType; ?> </td>
                            </tr>
                            <tr>
                                <th class="address_table"> CITY </th>
                                <td> <?php echo $owner_city; ?> </td>
                            </tr>
                            <tr>
                                <th class="address_table"> STATE </th>
                                <td> <?php echo $owner_state; ?> </td>
                            </tr>
                            <tr>
                                <th class="address_table"> ZIP </th>
                                <td> <?php echo $owner_zip; ?> </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="reviews">
                    <?php
                        // Property Owner reviews
                        echo "<h3 class='viewprofile_h3'> Your Ratings </h3>";
                        echo "<h4> Ratings From Previous Tenants </h4>";

                        // get owner id
                        $sql_owner_id = "SELECT ownerID FROM PropertyOwner WHERE ownerID = '$userID'";
                        $result_owner_id = mysqli_query($conn, $sql_owner_id);
                        if (mysqli_num_rows($result_owner_id) > 0) {
                            $row = mysqli_fetch_array($result_owner_id);
                            $owner_prop_id = $row[0];

                            // get student rater id
                            $sql_student_id = "SELECT raterID FROM OwnerRatings WHERE rateeID = '$userID'";
                            $result_student_id = mysqli_query($conn, $sql_student_id);
                            if (mysqli_num_rows($result_student_id) > 0) {
                                $row = mysqli_fetch_array($result_student_id);
                                $student_propRater_id = $row[0];

                                // get rating data
                                $sql_ratings = "SELECT raterID, date, rating, review FROM OwnerRatings WHERE rateeID = '$owner_prop_id' ORDER BY rating DESC";
                                $result_ratings = mysqli_query($conn, $sql_ratings);
                                if (mysqli_num_rows($result_ratings) > 0) {

                                    //table
                                    echo "<table class='ratings'>
                                    <tr>
                                    <th>Rater's Name</th>
                                    <th>Date</th>
                                    <th>Rating</th>
                                    <th>Review</th>
                                    </tr>";

                                    while($row = mysqli_fetch_array($result_ratings)) {
                                        $sql_student_propRaterName = "SELECT name FROM Student WHERE studentID='$row[0]'";
                                        $result_student_propRaterName = mysqli_query($conn, $sql_student_propRaterName);
                                        $student_propName_row = mysqli_fetch_array($result_student_propRaterName);
                                        $student_propName = $student_propName_row[0];
                                        echo "<tr>";
                                        echo "<td>" . $student_propName . "</td>";
                                        echo "<td>" . $row[1] . "</td>";
                                        echo "<td>" . $row[2] . "</td>";
                                        echo "<td>" . $row[3] . "</td>";
                                        echo "</tr>";
                                    }

                                    echo "</table>";
                                } else {
                                    echo '<p class="no_ratings"> No ratings yet! </p>';
                                }
                            } else {
                                echo '<p class="no_ratings"> No ratings yet! </p>';
                            }
                        } else {
                            echo '<p class="no_ratings"> Cannot find owner ID! </p>';
                        }
                    ?>
                    <div class="profile_report">
                        <p class="profile_report">
                            See a problem?
                            <a href="contact_us.php" class="profile_report">Let us know</a>
                        </p>
                    </div>
                </div>

                <!-- Edit profile -->
                <div class="edit_button">
                    <button onclick="location.href='editProfile.php'" type="button"> Edit </button>
				</div>
            </div>
        </main>

<?php elseif ($student == TRUE): ?>
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
            <!-- Student profile -->
                <div class="profileIMG">
                    <img src="<?php echo $student_profileIMG; ?>">
                </div>

                <h2 class="profile_name"><?php echo $student_name; ?></h2>

                <div class="profile_bio">
                    <p class="bio_info"><?php echo $student_bio; ?></p>
                    <!-- &#9; &nbsp; &emsp; -->
                </div>

                <h3 class="viewprofile_h3"> Basic Info </h3>
                <div class="basic_info_small">
                    <div class="basic_info">

                        <p class="profile_label"> QUESTIONNAIRE SCORE </p>
                        <p class="profile_info"><?php echo $score; ?></p>

                        <p class="profile_label"> PREFERRED NAME </p>
                        <p class="profile_info"><?php echo $student_preferredName; ?></p>

                        <p class="profile_label"> PREFERRED GENDER </p>
                        <p class="profile_info"><?php echo $student_gender; ?></p>

                        <p class="profile_label"> EMAIL </p>
                        <p class="profile_info"><?php echo $student_email; ?></p>
                    </div>
                </div>

                <div class="basic_info_large">
                    <div class="basic_info">
                        <table>
                            <tr>
                                <th class="basic_table"> QUESTIONNAIRE SCORE </th>
                                <td> <?php echo $score; ?> </td>
                            </tr>
                            <tr>
                                <th class="basic_table"> PREFERRED NAME </th>
                                <td> <?php echo $student_preferredName; ?> </td>
                            </tr>
                            <tr>
                                <th class="basic_table"> PREFERRED GENDER </th>
                                <td> <?php echo $student_gender; ?> </td>
                            </tr>
                            <tr>
                                <th class="basic_table"> EMAIL </th>
                                <td> <?php echo $student_email; ?> </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <h3 class="viewprofile_h3"> Address </h3>
                <div class="address_small">
                    <div class="address">

                        <p class="profile_label"> STREET </p>
                        <p class="profile_info"><?php echo $student_str; ?></p>

                        <p class="profile_label"> APT / STE / OTHER </p>
                        <p class="profile_info"><?php echo $student_addType; ?></p>

                        <p class="profile_label"> CITY </p>
                        <p class="profile_info"><?php echo $student_city; ?></p>

                        <p class="profile_label"> STATE </p>
                        <p class="profile_info"><?php echo $student_state; ?></p>

                        <p class="profile_label"> ZIP CODE </p>
                        <p class="profile_info"><?php echo $student_zip; ?></p>
                    </div>
                </div>

                <div class="address_large">
                    <div class="address">
                        <table>
                            <tr>
                                <th class="address_table"> STREET </th>
                                <td> <?php echo $student_str; ?> </td>
                            </tr>
                            <tr>
                                <th class="address_table"> APT / STE / OTHER </th>
                                <td> <?php echo $student_addType; ?> </td>
                            </tr>
                            <tr>
                                <th class="address_table"> CITY </th>
                                <td> <?php echo $student_city; ?> </td>
                            </tr>
                            <tr>
                                <th class="address_table"> STATE </th>
                                <td> <?php echo $student_state; ?> </td>
                            </tr>
                            <tr>
                                <th class="address_table"> ZIP </th>
                                <td> <?php echo $student_zip; ?> </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class = "reviews">
                    <?php
                        // Roommate Reviews
                        echo "<h3 class='viewprofile_h3'> Ratings </h3>";

                        // get student id
                        $sql_studentRatings_id = "SELECT studentID FROM Student WHERE studentID = '$userID'";
                        $result_studentRatings_id = mysqli_query($conn, $sql_studentRatings_id);
                        $row = mysqli_fetch_array($result_studentRatings_id);
                        if (mysqli_num_rows($result_studentRatings_id) > 0) {
                            $studentRatings_id = $row[0];

                            // get ratings of previous roommates
                            $sql_roommateRatings = "SELECT rateeID, date, rating, review FROM RoommateRatings WHERE raterID = '$studentRatings_id' ORDER BY rating DESC";
                            $result_roommateRatings = mysqli_query($conn, $sql_roommateRatings);

                            echo "<h4> Ratings of Previous Roommates </h4>";
                            echo "<div class='ratings_of_others'>";

                            if (mysqli_num_rows($result_roommateRatings) > 0) {
                                //table
                                echo "<table class='ratings'>
                                <tr>
                                <th>Ratee's Name</th>
                                <th>Date</th>
                                <th>Rating</th>
                                <th>Review</th>
                                </tr>";

                                while($row = mysqli_fetch_array($result_roommateRatings)) {
                                    $sql_studentIsRater_rateeName = "SELECT name FROM Student WHERE studentID='$row[0]'";
                                    $result_studentIsRater_rateeName = mysqli_query($conn, $sql_studentIsRater_rateeName);
                                    $row1 = mysqli_fetch_array($result_studentIsRater_rateeName);
                                    $studentIsRater_rateeName = $row1[0];
                                    echo "<tr>";
                                    echo "<td>" . $studentIsRater_rateeName . "</td>";
                                    echo "<td>" . $row[1] . "</td>";
                                    echo "<td>" . $row[2] . "</td>";
                                    echo "<td>" . $row[3] . "</td>";
                                    echo "</tr>";
                                }

                                echo "</table>";
                            } else {
                                echo '<p class="no_ratings"> No ratings yet! </p>';
                            }
                            echo "</div>";

                            // get others' ratings of student
                            $sql_roommateRatings = "SELECT raterID, date, rating, review FROM RoommateRatings WHERE rateeID = '$studentRatings_id' ORDER BY rating DESC";
                            $result_roommateRatings = mysqli_query($conn, $sql_roommateRatings);

                            echo "<h4> Ratings of You </h4>";
                            echo "<div class='ratings_from_others'>";

                            if (mysqli_num_rows($result_roommateRatings) > 0) {
                                //table
                                echo "<table class='ratings'>
                                <tr>
                                <th>Rater's Name</th>
                                <th>Date</th>
                                <th>Rating</th>
                                <th>Review</th>
                                </tr>";

                                while($row = mysqli_fetch_array($result_roommateRatings)) {
                                    $sql_studentIsRatee_raterName = "SELECT name FROM Student WHERE studentID='$row[0]'";
                                    $result_studentIsRatee_raterName = mysqli_query($conn, $sql_studentIsRatee_raterName);
                                    $row1 = mysqli_fetch_array($result_studentIsRatee_raterName);
                                    $studentIsRatee_raterName = $row1[0];
                                    echo "<tr>";
                                    echo "<td>" . $studentIsRatee_raterName . "</td>";
                                    echo "<td>" . $row[1] . "</td>";
                                    echo "<td>" . $row[2] . "</td>";
                                    echo "<td>" . $row[3] . "</td>";
                                    echo "</tr>";
                                }

                                echo "</table>";
                            } else {
                                echo '<p class="no_ratings"> No ratings yet! </p>';
                            }
                            echo "</div>";

                            // get ratings of previously rented properties
                            $sql_propertyRatings = "SELECT rateeID, date, rating, review FROM OwnerRatings WHERE raterID = '$studentRatings_id' ORDER BY rating DESC";
                            $result_propertyRatings = mysqli_query($conn, $sql_propertyRatings);

                            echo "<h4> Ratings of Previously Rented Properties </h4>";
                            echo "<div class='ratings_of_property'>";

                            if (mysqli_num_rows($result_propertyRatings) > 0) {
                                //table
                                echo "<table class='ratings'>
                                <tr>
                                <th>Address</th>
                                <th>Company Name</th>
                                <th>Date</th>
                                <th>Rating</th>
                                <th>Review</th>
                                </tr>";

                                while($row = mysqli_fetch_array($result_propertyRatings)) {
                                    // address
                                    $sql_propertyAddress = "SELECT str, addType, city, state, zip FROM RoommateHistory WHERE (ownerID = '$row[0]') AND ((studentID='$userID') OR (roommateID='$userID'))";
                                    $result_propertyAddress = mysqli_query($conn, $sql_propertyAddress);
                                    $row1 = mysqli_fetch_array($result_propertyAddress);
                                    $address = $row1[0] . " " . $row1[1] . " " . $row1[2] . " " . $row1[3] . " " . $row1[4];
                                    echo "<tr>";
                                    echo "<td>" . $address . "</td>";

                                    // company name
                                    $sql_companyName = "SELECT companyName FROM PropertyOwner WHERE ownerID = '$row[0]'";
                                    $result_companyName = mysqli_query($conn, $sql_companyName);
                                    $row2 = mysqli_fetch_array($result_companyName);
                                    $companyName = $row2[0];
                                    echo "<td>" . $companyName . "</td>";
                                    echo "<td>" . $row[1] . "</td>";
                                    echo "<td>" . $row[2] . "</td>";
                                    echo "<td>" . $row[3] . "</td>";
                                    echo "</tr>";
                                }

                                echo "</table>";
                            } else {
                                echo '<p class="no_ratings"> No ratings yet! </p>';
                            }
                            echo "</div>";
                        } else {
                            echo '<p class="no_ratings"> Cannot find your student ID for ratings </p>';
                        }
                    ?>
                    <div class="profile_report">
                        <p class="profile_report">
                            See a problem?
                            <a href="contact_us.php" class="profile_report">Let us know</a>
                        </p>
                    </div>
                </div>

                <!-- Edit profile -->
                <div class="edit_button">
                    <button onclick="location.href='editProfile.php'" type="button"> Edit </button>
				</div>
            </div>
        </main>

<?php else: ?>
    <!-- Error of some sort -->
    <p> Profile not found </p>
<?php endif; ?>

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
