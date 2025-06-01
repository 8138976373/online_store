<?php
function showdetails($CLASS, $ROLLNO)
{
	include('dbconnect.php');
	$sql = "SELECT * FROM student WHERE rollno='$ROLLNO' and class='$CLASS'";
	$result = mysqli_query($db, $sql);
	if (mysqli_num_rows($result) > 0) {
		$result = mysqli_fetch_assoc($result);
?>
		<table border="1">
			<tr>
				<th colspan="3">
					<h4>Student Details<h4>
				</th>
			</tr>
			<tr>
				<td rowspan="5"><img src="dataimg/<?php echo $result['image']; ?>" style="max-height: 300px;max-width: 500px; height: auto;" /></td>
				<th> Full Name</th>
				<td><?php echo $result['name']; ?></td>
			</tr>
			<tr>
				<th> Class</th>
				<td><?php echo $result['class']; ?></td>
			</tr>
			<tr>
				<th> Roll No</th>
				<td><?php echo $result['rollno']; ?></td>
			</tr>
			<tr>
				<th> Parents Contact</th>
				<td><?php echo $result['pcon']; ?></td>

			</tr>
			<tr>
				<th> City</th>
				<td><?php echo $result['city']; ?></td>
			</tr>
		</table>
	<?php
	} else {
	?>
		<script>
			alert('no data found');
		</script>
<?php
	}
}
?>