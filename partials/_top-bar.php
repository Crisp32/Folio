<?php

session_start();
include_once $_SERVER["DOCUMENT_ROOT"] . "/utils/app_main.php";

if (validateSession($_SESSION["user"])) {
	$user = $_SESSION["user"];
}

?>

<!--User Inbox Modal-->
<div id="inbox-modal" class="modal-bg" >
	<div class="modal-content" >
		<h2 class="modal-title" >Notification Inbox</h2>

		<button class="close-search-btn close-user-posts" onclick="closeModal()">Close</button>
		<button class="close-search-btn close-user-posts delete-all" onclick="deleteAllNotifs()">Delete All</button>

		<div id="notifications-container" >
			<div class="notification-wrapper" ><div class="res-empty notifs-empty" >Loading Inbox...</div></div>
		</div>
	</div>
</div>

<!--User Settings Menu-->
<div id="settings-bg" >
	<div id="settings-menu" >
		<div id="settings-load-screen" >Loading Profile...<br /><img src="../images/other/folioLogoWhite.svg" ></div>
		<div id="settings-load" >
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
				<select type="text" class="input-field dropdown account-loc-setting" id="location-setting" >
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

			<div class="p-header settings-option-header" >Email Address<br />
				<div class="settings-email" >example@example.ca</div>
			</div><br />

			<div class="p-header settings-option-header" >Change Password<br />
				<input type="password" class="input-field" id="old-pass" placeholder="Old Password" />
				<input style="margin-top: 25px" type="password" class="input-field" id="new-pass" placeholder="New Password" />
				<input type="password" class="input-field" id="conf-new-pass" placeholder="Confirm New Password" />
				<button class="standard-button confirm-pass-change" onclick="changePass()" >Confirm</button>
			</div><br />

			<div class="p-header settings-option-header" >Danger<br />
				<input type="text" class="input-field del-account-code" placeholder="Verification Code" />
				<button class="member-option-default member-option-red del-account" onclick="deleteAccount()" >Delete Account</button>
			</div><br />
		</div>
	</div>
</div>

<!--Search Menu Modal-->
<div id="search-menu" class="modal-bg" >
	<div class="modal-content" >
		<h2 class="modal-title" >Search Folio</h2>

		<input id="user-search" class="input-field" placeholder="Search Term" />
		<select type="text" class="input-field search-filter" >
			<option default value="all" >Filter: All</option>
			<option default value="users" >Filter: Users</option>
			<option default value="forums" >Filter: Forums</option>
        </select>
		<button id="search-button" onclick="search()" >Search</button>

		<div style="text-align: center" ><div class="search-res-empty res-empty" >Nothing to See Here</div><div id="search-res" ></div></div>
		<button class="close-search-btn" onclick="closeSearchMenu()" >Close</button>
	</div>
</div>

<!--Top Options Bar-->
<div id="top-bar" >
	<a href="/index.php" ><img title="Folio - Home" id="logo-img-small" src="/images/other/folioLogoSmall.svg" ><img title="Folio - Home" id="logo-img" src="/images/other/folioLogoWhite.svg" ></a>

	<div style="float: left" >
		<button id="open-search" onclick="openSearchMenu()" ><div class="bullet-point">-&gt;</div> Search <div class="bullet-point">&lt;-</div></button>
	</div>
	
	<div id="top-buttons" >
		<?php

		if (isset($user)) {
			require("_user-info-bar.php");
		}
		else {
			if ($_SERVER["REQUEST_URI"] != "/login.php") {
				echo '<button class="standard-button inl top-bar-btns" onclick="location.replace(\'/login.php\')" >Login</button>';
			}
	
			if ($_SERVER["REQUEST_URI"] != "/register.php" && $_SERVER['REQUEST_URI'] != "/login.php") {
				echo '<button class="standard-button inl top-bar-btns" onclick="location.replace(\'/register.php\')" >Register</button>';
			}

			echo '
			<div class="profile-bar-container" id="open-basic-options" >
				<div style="float: left; margin-right: -15px" class="top-bar-btns-menu" >
					<img src="/images/other/options-icon.svg" class="profile-image-bar" />
					<button class="standard-button inl profile-username-btn" onclick="toggleOptions()" ></button>
				</div>

				<div class="account-options" id="not-logged-in-options" >
					<a class="skip-reg" href="/login.php" ><div class="bullet-point" >-&gt;</div><div class="account-option" > Login</div></a><br />
					<a class="skip-reg" href="/register.php" ><div class="bullet-point" >-&gt;</div><div class="account-option" > Register</div></a>

					<br/><button class="close-options standard-button" onclick="toggleOptions()" >Close</button>
				</div>
			</div>
			';
		}

		?>
	</div>
</div>