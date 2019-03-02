<?php
$loggedOutOk = true;
include 'classes/init.php';

if(empty(Sql::query("SELECT * FROM taken WHERE team_number = " . Sql::val($_POST['team_number'])))){
    Sql::query("INSERT INTO taken (team_number, taken) "
             . "VALUES (". Sql::val($_POST["team_number"]) . "," . Sql::val($_POST['taken']) . ")");
} else {
    Sql::query("UPDATE taken SET taken = " . Sql::val($_POST['taken']) . " WHERE team_number = " . Sql::val($_POST['team_number']));
}

touch("_lastsave.txt");