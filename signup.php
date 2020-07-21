<?php
session_start();
if (isset($_SESSION['user'])) {
	header("Location: /");
	exit;
}

include_once "includes/header.inc.php";
?>

<main class="main--signup">
	<div class="signup-container">
		<h3 class="signup-container__heading">Sign Up</h3>
		<div class="" id="error-div"></div>
		<form id="signup-form" action="includes/signup.inc.php" method="post">
			<input type="text" name="fullname" id="fullname" placeholder="Full Name" required>
			<input type="text" name="username" id="username" placeholder="Username" required>
			<input type="password" name="password" id="password" placeholder="Password" required>
			<input type="password" name="password-confirm" id="password-confirm" placeholder="Confirm Password" required>
			<input type="submit" value="Sign up" name="submit" class="submit-btn" id="submit">
		</form>
		<p>Already have an account? <a href="login">Log In</a></p>
	</div>
</main>

<script>
	document.getElementById('signup-form').addEventListener("submit", function(event) {
		let xhr = new XMLHttpRequest();
		const url = "includes/signup.inc.php";
		event.preventDefault();
		let fullname = document.getElementById('fullname').value;
		let username = document.getElementById('username').value;
		let password = document.getElementById('password').value;
		let password2 = document.getElementById('password-confirm').value;
		let data = "fullname=" + fullname + "&username=" + username + "&password=" + password + "&password-confirm=" + password2 + "&submit=on";
		xhr.open("POST", url, true);
		xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xhr.onloadstart = function() {
			document.getElementById("submit").value = "Wait...";
		}
		xhr.onreadystatechange = function() {
			if (xhr.readyState == 4 && xhr.status == 200) {
				document.getElementById("submit").value = "Sign up";
				// let return_text = xhr.responseText;
				console.log(this.responseText);
				var return_data = JSON.parse(xhr.responseText);
				signup_process(return_data);
			}
		}
		xhr.send(data);
	})
	const signup_process = (value) => {
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
					document.getElementById('fullname').classList.add("input--error");
					document.getElementById('username').classList.add("input--error");
					document.getElementById('password-confirm').classList.add("input--error");
					document.getElementById('password').classList.add("input--error");
					errordiv.innerText = "Please fill in all fields!";
					break;
				case "user":
					document.getElementById('username').classList.add("input--error");
					document.getElementById('password').value = "";
					document.getElementById('password-confirm').value = "";
					errordiv.innerText = "Username taken!";
					break;
				case "password":
					document.getElementById('password').classList.add("input--error");
					document.getElementById('password-confirm').classList.add("input--error");
					errordiv.innerText = "Passwords do not match!";
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