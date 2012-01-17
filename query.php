<?php
  if ($_POST['user'] && !empty($_POST['user'])) {
  	include("tcloud.class.php");
  	$cloud = new tCloud(false, 500);
  	echo $cloud->showCloud($_POST['user']);
  }
?>