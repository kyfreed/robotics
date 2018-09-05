<?php
$loggedOutOk = true;
include_once 'classes/init.php';
$title = "Scouting App 0.4.0";
if(!empty($_REQUEST['code'])){
    $USER->login($_REQUEST['code']);
    ?>
    <div style="color:#f00">
    Invalid code.
    </div>
    <?php
}
?>
Enter invite code:&nbsp;
<form action="login.php" method="post">
    <input type="text" id="code" name="code">
    <br>
    <br>
    <button type="submit">Log in</button>
</form>