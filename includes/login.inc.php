<?php

if (!isset($_POST['submit'])) {
	header("Location: /");
	exit;
}

$success = false;
$error = "";

$username = $_POST['username'];
$password = $_POST['password'];

if (empty($username) || empty($password)) {
	// header("Location: /login.php?error=emptyfields");
	$error = "empty";
} else {
	require_once "dbh.inc.php";

	$query = "SELECT `username`, `password` FROM `user` WHERE `username`=?";
	$stmt = mysqli_prepare($link, $query);
	if (!$stmt) {
		$error = mysqli_error($link);
	} else {
		mysqli_stmt_bind_param($stmt, "s", $username);
		mysqli_stmt_execute($stmt);
		$result = mysqli_stmt_get_result($stmt);
		if (mysqli_num_rows($result) === 0) {
			mysqli_stmt_close($stmt);
			$error = "user";
		} else {
			$user = mysqli_fetch_assoc($result);
			mysqli_stmt_close($stmt);
			if (!password_verify($password, $user['password'])) {
				$error = "password";
			} else {
				session_start();
				$_SESSION['user'] = $username;
				// header("Location: /");
				$success = true;
			}
		}
	}
}

$return = [
	'success' => $success,
	'error' => $error,
];

$return = json_encode($return);
echo $return;
