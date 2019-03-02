<?php
include_once 'classes/init.php';
$PAGE->addTitle("753");
$PAGE->addBodyClass("blank");
$PAGE->addHead('<link rel="icon" href="favicon.ico" type="image/x-icon"/>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>
    <link rel="stylesheet" href="main.css">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Steamworks Score Sheet">
    <meta name="author" content="Jon Kiddy, jon.kiddy@gmail.com, FRC Team 4930">
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link href="inc/bootstrap/bootstrap.css" rel="stylesheet">
    <link href="inc/sweetalert/sweetalert.css" rel="stylesheet">');
?>

<div class="content portrait">
    <div id="header" style="position:fixed">
        <label>&nbsp;Team:</label>
        <input type="text" id="team_number" name="num" autocomplete="off"
               onfocus="if (getState() == 'edit') {
                                       save();
                                       setState('blank');
                                   }">
        <label>&nbsp;Match #:</label>
        <select name="time" id="time" autocomplete="off"
                onfocus="if (getState() == 'edit') {
                        save(); setState('blank'); }">
                    <?php
                    $times = Sql::query("SELECT * FROM matches");
                    foreach ($times as $time) {
                        echo "<option value=";
                        echo $time['number'];
                        echo ">";
                        echo $time['number'];
                        echo ": ";
                        echo $time['time'];
                        echo "</option>";
                    }
                    ?>
        </select>
        <div class="save-indicator" style="display:none;"></div>
    </div>
    <div id="header-spacer"> </div>
    <div id="edit">
        <div class='form-inline'>
            <div class='input-group mb-2 mr-sm-2 mb-sm-0'>
                <div class='input-group-addon'>Side:</div>
                <input type="text" class='form-control counters' name="side" id="side">
                &nbsp;
                <button class="btn btn-outline-info multiplier-tag notransition"
                        onclick="$('#side').val('b');save();">b</button>
                &nbsp;
                <button class="btn btn-outline-danger multiplier-tag notransition"
                        onclick="$('#side').val('r');save();">r</button>
            </div>
        </div>
        <?php
        $currentSection = "";
        $actions = Sql::query("SELECT * FROM actions ORDER BY order_by ASC");
        foreach ($actions as $action) {
            if (!($currentSection == $action["section"])) {
                $currentSection = $action["section"];
                ?>
                <br>
                <h5 class="muted"><?= $action["section"] ?></h5>
                <?php
            }
            $name = str_replace("[]", "&#x25a0;", $action['name']);
            ?>
            <div class='form-inline'>
                <div class='input-group mb-2 mr-sm-2 mb-sm-0'>
                    <div class='input-group-addon'><?= $name ?></div>
                    <?php
                    if ($action["type"] == "INT") {
                        ?>
                        <input type="text" class='form-control counters' name=<?= $action['id'] ?> value=0>
                        &nbsp;
                        <button class="btn btn-outline-success up multiplier-tag notransition">+</button>
                        &nbsp;
                        <button class='btn btn-outline-danger down multiplier-tag notransition'>-</button>
                        <?php
                    } else if ($action["type"] == "BOOLEAN") {
                        ?>
                        <input type='checkbox' class='box checkbox' name=<?= $action['id'] ?>>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
        <br>
        <button id="send" class="btn btn-outline-primary">Done</button>
    </div>
    <div id="loading" style='background-color: #000;color: #0f0;font-family: "Courier";position: absolute;top: 0;bottom: 0; right: 0; left: 0; text-align: center;font-size: 2em;padding-top: 35%;'>Loading...</div>
    <div id="blank" style='background-color: #fff; margin-top: 2em; text-align: center;'>
        <button class="btn btn-outline-primary" onclick="setHash('/' + $('#team_number').val() + '/' + $('#time').val())">Begin</button>
        <br>
        <br>
        <button class="btn btn-outline-primary" onclick="location.href = 'rank.php'">Rankings</button>
        <br>
        <br>
        <button class="btn btn-outline-primary" onclick="location.href = 'alliance.php'">Alliance Builder</button>
    </div>
</div>
<script src="inc/jquery/jquery-3.3.1.js"></script>
<script src="inc/tether/tether.js"></script>
<script src="inc/bootstrap/bootstrap.js"></script>
<script src="inc/fastclick/fastclick.js"></script>
<script src="record.js?<?= filemtime('record.js') ?>"></script>