<div id="top-bar" >
	<h1 style="float: left" class="header" >Folio</h1>

	<div style="float: left" >
		<input type="text" class="input-field" id="user-search" placeholder="Search Folio" />

		<div id="search-res" >
		</div>
	</div>
	
	<div id="top-buttons" >
		<?php

		session_start();

		if (isset($_SESSION["user"])) {
			$user = $_SESSION["user"];

			require("_user-info-bar.php");
		}
		else {
			if ($_SERVER['REQUEST_URI'] != "/login.php") {
				echo '<button class="standard-button inl" onclick="location.replace(\'/login.php\')" >Login</button>';
			}
	
			if ($_SERVER['REQUEST_URI'] != "/register.php" && $_SERVER['REQUEST_URI'] != "/login.php") {
				echo '<button class="standard-button inl" onclick="location.replace(\'/register.php\')" >Register</button>';
			}
		}

		?>
	</div>
</div>