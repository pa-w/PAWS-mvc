<!DOCTYPE html>
<html lang="es-MX">
	<head>
		<title>{$title}</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
		<script src="http://code.jquery.com/jquery-migrate-1.2.1.min.js"></script>
		<link rel="stylesheet" href="http://netdna.bootstrapcdn.com/bootstrap/3.0.1/css/bootstrap.min.css">
		<link rel="stylesheet" href="/css/style.css">
		<script src="http://getbootstrap.com/dist/js/bootstrap.js"></script>
	</head>
	<body>
		<div id="main">
			{include 'navigation.tpl'}
			<div id="content">
				<div class="container">
					{$content}
				</div>
			</div>
			<div class="footer">
				{include 'footer.tpl'}
			</div>
		</div>
	</body>
</html>

