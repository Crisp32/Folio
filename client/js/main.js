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

    // Search bar Functionality
    $("#user-search").keyup(function(e) {

        let term = $("#user-search").val();

        // Get Search Result from Server
        $.ajax({
            type: "POST",
            url: "../../utils/search.php",
            dataType: "json",
            data: {
                term: term
            },
            success: function(res) {
                // Display Results
                if (term != "" && term != null) {
                    let html = "";
                    let htmlEmpty = '<div class="res-empty" >Nothing to see Here</div>';
                    let index = 0;

                    for (let key in res) {
                        if (res[key].type === "user") {
                            if (index != res.length - 1) {
                                html += '<div class="res-item underline" ><a href="/profile.php?uquery=' + res[key].name + '" ><div class="bullet-point" >-&gt;</div> ' + res[key].name + ' (user)</a></div><img class="res-img" src="' + res[key].profileImage + '" >';
                            }
                            else {
                                html += '<div class="res-item" ><a href="/profile.php?uquery=' + res[key].name + '" ><div class="bullet-point" >-&gt;</div> ' + res[key].name + ' (user)</a></div><img class="res-img res-img-undl" src="' + res[key].profileImage + '" >';
                            }                       
                        }
                        index++;
                    }

                    if (html != null && html != "") {
                        $("#search-res").html(html);
                    }
                    else {
                        $("#search-res").html(htmlEmpty);
                    }
                }
                else {
                    $("#search-res").empty();
                }
            },
            error: function(err) {
                popUp("clientm-fail", "Server Request Error: " + err, null);
            }
        });

    });

    // Click Events
    this.document.addEventListener("click", function(e) {

        // Account Options
        if (document.getElementById("open-options") != null) {
            if (!document.getElementById("open-options").contains(e.target)) {
                if (!document.getElementById("acc-options").contains(e.target)) {
                    hideOptions();
                }
            }
        }
        

        // Search Bar
        if (document.getElementById("search-res") != null) {
            if (!document.getElementById("search-res").contains(e.target) && !document.getElementById("user-search").contains(e.target)) {
                $("#search-res").css("display", "none");
            }
            else {
                $("#search-res").css("display", "block");
            }
        }
    });

    // Run On Load from other Scripts
    if (window.location.pathname == "/profile.php") {
        triggerOnLoad();
    }
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

// Logout Function
function logout() {

    // Send Logout Request
    $.ajax({
        type: "POST",
        url: "../../utils/logout_user.php",
        dataType: "json",
        success: function(res) {
            location.reload();
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

// Account Options Hide/Show Functionality
function toggleOptions() {

    if ($(".account-options").css("display") == "none") {
        showOptions();
    }
    else {
        hideOptions();
    }

}

function showOptions() {
    $(".account-options").css("display", "block");
}

function hideOptions() {
    $(".account-options").css("display", "none");
}