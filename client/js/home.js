/**
 * Folio Home Page Javascript
 * @author Connell Reffo
 */

// Global Variables
let canStart = false;

function triggerOnLoad() {
    loadTopUsers();
    loadTopForums();
    loadSuggestedForumPosts();

    // Load Forum Posts as Client Scrolls
    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() == $(document).height()) {
            if (!loadedAllPosts && canStart) {
                $.ajax({
                    type: "POST",
                    url: "../../utils/get_forum_posts.php",
                    dataType: "json",
                    data: {
                        suggested: true,
                        min: loadedPosts,
                        max: postLoadAmounts,
                        sort: "popular"
                    },
                    success: function(res) {
                        if (res.success) {
                            if (res.posts == [] || res.posts == "" || res.posts == null || res.posts.length < postLoadAmounts) {
                                loadedAllPosts = true;
                            }
 
                            loadForumPosts(res.posts, true);
                            loadedPosts += postLoadAmounts;
                        }
                        else {
                            loadedAllPosts = true;
                        }
                    },
                    error: function(err) {
                        popUp("clientm-fail", "Failed to Contact Server", null);
                    }
                });

                showEmptyMsg = false;
            }
        }
    });
}

function loadSuggestedForumPosts() {
    $.ajax({
        type: "POST",
        url: "../../utils/get_forum_posts.php",
        dataType: "json",
        data: {
            suggested: true,
            min: 0,
            max: postLoadAmounts,
            sort: "popular"
        },
        success: function(res) {
            if (res.success) {
                let noMorePosts = (res.posts.length < postLoadAmounts);

                if (res.posts == [] || res.posts == "" || res.posts == null || noMorePosts) {
                    if (noMorePosts) {
                        loadedAllPosts = true;
                        $(".posts-empty").remove();
                    }
                    else {
                        $(".posts-empty").text("No Posts to Display");
                    }
                }
                else {
                    $(".posts-empty").remove();
                }

                loadForumPosts(res.posts, true);
                loadedPosts += postLoadAmounts;

                canStart = true;
                initForumButtons();
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

function loadTopUsers() {
    $("#top-users-container").find(".res-empty").text("Loading Users...");

    $.ajax({
        type: "POST",
        url: "../../utils/get_top_content.php",
        dataType: "json",
        data: {
            contentType: "users"
        },
        success: function(res) {
            if (res.success) {
                if (res.users.length > 0) {
                    $("#top-users-container").empty();
                    renderUserList(res.users);
                }
                else {
                    $("#top-users-container").find(".res-empty").text("No Content to Display");
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

function loadTopForums() {
    $("#popular-forums-container").find(".res-empty").text("Loading Forums...");

    $.ajax({
        type: "POST",
        url: "../../utils/get_top_content.php",
        dataType: "json",
        data: {
            contentType: "forums"
        },
        success: function(res) {
            if (res.success) {
                if (res.users.length > 0) {
                    $("#popular-forums-container").empty();
                    renderForumList(res.users);
                }
                else {
                    $("#popular-forums-container").find(".res-empty").text("No Content to Display");
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

function renderUserList(usersJSON) {
    for (let u = 0; u < usersJSON.length; u++) {
        let user = usersJSON[u];

        let voteWord = " Votes";

        if (user.votes == 1 || user.votes == -1) {
            voteWord = " Vote";
        }

        let html = '<div class="home-list-item" ><div class="item-num" >'+(u + 1)+' <div class="bullet-point" style="color: #0cdc00" >&#8226;</div></div><img class="item-icon" src="'+user.image+'" ><div class="item-title-sub" ><div class="item-title" ><a href="/profile.php?uquery='+user.username+'" >'+user.username+'</a></div><div class="item-sub" >'+user.votes+voteWord+'</div></div></div>';
        $("#top-users-container").append(html);
    }
}

function renderForumList(forumsJSON) {
    for (let f = 0; f < forumsJSON.length; f++) {
        let forum = forumsJSON[f];

        let memberWord = " Member";

        if (forum.members > 1) {
            memberWord = " Members";
        }

        let html = '<div class="home-list-item" ><div class="item-num" >'+(f + 1)+' <div class="bullet-point" style="color: #0cdc00" >&#8226;</div></div><img class="item-icon" src="'+forum.icon+'" ><div class="item-title-sub" ><div class="item-title" ><a href="/forum.php?fquery='+forum.name+'" >'+forum.name+'</a></div><div class="item-sub" >'+forum.members+memberWord+'</div></div></div>';
        $("#popular-forums-container").append(html);
    }
}