<?php
$loggedOutOk = true;
include 'classes/init.php';

if(empty(Sql::query("SELECT * FROM taken WHERE team_number = " . Sql::val($_POST['team_number'])))){
    Sql::query("INSERT INTO taken (team_number, taken) "
             . "VALUES (". Sql::val($_POST["team_number"]) . "," . Sql::val($_POST['taken']) . ")");
} else {
    Sql::query("UPDATE taken SET team_number = " . Sql::val($_POST["team_number"]) . ", taken = " . Sql::val($_POST['taken']));
}

touch("_lastsave.txt");