<?php
	require_once("pageutils.php");
	require_once("dbutils.php");
	require_once("loginutils.php");
	$conn = connect();
	
	if (!isCookieValidLoginWithType($conn, "student")) {
		header("Location: home.php");
	}
	
	createHeader("Edit Info");
?>
<script type="text/javascript">
	jQuery(function(){
		$("#changePassword").click(function(){
			$(".error").hide();
			var hasError = false;
			var passwordVal = $("#newpassword").val();
			var checkVal = $("#password-check").val();
			if (passwordVal == '') {
				$("#newpassword").after('<span class="error">Please enter a password.</span>');
				hasError = true;
			} else if (checkVal == '') {
				$("#password-check").after('<span class="error">Please re-enter your password.</span>');
				hasError = true;
			} else if (passwordVal != checkVal ) {
				$("#password-check").after('<span class="error">Passwords do not match.</span>');
				hasError = true;
			}
			if(hasError == true) {return false;}
		});
	});
</script>
<?php
	$query = "
		SELECT student_id, school_id, email FROM students WHERE
		username = ? AND
		password = ?;
	";
	
	$stmt = $conn->prepare($query) or die("Couldn't execute 'student_id' query. " . $conn->error);
	$stmt->bind_param("ss", $_COOKIE["Username"], $_COOKIE["Password"]);
	$stmt->execute() or die("Couldn't execute 'student_id' query. " . $conn->error);
	
	$stmt->bind_result($student_id, $school_id, $email);
	$stmt->fetch();
	$stmt->close();
	
	// $result = $stmt->get_result();
	// $row = $result->fetch_array(MYSQLI_ASSOC);
	
	// $student_id = $row["student_id"];
	// $email = $row["email"];
?>
<h2>Update Information</h2>
<form action="endstudentedit.php" method="post">
	School ID: <input type='text' name='school_id' value=<?php echo $school_id; ?>><br>
	Email: <input type='text' name='email' value=<?php echo $email; ?>><br>
	<input type='submit' value='Update'>
</form>
<h2>Change Password</h2>
<form action="changepassword.php" method="post">
	Old Password: <input type='password' name='oldpassword'><br>
	New Password: <input type='password' name='newpassword' id='newpassword'><br>
	Verify Password: <input type='password' name='password-check' id='password-check'><br>
	<input type='submit' value='Submit'>
</form>
<?php
	$conn->close();
	createFooter();
?>