/**
 * Folio Forum Javascript File
 * Connell Reffo 2019
 */

// Global Variables
let joined = false;
let savedForum = false;
let hasShowed = false;
let loadedBannedUsers = false;

let loadedPosts = 0; // Tracks how many Posts currently Loaded
let loadAmounts = 6; // How many Posts to Request and Load when Needed
let loadedAllPosts = false;

let confirm = {
    action: "",
    profile: "",
    delElement: false,
    element: null
};

// On Load
function triggerOnLoad() {
    loadForum(forum);

    // View Member Button
    $(document).on("click", "#members-modal .view-member, .banned-members-container .view-member", function (e) {
        let profile = $(this).parent().parent().attr("data-profile");
        let URL = "/profile.php?uquery=" + profile;

        location.replace(URL);
    });

    // Member Actions
    $(document).on("click", ".member-action", function (e) {
        let profile = $(this).parent().parent().attr("data-profile");
        let action = $(this).attr("data-action");
        let element = $(this).parent().parent();
        let warning = "";
        let delElement = false;

        switch (action) {
            case "kick": warning = "Are you sure you want to Kick " + profile + "?"; delElement = true; break;
            case "ban": warning = "Are you sure you want to Ban " + profile + "?"; delElement = true; break;
            case "promote": warning = "Are you sure you want to Promote " + profile + "?"; break;
            case "demote": warning = "Are you sure you want to Demote " + profile + "?"; break;
            case "unban": warning = "Are you sure you want to Unban " + profile + "?"; delElement = true; break;
        }

        closeMembers();
        closeForumSettings();
        $("#confirm-member-action-modal").css("display", "block");
        $("#confirm-member-action-modal .leave-forum-msg").text(warning);

        // Set Values for Confirmation
        confirm.action = action;
        confirm.profile = profile;
        confirm.delElement = delElement;
        confirm.element = element;
    });

    // On Confirm Button Clicked
    $(document).on("click", "#confirm-member-action", function (e) {
        if (confirm.delElement) {
            $(confirm.element).remove();

            if ($(".banned-members-container").length == 1) {
                $(".bans-empty").remove();
                $(".banned-members-container").append('<div class="bans-empty res-empty" style="font-size: 25px; display: block;">No Banned Members</div>');
            }
        }

        memberAction(confirm.profile, confirm.action);
    });

    // Load Forum Posts as Client Scrolls
    $(window).scroll(function() {
        if($(window).scrollTop() + $(window).height() == $(document).height()) {
            if (!loadedAllPosts) {
                let requestedPosts = getForumPosts(loadedPosts, loadAmounts);
    
                if (!requestedPosts) {
                    loadedAllPosts = true;
                }

                loadedPosts += loadAmounts;
            }
        }
     });
}

function loadForum(fquery) {
    if (fquery !== null && fquery !== "") {
        
        // Send Request
        $.ajax({
            type: "POST",
            url: "../../utils/view_forum.php",
            dataType: "json",
            data: {
                fquery: fquery
            },
            success: function(res) {
                if (res.redirect) {
                    location.replace("/index.php");
                }
                else if (res.success) {

                    // Load JSON Response into Webpage
                    let forum = res.forum;

                    joined = forum.joined;
                    if (!forum.banned) {
                        if (joined) {
                            $("#post-forum-section").css("display", "inline-block");
                            displayLeaveForumBtn();
                        }
                        else {
                            $("#post-forum-section").css("display", "none");
                            displayJoinForumBtn();
                        }
                    }
                    else {
                        displayBannedForumBtn();
                    }

                    if (!res.forum.moderator) {
                        $(".edit-forum-btn").remove();
                    }
                    else {
                        $(".edit-forum-btn").css("display", "block");
                    }

                    $("#profile-img").attr("src", forum.icon);
                    $("#bio").val(forum.description);
                    $("#profile-name").text(forum.name);
                    $("#forum-members").text(forum.members);
                    $("#creation-date").text(forum.date);

                    // Load Posts
                    getForumPosts(0, loadAmounts);
                    loadedPosts += loadAmounts;
                }
                else {
                    popUp("clientm-fail", res.message, null);
                }
            },
            error: function(err) {
                popUp("clientm-fail", "Failed to Contact Server", null);
            }
        }).done(function() {
            $("#content").css("display", "block");
            $("#loading-info").css("display", "none");
    
            console.log("Finished Loading Forum");
        });;
    }
    else {
        popUp("clientm-fail", "Invalid Forum Query", null);
    }
}

// Show Members of Forum in Modal
function showMembers() {
    $("#members-modal").css("display", "block");

    if (!hasShowed) {
        $("#view-members-content").css("display", "none");
        $("#members-load-screen").css("display", "block");

        $.ajax({
            type: "POST",
            url: "../../utils/forum_members.php",
            dataType: "json",
            data: {
                forum: forum
            },
            success: function(res) {
                if (res.success) {
                    hasShowed = true;

                    // Load List of Users
                    $("#members-container").empty();
                    loadMembers(res.members);
                }
                else {
                    popUp("clientm-fail", res.message, null);
                }
            },
            error: function(err) {
                popUp("clientm-fail", "Failed to Contact Server", null);
            }
        }).done(function() {
            $("#view-members-content").css("display", "block");
            $("#members-load-screen").css("display", "none");
        });
    }
}

function closeMembers() {
    $("#members-modal").css("display", "none");
}

function closeLeaveForum() {
    $("#leave-forum-modal").css("display", "none");
}

function closeMemberActionConfirmation() {
    $("#confirm-member-action-modal").css("display", "none");
}

// Render Forum Members JSON as HTML
function loadMembers(members) {
    let container = $("#members-container");

    for (let member in members) {
        let html = '<div class="profile-forum" data-profile="'+members[member].username+'" ><img class="member-img" src="'+members[member].image+'" ><div class="forum-member-name" >'+members[member].username+'</div><br /><div class="member-options" ><button class="view-member member-default-option" >View</button></div></div>';
        $(container).prepend(html);

        let element = $(container).children().first();
        let memberData = members[member];

        // Show Change DOM based on Permission Level
        let title = $(element).find(".forum-member-name")

        // Coloured Names
        if (memberData.owner) {
            $(title).css("color", "violet");
            $(title).text(memberData.username + " (Owner)");
        }
        else if (memberData.moderator) {
            $(title).css("color", "orange");
            $(title).text(memberData.username + " (Mod)");
        }

        // Options (Kick, Ban, Promote)
        if (memberData.removable) {
            let options = $(element).find(".member-options");
            
            $(options).append('<button class="member-action member-default-option member-option-red" data-action="kick" >Kick</button>');
            $(options).append('<button class="member-action member-default-option member-option-red" data-action="ban" >Ban</button>');
            
            if (memberData.promotable) {
                $(options).append('<button class="member-action member-default-option member-option-green" data-action="promote" >Promote</button>');
            }

            if (memberData.demotable) {
                $(options).append('<button class="member-action member-default-option member-option-red" data-action="demote" >Demote</button>');
            }
        }
    }
}

// Allow Users to Join Forums
function joinForum() {

    // Close Modal
    closeLeaveForum();

    // Send Request
    $.ajax({
        type: "POST",
        url: "../../utils/join_forum.php",
        dataType: "json",
        data: {
            forum: forum
        },
        success: function(res) {
            if (res.success) {
                joined = res.joined;

                if (joined) {
                    $("#forum-members").text(parseInt($("#forum-members").text()) + 1);
                    $("#post-forum-section").css("display", "inline-block");
                    displayLeaveForumBtn();
                }
                else {
                    $("#forum-members").text(parseInt($("#forum-members").text()) - 1);
                    $("#post-forum-section").css("display", "none");
                    $(".edit-forum-btn").remove();
                    displayJoinForumBtn();
                }

                if (res.reload) {
                    location.reload();
                }

                hasShowed = false;
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

function displayLeaveForumBtn() {
    $(".join-forum-btn").addClass("joined-forum-btn");
    $(".join-forum-btn").text("Leave Forum");
    $(".join-forum-btn").attr("onclick", "confirmLeave()");
}

function displayJoinForumBtn() {
    $(".join-forum-btn").removeClass("joined-forum-btn");
    $(".join-forum-btn").text("Join Forum");
    $(".join-forum-btn").attr("onclick", "joinForum()");
}

function displayBannedForumBtn() {
    $(".join-forum-btn").addClass("joined-forum-btn");
    $(".join-forum-btn").text("Banned");
    $(".join-forum-btn").removeAttr("onclick");
}

function confirmLeave() {
    $("#leave-forum-modal").css("display", "block");
}

// Forum Settings Editor
function openForumSettings() {
    $(".create-forum-modal-bg").css("display", "block");

    // Fill Values
    let imgURL = $(".forum-img#profile-img").attr("src");
    if (!imgURL.includes("http")) {
        imgURL = "";
    }

    $(".create-forum-modal #forum-img-url").val(imgURL);
    $(".create-forum-modal .forum-icon-sel").attr("src", $(".forum-img#profile-img").attr("src"));
    $(".create-forum-modal #forum-desc-textarea").val($("#bio").val());

    // Get Banned Users
    if (!loadedBannedUsers) {
        $(".bans-empty").text("Loading Bans...");

        $.ajax({
            type: "POST",
            url: "../../utils/get_bans.php",
            dataType: "json",
            data: {
                forum: forum
            },
            success: function(res) {
                if (res.success) {
                    loadedBannedUsers = true;

                    // Modify DOM
                    loadBannedMembers(res.bans);
                }
                else {
                    popUp("clientm-fail", res.message, null);
                }
            },
            error: function(err) {
                popUp("clientm-fail", "Failed to Contact Server", null);
            }
        }).done(function() {
            $(".bans-empty").text("No Banned Members");
        });
    }

    savedForum = false;
}

function loadBannedMembers(bans) {
    let container = $(".banned-members-container");

    if (bans == "" || bans == null || bans == []) {
        $(".bans-empty").css("display", "block");
    }
    else {
        $(container).empty();
        for (let ban in bans) {
            let bannedMember = bans[ban];
            $(container).append('<div class="profile-forum" data-profile="'+bannedMember.username+'" ><img class="member-img" src="'+bannedMember.image+'" ><div class="forum-member-name" >'+bannedMember.username+'</div><br /><div class="member-options" ><button class="view-member member-default-option" >View</button><button class="member-action member-default-option member-option-green" data-action="unban" >Unban</button></div></div>');
        }
    }
}

function closeForumSettings() {
    if (savedForum) {
        location.reload();
    }
    else {
        $(".create-forum-modal-bg").css("display", "none");

        $(".create-forum-modal input").val("");
        $(".create-forum-modal textarea").val("Sample Text");
    }
}

function saveForum() {
    let iconURL = $("#forum-img-url").val();
    let description = $("#forum-desc-textarea").val()

    // Client Side Validation
    if (iconURL.length > 150) {
        popUp("clientm-fail", "Forum Icon URL Must be 150 Characters or Less", null);
    }
    else if (description.length > 300) {
        popUp("clientm-fail", "Forum Description Must be 300 Characters or Less", null);
    }
    else if (description.length == 0) {
        popUp("clientm-fail", "Forum Description Must be Greater than 0 Characters", null);
    }
    else {
        // Send Request
        $.ajax({
            type: "POST",
            url: "../../utils/save_forum.php",
            dataType: "json",
            data: {
                forum: forum,
                icon: iconURL,
                desc: description
            },
            success: function(res) {
                if (res.success) {
                    savedForum = true;
                    $(".forum-icon-sel").attr("src", res.icon);
                    popUp("clientm-success", "Saved!", null);
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

// Kick, Ban, Promote, or Demote Action
function memberAction(user, action) {
    if (action != null && action != "") {
        // Send Request
        $.ajax({
            type: "POST",
            url: "../../utils/member_action.php",
            dataType: "json",
            data: {
                forum: forum,
                action: action,
                user: user
            },
            success: function(res) {
                if (res.success) {
                    popUp("clientm-success", res.message, null);

                    // Modify DOM
                    switch (action) {
                        case "kick":
                            $("#forum-members").text(parseInt($("#forum-members").text()) - 1);
                            break;
                        case "ban":
                            $("#forum-members").text(parseInt($("#forum-members").text()) - 1);
                            loadedBannedUsers = false;
                            break;
                        case "promote":
                            hasShowed = false;
                            break;
                        case "demote":
                            hasShowed = false;
                            break;
                        case "unban":
                            $("#forum-members").text(parseInt($("#forum-members").text()) + 1);
                            hasShowed = false;
                            break;
                    }

                    $(".bans-empty").text("No Banned Members");
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
    else {
        popUp("clientm-fail", "Invalid Action", null);
    }

    // Close Confirmation Page
    closeMemberActionConfirmation();
}

// Forum Posting
function addForumPost() {
    let title = $(".forum-post-title").val();
    let body = $(".forum-post-textarea").val();

    // Client Side Validation
    if (title.length > 20) {
        popUp("clientm-fail", "Title Must not Exceed 20 Characters", null);
    }
    else if (title.length == 0) {
        popUp("clientm-fail", "Title Must be Greater than 0 Characters", null);
    }
    else if (body.length > 300) {
        popUp("clientm-fail", "Body Must not Exceed 300 Characters", null);
    }
    else if (body.length == 0) {
        popUp("clientm-fail", "Body Must be Greater than 0 Characters", null);
    }
    else {
        // Send Request
        $.ajax({
            type: "POST",
            url: "../../utils/add_forum_post.php",
            dataType: "json",
            data: {
                forum: forum,
                title: title,
                body: body
            },
            success: function(res) {
                if (res.success) {
                    popUp("clientm-success", "Posted!", null);

                    // Add Post on Client End
                    loadForumPosts(res.post, false);

                    // Clear Input
                    $(".forum-post-title").val("");
                    $(".forum-post-textarea").val("");
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

function getForumPosts(min, max) {
    let retValue;

    $.ajax({
        type: "POST",
        url: "../../utils/get_forum_posts.php",
        dataType: "json",
        async: false,
        data: {
            forum: forum,
            min: min,
            max: max
        },
        success: function(res) {
            if (res.success) {
                loadForumPosts(res.posts, true);
                
                if (res.posts == null || res.posts == "") {
                    retValue = false;
                }
                else {
                    retValue = true;
                }
            }
            else {
                popUp("clientm-fail", res.message, null);
                retValue = false;
            }
        },
        error: function(err) {
            popUp("clientm-fail", "Failed to Contact Server", null);
            retValue = false;
        }
    });

    return retValue;
}

function loadForumPosts(posts, append) {
    let container = $("#forum-posts-container");

    for (let post in posts) {
        let postObject = posts[post];
        let posterNameColour = "lightgrey";

        switch (postObject.rank) {
            case "owner":
                posterNameColour = "violet";
                break;
            case "mod":
                posterNameColour = "orange";
                break;
        }

        let html = '<div class="profile-section forum-post-container" ><h2 class="section-title" >'+postObject.title+'</h2><br /><div class="forum-post-info" >Posted '+postObject.date+' by <a style="color: '+posterNameColour+'" href="/profile.php?uquery='+postObject.posterName+'" >'+postObject.posterName+'</a></div><div class="forum-post-body" >'+postObject.body+'</div><div class="forum-post-voting" ><div class="forum-post-votes" >0</div><button class="upvote vote" ><img src="/images/other/voteIcon.svg" ></button><button class="downvote vote" ><img src="/images/other/voteIcon.svg" ></button></div></div><br />';

        switch (append) {
            case true:
                $(container).append(html);
                break;
            default:
                $(container).prepend(html);
                break;
        } 
    }
}