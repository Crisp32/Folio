/**
 * Folio Profile Manager Front End
 * Connell Reffo 2019
 */

function triggerOnLoad() {

    // Load Profile Data into Page
    loadProfile(profile);

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

    // Delete Comment
    $(document).on("click", ".del-comment", function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();

        let element = this;

        // Send Request
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
                    $(element).parent().parent().parent().remove();
                    if ($(".delete-comment").length == 0) {
                        $("#comments-container").prepend('<div style="font-size: 25px" class="res-empty">No Comments to Display</div>');
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
    });

    // Delete Reply
    $(document).on("click", ".reply-del-comment", function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();

        let element = this;
        let cid = $(element).parent().parent().parent().parent().siblings(".comment").find(".del-comment").attr("name");

        // Send Request
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
                popUp("clientm-fail", "Server Request Error: " + err, null);
            }
        });
    });

    // Reply to Comment
    $(document).on("click", ".post-reply-btn", function (e) {
        let replyContent = $(this).siblings(".add-comment").val();
        let commentCID = $(this).parent().siblings(".comment").find(".delete-comment").attr("name");

        let element = this;

        // Client Side Validation
        if (replyContent.length > 120) {
            popUp("clientm-fail", "Reply must not Exceed 120 Characters", null);
        }
        else if (replyContent.length == 0) {
            popUp("clientm-fail", "Reply must be Greater than 0 Characters", null);
        }
        else {
            // Send Request
            $.ajax({
                type: "POST",
                url: "../../utils/add_reply.php",
                dataType: "json",
                data: {
                    cid: commentCID,
                    content: replyContent,
                    type: "profile"
                },
                success: function(res) {
                    if (res.success) {
                        popUp("clientm-success", res.message, null);
    
                        // Clear Input Field
                        $(element).siblings(".add-comment").val("")
    
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
                    popUp("clientm-fail", "Server Request Error: " + err, null);
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
                $("#bio").text(res.bio);
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

                // Hide Disabled Label
                if (res.comments == 1) {
                    $("#comments-disabled-info").css("display", "none");
                }

                // Display Comments
                if (res.accountComments == null || res.accountComments == "") {
                    $("#comments-container").append('<div style="font-size: 25px" class="res-empty">No Comments to Display</div>');
                }
                else {
                    loadComments(res.accountComments);
                }
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

    // Comment Liking
    $(document).on("click", "a.likes-icon > img", function (e) {
        e.stopPropagation();
        e.stopImmediatePropagation();

        let element = this;
        let cid = $(element).parent().siblings(".del-comment").attr("name");

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
                    }
                    else {
                        $(element).parent().siblings(".likes-count").removeClass("liked");
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
    });

}

function loadErrorProfile() {

    // Load Backup Profile Image
    $("#profile-img").attr("src", "/images/avatars/01.png");
}

// Appends a JSON Object containing an account's comment section to the markup
function loadComments(commentsJSON) {

    let index = 0;
    for (let comment in commentsJSON) {

        // Load Replies
        let replyHTML = loadReplies(true, commentsJSON[comment][index].replies);

        // Load Comments
        let likedClass = "";
        if (commentsJSON[comment][index].liked) {
            likedClass = " liked";
        }

        let commentHTML = '<div class="comment-full" ><div class="comment" ><div class="commenter-name" ><a href="../../profile.php?uquery='+commentsJSON[comment][index].user+'" >'+commentsJSON[comment][index].user+'</a> <div class="comment-post-date" >'+commentsJSON[comment][index].date+'</div></div><div class="likes-container" ><a class="likes-icon" ><img title="I Like this Comment" src="/images/other/like-icon.png" ></a><div class="likes-count'+likedClass+'" >'+commentsJSON[comment][index].likes+'</div><br /><div name="'+commentsJSON[comment][index].cid+'" class="del-comment delete-comment noselect" style="display: '+commentsJSON[comment][index].delDisplay+'" >Delete</div></div><div class="comment-content" >'+commentsJSON[comment][index].content+'</div></div><div class="add-reply" ><input class="add-comment" placeholder="Reply" /><button class="add-comment-btn post-reply-btn" >Post</button></div><div class="replies-container" >'+replyHTML+'</div></div>';
        
        $("#comments-container").prepend(commentHTML);

        index++;
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
            replyHTML += '<div class="comment reply" ><div class="reply-indent" ></div><div class="commenter-name" >'+replies[reply].user+' <div class="comment-post-date" >'+replies[reply].date+'</div></div><div><div name="'+replies[reply].rid+'" class="delete-comment reply-del-comment noselect" style="display: '+replies[reply].delDisplay+'" >Delete</div></div><div class="comment-content" style="margin-bottom: 5px" >'+replies[reply].content+'</div></div>';
        }
        replyHTML += endReplyTag;
    }

    return replyHTML;
}