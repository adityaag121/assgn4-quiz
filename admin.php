<?php
session_start();
if (!isset($_SESSION['user'])) {
	header("Location: /login.php");
	exit;
}

include_once "includes/header.inc.php";
if ($_SESSION['user'] != "admin") {
	echo "<h2>Sorry, You are not authorised to access this page</h2>";
	include_once "includes/footer.inc.php";
	exit;
}

//Password is admin@ktjquiz.1234
?>

<main class="main--admin">
	<div class="admin__container">
		<form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="questionForm">
			<input required type="text" name="number" id="number" placeholder="Question No." size="12"><br>
			<input required type="text" name="question" id="question" placeholder="Question" size="100"><br>
			<label for="level">Level:</label>
			<select required name="level" id="level" onchange="updateFormByLevel(this.value)">
				<option value="1">1</option>
				<option value="2">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
			</select>
			<div id="optionform">
				Enter four options<br>
				<input required type="text" name="option1" id="option1" placeholder="Correct option"><br>
				<input required type="text" name="option2" id="option2" placeholder="Other option"><br>
				<input required type="text" name="option3" id="option3" placeholder="Other option"><br>
				<input required type="text" name="option4" id="option4" placeholder="Other option"><br>
				(Don't worry about the order! It will be shuffled before being inserted into the database)<br>
			</div>
			<input type="submit" value="Submit" name="submit" class="btn btn--primary">
		</form>

		<?php
		if (isset($_POST['submit'])) {
			require_once "includes/dbh.inc.php";
			$query = "SELECT * FROM `question` WHERE `id`=?";
			$stmt = mysqli_prepare($link, $query);
			mysqli_stmt_bind_param($stmt, "i", $_POST['number']);
			mysqli_stmt_execute($stmt);
			$result = mysqli_stmt_get_result($stmt);
			mysqli_stmt_close($stmt);

			if ($_POST['level'] == 4) {
				$questionimage = $_FILES['questionimage'];
				$filetmpname = $questionimage['tmp_name'];
				$fileExt = explode('.', $questionimage['name']);
				$fileExt = strtolower(end($fileExt));
				$allowedExt = ['jpg', 'jpeg', 'png'];
				if (!in_array($fileExt, $allowedExt)) {
					echo "Failed! You can only upload jpg, jpeg or png files";
					exit;
				} elseif ($questionimage['size'] > 1000000) {
					echo "Failed! Uploaded file can not be larger than 1000000 bytes";
					exit;
				} elseif ($questionimage['error']) {
					echo "Failed! Uploaded file can not be larger than 1000000 bytes";
					exit;
				} else {
					$fileNameNew = "images/questions/question" . $_POST['number'] . '.' . $fileExt;
					move_uploaded_file($filetmpname, $fileNameNew);
				}
			}

			if (mysqli_num_rows($result) == 0) {
				$query = "INSERT INTO `question` ";
				$query .= "(`id`,`question`,`level`) ";
				$query .= "VALUES (?,?,?)";
				$stmt = mysqli_prepare($link, $query);
				mysqli_stmt_bind_param($stmt, "isi", $_POST['number'], $_POST['question'], $_POST['level']);
				mysqli_stmt_execute($stmt);
			} else {
				$query = "UPDATE `question` SET `question`=?,`level`=? WHERE `id`=?";
				$stmt = mysqli_prepare($link, $query);
				mysqli_stmt_bind_param($stmt, "sii", $_POST['question'], $_POST['level'], $_POST['number']);
				mysqli_stmt_execute($stmt);
			}
			$affectedrows = mysqli_stmt_affected_rows($stmt);
			mysqli_stmt_close($stmt);

			$query = "DELETE FROM `answer` WHERE `question_id`=?";
			$stmt = mysqli_prepare($link, $query);
			mysqli_stmt_bind_param($stmt, "i", $_POST['number']);
			if (!mysqli_stmt_execute($stmt)) {
				echo "<p style={color: red}>Error occured:" . mysqli_stmt_error($stmt) . "</p>";
			}
			mysqli_stmt_close($stmt);


			switch ($_POST['level']) {
				case 1:
				case 2:
					$answer = array(
						array("value" => $_POST['option1'], "iscorrect" => 1),
						array("value" => $_POST['option2'], "iscorrect" => 0),
						array("value" => $_POST['option3'], "iscorrect" => 0),
						array("value" => $_POST['option4'], "iscorrect" => 0),
					);
					if ($_POST['level'] == 2) {
						for ($i = 0; $i < 4; $i++) {
							$answer[$i]['iscorrect'] = in_array($i + 1, $_POST['iscorrect']);
						}
					}
					shuffle($answer);
					$query = "INSERT INTO `answer` (`answer`, `question_id`, `iscorrect`) VALUES ";
					$query .= "(?,?,?), ";
					$query .= "(?,?,?), ";
					$query .= "(?,?,?), ";
					$query .= "(?,?,?) ";
					$stmt = mysqli_prepare($link, $query);
					mysqli_stmt_bind_param(
						$stmt,
						"siisiisiisii",
						$answer[0]['value'],
						$_POST['number'],
						$answer[0]['iscorrect'],
						$answer[1]['value'],
						$_POST['number'],
						$answer[1]['iscorrect'],
						$answer[2]['value'],
						$_POST['number'],
						$answer[2]['iscorrect'],
						$answer[3]['value'],
						$_POST['number'],
						$answer[3]['iscorrect']
					);
					mysqli_stmt_execute($stmt);
					if (mysqli_stmt_affected_rows($stmt) == 4 || $affectedrows == 1) {
						echo "<p class='correctalert'>Question Successfully Inserted</p>";
					} else {
						echo "<p style={color: red}>Error occured:" . mysqli_stmt_error($stmt) . "</p>";
					}
					break;
				case 3:
				case 4:
					$query = "INSERT INTO `answer` (`answer`, `question_id`, `iscorrect`) VALUES (?,?,1)";
					$stmt = mysqli_prepare($link, $query);
					mysqli_stmt_bind_param($stmt, "si", $_POST['answer'], $_POST['number']);
					mysqli_stmt_execute($stmt);
					if (mysqli_stmt_affected_rows($stmt) == 1 || $affectedrows == 1) {
						echo "<p class='correctalert'>Question Successfully Inserted</p>";
					} else {
						echo "<p style={color: red}>Error occured:" . mysqli_stmt_error($stmt) . "</p>";
					}
					break;
			}
		}
		?>
	</div>
	<div class="admin__questions__container">
		<table class="leaderboard__table">
			<thead>
				<tr>
					<th>S.No.</th>
					<th>Level</th>
					<th>Question</th>
					<th>Correct Answer(s)</th>
				</tr>

			</thead>
			<tbody>

				<?php
				require_once "includes/dbh.inc.php";
				$query = "SELECT `question`.`id`,`level`,`question`,`answer` FROM `question` JOIN `answer` ON (`question`.`id`=`answer`.`question_id`) AND `iscorrect`=1";
				$result = mysqli_query($link, $query);
				while ($row = mysqli_fetch_assoc($result)) {
					echo '<tr><td>' . $row['id'] . '</td>
					<td>' . $row['level'] . '</td>
					<td>' . $row['question'] . '</td>
					<td>' . $row['answer'] . '</td></tr>';
				}
				mysqli_close($link);
				?>
			</tbody>
		</table>
	</div>
</main>

<script>
	const updateFormByLevel = (level) => {
		let html;
		switch (parseInt(level)) {
			case 1:
				html = 'Enter four options<br>';
				html += '<input required type="text" name="option1" id="option1" placeholder="Correct option"><br>';
				html += '<input required type="text" name="option2" id="option2" placeholder="Other option"><br>';
				html += '<input required type="text" name="option3" id="option3" placeholder="Other option"><br>';
				html += '<input required type="text" name="option4" id="option4" placeholder="Other option"><br>';
				html += '(Don\'t worry about the order! It will be shuffled before being inserted into the database)<br>';
				break;
			case 2:
				html = 'Enter four options and choose which are correct<br>';
				html += '<input required type="text" name="option1" id="option1" placeholder="Option 1"><input type="checkbox" name="iscorrect[]" id="1" value="1"><br>';
				html += '<input required type="text" name="option2" id="option2" placeholder="Option 2"><input type="checkbox" name="iscorrect[]" id="2" value="2"><br>';
				html += '<input required type="text" name="option3" id="option3" placeholder="Option 3"><input type="checkbox" name="iscorrect[]" id="3" value="3"><br>';
				html += '<input required type="text" name="option4" id="option4" placeholder="Option 4"><input type="checkbox" name="iscorrect[]" id="4" value="4"><br>';
				break;
			case 3:
			case 4:
				html = 'Enter a single text based answer(case-insensitive):';
				html += '<input required type="text" name="answer" id="answer" placeholder="Answer"><br>';
				break;
		}
		document.getElementById('optionform').innerHTML = html;
		if (parseInt(level) == 4) {
			// let element = document.getElementById('question');
			let element = document.createElement("input");
			element.setAttribute("type", "file");
			element.setAttribute("name", "questionimage");
			element.setAttribute("required", "true");
			element.setAttribute("id", "questionimage")
			// element.removeAttribute("placeholder");
			let label = document.createElement("label");
			label.setAttribute("id", "Uploadlabel")
			label.setAttribute("for", "questionimage");
			label.appendChild(document.createTextNode("Upload the Question Image"));
			let form = document.getElementById("questionForm");
			// form.insertBefore(element, document.getElementById('question'));
			document.getElementById('question').insertAdjacentElement("afterend", element);
			form.insertBefore(label, element);
			let br = document.createElement("br");
			form.insertBefore(element, br);
			form.setAttribute("enctype", "multipart/form-data");
		} else {
			let form = document.getElementById("questionForm");
			form.removeAttribute("enctype");
			form.removeChild(document.getElementById("Uploadlabel"));
			form.removeChild(document.getElementById("questionimage"));
		}
	}
</script>

<?php
include_once "includes/footer.inc.php";
?>