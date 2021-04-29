<?php
//Start session
session_start();

//Add database info
require "googleLogin/dbh-inc.php";

//If user is not logged in, redirect
$userID = $_SESSION["login_id"];
if (!isset($_SESSION["login_id"])) {
    header("Location: googleLogin/login.php");
    exit;
}

$getgogID = "SELECT googleID FROM PropertyOwner WHERE ownerID = '$userID'";
$gogID = mysqli_query($conn, $getgogID);
if (mysqli_num_rows($gogID) > 0) {
    $row = mysqli_fetch_array($gogID);
    $gogID = $row[0];
    $_SESSION['ownerGoogleID'] = $gogID;
}

//If form is already completed, redirect
$query1 = "SELECT IFNULL(zip, 0) FROM PropertyOwner WHERE ownerID = ?";
$stmt1 = $conn->prepare($query1);
$stmt1->bind_param("i", $userID);
$stmt1->execute();
$result1 = $stmt1->get_result();
$value1 = $result1->fetch_row()[0];
if ($value1 != 0) {
    header("Location: ../app/home.php");
    exit;
}

//Save form data once saved
if(isset($_POST["Save"])) {
    //Get POST data from form
    $userCompany = mysqli_real_escape_string($conn, $_POST["company"]);
    $userStreet = mysqli_real_escape_string($conn, $_POST["street"]);
    $userAddType = mysqli_real_escape_string($conn, $_POST["addType"]);
    $userCity = mysqli_real_escape_string($conn, $_POST["city"]);
    $userState = mysqli_real_escape_string($conn, $_POST["state"]);
    $userZip = mysqli_real_escape_string($conn, $_POST["zip"]);
    $userBio = mysqli_real_escape_string($conn, $_POST["bio"]);
    $userWebAddress = mysqli_real_escape_string($conn, $_POST["webAddress"]);

    //Make sure all inputs are filled in
    function emptyInputSignup($userCompany, $userStreet, $userCity, $userState, $userZip, $userBio, $userWebAddress) {
        $answer;
        if (empty($userCompany) || empty($userStreet) || empty($userCity) || empty($userState) || empty($userZip) || empty($userBio) || empty($userWebAddress)) {
            $answer = true;
        } else {
            $answer = false;
        }
        return $answer;
        }

        //If any inputs are empty, redirect
        if (emptyInputSignup($userCompany, $userStreet, $userCity, $userState, $userZip, $userBio, $userWebAddress) !== false) {
        header("location: owner-info.php?error=emptyinput");
        exit();
        }

    //Add user info to PropertyOwner table in database after validation
    if (empty($userAddType) == FALSE) {
        $sql = "UPDATE PropertyOwner SET addType = '$userAddType' WHERE ownerID = '$userID'";
        $query = mysqli_query($conn, $sql);
    }
    $sql = "UPDATE PropertyOwner SET companyName = '$userCompany', str = '$userStreet', city = '$userCity', state = '$userState', zip = '$userZip', bio = '$userBio', websiteAddress = '$userWebAddress' WHERE ownerID = '$userID'";

    $query = mysqli_query($conn, $sql);

    if($query){
        header("Location: ../app/home.php");
        exit;
    }
    else{
        echo "Data insert failed! Something went wrong.";
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
        
        <!-- resets browser defaults -->
        <link rel="stylesheet" type="text/css" href="css/normalize.css">
        <!-- custom styles -->
        <link rel="stylesheet" type="text/css" href="css/styles.css">

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
            <!-- Header Text --> 
            <div class="header-text"> 
                <h1>Room@IU</h1>
            </div>
		</header>
        <main>
            <div class="additional_container">
                <h2 class="additional_h2"> Additional Property Owner Information</h2><br>

                <form action="owner-info.php" method="post">
                    <label for="company">Company Name:</label><br>
                    <input type="text" id="add_company" name="company" size="40" placeholder="Cool Company (required)" required><br>
                    
                    <label for="street">Street Address:</label><br>
                    <input type="text" id="street" name="street" size="40" placeholder="123 Cool Company Blvd (required)" required><br>
                    
                    <label for="addType">APT / STE / Other:</label><br>
                    <input type="text" id="addType" name="addType" size="40" placeholder="STE 0 (optional)"><br>
                    
                    <label for="city">City:</label><br>
                    <input type="text" id="city" name="city" size="40" placeholder="Cool Company City (required)" required><br>
                    
                    <label for="state">State:</label><br>
                    <select id="state" name="state" required>
                        <option value="" disabled selected>Select your state (required)</option>
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
                    
                    <label for="zip">Zip:</label><br>
                    <input type="text" id="zip" name="zip" pattern="[0-9]*" maxlength="5" placeholder="00000 (required)"
                    oninput="javascript: if (numberInput.validity.valueMissing && !numberInput.validity.badInput) {errorMessage.textContent = 'Field must consist of exactly 5 numbers';}"
                    oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" required><br>
                    
                    <label for="bio">Property Bio:</label><br>
                    <textarea class="textarea" id="bio" name="bio" placeholder="Tell us about your company! (required)" required></textarea> <br>
                    
                    <label for="webAddress">Website Address:</label><br>
                    <input type="text" id="add_webAddress" name="webAddress" placeholder="https://www.coolcompany.com (required)" required><br>
                    
                    <?php
                    //Display error
                    if (isset($_GET["error"])) {
                        if ($_GET["error"] == "emptyinput") {
                            echo "<p>Please fill in all fields (required)</p>";
                            }
                        }
                    ?>
                    
                    <div class="button">
                        <button type="submit" name="Save">Save</button>
                    </div>
                </form>
                
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
        <script src="js/nav.js"></script>
    </body>
</html>