<?php echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" href="/style.css" />
  <title>Sysalerter</title>
</head>
<body>
<a href="<?php echo base_url() ?>">Go back</a>
<h1>Information for <?php echo "$host ($ncpu CPUS) ($ipnum)" ?></h1>

<p>
Last seen: <?php print $seen ?> (HH:MM:SS)
</p>
<p>
Wiki link: <?php 
	$link = "http://wiki.otlupc.net/index.php/$host";
	print anchor($link, $link);
?>
</p>
<?php if (isset($userinfo)) { ?>
<h2>Active users:</h2>
<table id="hostlist">
<th>username</th><th>terminal</th><th>last logon</th>
<?php foreach ($userinfo as $user): ?>
<tr>
	<td><?php print $user["username"] ?></td>
	<td><?php print $user["pty"] ?></td>
	<td><?php print $user["lastlogin"] ?></td>	
</tr>
<?php endforeach; 
}
?>
</table>
<br/>
<h2>Host graphs:</h2>
<div>
<?php
$dropdown = array(
	"hour" => "Hour",
	"day" => "Day",
	"week" => "Week",
	"month" => "Month",
	"year" => "Year",
	);
echo form_open("hostinfo/machine/$host");
echo form_dropdown("period", $dropdown, $period);
echo form_submit("sub", "go");
echo form_close();
?>
</div>
<br />
<?php foreach ($items as $item): ?>
<table id="hostlist">
<th><?php echo $item?></th>
<tr id="host">
<?php

if ($item == "disk") {
	foreach ($disks as $disk) {
		print "<td><img src=\"../../../draw.php?hostname=$host&status=$item&disk=$disk&period=$period\" /></td>";
	}
} else {
		print "<td><img src=\"../../../draw.php?hostname=$host&status=$item&period=$period\" /></td>";
}
?>
</tr>
</table>
<?php endforeach;?>
</p>
<a href="<?php echo base_url() ?>">Go back</a>
</body>
</html>
