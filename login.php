<?php
session_start();

if (isset($_SESSION['user'])) {
	header("Location: /");
	exit;
}

include_once "includes/header.inc.php";
?>

<main>
	<div class="login-container">
		<h3 class="login-container__heading">Log In</h3>
		<div class="" id="error-div"></div>
		<form id="login-form" action="includes/login.inc.php" method="post">
			<input type="text" name="username" id="username" placeholder="Username" required>
			<input type="password" name="password" id="password" placeholder="Password" required>
			<input type="submit" name="submit" value="Log In" class="submit-btn" id="submit">
		</form>
		<p>Don't have an account? <a href="signup.php">Sign up</a></p>
	</div>
</main>

<script>
	document.getElementById('login-form').addEventListener("submit", function(event) {
		let xhr = new XMLHttpRequest();
		const url = "includes/login.inc.php";
		event.preventDefault();
		let username = document.getElementById('username').value;
		let password = document.getElementById('password').value;
		let data = "username=" + username + "&password=" + password + "&submit=on";
		xhr.open("POST", url, true);
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.onloadstart = function() {
			document.getElementById("submit").value = "Wait...";
		}
		xhr.onreadystatechange = function() {
			if (xhr.readyState == 4 && xhr.status == 200) {
				document.getElementById("submit").value = "Log In";
				// let return_text = xhr.responseText;
				// console.log(this.responseText);
				var return_data = JSON.parse(xhr.responseText);
				login_process(return_data);
			}
		}
		xhr.send(data);
	})
	const login_process = (value) => {
		let errordiv = document.getElementById("error-div");
		errordiv.classList = "";
		if (value.success) {
			errordiv.classList.add("correctalert");
			errordiv.innerText = "Success!";
			window.location.pathname = "";
		} else {
			errordiv.classList.add("incorrectalert");
			switch (value.error) {
				case "empty":
					document.getElementById('username').classList.add("input--error");
					document.getElementById('password').classList.add("input--error");
					errordiv.innerText = "Please fill in all fields!";
					break;
				case "user":
					document.getElementById('username').classList.add("input--error");
					document.getElementById('password').classList.add("input--error");
					errordiv.innerText = "User does not exist!";
					break;
				case "password":
					document.getElementById('password').classList.add("input--error");
					errordiv.innerText = "Incorrect Password!";
					break;
				default:
					errordiv.innerText = value.error;
					break;
			}
		}
	}
</script>

<?php
include_once "includes/footer.inc.php";
?>