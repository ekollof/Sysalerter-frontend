<?php echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>Sysalerter</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta http-equiv="refresh" content="120" />
<link rel="stylesheet" type="text/css" href="/style.css" />
</head>
<body>
<pre>
</pre>
<h1>Status overview</h1>
<table id="hostlist">
<th>System name</th><th>Monitoring</th><th># CPU</th><th>Users</th><th>last login</th><th>Del?</th>
<?php foreach ($hosts as $host): ?>
<tr id="host">
	<td   <?php $alerts[$host] ? print "id=\"broken\"" : print "id=\"fixed\""; ?> ><img src="/images/<?php echo $ostype[$host]?>.png" alt="<?php echo $ostype[$host]?>" /> <?php echo anchor("hostinfo/machine/$host", $host)?> </td>
	<td> 
		<?php foreach ($watches[$host] as $stat): ?>
		<?php $broken = $breakage[$host]; ?>
		<span <?php isset($broken[$stat]) ? print "id=\"broken\"" : print "id=\"fixed\""; ?>>
		<?php echo "<img src='/images/$stat.png' alt='$stat' width='32' height='32' />"; ?>
		</span>
		<?php endforeach; ?>
	</td>
	<td>
	<?php print $ncpu[$host]; ?>
	</td>
	<td>
		<?php !$users ? print "N/A" : print count($users[$host]);  ?>
	</td>
	<td id="fixedt">
		<?php
			foreach($lastlog[$host] as $llog) {
				print $llog['user']." @ ".$llog['lastlog']."<br/>\n";
			}
?>
	</td>
	<td>
		<span id="delete">
		<?php echo anchor("sysalert/deletehost/".$host, '[X]', array("id" => "delete")); ?>
		</span>
	</td>
</tr>
<?php endforeach; ?>
</table>
<pre>
<?php ?>
</pre>
</body>
</html>
