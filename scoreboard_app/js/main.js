//creating DOM variables
var elapsedTime = document.querySelector("#elapsed");
var homeTeamLogo = document.querySelector("#home_logo");
var homeTeamName = document.querySelector("#home_name");
var awayTeamLogo = document.querySelector("#away_logo");
var awayTeamName = document.querySelector("#away_name");
var lastMatchGoals = document.querySelector("#score");
var matchTable = document.querySelector("#match_table");

var matchTable = document.querySelector("#match_table");

//function for building match tiles and UI
function addMatchTile(){
    //create match tile div
    var matchFile = document.createElement('div');
    addMatchTile.classList.add("match-tile"); //adds class to div tag

    //box for home team
    var homeTeam = document.createElement('div');
    homeTeam.classList.add("team");

    //box for home team logo and name
    var homeTileLogo = document.createElement('img');
    var homeTileName = document.createElement('p');
    homeTileLogo.src = data['teams']['home']['logo'];
    homeTileName.innerHTML = data['teams']['home']['name'];

    //box for away team
    var awayTeam = document.createElement('div');
    awayTeam.classList.add("team");

    //box for away team logo and name
    var awayTileLogo = document.createElement('img');
    var awayTileName = document.createElement('p');
    awayTileLogo.src = data['teams']['away']['logo'];
    awayTileName.innerHTML = data['teams']['away']['name'];

    homeTeam.appendChild(homeTeamLogo);
    homeTeam.appendChild(homeTeamName);

    awayTeam.appendChild(awayTeamLogo);
    awayTeam.appendChild(awayTeamLogo);

    var score = document.createElement('p');
    score.innerHTML = data['goals']['home'] + "  :  " + data['goals']['away'];

    //append all elements to the match tiles
    MatchTile.appendChild(homeTeam);
    MatchTile.appendChild(score);
    MatchTile.appendChild(awayTeam);
}

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