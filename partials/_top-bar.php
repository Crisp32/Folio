<div id="top-bar" >
	<h1 class="header" >Folio</h1>
	<div class="side-logo" >
		<div class="ver-txt" ><div class="bullet-point" >{</div>  NA | Beta <div class="bullet-point" >}</div> </div><br />
		<div class="dev-name" >By Connell Reffo</div>
	</div>

	<div id="top-buttons" >
		<?php

		if ($_SERVER['REQUEST_URI'] != "/login.php") {
			echo '<button class="standard-button inl" onclick="location.replace(\'/login.php\')" >Login</button>';
		}

		if ($_SERVER['REQUEST_URI'] != "/register.php") {
			echo '<button class="standard-button inl" onclick="location.replace(\'/register.php\')" >Register</button>';
		}

		?>
	</div>
</div>