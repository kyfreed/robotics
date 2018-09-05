<?php
include_once 'classes/init.php';
?>
<link rel="stylesheet" href="table.css">
Team number:&nbsp;
<input type="text" name="num" id="num">
<br>
Match start time:&nbsp;
<input type="text" name="start" id="start">
<br>
<br>
<button type="submit" onclick="getTable()">Get stats</button>
<table id="table"></table>
<script src="inc/jquery/jquery-3.3.1.js"></script>
<script src="report.js"></script>