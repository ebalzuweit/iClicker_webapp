<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	createHeader("Create Assignment");
	
	$assignment_week = -1;
	if (isset($_GET["week"])) {
		$assignment_week = (int) $_GET["week"];
	}
?>
<script type='text/javascript'>
	$(function() {
		$('#datepicker').datepicker();
	});
</script>
	<table>
		<tr>
			<th>Week / Date</th>
			<th>Question #</th>
			<th>Screen Picture</th>
			<th>Chart Picture</th>
			<th>Include in Assignment?</th>
		</tr>
		<form action='endassignment.php' method='post'>
<?php
	$section_id = $_GET['section_id'];
	
	// get all the sessions
	
	$query = "
		SELECT session_id, session_date FROM sessions WHERE section_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'sessions' query. " . $conn->error);
	$stmt->bind_param("i", $section_id);
	$stmt->execute() or die("Couldn't execute 'sessions' query. " . $conn->error);
	
	$stmt->bind_result($session_id, $session_date);
	
	$sessions = array();
	
	while ($stmt->fetch()) {
		$sessions[$session_id] = $session_date;
	}
	$stmt->close();
	
	$dayOfWeek = 0;
	$day = 0;
	$month = 0;
	$week = 0;
	$isFirstWeek=1;
	// create entries for each session and its questions
	foreach ($sessions as $session_id => $session_date) {
		$date = DateTime::createFromFormat("m/d/y H:i", $session_date);
		$newDayOfWeek = (int) date("w", $date->getTimestamp());
		$newDay = (int) date("j", $date->getTimestamp());
		$newMonth = (int) date("n", $date->getTimestamp());
		//if ($newDayOfWeek < $dayOfWeek || $newDay >= $day + 7 || $newMonth > $month) {
		if ($newDayOfWeek < $dayOfWeek || $newDay >= $day + 7 || $isFirstWeek) {
			$isFirstWeek=0;
			$week++;
		
			echo "
				<tr>
					<th><a name='week$week'>Week $week</a></th>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			";
		}
		if ($week!=$assignment_week && $assignment_week > -1) {
			continue;
		}
		$dayOfWeek = $newDayOfWeek;
		$day = $newDay;
		$month = $newMonth;
		
		// only printing the assigned week
		// we'll print every week for 
		/*if ($assignment_week == $week || $assignment_week = -1)*/ {
			echo "
				<tr>
					<td> " . $session_date . "</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			";
			
			$query = "
				SELECT question_id, question_number, screen_picture, chart_picture, ignore_question FROM questions WHERE session_id = ?;
			";
			
			$stmt = $conn->prepare($query) or die("Couldn't prepare 'questions' query. " . $conn->error);
			$stmt->bind_param("i", $session_id);
			$stmt->execute() or die("Couldn't execute 'questions' query. " . $conn->error);
			
			$stmt->bind_result($question_id, $question_number, $screen_picture, $chart_picture, $ignore_question);
			
			$gv = false;
			while ($stmt->fetch()) {
				$checked = "";
				if ($ignore_question == 0) {
					// only check group votes in the week we're creating an assignment for
					if ($gv && $assignment_week > -1 && $assignment_week == $week) {
						$checked = "checked";
					}
					$gv = !$gv;
				}
				
				echo "
					<tr>
						<td></td>
						<td>$question_number</td>
						<td><a href='pictures/$screen_picture' title='Picture of screen' data-lightbox='$question_id'><img src='pictures/$screen_picture' alt='Picture of screen' width='175' height='100'></td>
						<td><a href='pictures/$chart_picture' title='Chart of responses' data-lightbox='$question_id'><img src='pictures/$chart_picture' alt='Chart of responses' width='175' height='100'></td>
						<td><input type='checkbox' name='questions[]' value='$question_id' $checked></td>
					</tr>
				";
			}
			$stmt->close();
		}
	}
?>
	<tr>
		<td><input type='hidden' name='section_id' value=<?php echo $section_id; ?>></td>
		<td></td>
		<td>Date: <input type='text' name='due' id='datepicker' ></td>
		<td><input type='text' name='hour' size='2' value='11'> : <input type='text' name='minute' size='2' value='59'></td>
		<td><input type='submit' value='Create Assignment'></td>
		</form>
	</tr>
</table>
<?php
	$conn->close();
	createFooter();
?>