/**
 * Folio Profile Manager Front End
 * Connell Reffo 2019
 */

function triggerOnLoad() {

    // Load Profile Data into Page
    loadProfile(profile);
}

function loadProfile(username) {

    // Send Request to Server
    $.ajax({
        type: "POST",
        url: "../../utils/user_profile.php",
        dataType: "json",
        data: {
            query: username
        },
        success: function(res) {
            // Display Success/Error to user
            if (res.success) {

                // Load Requested data into Page
                $("#profile-img").attr("src", res.image);
                $("#profile-name").text(res.username);
                $("#bio").text(res.bio);
                $("#profile-location").text(res.location);
            }
            else {
                popUp("clientm-fail", res.message, null);
                loadErrorProfile();
            } 
        },
        error: function(err) {
            popUp("clientm-fail", "Server Request Error: " + err, null);
            loadErrorProfile();
        }
    });

}

function loadErrorProfile() {

    // Load Backup Profile Image
    $("#profile-img").attr("src", "https://encrypted-tbn0.gstatic.com/images?q=tbn%3AANd9GcS6r3ITg2jF5uliPX_sh5cHmGaA7S0Yhn59WRnaE26S14czvpa2");
}