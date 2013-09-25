<?php
require_once("pageutils.php");
require_once("dbutils.php");

$section_id=$_GET['section_id'];
if (!isset($section_id)) {
	endOutput("0");
}

$conn = connect();

$query="
SELECT sessions.session_tag
FROM sessions, sections
WHERE sessions.section_id=sections.section_id
AND sessions.section_id=?
";

$stmt = $conn->prepare($query) or die("Couldn't prepare query. " . $conn->error);
$stmt->bind_param("i", $section_id);
$stmt->execute() or die("Couldn't execute query. " . $conn->error);
$stmt->bind_result($session_tag);
while ($stmt->fetch()) {
	echo "$session_tag\n";
}
$conn->close();
?>