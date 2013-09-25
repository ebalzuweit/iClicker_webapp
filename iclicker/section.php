<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	
	$section_id = $_GET["section_id"];
	if (!isset($section_id)) {
		endOutput("Must include section_id as a GET parameter so we know for which section to display information");
	}
	
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "admin")) {
		header("Location: home.php");
	}
	
	createHeader("Section");
	
?>
<body>
	<div>
		<h2>Sessions</h2>
		<table>
<?php	
	$query = "
		SELECT session_id, session_date, post_processed FROM sessions 
		WHERE section_id = ?
		ORDER BY session_tag ASC;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare sessions query. " . $conn->error);
	$stmt->bind_param("i", $section_id);
	$stmt->execute() or die("Couldn't execute sessions query. " . $conn->error);
	
$stmt->bind_result($session_id, $session_date, $post_processed);
	// $result = $stmt->get_result();
	
	$dayOfWeek = 0;
	$day = 0;
	$month = 0;
	$week = 0;
	$dayOfYear=-1;
	// special case to detect the first week!
	$isFirstWeek=1;
	while ($stmt->fetch()/*$row = $result->fetch_array(MYSQLI_ASSOC)*/) {
		$date = DateTime::createFromFormat("m/d/y H:i", $session_date);
		$newDayOfWeek = (int) date("w", $date->getTimestamp());
		$newDay = (int) date("j", $date->getTimestamp());
		$newMonth = (int) date("n", $date->getTimestamp());

		//if ($newDayOfWeek < $dayOfWeek || $newDay >= $day + 7 || $newMonth > $month) {
		if ($newDayOfWeek < $dayOfWeek || $newDay >= $day + 7 || $isFirstWeek) {
			// special case to detect the first week
			$isFirstWeek=0;
			// new week
			$week++;
			echo "
				</table>
				<table class='collection'>
				<tr>
					<th> Updated? </th>

					<th>Week $week</th>
					<th>
						<form action='createassignment.php#week$week' method='get'>
							<input type='hidden' name='section_id' value='$section_id'>
							<input type='hidden' name='week' value='$week'>
							<input type='submit' value='Create Assignment for this Week'>
						</form>
					</th>
				</tr>
			";
		}
		$dayOfWeek = $newDayOfWeek;
		$day = $newDay;
		$month = $newMonth;
		
		$dayString = date("l", $date->getTimestamp());
		$postProcessedString="";
		if ($post_processed) {
			$postProcessedString="*yes*";
		}
		echo "
			<tr>
				<td align=\"center\"> $postProcessedString </td>
				<td> <a href='session.php?session_id=$session_id'>$dayString</a></td>
				<td><a href='session.php?session_id=$session_id'>$session_date</a></td>
			</tr>
		";
	}
?>
</table>
<br>
<b><a href="uploadform.php?section_id=<?= $section_id ?>"> Upload new session(s) </a></b>
<h2>Assignments</h2>
<table class='collection'>
	<tr>
		<th>Number of Questions</th>
		<th>Due Date</th>
	</tr>
<?php
	$query = "
		SELECT assignment_id, due FROM assignments WHERE section_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'assignments' query. " . $conn->error);
	$stmt->bind_param("i", $section_id);
	$stmt->execute() or die("Couldn't execute 'assignments' query. " . $conn->error);
	
	$stmt->bind_result($assignment_id, $due);
	
	$assignments = array();
	while ($stmt->fetch()) {
		$assignments[$assignment_id] = $due;
	}
	$stmt->close();
	
	foreach ($assignments as $assignment_id => $due) {
		$query = "
			SELECT atq_id FROM assignmentstoquestions WHERE assignment_id = $assignment_id;
		";
		
		$result = $conn->query($query) or die("Couldn't execute 'atq' query. " . $conn->error);
		$count = mysqli_num_rows($result);
		echo "
			<tr>
				<td><a href='assignmentreport.php?assignment_id=$assignment_id' >$count</a></td>
				<td><a href='assignmentreport.php?assignment_id=$assignment_id' >" . DateFromUTC($due) . "</a></td>
			</tr>
		";
	}
?>
</table>
<br>
<a href='createassignment.php?section_id=<?php echo $section_id; ?>'>Create Assignment</a>
<br>
<h2>Students</h2>
<table class='collection'>
	<tr>
		<th>School ID</th>
		<th>iClicker ID</th>
		<th>Name</th>
	</tr>
<?php
	
	$query = "
		SELECT distinct students.student_id, iclicker_id, school_id, first_name, last_name FROM students, sections, sessions, questions, responses WHERE
		students.student_id = responses.student_id AND
		responses.question_id = questions.question_id AND
		questions.session_id = sessions.session_id AND
		sessions.section_id = sections.section_id AND
		sections.section_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare students query. " . $conn->error);
	$stmt->bind_param("i", $section_id);
	$stmt->execute() or die("Couldn't execute students query. " . $conn->error);
	
	$stmt->bind_result($student_id, $iclicker_id, $school_id, $first_name, $last_name);
	
	// $result = $stmt->get_result();
	
	while ($stmt->fetch()/*$row = $result->fetch_array(MYSQLI_ASSOC)*/) {
		echo "
			<tr>
				<td><a href='student.php?student_id=" . $student_id . "'>" . $school_id . "</a></td>
				<td><a href='student.php?student_id=" . $student_id . "'>" . $iclicker_id . "</a></td>
				<td><a href='student.php?student_id=" . $student_id . "'>" . $last_name . ", " . $first_name . "</a></td>
			</tr>
		";
	}
	
	echo "</table>";
?>
<?php
	$conn->close();
	createFooter();
?>