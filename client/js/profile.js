/**
 * Folio Profile Manager Front End
 * Connell Reffo 2019
 */

// Variables
let loadedComments = 0; // Tracks how many are currently Loaded
let loadAmounts = 5; // How many Comments to Request and Load when Needed
let loadedAllComments = false;

// Execute when Page Loads
function triggerOnLoad() {

    // Load Profile Data into Page
    loadProfile(profile);

    // Load Comments as Client Scrolls
    $(window).scroll(function() {
        if($(window).scrollTop() + $(window).height() == $(document).height()) {
            if (!loadedAllComments) {
                let requestedComments = getProfileComments(loadedComments, loadAmounts, profile);
    
                if (requestedComments != null && requestedComments != "") {
                    loadComments(requestedComments, "append");
                    loadedComments += loadAmounts;
                }
                else {
                    loadedAllComments = true;
                }
            }
        }
     });

    // Delete Comment
    $(document).on("click", ".del-comment", function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();

        let element = this;

        // Send Request
        if (!$(element).attr("disabled")) {
            $(element).attr("disabled", true);
    
            $.ajax({
                type: "POST",
                url: "../../utils/delete_comment.php",
                dataType: "json",
                data: {
                    cid: $(this).attr("name"),
                    profile: $("#profile-name").text()
                },
                success: function(res) {
                    if (res.success) {
                        popUp("clientm-success", res.message, null);
    
                        // Remove Comment HTML on Client End
                        $(element).removeAttr("disabled");
                        $(element).parent().parent().parent().remove();

                        if ($(".delete-comment").length == 0) {
                            $("#comments-container").prepend('<div style="font-size: 25px" class="comments-empty res-empty">No Comments to Display</div>');
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
                $("#bio").html(highlightHyperlinks(res.bio, false));
                $("#profile-location").text(res.location);
                $(".votes").text(res.votes);
                $("#join-date").text(res.date);

                // Display Vote
                if (res.upvoted) {
                    upVoteClick(false);
                }
                else if (res.downvoted) {
                    downVoteClick(false);
                }

                // Display new Forum Button
                if (res.activeUser) {
                    $(".new-forum").css("display", "block");
                }
                else {
                    $(".new-forum").remove();
                    $(".edit-forum-btn").remove();
                }

                // Hide Disabled Label
                if (res.comments == 1) {
                    $("#comments-disabled-info").css("display", "none");
                }

                // Display Comments
                let accountComments = getProfileComments(0, loadAmounts, username);
                if (accountComments == null || accountComments == "" || accountComments == []) {
                    $("#comments-container").append('<div style="font-size: 25px" class="comments-empty res-empty">No Comments to Display</div>');
                }
                else {
                    loadedComments += loadAmounts;
                    loadComments(accountComments, "append");
                }

                // Display Joined Forums
                let profileForums = getProfileForums(username);

                if (profileForums == null || profileForums == "") {
                    $(".forums-empty").css("display", "block");
                }
                else {
                    loadJoinedForums(profileForums, true);
                }
            }
            else {
                popUp("clientm-fail", res.message, null);
                $("#comments-container").append('<div style="font-size: 25px" class="comments-empty res-empty">No Comments to Display</div>');
                loadErrorProfile();
            } 
        },
        error: function(err) {
            popUp("clientm-fail", "Failed to Contact Server", null);
            loadErrorProfile();
        },
    }).done(function() {
        $("#content").css("display", "block");
        $("#loading-info").css("display", "none");

        console.log("Finished Loading Profile");
    });

}

function loadErrorProfile() {

    // Load Backup Profile Image
    $("#profile-img").attr("src", "/images/avatars/01.png");
}

// Profile User Voting
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
            target: profile
        },
        success: function(res) {
            if (res.success) {
                $(".votes").text(res.votes);
            }
            else {
                $(".upvote").removeClass("upvote-selected");
                $(".downvote").removeClass("downvote-selected");
                $(".votes").removeAttr("style");

                popUp("clientm-fail", res.message, null);
            }
        },
        error: function(err) {
            popUp("clientm-fail", "Failed to Contact Server", null);
        }
    });
}

// Appends a JSON Object containing an account's comment section to the markup
function loadComments(commentsJSON, method) {

    for (let comment in commentsJSON) {
        let nameColour = "lightgrey";

        // Get Rank Colour
        switch (commentsJSON[comment].rank) {
            case "owner":
                nameColour = "violet";
                break;
            case "mod":
                nameColour = "orange";
                break;
        }

        // Load Replies
        let replyHTML = loadReplies(true, commentsJSON[comment].replies);
        
        // Load Comments
        let commentBody = highlightHyperlinks(commentsJSON[comment].content, false);

        let likedClass = "";
        let imgLikedClass = "";
        
        if (commentsJSON[comment].liked) {
            likedClass = " liked";
            imgLikedClass = " like-icon-selected";
        }

        let commentHTML = '<div class="comment-full" ><div class="comment" ><div class="commenter-name" ><a style="color: '+nameColour+'" href="../../profile.php?uquery='+commentsJSON[comment].user+'" >'+commentsJSON[comment].user+'</a> <div class="comment-post-date" >'+commentsJSON[comment].date+'</div></div><div class="likes-container" ><button class="likes-icon'+imgLikedClass+'" ><img title="I Like this Comment" src="/images/other/like-icon.svg" ></button><div class="likes-count'+likedClass+'" >'+commentsJSON[comment].likes+'</div><br /><div name="'+commentsJSON[comment].cid+'" class="del-comment delete-comment noselect" style="display: '+commentsJSON[comment].delDisplay+'" >Delete</div></div><div class="comment-content" >'+commentBody+'</div></div><div class="add-reply" ><input class="add-comment" placeholder="Reply" /><button class="add-comment-btn post-reply-btn" >Post</button></div><div class="replies-container" >'+replyHTML+'</div></div>';
        
        if (method == "append") {
            $("#comments-container").append(commentHTML);
        }
        else {
            $("#comments-container").prepend(commentHTML);
        }
    }
}

// Get Profile Comments From Server with Specified Range
function getProfileComments(min, max, username) {
    let commentsXHR = $.ajax({
        type: "POST",
        url: "../../utils/get_comments.php",
        dataType: "json",
        async: false,
        data: {
            type: "profile",
            username: username,
            min: min,
            max: max
        },
        error: function() {
            $("#comments-container").append('<div style="font-size: 25px" class="comments-empty res-empty">No Comments to Display</div>');
        }
    });

    return commentsXHR.responseJSON.comments;
}

// Get Profile Forums
function getProfileForums(username) {
    let forumsXHR = $.ajax({
        type: "POST",
        url: "../../utils/get_profile_forums.php",
        dataType: "json",
        async: false,
        data: {
            profile: username
        }
    });

    return forumsXHR.responseJSON.forums;
}

// Forum Creation Menu
function openForumMenu() {
    $(".create-forum-modal-bg").css("display", "block");
}

function closeForumMenu() {
    $(".create-forum-modal-bg").css("display", "none");

    $(".create-forum-modal input").val("");
    $(".create-forum-modal textarea").val("Sample Text");
}

// Render Joined Forums JSON as HTML
function loadJoinedForums(forums, append) {
    let container = $("#joined-forums-container");

    $(container).css("display", "block");
    $(".forums-empty").css("display", "none");

    for (let forum in forums) {
        let description = highlightHyperlinks(forums[forum].description, false);
        let html = '<div class="profile-forum" ><img class="profile-forum-icon" src="'+forums[forum].icon+'" ><div class="profile-forum-title" title="Owned By '+forums[forum].owner+'" ><a href="/forum.php?fquery='+forums[forum].name+'" >'+forums[forum].name+'</a><div class="profile-forum-date" >'+forums[forum].date+'</div></div><div class="profile-forum-desc" >'+description+'</div></div>';
        switch (append) {
            case true: $(container).append(html); break;
            default: $(container).prepend(html); break;
        }
    }
}

// Forum Creation
function createForum() {
    let forumName = $("#forum-name").val();
    let forumIconURL = $("#forum-img-url").val();
    let forumDescription = $("#forum-desc-textarea").val();

    // Display 'Loading'
    popUp("clientm-fail", "Loading", null);

    // Client Side Check
    if (forumName.length > 15) {
        popUp("clientm-fail", "Forum Name Must be Under 15 Characters", null);
    }
    else if (forumName.length == 0) {
        popUp("clientm-fail", "Forum Name Must be More than 0 Characters", null);
    }
    else if (forumIconURL.length > 150) {
        popUp("clientm-fail", "Forum Icon URL Must be Under 150 Characters", null);
    }
    else if (forumDescription.length > 300) {
        popUp("clientm-fail", "Forum Description Must be Under 300 Characters", null);
    }
    else if (forumDescription.length == 0) {
        popUp("clientm-fail", "Forum Description Must be More than 0 Characters", null);
    }
    else if (forumIconURL.length > 150) {
        popUp("clientm-fail", "Forum Icon URL Must be Under 150 Characters", null);
    }
    else if (!forumName.replace(/\s/g, "").length) {
        popUp("clientm-fail", "Invalid Forum Name", null);
    }
    else if (!forumDescription.replace(/\s/g, "").length) {
        popUp("clientm-fail", "Invalid Forum Description", null);
    }
    else {

        // Send Request
        $.ajax({
            type: "POST",
            url: "../../utils/create_forum.php",
            dataType: "json",
            data: {
                name: forumName,
                description: forumDescription,
                icon: forumIconURL
            },
            success: function(res) {
                if (res.success) {
                    popUp("clientm-success", "Created Forum!", null);
                    loadJoinedForums(res.forum, false);
                    closeForumMenu();
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

// View User's Forum Posts
let hasLoadedUserPosts = false;

function showUserPosts() {
    $("#user-posts-modal").css("display", "block");

    if (!hasLoadedUserPosts) {
        initForumButtons();

        // Load Forum Posts as Client Scrolls
        $(window).on("scroll", function() { 
            if ($(window).scrollTop() >= $("#forum-posts-container").offset().top + $("#forum-posts-container").offset().top + $("#forum-posts-container").outerHeight() - window.innerHeight) {
                if (!loadedAllPosts) {
                    getUserForumPosts(loadedPosts, postLoadAmounts, false);

                    loadedPosts += postLoadAmounts;
                    showEmptyMsg = false;
                }
            } 
        });
        
        // Send Request
        $("#forum-posts-container").append('<div class="res-empty posts-empty profile-section">Loading Posts...</div>');

        getUserForumPosts(0, postLoadAmounts, true);
        hasLoadedUserPosts = true;
    }
}

function getUserForumPosts(min, max, async) {
    $.ajax({
        type: "POST",
        url: "../../utils/get_forum_posts.php",
        dataType: "json",
        async: async,
        data: {
            username: profile,
            min: min,
            max: max,
            sort: sort
        },
        success: function(res) {
            if (res.success) {
                if (res.posts == [] || res.posts == "" || res.posts == null || res.posts.length < postLoadAmounts) {
                    loadedAllPosts = true;
                }

                $(".posts-empty").remove();

                loadForumPosts(res.posts, true);
                loadedPosts += postLoadAmounts;
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