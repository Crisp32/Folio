/**
 * Folio Main Javascript File
 * Connell Reffo 2019
 */

// Display loading until page loads
window.onload = function() {
    $("#content").css("display", "block");
    $("#loading-info").css("display", "none");

    // Login on Enter Clicked
    $("#login-pass").keypress(function(e) {
        if(e.which == 13) {
            login();
        }
    });
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
                popUp("clientm-success", res.message, null);
                
                // Prompt User for Verification Code
                if (res.verify) {
                    verifyPage();
                }
            }
            else {
                popUp("clientm-fail", res.message, null);
            }
        },
        error: function(err) {
            popUp("clientm-fail", "Server Request Error: " + err, null);
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
                popUp("clientm-success", res.message, null);
            }
            else {
                popUp("clientm-fail", res.message, null);
            }
        },
        error: function(err) {
            popUp("clientm-fail", "Server Request Error: " + err, null);
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

                // Prompt user to go to login page when Verified
                if (res.redirect) {
                    popUp("clientm-success", res.message + ". Click Here to Login", "../../login.php");
                }
                else {
                    popUp("clientm-success", res.message, null);
                }
            }
            else {
                popUp("clientm-fail", res.message, null);
            }
        },
        error: function(err) {
            popUp("clientm-fail", "Server Request Error: " + err, null);
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
    let password = $("#login-pass").val();

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
                popUp("clientm-success", res.message + ". Click Here to go to Home Page", "../../index.php");
            }
            else {
                popUp("clientm-fail", res.message, null);
            }
        },
        error: function(err) {
            popUp("clientm-fail", "Server Request Error: " + err, null);
        }
    });
}

// Server to Client message/error box
function popUp(cssClass, content, onclickHyperlink) {

    let element = $("div.clientm");
    let elementChild = element.children();

    if (onclickHyperlink != null && onclickHyperlink != "") {
        elementChild.attr("onclick", "location.replace('" + onclickHyperlink + "')");
    }
    else {
        elementChild.attr("onclick", "popDown()");
    }
    
    elementChild.removeClass(elementChild.attr("class"));
    elementChild.addClass(cssClass);
    elementChild.text(content);

    element.css("transform", "translate(0, 0)");
}

function popDown() {
    let element = $("div.clientm");

    element.css("transform", "translate(0, 200px)");
}