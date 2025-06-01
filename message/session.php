<?php

if (isset($_SESSION['success']) && $_SESSION['success'] != '') {
	echo '<p  class="alert alert-success" align="center">' . $_SESSION['success'] . '</p>';
	unset($_SESSION['success']);
}
