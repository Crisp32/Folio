/**
 * Folio Client End Search Functionality
 * @author Connell Reffo
 */

function initSearch() {

    // View Member Button
    $(document).on("click", ".view-member", function (e) {
        let search = $(this).parent().parent().attr("data-search");
        let type = $(this).parent().parent().attr("data-type");
        let URL;

        if (type == "user") {
            URL = "/profile.php?uquery=" + search;
        }
        else {
            URL = "/forum.php?fquery=" + search;
        }

        location.replace(URL);
    });
}

function search() {
    let term = $("#user-search").val();
    let filter = $(".search-filter").val();

    // Get Search Result from Server
    if (term != "" && term != null) {

        // Display Searching
        $(".search-res-empty").text("Searching...");

        $.ajax({
            type: "POST",
            url: "../../utils/search.php",
            dataType: "json",
            data: {
                term: term,
                filter: filter
            },
            success: function(res) {
                // Display Results
                if (res != [] && res != null && res != "") {
                    let html = "";

                    for (let key in res) {
                        let search = res[key];
                        html += '<div class="profile-forum" data-search="'+search.name+'" data-type="'+search.type+'" ><img class="member-img" src="'+search.profileImage+'" ><div class="forum-member-name" >'+search.name+' <div class="bullet-point">-&gt;</div> ('+search.type+')</div><br /><div class="member-options" ><button class="view-member member-default-option" >View</button></div></div>';
                    }

                    if (html != null && html != "") {
                        $("#search-res").html(html);

                        // Hide Empty Message
                        $(".search-res-empty").css("display", "none");
                    }
                }
                else {
                    $("#search-res").empty();
                    $(".search-res-empty").css("display", "block");
                }
            },
            error: function(err) {
                popUp("clientm-fail", "Failed to Contact Server", null);
            }
        }).done(function() {
            $(".search-res-empty").text("Nothing to see Here");
        });
    }
}

// Search Buttons
function openSearchMenu() {
    $("#search-menu").css("display", "block");
}

function closeSearchMenu() {
    $("#search-menu").css("display", "none");
}