<!DOCTYPE html>
<html>

<head>
	<title>
		Welcome to College management System</title>
</head>
<h4><a href="index.php" style="float: left;margin-left: 50px; margin-top:5px;font-size: 20px;color: red;">Back </a></h4>
<form method="POST" action="page.php">

	<body bgcolor="#DAF7A6">
		<h1 align="Center">Welcome to College management System</h1>
		<table style="width: 35%" align="Center" border="1">
			<tr>
				<td colspan="2" align="Center">Student Information</td>
			</tr>
			<tr>
				<td align="left">Enter Class</td>
				<td><input type="number" name=" class" required></td>

			</tr>
			<tr>
				<td align="left">Enter Rollno</td>
				<td><input type="number" name=" rollno" required></td>
			</tr>
			<tr>
				<td colspan="2" align="Center"><input type="Submit" name="Submit" value="Show Details"></td>
			</tr>
		</table>
</form>

</body>

</html>
<?php
if (isset($_POST['Submit'])) {
	$CLASS = $_POST['class'];
	$ROLLNO = $_POST['rollno'];
	include('dbconnect.php');
	include('function.php');
	showdetails($CLASS, $ROLLNO);
}
?>