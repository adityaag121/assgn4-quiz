<?php
session_start();
if (!isset($_SESSION['user'])) {
	header("Location: login");
	exit;
}
include_once "includes/header.inc.php";
?>

<main>
	<div class="hero">
		<div class="hero__text">
			<h1 class="hero__heading">Take the quiz now!</h1>
			<p class="hero__paragraph">
				This quiz is divided into four levels of 5 questions each.
				The structure of the levels is as follows:
			</p>
			<ol class="hero__level-info">
				<li>Multiple Choice Questions(MCQs) with Single Correct Option</li>
				<li>MCQs with One or More than one Correct Option</li>
				<li>Text based answer to a text hint</li>
				<li>Text based answer to an image hint</li>
			</ol>
			<p class="hero__paragraph">
				You will move to the next question after answering the current question.
				Scoring scheme is (+100) for a correct answer and (-25) for an incorrect one.
			</p>
			<div class="hero__buttons">
				<a href="/quiz" class="btn btn--primary">Start Quiz</a>
				<a href="/leaderboard" class="btn btn--secondary">View Leaderboard</a>
			</div>
		</div>
		<div class="hero__image">
			<img src="images/126.jpg" alt="ktjquiz">
		</div>
	</div>
</main>

<?php
include_once "includes/footer.inc.php";
?>