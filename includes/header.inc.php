<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="style.css">
	<title>KTJQuiz</title>
</head>

<body>
	<header>
		<a href="/">
			<div class="header__logo">KTJQuiz</div>
		</a>
		<?php
		if (isset($_SESSION['user'])) {
			if ($_SESSION['user'] == "admin") {
				echo '<a href="/admin" class="logout-btn">Admin Panel</a>';
			}
			echo '<form action="includes/logout.inc.php" method="post">
			<div class="logout__username">' . $_SESSION['user'] . '
			<input type="submit" value="Logout" name="logout" class="logout-btn">
		</form>';
		}
		?>
	</header>