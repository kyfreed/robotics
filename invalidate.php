<?php
$loggedOutOk = true;
include 'classes/init.php';
if(empty(Sql::query("SELECT * FROM invalidated WHERE team_number = " . Sql::val($_POST['team_number']) . " AND match_number = " . Sql::val($_POST["match_number"]))) && Sql::val($_POST["invalidated"] == 1)){
    Sql::query("INSERT INTO invalidated (team_number, match_number) "
             . "VALUES (". Sql::val($_POST["team_number"]) . "," . Sql::val($_POST["match_number"]) . ")");
} else if (!(empty(Sql::query("SELECT * FROM invalidated WHERE team_number = " . Sql::val($_POST['team_number']) . " AND match_number = " . Sql::val($_POST["match_number"])))) && Sql::val($_POST["invalidated"]) == 0){
    Sql::query("DELETE FROM invalidated WHERE team_number = " . Sql::val($_POST['team_number']) . " AND match_number = " . Sql::val($_POST["match_number"]));
}

touch("_lastsavei.txt");