<?php

// mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);

$mysqli_host = 'localhost';
$mysqli_user = 'root';
$mysqli_password = '';
$mysqli_database = 'ktjquiz';

$link = mysqli_connect($mysqli_host, $mysqli_user, $mysqli_password, $mysqli_database);

if (!$link)
	die("MySQL connection error:" . mysqli_connect_error() . " (" . mysqli_connect_errno() . ")");
