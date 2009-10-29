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
		<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Morbi porta lacinia dictum. Etiam commodo egestas ipsum, sit amet consequat lorem pharetra a. Sed ut leo ipsum, in luctus tellus. Ut velit velit, eleifend at egestas ac, pretium at sem. Mauris facilisis, tortor id volutpat pharetra, odio mi interdum arcu, vitae imperdiet magna lorem vitae odio. Curabitur vel porta massa. Pellentesque lorem magna, suscipit eget hendrerit sed, molestie et tellus. Donec rutrum pulvinar bibendum. Ut nunc justo, cursus quis viverra ac, ornare id elit. Vestibulum tincidunt placerat pellentesque. Fusce sem dui, venenatis et egestas in, commodo id diam. Pellentesque blandit gravida libero ut ultrices. Ut eleifend viverra augue, vel vestibulum sapien varius eu.</p>

		<p>Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Vestibulum et pharetra velit. Nam commodo laoreet lectus, in hendrerit nisl iaculis sed. In posuere, metus et aliquam imperdiet, quam odio sodales risus, a tempus dui nulla in leo. Nulla iaculis lacus risus. Fusce varius porttitor lectus, sed ornare eros aliquam at. Aliquam sed mauris eget dui facilisis tempor. Aenean sodales ante magna, et lacinia metus. Aliquam odio ante, faucibus nec porta a, imperdiet nec felis. Maecenas posuere, diam at mattis congue, urna purus hendrerit ante, ac sodales augue lacus quis mi. Fusce pharetra sem sit amet ante mattis et congue ipsum varius. Nullam nec porta eros.</p>

		<p>Proin ac ullamcorper risus. Sed auctor posuere urna sed aliquet. Praesent egestas metus tempus leo sagittis blandit. Vestibulum massa turpis, dapibus eget tincidunt pellentesque, pharetra eget neque. Donec urna tortor, hendrerit in accumsan sit amet, bibendum eget urna. In nec erat vehicula dui iaculis aliquam. Cras euismod pretium erat id vulputate. Vivamus tortor arcu, pharetra in vehicula eu, commodo et enim. Morbi pharetra, libero sed volutpat sagittis, felis mi fermentum risus, in fringilla libero velit eget tellus. Sed nec congue augue. Morbi neque ipsum, euismod vel consectetur vel, interdum non massa. Maecenas lacinia diam eget elit blandit ultrices. Praesent auctor diam in dui adipiscing eleifend et at diam. Proin convallis condimentum dolor, in consectetur massa convallis at. Duis sed elit quis nisi iaculis accumsan feugiat non nunc.</p>

		<p>Ut nec nisi metus. Praesent convallis sollicitudin massa, sed pharetra mauris fermentum a. Donec ornare tempor massa, nec facilisis nisl luctus eu. Praesent non lorem ipsum, eu accumsan lacus. Ut semper nisl sit amet orci dignissim a venenatis quam mollis. Duis laoreet consectetur metus non sodales. Proin quis est tortor, non congue massa. Cras vulputate euismod felis, eget elementum quam tristique id. Proin mattis commodo velit, vel sagittis nisi consequat sed. Donec et facilisis libero. Nam blandit neque eget tellus pellentesque fermentum. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus luctus risus ut metus vulputate eget aliquet felis cursus.</p>

		<p>Curabitur neque metus, malesuada sit amet placerat ut, adipiscing ut felis. Sed consequat, tellus at scelerisque convallis, lacus erat semper magna, quis faucibus ipsum ante nec urna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur ante erat, laoreet at congue et, hendrerit molestie felis. Fusce mauris risus, tincidunt nec fringilla a, ultricies a lacus. Vestibulum posuere justo in dui auctor luctus. Quisque nec pulvinar mauris. Fusce fermentum mi et tortor mattis rhoncus. Praesent dui quam, pellentesque egestas mollis vitae, aliquam ullamcorper nibh. Praesent sed metus metus, eu malesuada urna. Vestibulum ac eros enim, vel malesuada lectus.</p>
	</div>
</body>
</html>
