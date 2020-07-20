<?php

if (!isset($_POST['submit'])) {
	header("Location: /");
	exit;
}

$fullname = $_POST['fullname'];
$username = $_POST['username'];
$password = $_POST['password'];
$password2 = $_POST['password-confirm'];

$success = false;
$error = "";

if (empty($username) || empty($password) || empty($fullname)) {
	// header("Location: /signup.php?error=emptyfields");
	$error = "empty";
	// exit;
} else {
	if ($password !== $password2) {
		// header("Location: /signup.php?error=passwordsdontmatch");
		// exit;
		$error = "password";
	} else {
		require_once "dbh.inc.php";

		$query = "SELECT `username` FROM `user` WHERE `username`=?";
		$stmt = mysqli_prepare($link, $query);
		if (!$stmt) {
			// die("MySQL query error:" . mysqli_error($link) . " (" . mysqli_errno($link) . ")");
			$error = mysqli_error($link);
		} else {
			mysqli_stmt_bind_param($stmt, "s", $username);
			mysqli_stmt_execute($stmt);
			if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) !== 0) {
				mysqli_close($link);
				// header("Location: /signup.php?error=usernametaken");
				// exit;
				$error = "user";
			} else {
				mysqli_stmt_close($stmt);

				$order = str_shuffle("12345");

				$query = "INSERT INTO `user` (`username`, `password`,`fullname`,`quizorder`) VALUES (?,?,?,?)";
				$stmt = mysqli_prepare($link, $query);
				if (!$stmt) {
					// die("MySQL query error:" . mysqli_error($link) . " (" . mysqli_errno($link) . ")");
					$error = mysqli_error($link);
				} else {
					$password = password_hash($password, PASSWORD_DEFAULT);
					mysqli_stmt_bind_param($stmt, "ssss", $username, $password, $fullname, $order);
					mysqli_stmt_execute($stmt);

					if (mysqli_stmt_affected_rows($stmt) === 1) {
						// header('Location: /login.php?signup=success' . mysqli_stmt_affected_rows($stmt));
						$success = true;
					} else {
						$error = "unknown";
					}
					mysqli_stmt_close($stmt);
					mysqli_close($link);
				}
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
