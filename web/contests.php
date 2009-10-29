<?php
include("inc/db.inc.php");

session_start();
if(!session_is_registered("smacoid")) header("Location: index.php?msg=require");
?>
<html>
<head>
	<title>sudo make a contest</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
	<link rel="stylesheet" type="text/css" href="style.css" />
</head>
<body class="content">
	<div id="header">
		<h1>sudo make a contest</h1>
<?php
include("tabs.php");
?>
	</div>
	<div id="content">
		<p>Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vestibulum et pharetra velit. Nam commodo laoreet lectus, in hendrerit nisl iaculis sed. In posuere, metus et aliquam imperdiet, quam odio sodales risus, a tempus dui nulla in leo. Nulla iaculis lacus risus. Fusce varius porttitor lectus, sed ornare eros aliquam at. Aliquam sed mauris eget dui facilisis tempor. Aenean sodales ante magna, et lacinia metus. Aliquam odio ante, faucibus nec porta a, imperdiet nec felis. Maecenas posuere, diam at mattis congue, urna purus hendrerit ante, ac sodales augue lacus quis mi. Fusce pharetra sem sit amet ante mattis et congue ipsum varius. Nullam nec porta eros.</p>
	</div>
</body>
</html>
