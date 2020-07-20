<?php
session_start();
if (!isset($_SESSION['user'])) {
	header("Location: /login.php");
	exit;
}

include_once "includes/header.inc.php";
$username = $_SESSION['user'];
?>

<main id="main--quiz">
	<div class="quiz__container">
		<?php
		require "includes/dbh.inc.php";

		$query = "SELECT `progress`,`score`,`quizorder` FROM `user` WHERE `username`='$username'";
		$result =  mysqli_fetch_assoc(mysqli_query($link, $query));
		$progress = intval($result['progress']);
		$score = $result['score'];
		$quizorder = $result['quizorder'];
		$quizorder = str_split($quizorder);
		$question_number = $progress + 1;
		$actual_question_number = $progress - $progress % 5 + $quizorder[$progress % 5];

		// Checking previous question
		if (isset($_POST['submit'])) {
			$query = "SELECT `level` FROM `question` WHERE `id`=$actual_question_number";
			$level = mysqli_fetch_assoc(mysqli_query($link, $query))['level'];
			$correct = false;

			switch ($level) {
				case 1:
					$query = "SELECT `iscorrect`,`question_id` FROM `answer` WHERE `id`=?";
					$stmt = mysqli_prepare($link, $query);
					if ($stmt === false) {
						echo '<h2>Error</h2>';
					}
					mysqli_stmt_bind_param($stmt, "i", $_POST['answer']);
					if (!mysqli_stmt_execute($stmt)) {
						echo '<h2>Error</h2>';
					}
					echo mysqli_stmt_error($stmt);
					$result = mysqli_stmt_get_result($stmt);
					// var_dump($result);
					$result = mysqli_fetch_assoc($result);
					$correct = $result['iscorrect'] && $actual_question_number == $result['question_id'];
					// var_dump($result);
					// echo $result['iscorrect'] && $question_number == $result['question_id'] ? "Correct" : "Incorrect";
					break;
				case 2:
					$query = "SELECT `id` FROM `answer` WHERE `question_id`=$actual_question_number AND `iscorrect`=1";
					$answer = mysqli_fetch_all(mysqli_query($link, $query), MYSQLI_ASSOC);
					// var_dump($answer);
					// var_dump($_POST['answer']);
					if (sizeof($answer) == sizeof($_POST['answer'])) {
						$correct = true;
						for ($i = 0; $i < sizeof($answer); $i++) {
							if (!in_array($answer[$i]['id'], $_POST['answer'])) {
								$correct = false;
								break;
							}
						}
					}
					break;
				case 3:
				case 4:
					$query = "SELECT `answer` FROM `answer` WHERE `question_id`=$actual_question_number AND `iscorrect`=1";
					$result = mysqli_fetch_assoc(mysqli_query($link, $query));
					$correct = !strcasecmp($result['answer'], $_POST['answer']);
					// var_dump($result['answer']);
					// var_dump($_POST['answer']);
					break;
			}

			if ($correct) {
				$score += 100;
				$progress++;
				$question_number++;
				$actual_question_number = $progress - $progress % 5 + $quizorder[$progress % 5];
				if ($progress == 20) {
					$query = "UPDATE `user` SET `completed_time`=CURRENT_TIMESTAMP() WHERE `username`='$username'";
					if (!mysqli_query($link, $query)) {
						echo "Error";
					}
				}
			} else {
				$score -= 25;
			}
			echo $correct ? "<h4 class='correctalert'>Correct</h4>" : "<h4 class='incorrectalert'>Incorrect! Please try again!</h4>";

			$query = "UPDATE `user` SET `score`=$score,`progress`=$progress WHERE `username`='$username'";
			if (!mysqli_query($link, $query)) {
				echo "Error";
			}
		}
		if ($progress < 20) {
			$query = "SELECT * FROM `question` WHERE `id`=$actual_question_number";
			$result = mysqli_query($link, $query);
			$question = mysqli_fetch_assoc($result);
			echo '<h2 class="question-no">Question ' . $question_number . '.</h2>
				<h3>' . $question['question'] . '</h3>';

			$level = $question['level'];

			if ($level == 4) {
				$imagefile = glob("images/questions/question$actual_question_number.*");
				echo '<img class="question-image" src="' . $imagefile[0] . '" alt="Question Image">';
			}
			echo '<form action="/quiz.php" method="post">';
			if ($level == 1 || $level == 2) {
				$query = "SELECT * FROM `answer` WHERE `question_id`=$actual_question_number";
				$result = mysqli_query($link, $query);
				$answer = mysqli_fetch_all($result, MYSQLI_ASSOC);
				shuffle($answer);
				$type = $level == 1 ? "radio" : "checkbox";
				$inputName = $level == 1 ? '' : '[]';
				for ($i = 1; $i <= 4; $i++) {
					echo '<input type="' . $type . '" class="optionlevel12" name="answer' . $inputName . '" id="option' . $i . '" value="' . $answer[$i - 1]['id'] . '">
		 		<label for="option' . $i . '"><div class="radio"></div><div class="label__text">' . $answer[$i - 1]['answer'] . '</div></label>';
				}
			} else {
				echo '<input type="text" name="answer" id="answer" class="optionlevel34" placeholder="Answer">';
			}
			echo '<input type="submit" name="submit" value="Submit" class="btn btn--primary">';
			echo '</form>';
		} else {
			echo '<h2>Congratulations! You have completed the quiz</h2><br>';
			echo '<p>You did great. You have completed all the levels and your total score is ' . $score . '</p><br>';
			echo '<p>View the complete leaderboard by clicking the button below</p><br>';
			echo '<a href="/leaderboard.php" class="btn btn--primary">View Leaderboard</a>';
		}
		mysqli_close($link);
		?>
	</div>
	<div class="quiz__leaderboard">
		<h2>Leaderboard (Top-10)</h2>
		<table class="leaderboard__table">
			<thead>
				<tr>
					<th>S.No.</th>
					<th>Name</th>
					<th>Score</th>
				</tr>
			</thead>
			<tbody>
				<?php
				require "includes/dbh.inc.php";

				$query = "SELECT `fullname`,`score`,`username` FROM `user` ORDER BY `score` DESC,`completed_time` LIMIT 10";
				$result = mysqli_query($link, $query);
				for ($i = 1; $i <= mysqli_num_rows($result); $i++) {
					$row = mysqli_fetch_assoc($result);
					// echo $row['username'];
					$class = $row['username'] == $_SESSION['user'] ? 'class="active-row"' : '';
					echo '<tr ' . $class . '>
						<td>' . $i . '</td>
						<td>' . $row['fullname'] . '</td>
						<td>' . $row['score'] . '</td>
						</tr>';
				}
				?>
			</tbody>
		</table>
	</div>
</main>

<?php
include_once "includes/footer.inc.php";
?>