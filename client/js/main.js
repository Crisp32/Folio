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
                                html += '<div class="res-item last-item" ><a href="/profile.php?uquery=' + res[key].name + '" ><div class="bullet-point" >-&gt;</div> ' + res[key].name + ' (user)</a></div><img style="transform: translate(10px, -50px)" class="res-img res-img-undl" src="' + res[key].profileImage + '" >';
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

    popUp("clientm-fail", "Loading...", null);

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

function logout_user() {
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

function toggleOptions() {
    if ($(".account-options").css("display") == "block") {
        $(".account-options").css("display", "none");
    }
    else {
        $(".account-options").css("display", "block");
    }
}

// Vote Button Click
function upVoteClick(sendReq) {
    if ($(".upvote").hasClass("upvote-selected")) {
        $(".upvote").removeClass("upvote-selected");
        $(".votes").css("color", "lightgrey");
    }
    else {
        $(".upvote").addClass("upvote-selected");
        $(".downvote").removeClass("downvote-selected");

        $(".votes").css("color", "#6a9aba");
    }

    if (sendReq) { voteUser(); } 
}

function downVoteClick(sendReq) {
    if ($(".downvote").hasClass("downvote-selected")) {
        $(".downvote").removeClass("downvote-selected");
        $(".votes").css("color", "lightgrey");
    }
    else {
        $(".downvote").addClass("downvote-selected");
        $(".upvote").removeClass("upvote-selected");

        $(".votes").css("color", "#c274c2");
    }

    if (sendReq) { voteUser(); } 
}

function voteUser() {

    // Get State of Buttons
    let upvote = $(".upvote").hasClass("upvote-selected");
    let downvote = $(".downvote").hasClass("downvote-selected");

    // Send Request to Server
    $.ajax({
        type: "POST",
        url: "../../utils/vote_user.php",
        dataType: "json",
        data: {
            upvote: upvote,
            downvote: downvote,
            target: $("#profile-name").text()
        },
        success: function(res) {
            if (res.error != null) {
                popUp("clientm-fail", res.error, null);

                $(".upvote").removeClass("upvote-selected");
                $(".downvote").removeClass("downvote-selected");

                $(".votes").css("color", "lightgrey");
            }
            else {
                $(".votes").text(res.votes);
            }
        },
        error: function(err) {
            popUp("clientm-fail", "Server Request Error: " + err, null);
        }
    });
}

// Settings Functions
function openSettings() {
    $("#settings-bg").css("display", "block");
    hideOptions();

    // Get User Settings
    $.ajax({
        type: "POST",
        url: "../../utils/user_settings.php",
        dataType: "json",
        success: function(res) {

            // Display Success/Error to user
            if (res.success) {

                let loc = res.location;
                if (loc == "Unknown") {
                    loc = null;
                }

                let imgURL = res.image;
                if (!imgURL.includes("http")) {
                    imgURL = "";
                }

                // Load User Data
                $("#profile-img-select").attr("src", res.image);
                $("#prof-img-url").attr("value", imgURL);
                $("#bio-textarea").text(res.bio);
                $(".account-loc-setting").val(loc);
                $("#allowComments").val(res.comments);
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

function closeSettings() {
    location.reload();
}

function saveSettings() {
    let imgURL = $("#prof-img-url").val();
    let bio = $("#bio-textarea").val();
    let location = $("#location").val();
    let allowComments = $("#allowComments").val();

    // Display Loading Popup
    popUp("clientm-fail", "Loading...", null);

    // Client End Validation
    if (imgURL.length > 150) {
        popUp("clientm-fail", "Image URL cannot exceed 150 Characters", null);
    }
    else if (bio.length > 300) {
        popUp("clientm-fail", "Bio cannot exceed 300 Characters", null);
    }
    else if (bio.length == 0) {
        popUp("clientm-fail", "Bio must be more than 0 Characters", null);
    }
    else if (location.length > 30) {
        popUp("clientm-fail", "There is a Country with a Name that long?", null);
    }
    else {
        // Send Request
        $.ajax({
            type: "POST",
            url: "../../utils/save_settings.php",
            dataType: "json",
            data: {
                image: imgURL,
                bio: bio,
                location: location,
                comments: allowComments
            },
            success: function(res) {
                
                // Display Success/Error to user
                if (res.success) {
                    popUp("clientm-success", "Saved!", null);

                    $("#profile-img-select").attr("src", res.imgURL);
                }
                else {
                    popUp("clientm-fail", res.message, null);
                } 
            },
            error: function(err) {
                popUp("clientm-fail", "Failed to Save your Settings", null);
            }
        });
    }
}

// Profile Comment Functionality
function addComment() {
    let comment = $(".add-comment").val();
    let profile = $("#profile-name").text();

    // Client Side Validation
    if (comment.length > 120) {
        popUp("clientm-fail", "Comment Must be Less than 120 Characters", null);
    }
    else if (comment.length == 0) {
        popUp("clientm-fail", "Comment Must be Greater than 0 Characters", null);
    }
    else {

        // Send Request
        $.ajax({
            type: "POST",
            url: "../../utils/add_comment.php",
            dataType: "json",
            data: {
                type: "profile",
                profile: profile,
                content: comment
            },
            success: function(res) {
                
                // Display Success/Error to user
                if (res.success) {
                    loadComments(res.comment);
                    popUp("clientm-success", "Posted Comment!", null);
                    $(".comments-empty.res-empty").css("display", "none");
                    $(".add-comment").val("");
                }
                else {
                    popUp("clientm-fail", res.message, null);
                } 
            },
            error: function(err) {
                popUp("clientm-fail", "Failed to Contact Server", null);
            }
        });
    }
}