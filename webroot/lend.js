function success_take_object(post_data) {
    var page = $('#actual_page').val();
    if (post_data === "OK") {
        $.get("objects_list.php?mode=ajax&msg=taken&page=" + page, function (get_data) {
            close_ajax();
            $('#lend_content').html(get_data);
        });
    }
}

function get_checked_objets_ids() {
    if (!$(':checkbox:checked').length) {
        return false;
    }
    var objectsIds = '';
    $(':checkbox:checked').each(function () {
        objectsIds += $(this).val() + ',';
    });
    return objectsIds;
}

function take_more_objects() {
    show_ajax(Math.min(200 + $(':checkbox:checked').length * 100, 600));
    var ids = get_checked_objets_ids();
    $.get('take_more_objects_away.php?mode=ajax&object_ids=' + ids, function (data) {
        $('#ajax_lend').html(data);
    });
}

function ajax_take_more_objects_away() {
    $('#button_container').html('<img src="picts/wait.png" alt="Loading / Chargement en cours ..." height="32" width="32"/>');
    $.ajax({
        type: "POST",
        url: "take_more_objects_away.php",
        data: $('#form_take_more_objects_away').serialize() + "&yes=1",
        success: success_take_object
    });
    return false;
}


function success_give_object_back(post_data) {
    var page = $('#actual_page').val();
    if (post_data === "OK") {
        $.get("objects_list.php?mode=ajax&msg=given&page=" + page, function (get_data) {
            close_ajax();
            $('#lend_content').html(get_data);
        });
    }
}

function give_more_objects_back() {
    show_ajax(Math.min(200 + $(':checkbox:checked').length * 100, 600));

    var ids = get_checked_objets_ids();
    $.get('give_more_objects_back.php?mode=ajax&object_ids=' + ids, function (data) {
        $('#ajax_lend').html(data);
    });
}

function ajax_give_more_objects_back() {
    $('#button_container').html('<img src="picts/wait.png" alt="Loading / Chargement en cours ..." height="32" width="32"/>');
    $.ajax({
        type: "POST",
        url: "give_more_objects_back.php",
        data: $('#form_give_more_objects_back').serialize() + "&yes=1",
        success: success_give_object_back
    });
    return false;
}

function show_ajax(top) {
    var y = mouseY - 230;
    if (typeof top === 'number' && top > 0) {
        y = mouseY - top;
    }

    $('body').append('<div id="ajax_overlay"></div>');
    $('#ajax_overlay').click(close_ajax);
    $('body').append('<div id="ajax_lend"></div>');
    $('#ajax_lend').html('<center><img src="picts/wait.png" alt="Loading / Chargement en cours ..."/></center>');
    $('#ajax_lend').css({"top": y});
}

function close_ajax() {
    $("#ajax_lend").fadeOut(600, function () {
        $("#ajax_overlay").remove();
        $("#ajax_lend").remove();
    });
}

var mouseX = -1;
var mouseY = -1;

$(document).on("mousemove", function (event) {
    mouseX = event.pageX;
    mouseY = event.pageY;
});
