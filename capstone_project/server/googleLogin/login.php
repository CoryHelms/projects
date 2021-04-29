<?php
//Start session
session_start();

//Add database info
require 'dbh-inc.php';

//If user is already logged in, redirect to view Profile
if(isset($_SESSION['login_id'])){
    header('Location: ../../app/home.php');
    exit;
}

//Add google-api folder
require 'google-api/vendor/autoload.php';

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
            header('Location: ../owner-info.php');
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
                header('Location: ../student-info.php');
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
                        header('Location: ../student-info.php');
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
                        header('Location: ../owner-info.php');
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
        header('Location: login.php');
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

        <!-- resets browser defaults -->
        <link rel="stylesheet" type="text/css" href="../css/normalize.css">
        <!-- custom styles -->
        <link rel="stylesheet" type="text/css" href="../css/styles.css">

        <!-- Google Fonts -->
        <link rel="preconnect" href="https://fonts.gstatic.com">

        <link href="https://fonts.googleapis.com/css2?family=Montserrat&family=Mulish&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Ubuntu&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Mulish&display=swap" rel="stylesheet">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
	</head>
    <body>
        <header>
            <!-- Navigation -->
            <div class="nav">
                <nav id="mySidenav" class="sidenav">
                    <a class="closebtn">&times;</a>
                    <a href="login.php">Home</a>
                    <a href="../../app/aboutUs.php">About Us</a>
                    <a href="<?php echo $clientConnection->createAuthUrl(); ?>">Student Login</a>
                    <a href="<?php echo $clientConnection->createAuthUrl(); ?>">Property Owner Login</a>
                    <a href="../../app/map.php">Map</a>
                    <a href="../../app/contact_us.php">Contact Us</a>
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
                <h1><a href="login.php">Room@IU</a></h1>
            </div>
		</header>

        <main>
            <!-- Image and Text -->
            <div class="container">
                <div class="backgroundpurposes">
                    <div class="welcome1">
                        <h2> Welcome! </h2>
                    </div>

                    <div class="greeting1">
                        <p>
                            <strong>Room@IU</strong> will help you find your perfect roommate at Indiana University Bloomington!
                        </p>
                    </div>

                    <div class="image1">
                        <img src="../images/image1.jpg" alt="image1">
                    </div>

                    <div class="rightwelcome">
                        <div class="welcome2">
                            <h2>Welcome!</h2>
                        </div>

                        <div class="greeting2">
                            <p>
                                <strong>Room@IU</strong> will find you a compatible roommate at Indiana University Bloomington!
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <article class="highlights">
                    <h2 class="sr-only">Highlights</h2>
                    <!-- 3 sections tags with content for link to Student Login, Property Owner Login, and Map pages -->
                    <section class="student">
                    <a href="<?php echo $clientConnection->createAuthUrl(); ?>">
                            <h2>Student at Indiana University?</h2>
                            <p>Find your new Roommate with Room@IU!</p>
                            <div class="iu">
                                <a href="<?php echo $clientConnection->createAuthUrl(); ?>"><img alt="IU-Login-Button" src="../images/iulogin.png" max-width="100%"></a>
                            </div>
                        </a>
                    </section>
                    <section class="owner">
                        <a href="<?php echo $clientConnection->createAuthUrl(); ?>">
                            <h2>Bloomington Property Owner?</h2>
                            <p>List your property locations with Room@IU!</p>
                            <div class="google1">
                                <a href="<?php echo $clientConnection->createAuthUrl(); ?>"><img alt="Google-Login-Button" src="../images/button1.png" max-width="100%"></a>
                            </div>
                            <div class="google2">
                                <a href="<?php echo $clientConnection->createAuthUrl(); ?>"><img alt="Google-Login-Button" src="../images/button1.png" max-width="100%"></a>
                            </div>
                        </a>
                    </section>
                    <section class="map">
                        <a href="../../app/map.php">
                            <div class="mapsection1">
                                <div class="leftmap">
                                    <img alt="Map-Image" src="../images/mapIMG.png">
                                </div>
                                <div class="rightmap">
                                    <h2>Map</h2>
                                    <p>View a Map of Apartments and Rental Properties in the Area</p>
                                </div>
                            </div>
                            <div class="mapsection2">
                                <h2>Map</h2>
                                <p>View a Map of Apartments and Rental Properties in the Area</p>
                                <div class="middlemap">
                                    <img alt="Map-Image" src="../images/mapIMG.png">
                                </div>
                            </div>
                        </a>
                    </section>
                </article>

                <div class="image2">
                    <img src="../images/image1.jpg" alt="image1">
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
        <script src="../js/nav.js"></script>

    </body>
</html>

<?php endif; ?>
