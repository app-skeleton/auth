<?php defined('SYSPATH') or die('No direct script access.'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Recover your password</title>
<body>
<p>Hello <?php echo $name; ?>,</p>
<p>You have requested to reset your password for<br />
App name at <a href="<?php echo URL::base(); ?>"><?php echo URL::base(); ?></a></p>
<p>Email:    <?php echo $email; ?></p>
<p>Copy this link into your browser to reset your password:</p>
<p><a href="<?php echo $url; ?>"><?php echo $url; ?></a></p>
<p>If you did not request to reset your password, please disregard this email.</p>
</body>
</html>