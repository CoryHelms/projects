//creating DOM variables
var elapsedTime = document.querySelector("#elapsed");
var homeTeamLogo = document.querySelector("#home_logo");
var homeTeamName = document.querySelector("#home_name");
var awayTeamLogo = document.querySelector("#away_logo");
var awayTeamName = document.querySelector("#away_name");
var lastMatchGoals = document.querySelector("#score");
var matchTable = document.querySelector("#match_table");

//fetching function
function getData(){
    //connect to API
    fetch("https://v3.football.api-sports.io/fixtures?live=all", {
        "method" : "GET",
        "headers" : {
            "x-rapidapi-host" : "v3.football.api-sports.io",
            "x-rapidapi-key" : "" //API KEY (GET FROM SUBSCRIBING)
        }
    })
    .then(response => response.json().then(data =>{
        //get the data
        var matchesList = data['response'];

        //fetching info for scores
        var fixture = matchesList[0]['fixture']; //first fixture only
        var goals = matchesList[0]['goals']; //fetch goals for first fixture
        var teams = matchesList[0]['teams']; //fetch team data for first fixture

        //put data on the page
        elapsedTime.innerHTML = fixture['status']['elapsed'] + "'";
        homeTeamLogo.src = teams['home']['logo'];
        homeTeamName.innerHTML = teams['home']['name'];
        awayTeamLogo.src = teams['away']['logo'];
        awayTeamName.innerHTML = teams['away']['name'];
        lastMatchGoals.innerHTML = goals['home'] + "  :  " + goals['away'];

    }))
    .catch(err =>{
        console.log(err);
    })
}

//function call
getData();