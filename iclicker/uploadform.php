<?php
require_once("pageutils.php");
require_once("dbutils.php");
require_once("loginutils.php");

$section_id=$_GET['section_id'];
if (!isset($section_id)) {
	endOutput("The section_id must be set as a GET parameter so we know which course and section to upload new sessions to.<br>Note that many courses only have one section.");
}

$conn = connect();
checkAdmin($conn);

list($course_id, $course_name, $course_number) = lookupCourseBySectionId($conn, $section_id);

$conn->close();

createHeader("Upload New Session or Sessions");
?>
<div>

Uploading new session or sessions for <?= $course_name ?> with course_id <?= $course_id ?>
</div>

<div>
	<form action="processupload.php" method="post" enctype="multipart/form-data">
	<input type="hidden" name="section_id" value="<?= $section_id ?>"/>
	<p>
	<label>Filename:</label>
	<input name="file" type="file">
	</p>
	<p>
	<input type="submit" value="Submit">
	</p>
	</form>
</div>
<?php

createFooter();
?>