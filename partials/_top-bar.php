<?php include_once "utils/app_main.php"; ?>

<!--User Settings Menu-->
<div id="settings-bg" >
	<div id="settings-menu" >
		<img id="profile-img-select" src="https://www.macmillandictionary.com/external/slideshow/full/White_full.png" >
		<div id="settings-btns-container" >
			<button class="standard-button close-btn" onclick="closeSettings()" >Close</button>
			<button class="standard-button save-btn" onclick="saveSettings()" >Save</button>
		</div>
		<div class="p-header settings-option-header" >Profile Image URL
			<input type="text" class="input-field" id="prof-img-url" placeholder="Custom Profile Image URL" />
		</div><br />

		<div class="p-header settings-option-header" >Edit Bio
			<textarea type="text" class="input-field" id="bio-textarea" ></textarea>
		</div><br />

		<div class="p-header settings-option-header" >Account Location
			<select type="text" class="input-field dropdown account-loc-setting" id="location" >
            	<option default value="" >Account Location</option>
            	<?php echo fetchLocationsHtml(); ?>
            </select>
		</div><br />

		<div class="p-header settings-option-header" >Allow Comments<br />
			<select type="text" class="input-field dropdown" id="allowComments" style="margin-bottom: 35px" >
            	<option default value="1" >Yes</option>
            	<option value="0" >No</option>
            </select>
		</div><br />
	</div>
</div>

<!--Top Options Bar-->
<div id="top-bar" >
	<a href="/index.php" ><img title="Folio - Home" id="logo-img" src="/images/other/folioLogoWhite.svg" ></a>

	<div style="float: left" >
		<?php
			session_start();
			$extraCSS = " searchbar-short";

			if (isset($_SESSION["user"])) {
				$extraCSS = " searchbar-long";
			}
		?>
		<input type="text" class="input-field<?php echo $extraCSS; ?>" id="user-search" placeholder="Search Folio" />

		<div id="search-res" >
		</div>
	</div>
	
	<div id="top-buttons" >
		<?php

		if (isset($_SESSION["user"])) {
			require("_user-info-bar.php");
		}
		else {
			if ($_SERVER['REQUEST_URI'] != "/login.php") {
				echo '<button class="standard-button inl top-bar-btns" onclick="location.replace(\'/login.php\')" >Login</button>';
			}
	
			if ($_SERVER['REQUEST_URI'] != "/register.php" && $_SERVER['REQUEST_URI'] != "/login.php") {
				echo '<button class="standard-button inl top-bar-btns" onclick="location.replace(\'/register.php\')" >Register</button>';
			}
		}

		?>
	</div>
</div>