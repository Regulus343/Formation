<!DOCTYPE html>
<html lang="en-US">
	<head>
		<title>Formation Examples</title>

		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">

		<script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>

		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>

		<style type="text/css">
			#content { margin-top: 64px; padding-bottom: 76px; }

			fieldset { margin-bottom: 2rem; padding: 0.75rem; border: 1px solid #ddd; }
			legend { display: inline; width: auto; background: none; font-size: 1.2rem; }

			.checkbox label { margin-bottom: 0; }

			pre { margin: 0px 0 8px 0; font-size: 0.8rem; tab-size: 4; -moz-tab-size: 4; -o-tab-size: 4; }
			code { padding: 0; }
			pre code { color: #025aa5; }

			.item-info { margin-bottom: 18px; padding: 0 6px; font-size: 0.75rem; }

			.form-group-dark { padding: 0.3rem; background-color: #222; color: #fff; border-radius: 6px; }
			.red-bordered { border: 1px solid #900; }
		</style>
	</head>
	<body>
		<nav class="navbar navbar-toggleable-md navbar-inverse fixed-top bg-inverse">
			<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false" aria-label="Toggle navigation">
				<span class="navbar-toggler-icon"></span>
			</button>

			<a class="navbar-brand" href="#">Formation</a>

			<div class="collapse navbar-collapse" id="navbar">
				<ul class="navbar-nav mr-auto">
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="http://example.com" id="dropdown01" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Jump to a Section</a>
						<div class="dropdown-menu" aria-labelledby="dropdown01">
							<a class="dropdown-item" href="#form-elements-text">Texts / Textareas / Passwords</a>
							<a class="dropdown-item" href="#form-elements-select-checkbox-radio">Selects / Checkboxes / Radio Buttons</a>
						</div>
					</li>
				</ul>
			</div>
		</nav>

		<div class="container" id="content">

			@yield('content')

		</div><!-- /#content -->
	</body>
</html>