/**
 * Folio Home Page Javascript
 * @author Connell Reffo
 */

function triggerOnLoad() {
    loadSuggestedForumPosts();

    

    // Load Forum Posts as Client Scrolls
    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() == $(document).height()) {
            if (!loadedAllPosts) {
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