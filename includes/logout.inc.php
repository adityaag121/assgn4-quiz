<?php
if (!isset($_POST['logout'])) {
	header("Location: /");
} else {
	session_start();
	session_unset();
	session_destroy();
	header("Location: /");
}
