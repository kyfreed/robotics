<?php

include_once 'classes/init.php';
include_once 'classes/Sql.php';
$PAGE->setTemplate("json");
$info = json_decode($_POST["info"], true);
foreach ($info as $name=>$value) {
    if ($name == "num") {
        $team = $value;
    } else if ($name == "time") {
        $time = $value;
    } else if ($name == "side") {
        $side = $value;
    }
}
foreach ($info as $name=>$value) {
    if (is_numeric($name)) {
        $max = Sql::queryValue("SELECT max(modified_on) "
                               . "FROM match_team_actions "
                               . "WHERE action_id=" . Sql::val($name)
                               . " AND team_number=" . Sql::val($team)
                               . " AND match_number=" . Sql::val($time));
        if ($max) {
            Sql::query("UPDATE match_team_actions "
                     . "SET earned=" . Sql::val($value) 
                     . ",side=" . Sql::val($side) 
                     . " WHERE action_id=" . Sql::val($name) 
                     . " AND team_number=" . Sql::val($team) 
                     . " AND match_number=" . Sql::val($time));
        } else {
            Sql::query("INSERT INTO match_team_actions (team_number,match_number,earned,action_id,side) "
                     . "VALUES (" . Sql::val($team) . "," . Sql::val($time) . "," . Sql::val($value) . "," . Sql::val($name) . "," . Sql::val($side) . ")");
        }
    }
}

touch("_lastsave.txt");