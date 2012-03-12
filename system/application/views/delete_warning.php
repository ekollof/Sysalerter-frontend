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
<h1>Warning, this action is irreversible!</h1>
<p>
<?php echo anchor("sysalert/reallydeletehost/$host", "Clicking this link will delete $host from the database"); ?>
</p>
</body>
</html>