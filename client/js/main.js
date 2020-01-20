/**
 * Folio Main Javascript File
 * Connell Reffo 2019
 */

// Global Variables
let saved = false;

// Display loading until page loads
window.onload = function() {

    // Login on Enter Clicked
    $("#login-pass").keypress(function(e) {
        if(e.which == 13) {
            login();
        }
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
    });

    // Run On Load from other Scripts
    let pathname = window.location.pathname;
    if (pathname == "/profile.php" || pathname == "/forum.php") {
        triggerOnLoad();
    }

    // Comment Liking
    $(document).on("click", "button.likes-icon", function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();

        let element = $(this).find("img");
        let cid = $(element).parent().siblings(".del-comment").attr("name");

        $(element).parent().attr("disabled", true);

        // Send Request
        $.ajax({
            type: "POST",
            url: "../../utils/like_comment.php",
            dataType: "json",
            data: {
                cid: cid
            },
            success: function(res) {
                if (res.success) {

                    // Display Like Count
                    $(element).parent().siblings(".likes-count").text(res.likes);
                    
                    if (res.liked) {
                        $(element).parent().siblings(".likes-count").addClass("liked");
                        $(element).parent().addClass("like-icon-selected");
                    }
                    else {
                        $(element).parent().siblings(".likes-count").removeClass("liked");
                        $(element).parent().removeClass("like-icon-selected");
                    }

                    $(element).parent().attr("disabled", false);
                }
                else {
                    popUp("clientm-fail", res.message, null);
                }
            },
            error: function(err) {
                popUp("clientm-fail", "Failed to Contact Server", null);
            }
        });
    });

    // Reply to Comment
    $(document).on("click", ".post-reply-btn", function (e) {
        let replyContent = $(this).siblings(".add-comment").val();
        let commentCID = $(this).parent().siblings(".comment").find(".delete-comment").attr("name");

        let element = $(this);

        // Client Side Validation
        if (replyContent.length > 120) {
            popUp("clientm-fail", "Reply must not Exceed 120 Characters", null);
        }
        else if (replyContent.length == 0) {
            popUp("clientm-fail", "Reply must be Greater than 0 Characters", null);
        }
        else {
            $(element).siblings(".add-comment").val("");

            // Send Request
            $.ajax({
                type: "POST",
                url: "../../utils/add_reply.php",
                dataType: "json",
                data: {
                    cid: commentCID,
                    content: replyContent
                },
                success: function(res) {
                    if (res.success) {
                        popUp("clientm-success", res.message, null);
    
                        // Display Reply on Client Side
                        let repliesContainer = $(element).parent().siblings(".replies-container");
                        let noReplies = $(repliesContainer).html() == "";
                        let replyHTML = loadReplies(noReplies, res.reply);
                        
                        $(repliesContainer).find(".reply-options").css("display", "block");
                        $(repliesContainer).find(".replies").css("display", "block");
    
                        if (noReplies) {
                            $(repliesContainer).prepend(replyHTML);
                        }
                        else {
                            $(repliesContainer).find(".reply-options").text($(repliesContainer).find(".reply-options").text().replace("Expand", "Collapse"));
                            $(repliesContainer).find(".replies").prepend(replyHTML);
                        }
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
    });

    // Delete Reply
    $(document).on("click", ".reply-del-comment", function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();

        let element = this;
        let cid = $(element).parent().parent().parent().parent().siblings(".comment").find(".del-comment").attr("name");   

        // Send Request
        if (!$(element).attr("disabled")) {
            $(element).attr("disabled", true);

            $.ajax({
                type: "POST",
                url: "../../utils/delete_reply.php",
                dataType: "json",
                data: {
                    cid: cid,
                    rid: $(element).attr("name")
                },
                success: function(res) {
                    if (res.success) {
                        popUp("clientm-success", res.message, null);
    
                        // Remove Reply HTML on Client End
                        let repliesContainer = $(element).parent().parent().parent().parent();
                        let replies = $(element).parent().parent().parent();

                        $(element).removeAttr("disabled");
                        
                        $(element).parent().parent().remove();
                        
                        if ($(replies).children().length == 1) {
                            $(repliesContainer).empty();
                        }
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
    });

    // Toggles Replies Section of a Comment
    $(document).on("click", ".reply-options", function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();

        if ($(this).siblings(".replies").css("display") == "none") {
            $(this).siblings(".replies").css("display", "block");
            $(this).text($(this).text().replace("Expand", "Collapse"));
        }
        else {
            $(this).siblings(".replies").css("display", "none");
            $(this).text($(this).text().replace("Collapse", "Expand"));
        }
    });

    //
    initSearch();

    if (pathname !== "/profile.php" && pathname !== "/forum.php") {
        $("#content").css("display", "block");
        $("#loading-info").css("display", "none");
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
            popUp("clientm-fail", "Failed to Contact Server", null);
        }
    });
}

function resendVerification() {

    // Get User Input
    let user = $("#resend-to").val();

    // Display Loading Popup
    popUp("clientm-fail", "Loading...", null);

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
            popUp("clientm-fail", "Failed to Contact Server", null);
        }
    });
}

// Final Verification Step for registration
function verifyAccount() {

    // Get User Input
    let user = $("#resend-to").val();
    let code = $("#vcode").val();

    // Display Loading Popup
    popUp("clientm-fail", "Loading...", null);

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
            popUp("clientm-fail", "Failed to Contact Server", null);
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

    // Display Loading Popup
    popUp("clientm-fail", "Loading...", null);

    // Client Side Validation
    if (username.length > 20 || username.length == 0) {
        popUp("clientm-fail", "Invalid Username", null);
    }
    else if (password.length == 0) {
        popUp("clientm-fail", "Invalid Password", null);
    }
    else {
        
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

                    // Change Button
                    $(".reg-button").addClass("login-success");
                    $(".reg-button").removeClass("reg-button");
                    $(".login-success").removeAttr("onclick");
                    $(".login-success").text("Logged In");
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
            popUp("clientm-fail", "Failed to Contact Server", null);
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

// Settings Functions
function openSettings() {
    $("#settings-bg").css("display", "block");
    hideOptions();
    saved = false;

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

                // Hide Loading Screen
                $("#settings-load").css("display", "block");
                $("#settings-load-screen").css("display", "none");
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

function closeSettings() {
    if (saved) {
        location.reload();
    }
    else {
        $("#settings-bg").css("display", "none");
    }
}

function saveSettings() {
    let imgURL = $("#prof-img-url").val();
    let bio = $("#bio-textarea").val();
    let location = $("#location-setting").val();
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

                saved = true;
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
        $(".add-comment").val("");

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

// Set fullHTML to false if Only the singular Comment HTML is Required
function loadReplies(fullHTML, repliesJSON) {
    let replies = repliesJSON;
    let replyHTML = '';
    let endReplyTag = '';
    let replyOptions = '';

    // Generate HTML String
    if (replies != null && replies != "") {
        if (fullHTML) {
            replyOptions = '<div class="reply-options" >Expand Replies</div><div class="replies" >';
            endReplyTag = '<div class="end-replies" >Continue Comments</div></div>';
        }
        replyHTML += replyOptions;

        for (let reply in replies) {
            let nameColour = "lightgrey";

            // Get Rank Colour
            switch (replies[reply].rank) {
                case "owner":
                    nameColour = "violet";
                    break;
                case "mod":
                    nameColour = "orange";
                    break;
            }

            let replyBody = highlightHyperlinks(replies[reply].content, false);

            replyHTML += '<div class="comment reply" ><div class="reply-indent" ></div><div class="commenter-name" ><a style="color: '+nameColour+'" href="/profile.php?uquery='+replies[reply].user+'" >'+replies[reply].user+'</a> <div class="comment-post-date" >'+replies[reply].date+'</div></div><div><div name="'+replies[reply].rid+'" class="delete-comment reply-del-comment noselect" style="display: '+replies[reply].delDisplay+'" >Delete</div></div><div class="comment-content" style="margin-bottom: 5px" >'+replyBody+'</div></div>';
        }
        replyHTML += endReplyTag;
    }

    return replyHTML;
}

// Highlight Profile Links
function highlightProfileLinks(text) {
    let indicies = [];
    let final = text;

    // Get Index of Every @ Symbol
    for (let i = 0; i < text.length; i++) {
        let char = text[i];

        if (char == "@") { // Profile Pages
            if (!indicies.includes(i)) {
                indicies.push(i);
            }
        }
    }

    // Loop through each Index
    let breakChars = [" ", "\n", "(", ")", "<", ">"];

    for (let l = 0; l < indicies.length; l++) {
        let linkIndex = indicies[l];
        let linkHtml;
        let fullLink = "";

        for (let i = linkIndex; i < text.length; i++) {
            let char = text[i];

            if (!breakChars.includes(char)) {
                fullLink += char;       
            }
            else {
                break;
            }
        }

        linkHtml = '<a style="color: #57f54e" class="h-link" href="/profile.php?uquery='+fullLink.substr(1)+'" >' + fullLink + "</a>";

        final = final.replace(fullLink, linkHtml);
    }

    return final;
}

// Highlight Specific Elements in Text
function highlightHyperlinks(text, renderImages) {
    let indicies = [];
    let final = highlightProfileLinks(text);

    // Get Index of Every HTTP Keyword
    for (let i = 0; i < text.length; i++) {
        let word = text[i] + text[i + 1] + text[i + 2] + text[i + 3] + text[i + 4] + text[i + 5] + text[i + 6] + text[i + 7];

        if (word.includes("http://") || word.includes("https://")) { // Links
            if (!indicies.includes(i)) {
                indicies.push(i);
            }
        }
    }

    // Loop through each Index
    let breakChars = [" ", "\n", "(", ")", "<", ">"];

    for (let l = 0; l < indicies.length; l++) {
        let linkIndex = indicies[l];
        let linkHtml;
        let fullLink = "";

        for (let i = linkIndex; i < text.length; i++) {
            let char = text[i];

            if (!breakChars.includes(char)) {
                fullLink += char;       
            }
            else {
                break;
            }
        }

        linkHtml = '<a class="h-link" href="'+fullLink+'" target="_blank" >' + fullLink + "</a>";

        // Add Image Tag if Render Images = True
        if (renderImages && isImage(fullLink)) {
            linkHtml += '<br /><img src="'+fullLink+'" class="h-link-img" ><br />';
        }

        final = final.replace(fullLink, linkHtml);
    }

    return final;
}

// https://stackoverflow.com/questions/9714525/javascript-image-url-verify
function isImage(url) {
    return(url.match(/\.(jpeg|jpg|gif|png)$/) != null);
}