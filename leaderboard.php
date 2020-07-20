<?php
session_start();
if (!isset($_SESSION['user'])) {
	header("Location: login.php");
	exit;
}

include_once "includes/header.inc.php";
?>

<main>
	<div class="main__leaderboard">
		<h1>Leaderboard</h1>
		<table class="leaderboard__table">
			<thead>
				<tr>
					<th>S.No.</th>
					<th>Name</th>
					<th>Username</th>
					<th>Score</th>
				</tr>
			</thead>
			<tbody>
				<?php
				require "includes/dbh.inc.php";
				$max = 50;

				$n = 0;
				if (isset($_GET['n'])) $n = $_GET['n'];
				$n = intval($n);
				$offset = $n * $max;

				$query = "SELECT `fullname`,`score`,`username` FROM `user` ORDER BY `score` DESC, `completed_time` LIMIT ?, $max";
				$stmt = mysqli_prepare($link, $query);
				mysqli_stmt_bind_param($stmt, "i", $offset);
				mysqli_stmt_execute($stmt);
				$result = mysqli_stmt_get_result($stmt);
				for ($i = 1; $i <= mysqli_num_rows($result); $i++) {
					$row = mysqli_fetch_assoc($result);
					// echo $row['username'];
					$class = $row['username'] == $_SESSION['user'] ? 'class="active-row"' : '';
					echo '<tr ' . $class . '>
						<td>' . ($i + $offset) . '</td>
						<td>' . $row['fullname'] . '</td>
						<td>' . $row['username'] . '</td>
						<td>' . $row['score'] . '</td>
						</tr>';
				}
				?>
			</tbody>
		</table>
		<?php
		$query = "SELECT COUNT(*) FROM `user`";
		$count = intval(mysqli_fetch_row(mysqli_query($link, $query))[0]);
		if ($n > 0) {
			echo '<a class="leaderboard__pagelink" href="/leaderboard.php?n=' . ($n - 1) . '">&lt;Previous Page</a>';
		}
		if ($count > ($n + 1) * $max) {
			echo '<a class="leaderboard__pagelink" href="/leaderboard.php?n=' . ($n + 1) . '">Next Page&gt;</a>';
		}

		mysqli_close($link);
		?>
	</div>
</main>

<?php
include_once "includes/footer.inc.php";
?>