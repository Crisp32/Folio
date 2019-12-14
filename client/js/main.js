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
            console.log("Error: " + err);
        }
    });
}

function verifyPage() {
    $("#reg-form").load("../../partials/_verify-code.php .verify-code");
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