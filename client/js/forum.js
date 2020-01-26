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
let postLoadAmounts = 6; // How many Posts to Request and Load when Needed
let loadedAllPosts = false;
let finishedLoadingPosts = false;
let showEmptyMsg = true;

let loadedPostComments = []; // List of Post IDs of Comments Loaded
let commentLoadAmounts = 5;

let sort = "new"; // Sorting Method for Posts

let confirm = {
    action: "",
    profile: "",
    delElement: false,
    element: null
};

let deletePost = {
    pid: 0,
    element: null
}

// On Load
function triggerOnForumLoad() {
    let pathname = window.location.pathname;
    if (pathname == "/forum.php") {
        loadForum(forum);
    }

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
        if ($(window).scrollTop() + $(window).height() == $(document).height()) {
            if (!loadedAllPosts && finishedLoadingPosts) {
                finishedLoadingPosts = false;

                $.ajax({
                    type: "POST",
                    url: "../../utils/get_forum_posts.php",
                    dataType: "json",
                    data: {
                        forum: forum,
                        min: loadedPosts,
                        max: postLoadAmounts,
                        sort: sort
                    },
                    success: function(res) {
                        if (res.success) {
                            loadForumPosts(res.posts, true);

                            if (res.posts == null || res.posts == [] || res.posts == "" || res.posts.length < postLoadAmounts) {
                                loadedAllPosts = true;
                            }

                            loadedPosts += postLoadAmounts;
                        }
                        else {
                            popUp("clientm-fail", res.message, null);
                        }
                    },
                    error: function(err) {
                        popUp("clientm-fail", "Failed to Contact Server", null);
                    }
                }).done(function () {
                    finishedLoadingPosts = true;
                });

                showEmptyMsg = false;
            }
        }
    });

    initForumButtons();
}

function initForumButtons() {

    // Forum Post Upvote Button
    $(document).on("click", ".forum-post-container .upvote", function (e) {
        let element = $(this);
        let postId = $(element).parent().attr("data-pid");
        let voteCountElement = $(element).siblings(".forum-post-votes");

        let upvote = true;
        let downvote = false;

        let hasSelected = $(element).hasClass("upvote-selected");
        let countDir = 0;
        
        if (!$(element).attr("disabled")) {
            $(element).attr("disabled", true);

            if (hasSelected) {
                $(element).removeClass("upvote-selected");
                $(voteCountElement).css("color", "lightgrey");
    
                countDir = -1;
            }
            else {
                $(element).addClass("upvote-selected");
                $(voteCountElement).css("color", "rgb(106, 154, 186)");
    
                countDir = 1;
            }

            if ($(element).siblings(".downvote").hasClass("downvote-selected")) {
                countDir = 2;
            }
    
            $(element).siblings(".downvote").removeClass("downvote-selected");
    
            // Edit Count
            let voteCountText = $(voteCountElement).text();
            $(voteCountElement).text(parseInt(voteCountText) + countDir);
    
            // Send Request
            $.ajax({
                type: "POST",
                url: "../../utils/vote_forum_post.php",
                dataType: "json",
                data: {
                    pid: postId,
                    upvote: upvote,
                    downvote: downvote
                },
                success: function(res) {
                    if (!res.success) {
                        popUp("clientm-fail", res.message, null);
    
                        // Revert Class
                        if (!res.upvoted) {
                            $(element).removeClass("upvote-selected");
                            $(voteCountElement).css("color", "lightgrey");
                        }
                        else {
                            $(element).addClass("upvote-selected");
                            $(voteCountElement).css("color", "rgb(106, 154, 186)");
                        }
    
                        // Revert Count
                        voteCountText = $(voteCountElement).text();
                        $(voteCountElement).text(parseInt(voteCountText) + (countDir * -1));
                    }
                },
                error: function(err) {
                    popUp("clientm-fail", "Failed to Contact Server", null);
    
                    // Revert Class
                    if (hasSelected) {
                        $(element).removeClass("upvote-selected");
                        $(voteCountElement).css("color", "lightgrey");
                    }
                    else {
                        $(element).addClass("upvote-selected");
                        $(voteCountElement).css("color", "rgb(106, 154, 186)");
                    }
    
                    // Revert Count
                    voteCountText = $(voteCountElement).text();
                    $(voteCountElement).text(parseInt(voteCountText) + (countDir * -1));
                }
            }).done(function() {
                $(element).removeAttr("disabled");
            });
        }
    });

    // Forum Post Downvote Button
    $(document).on("click", ".forum-post-container .downvote", function (e) {
        let element = $(this);
        let postId = $(element).parent().attr("data-pid");
        let voteCountElement = $(element).siblings(".forum-post-votes");

        let upvote = false;
        let downvote = true;

        let hasSelected = $(element).hasClass("downvote-selected");
        let countDir = 0;

        if (!$(element).attr("disabled")) {
            $(element).attr("disabled", true);

            if (hasSelected) {
                $(element).removeClass("downvote-selected");
                $(voteCountElement).css("color", "lightgrey");
    
                countDir = 1;
            }
            else {
                $(element).addClass("downvote-selected");
                $(voteCountElement).css("color", "rgb(194, 116, 194)");
    
                countDir = -1;
            }

            if ($(element).siblings(".upvote").hasClass("upvote-selected")) {
                countDir = -2;
            }
    
            $(element).siblings(".upvote").removeClass("upvote-selected");
    
            // Decrement Count
            let voteCountText = $(voteCountElement).text();
            $(voteCountElement).text(parseInt(voteCountText) + countDir);
    
            // Send Request
            $.ajax({
                type: "POST",
                url: "../../utils/vote_forum_post.php",
                dataType: "json",
                data: {
                    pid: postId,
                    upvote: upvote,
                    downvote: downvote
                },
                success: function(res) {
                    if (!res.success) {
                        popUp("clientm-fail", res.message, null);
    
                        // Revert Class
                        if (!res.downvoted) {
                            $(element).removeClass("downvote-selected");
                            $(voteCountElement).css("color", "lightgrey");
                        }
                        else {
                            $(element).addClass("downvote-selected");
                            $(voteCountElement).css("color", "rgb(194, 116, 194)");
                        }
    
                        // Revert Count
                        voteCountText = $(voteCountElement).text();
                        $(voteCountElement).text(parseInt(voteCountText) + (countDir * -1));
                    }
                },
                error: function(err) {
                    popUp("clientm-fail", "Failed to Contact Server", null);
    
                    // Revert Class
                    if (hasSelected) {
                        $(element).removeClass("downvote-selected");
                        $(voteCountElement).css("color", "lightgrey");
                    }
                    else {
                        $(element).addClass("downvote-selected");
                        $(voteCountElement).css("color", "rgb(194, 116, 194)");
                    }
    
                    // Revert Count
                    voteCountText = $(voteCountElement).text();
                    $(voteCountElement).text(parseInt(voteCountText) + (countDir * -1));
                }
            }).done(function() {
                $(element).removeAttr("disabled");
            });
        }
    });

    // Forum Post Delete Button
    $(document).on("click", ".forum-post-container .delete-post", function (e) {
        let element = $(this);
        deletePost.element = $(element);
        deletePost.pid = $(deletePost.element).parent().siblings(".forum-post-voting").attr("data-pid");
        
        // Open Confirmation Modal
        closeModal();
        $("#confirm-post-delete-modal").css("display", "block");
    });

    // Confirm Forum Deletion
    $(document).on("click", "#confirm-post-delete", function (e) {
        closeModal();
        
        $(deletePost.element).attr("disabled", true);

        // Send XHR
        $.ajax({
            type: "POST",
            url: "../../utils/delete_forum_post.php",
            dataType: "json",
            data: {
                pid: deletePost.pid
            },
            success: function(res) {
                if (res.success) {
                    popUp("clientm-success", "Deleted Post!", null);

                    // Modify DOM
                    let postElement = $(deletePost.element).parent().parent().parent();
                    $(postElement).remove();

                    if ($("#forum-posts-container").children().length == 0) {
                        $("#forum-posts-container").append('<div class="res-empty posts-empty profile-section" >No Posts to Display</div>');
                    }

                    $(deletePost.element).attr("disabled", false);
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

    // View Forum Comments Button
    $(document).on("click", ".show-post-comments", function (e) {
        let element = $(this);
        let votingElement = $(element).parent().siblings(".forum-post-voting");
        let postId = $(votingElement).attr("data-pid");
        let commentCount = parseInt($(votingElement).attr("data-comments"));
        let hasLoaded = loadedPostComments.includes(postId);
        let commentsElement = $(element).parent().parent().siblings(".forum-post-comments");

        if ($(commentsElement).css("display") == "none") {
            $(element).text("Hide Comments");
            $(commentsElement).css("display", "inline-block");
        }
        else {
            $(element).text("View Comments");
            $(commentsElement).css("display", "none");
        }

        if (commentCount == 0) {
            hasLoaded = true;
        }

        if (!hasLoaded) {
            $(commentsElement).find(".res-empty").text("Loading Comments...");
            $(element).attr("disabled", true);

            $.ajax({
                type: "POST",
                url: "../../utils/get_comments.php",
                dataType: "json",
                data: {
                    type: "forumpost",
                    pid: postId,
                    min: 0,
                    max: commentLoadAmounts
                },
                success: function(res) {
                    if (res.success) {
                        loadedPostComments.push(postId);

                        // Modify DOM
                        let emptyElement = $(commentsElement).find(".res-empty");

                        if (res.comments == "" || res.comments == null || res.comments == []) {
                            $(emptyElement).text("No Comments to Display");
                            $(element).attr("disabled", false);
                        }
                        else {
                            $(emptyElement).css("display", "none");
                            $(element).attr("disabled", false);
                            loadPostComments(res.comments, true, $(commentsElement).find(".forum-post-comments-container"), true);
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

    // Add Forum Post Comment Button
    $(document).on("click", "button.post-forum-comment", function (e) {
        let element = $(this);
        let postContainer = $(element).parent().parent().siblings(".forum-post-container");
        let postId = $(postContainer).find(".forum-post-voting").attr("data-pid");
        let comment = $(element).siblings(".add-comment").val();
        let commentsEmptyElement = $(this).parent().siblings(".res-empty");

        // Client Side Validation
        if (comment.length > 120) {
            popUp("clientm-fail", "Comment Must be Less than 120 Characters", null);
        }
        else if (comment.length == 0) {
            popUp("clientm-fail", "Comment Must be Greater than 0 Characters", null);
        }
        else {
            $(element).siblings(".add-comment").val("");

            // Send Request
            $.ajax({
                type: "POST",
                url: "../../utils/add_comment.php",
                dataType: "json",
                data: {
                    type: "forumpost",
                    content: comment,
                    profile: postId
                },
                success: function(res) {
                    if (res.success) {
                        popUp("clientm-success", "Posted!", null);

                        // Load Comments into DOM
                        $(commentsEmptyElement).css("display", "none");
                        loadPostComments(res.comment, false, $(element).parent().siblings(".forum-post-comments-container"), false);
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

    // Delete Post Comment Button
    $(document).on("click", ".del-comment", function (e) {
        let element = $(this);
        let emptyElement = $(element).parent().parent().parent().parent().parent().find(".res-empty");
        let commentContainer = $(emptyElement).siblings(".forum-post-comments-container");

        // Send Request
        if (!$(element).attr("disabled")) {
            $(element).attr("disabled", true);

            $.ajax({
                type: "POST",
                url: "../../utils/delete_comment.php",
                dataType: "json",
                data: {
                    cid: $(element).attr("name"),
                    profile: $(element).parent().siblings(".forum-post-voting").attr("data-pid")
                },
                success: function(res) {
                    if (res.success) {
                        popUp("clientm-success", res.message, null);
    
                        // Mofify DOM
                        $(element).parent().parent().parent().remove();
    
                        if ($(commentContainer).children().length == 0) {
                            $(emptyElement).css("display", "block");
                        }
    
                        $(element).removeAttr("disabled");
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

    // Load More Comments Button
    $(document).on("click", ".load-more-comments", function (e) {
        let element = $(this);
        let votingElement = $(element).parent().parent().siblings(".forum-post-container").find(".forum-post-voting");
        let postId = $(votingElement).attr("data-pid");
        let commentsElement = $(element).parent().siblings(".forum-post-comments-container");

        $(element).text("Loading Comments...");
        $(element).attr("disabled", true);

        // Send Request
        $.ajax({
            type: "POST",
            url: "../../utils/get_comments.php",
            dataType: "json",
            data: {
                type: "forumpost",
                pid: postId,
                min: commentsElement.children().length,
                max: commentLoadAmounts
            },
            success: function(res) {
                if (res.success) {

                    // Modify DOM
                    if (res.comments == "" || res.comments == null || res.comments == []) {
                        $(element).text("No More Comments to Display");
                    }
                    else {
                        loadPostComments(res.comments, true, $(commentsElement), false);
                        $(element).text("Load More Comments");
                        $(element).attr("disabled", false);
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
                    $("#bio").html(highlightHyperlinks(forum.description, false));
                    $("#profile-name").text(forum.name);
                    $("#forum-members").text(forum.members);
                    $("#creation-date").text(forum.date);

                    // Load Posts
                    getForumPosts(0, postLoadAmounts);
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
        });
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
                    $(".delete-post").remove();
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
    $(".create-forum-modal #forum-desc-textarea").val($("#bio").text());

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
        popUp("clientm-fail", "Loading...", null);

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
    closeModal();
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
        $(".forum-post-title").val("");
        $(".forum-post-textarea").val("");

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
                    showEmptyMsg = false;

                    // Add Post on Client End
                    loadForumPosts(res.post, false);
                    $(".posts-empty").remove();
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
    $.ajax({
        type: "POST",
        url: "../../utils/get_forum_posts.php",
        dataType: "json",
        data: {
            forum: forum,
            min: min,
            max: max,
            sort: sort
        },
        success: function(res) {
            if (res.success) {
                if (res.posts == null || res.posts == "" || res.posts == []) {
                    $("#forum-posts-container").find(".res-empty").text("No Posts to Display");
                    loadedAllPosts = true;
                }
                else {
                    if (res.posts.length < postLoadAmounts) {
                        loadedAllPosts = true;
                    }

                    $("#forum-posts-container").empty();
                    loadForumPosts(res.posts, true);
                }

                loadedPosts += postLoadAmounts;
            }
            else {
                popUp("clientm-fail", res.message, null);
            }
        },
        error: function(err) {
            popUp("clientm-fail", "Failed to Contact Server", null);
        }
    }).done(function () {
        finishedLoadingPosts = true;
    });
}

function loadForumPosts(posts, append) {
    let container = $("#forum-posts-container");

    if (posts.length > 0) {
        for (let post in posts) {
            showEmptyMsg = false;

            let postObject = posts[post];
            let posterNameColour = "lightgrey";
            let upvoteClasses = "";
            let downvoteClasses = "";
            let actionButtons = '<button class="show-post-comments forum-post-action member-default-option" >View Comments</button>';
            let voteCountColour = "lightgrey";
    
            if (postObject.upvoted) {
                upvoteClasses = " upvote-selected";
                voteCountColour = "rgb(106, 154, 186)";
            }
            else if (postObject.downvoted) {
                downvoteClasses = " downvote-selected";
                voteCountColour = "rgb(194, 116, 194)";
            }
    
            if (postObject.canEdit) {
                actionButtons += '<button class="delete-post forum-post-action member-default-option member-option-red" >Delete</button>';
            }
    
            switch (postObject.rank) {
                case "owner":
                    posterNameColour = "violet";
                    break;
                case "mod":
                    posterNameColour = "orange";
                    break;
            }

            let forumNameHtml = '';
            let forumName = postObject.forumName;
            let forumLink = "/forum.php?fquery=" + postObject.forumName;

            if (forumName == null) {
                forumName = REMOVED_CONTENT;
                forumLink = "#";
            }

            if (window.location.pathname !== "/forum.php") {
                forumNameHtml = 'on <a style="color: #ffea4a" href="'+forumLink+'" >' + forumName + '</a>';
            }
    
            let postTitle = highlightHyperlinks(postObject.title, false);
            let postBody = highlightHyperlinks(postObject.body, true);

            let poster = postObject.posterName;
            let posterLink = "/profile.php?uquery=" + postObject.posterName;

            if (poster == null) {
                poster = REMOVED_CONTENT;
                posterLink = "#";
            }
    
            let html = '<div class="forum-post-wrapper" ><div class="profile-section forum-post-container" ><h2 class="section-title" >'+postTitle+'</h2><br /><div class="forum-post-info" >Posted '+postObject.date+' by <a style="color: '+posterNameColour+'" href="'+posterLink+'" >'+poster+'</a> '+forumNameHtml+'</div><div class="forum-post-body" >'+postBody+'</div><div class="forum-post-voting" data-comments="'+postObject.comments+'" data-pid="'+postObject.pid+'" ><button title="Upvote" class="upvote vote'+upvoteClasses+'" ><img src="/images/other/voteIcon.svg" ></button><button title="Downvote" class="downvote vote'+downvoteClasses+'" ><img src="/images/other/voteIcon.svg" ></button><div class="forum-post-votes" style="color: '+voteCountColour+'" >'+postObject.voteCount+'</div></div><div class="forum-post-actions" >'+actionButtons+'</div></div><br /><div class="profile-section forum-post-comments" ><div class="add-post-comment-div" ><input class="add-comment post-forum-comment" placeholder="Comment" /><button class="post-forum-comment add-comment-btn" >Post</button></div><div class="res-empty post-comments-empty" >No Comments to Display</div><div class="forum-post-comments-container" ></div></div></div></div>';
    
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
    else if (showEmptyMsg) {
        $(container).append('<div class="res-empty posts-empty profile-section" >No Posts to Display</div>');
        loadedAllPosts = true;
    }
}

// Load Comment JSON into DOM
function loadPostComments(commentsJSON, append, element, showLoadCommentsButton) {
    for (let comment in commentsJSON) {
        let nameColour = "lightgrey";

        // Load Replies
        let replyHTML = loadReplies(true, commentsJSON[comment].replies);

        // Load Comments
        let likedClass = "";
        let imgLikedClass = "";
        
        if (commentsJSON[comment].liked) {
            likedClass = " liked";
            imgLikedClass = " like-icon-selected";
        }

        // Get Rank Colour
        switch (commentsJSON[comment].rank) {
            case "owner":
                nameColour = "violet";
                break;
            case "mod":
                nameColour = "orange";
                break;
        }

        let commentBody = highlightHyperlinks(commentsJSON[comment].content, false);
        let commenter = commentsJSON[comment].user;
        let userLink = "/profile.php?uquery=" + commentsJSON[comment].user;

        if (commenter == null) {
            commenter = REMOVED_CONTENT;
            userLink = "#";
        }

        let commentHTML = '<div class="comment-full" ><div class="comment" ><div class="commenter-name" ><a style="color: '+nameColour+'" href="'+userLink+'" >'+commenter+'</a> <div class="comment-post-date" >'+commentsJSON[comment].date+'</div></div><div class="likes-container" ><button class="likes-icon'+imgLikedClass+'" ><img title="I Like this Comment" src="/images/other/like-icon.svg" ></button><div class="likes-count'+likedClass+'" >'+commentsJSON[comment].likes+'</div><br /><div name="'+commentsJSON[comment].cid+'" class="del-comment delete-comment noselect" style="display: '+commentsJSON[comment].delDisplay+'" >Delete</div></div><div class="comment-content" >'+commentBody+'</div></div><div class="add-reply" ><input class="add-comment" placeholder="Reply" /><button class="add-comment-btn post-reply-btn" >Post</button></div><div class="replies-container" >'+replyHTML+'</div></div>';
        
        if (append) {
            $(element).append(commentHTML);
        }
        else {
            $(element).prepend(commentHTML);
        }
    }

    if (commentsJSON.length > commentLoadAmounts - 1 && showLoadCommentsButton) {
        $(element).parent().append('<div style="text-align: center" ><button class="load-more-comments" >Load More Comments</button></div>');
    }
}

// Forum Post Sorting
function applySort() {
    let sortMethod = $(".sort-options").val();

    // Send Request
    if (sort !== sortMethod && !showEmptyMsg && $("#forum-posts-container").children().length > 1) {
        sort = sortMethod;
        popUp("clientm-fail", "Loading...", null);

        $.ajax({
            type: "POST",
            url: "../../utils/get_forum_posts.php",
            dataType: "json",
            data: {
                forum: forum,
                min: 0,
                max: postLoadAmounts,
                sort: sort
            },
            success: function(res) {
                if (res.success) {
                    loadedAllPosts = false;
                    loadedPosts = postLoadAmounts;

                    $("#forum-posts-container").empty();
                    loadForumPosts(res.posts, true);

                    popDown();
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