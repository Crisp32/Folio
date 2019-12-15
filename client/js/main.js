/**
 * Folio Main Javascript File
 * Connell Reffo 2019
 */

// Display loading until page loads
window.onload = function() {
    $("#content").css("display", "block");
    $("#loading-info").css("display", "none");
}

// Sign Up Request
function register() {
    let email = $("#email").val();
    let location = $("#location").val();
    let username = $("#username").val();
    let password = $("#pass").val();
    let confPass = $("#conf-pass").val();

    $.ajax({
        type: "POST",
        url: "../../utils/register_user.php",
        dataType: "json",
        data: {
            email: email,
            location: location,
            username: username,
            password: password,
            confPass: confPass
        },
        success: function(res) {
            // Display Success/Error to user
            if (res.success) {
                popUp("clientm-success", res.message);
                
                // Prompt User for Verification Code
                if (res.verify) {
                    verifyPage();
                }
            }
            else {
                popUp("clientm-fail", res.message);
            }
        },
        error: function(err) {
            popUp("clientm-fail", "Server Request Error: " + err);
        }
    });
}

function resendVerification() {

    // Get User Input
    let user = $("#resend-to").val();

    // Send Request
    $.ajax({
        type: "POST",
        url: "../../utils/resend_code.php",
        dataType: "json",
        data: {
            uname: user
        },
        success: function(res) {
            // Display Success/Error to user
            if (res.success) {
                popUp("clientm-success", res.message);
            }
            else {
                popUp("clientm-fail", res.message);
            }
        },
        error: function(err) {
            popUp("clientm-fail", "Server Request Error: " + err);
        }
    });
}

// Final Verification Step for registration
function verifyAccount() {

    // Get User Input
    let user = $("#resend-to").val();
    let code = $("#vcode").val();

    // Send Request
    $.ajax({
        type: "POST",
        url: "../../utils/verify_user.php",
        dataType: "json",
        data: {
            uname: user,
            code: code
        },
        success: function(res) {
            // Display Success/Error to user
            if (res.success) {
                popUp("clientm-success", res.message);
            }
            else {
                popUp("clientm-fail", res.message);
            }
        },
        error: function(err) {
            popUp("clientm-fail", "Server Request Error: " + err);
        }
    });
}

// Load Verification Prompt
function verifyPage() {
    $("#reg-form").load("../../partials/_verify-code.php .verify-code");
}

// Login Function
function login() {

    // Get User Input
    let username = $("#username").val();
    let password = $("#pass").val();

    // Send Request
    $.ajax({
        type: "POST",
        url: "../../utils/login_user.php",
        dataType: "json",
        data: {
            username: username,
            password: password
        },
        success: function(res) {
            // Display Success/Error to user
            if (res.success) {
                popUp("clientm-success", res.message);
            }
            else {
                popUp("clientm-fail", res.message);
            }
        },
        error: function(err) {
            popUp("clientm-fail", "Server Request Error: " + err);
        }
    });
}

// Server to Client message/error box
function popUp(cssClass, content) {

    let element = $("div.clientm");
    let elementChild = element.children();

    elementChild.removeClass(elementChild.attr("class"));
    elementChild.addClass(cssClass);
    elementChild.text(content);

    element.css("transform", "translate(0, 0)");
}

function popDown() {
    let element = $("div.clientm");

    element.css("transform", "translate(0, 200px)");
}