<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
		
	$session_id = $_GET["session_id"];

	$query = "
		SELECT section_id FROM sessions WHERE session_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'section_id' query. " . $conn->error);
	$stmt->bind_param("i", $session_id);
	$stmt->execute() or die("Couldn't execute 'section_id' query. " . $conn->error);
	
	$stmt->bind_result($section_id);
	$stmt->fetch();
	$stmt->close();

createHeader("Session", true, "<a href='section.php?section_id=$section_id'> Back to Sessions and Assignments </a>");

?>
<h1>
	Questions
</h1>
<table>
<form action='endeditsession.php' method='post'>
	<input type="hidden" name="session_id" value="<?= $_GET[session_id] ?>"/>
	<tr>
		<th>Ignore?</th>
		<th>#</th>
		<th>Type</th>
		<th>Question Picture</th>
		<th>Chart Picture</th>
		<th>Answers</th>
		<th>Compare</th>
	</tr>
<?php
	$query = "
		SELECT question_id, question_number, screen_picture, chart_picture, correct_answer, ignore_question FROM questions WHERE
		session_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
	$stmt->bind_param("i", $session_id);
	$stmt->execute() or die("Couldn't execute query. " . $conn->error);
	
	$stmt->bind_result($question_id, $question_number, $screen_picture, $chart_picture, $correct_answer, $ignore_question);
	
	// $result = $stmt->get_result();
	
	$q = 1;
	$num = 1;
	$iv_id;
	while ($stmt->fetch()/*$row = $result->fetch_array(MYSQLI_ASSOC)*/) {
		$type = "IV";
		if ($q % 2 === 0) {
			$type = "GV";
		}
		
		echo "<tr>";
		$checked = "";
		if ($ignore_question == 1) {
			$checked = "checked";
		}
		echo "
			<td><input type='checkbox' name='ignore[]' value='" . $question_id . "'" . $checked . "></td>
			<td><a href='question.php?question_id=" . $question_id . "'>Question " . $num . "</a></td>
		";
		
		if ($ignore_question == 1) {
			echo "
				<td>Ignored</td>
			";
		} else {
			echo "
				<td>" . $type . "</td>
			";
		}
		
		$a = "";
		$b = "";
		$c = "";
		$d = "";
		$e = "";
		
		if (strpos($correct_answer, 'A') !== false) {
			$a = "checked";
		}
		if (strpos($correct_answer, 'B') !== false) {
			$b = "checked";
		}
		if (strpos($correct_answer, 'C') !== false) {
			$c = "checked";
		}
		if (strpos($correct_answer, 'D') !== false) {
			$d = "checked";
		}
		if (strpos($correct_answer, 'E') !== false) {
			$e = "checked";
		}
		
		echo "
			<td><a href='pictures/" . $screen_picture . "' title='Picture of screen' data-lightbox='" . $question_id . "'><img src='pictures/" . $screen_picture . "' alt='Picture of screen' width='175' height='100'></td>
			<td><a href='pictures/" . $chart_picture . "' title='Chart of responses' data-lightbox='" . $question_id . "'><img src='pictures/" . $chart_picture . "' rel='lightbox' title='Chart of responses' width='175' height='100'></td>
			<td>
				<input type='checkbox' name='A[]' value='" . $question_id . "' " . $a . ">A
				<input type='checkbox' name='B[]' value='" . $question_id . "' " . $b . ">B
				<input type='checkbox' name='C[]' value='" . $question_id . "' " . $c . ">C
				<input type='checkbox' name='D[]' value='" . $question_id . "' " . $d . ">D
				<input type='checkbox' name='E[]' value='" . $question_id . "' " . $e . ">E
				<input type='hidden' name='id[]' value='" . $question_id . "'>
			</td>
		";
		
		if ($ignore_question != 1) {
			if ($q % 2 === 1) {
				$iv_id = $question_id;
			} else {
				echo "
					<td><a href='compare.php?iv=" . $iv_id . "&gv=" . $question_id . "'>Compare</a></td>
				";
				$num++;
			}
			$q++;
		}
		echo "</tr>";
	}
?>
</table>
	<p>
	<input type='submit' value='Update'>
	</p>
</form>
<?php
	$conn->close();
if (isset($_GET['message'])) {
	echo "<h2> $_GET[message] </h2>";
}
createFooter(true, "section.php?section_id=$section_id");
?>