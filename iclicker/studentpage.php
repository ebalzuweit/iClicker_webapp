<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
	
	createHeader("Student Page");
	
	$query = "
		SELECT student_id FROM students WHERE
		username = ? AND
		password = ?;
	";

	$stmt = $conn->prepare($query) or die("Couldn't prepare 'student_id' query. " . $conn->error);
	$stmt->bind_param("ss", $_COOKIE["Username"], $_COOKIE["Password"]);
	$stmt->execute() or die("Couldn't execute 'student_id' query. " . $conn->error);

	$stmt->bind_result($student_id);
	$stmt->fetch();
	$stmt->close();
?>
<h2>Assignments</h2>
<table class='collection'>
	<tr>
		<th>Answered</th>
		<th>Questions</th>
		<th>Due Date</th>
	</tr>
<?php
	$query = "
		SELECT assignment_id, due FROM assignments, sections, sessions, questions, responses WHERE 
		sections.section_id = sessions.section_id AND
		sessions.session_id = questions.session_id AND
		questions.question_id = responses.question_id AND
		responses.student_id = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't prepare 'assignments' query. " . $conn->error);
	$stmt->bind_param("i", $student_id);
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
		$questioncount = mysqli_num_rows($result);
		
		$query = "
			SELECT DISTINCT onlineresponses.question_id FROM onlineresponses, assignmentstoquestions WHERE
			onlineresponses.student_id = ? AND
			onlineresponses.question_id = assignmentstoquestions.question_id AND
			assignmentstoquestions.assignment_id = ?;
		";
		
		$stmt = $conn->prepare($query) or die("Couldn't prepare 'responses' query. " . $conn->error);
		$stmt->bind_param("ii", $student_id, $assignment_id);
		$stmt->execute() or die("Couldn't execute 'responses' query. " . $conn->error);
		$stmt->store_result();
		$answercount = mysqli_stmt_num_rows($stmt);

		echo "
			<tr>
				<td><a href='viewassignment.php?assignment_id=$assignment_id'>$answercount</a></td>
				<td><a href='viewassignment.php?assignment_id=$assignment_id'>$questioncount</a></td>
				<td><a href='viewassignment.php?assignment_id=$assignment_id'>" . DateFromUTC($due) . "</a></td>
			</tr>
		";
		
		$stmt->close();
	}
?>
	</table>
<br>
<a href='editstudentinfo.php'>Edit Info</a>
<?php
	$conn->close();
	createFooter();
?>