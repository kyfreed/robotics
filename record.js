(function ($) {

    // when a button is clicked...
    $('button').click(function (e) {
        if ($(this).hasClass("up")) {
            $(this).siblings("input")[0].value++;
			save();
        } else if ($(this).hasClass("down")) {
            $(this).siblings("input")[0].value--;
			save();
        }
        if ($(this).attr("id") == "send") {
			save();
            setState('blank');
            send();
        }
    });
	// when an input is edited...
	$("#edit input").change(function () {
		save();
	});	
    // call fastclick for use in mobile browsers
    FastClick.attach(document.body);
	
	$(window).on("hashchange", onHashChange);
	onHashChange();
	
	// Try to send data every 20 seconds
	setInterval(send, 20000);
	
})(jQuery);
$(document).ready(function () {
    if(location.hash.split("/")[2] == "" || location.hash.split("/")[2] == undefined){
        var now = new Date();
        var time = now.getHours();
		if (time > 12) {
			time -= 12;
		}
		time += ":" + (now.getMinutes()<10 ? "0" : "") + now.getMinutes();
        $("#time").val(time);
        $("#team_number").val(753);
    } else {
        $("#time").val(decodeURI(location.hash.split("/")[2]));
        $("#team_number").val(location.hash.split("/")[1]);
    }
});

function setState(state) {
    document.body.className = state;
	if (state=="blank") {
		location.hash = '/' + $('#team_number').val();
	}
}
function setHash(newhash) {
	newhash = "#" + newhash;
	if (location.hash == newhash) {
		onHashChange();
	} else {
		location.hash = newhash;
	}
}
function getState(){
    return document.body.className;
}
function onHashChange() {
    $("#team_number").val("");
    var hash = decodeURI(location.hash).split("/");
    if (hash[1] && hash[1].length) {
        $("#team_number").val(hash[1]);
    }
    if (hash[2] && hash[2].length) {
        $("#time").val(hash[2]);
    }
	$("#team_number").val(hash[1] || "");
	$("#time").val(hash[2] || "");
	setState("blank");
    if (hash[1] && hash[1].length && hash[2] && hash[2].length) {
        read();
    }
}
function getTeamnumberTime() {
	var team = $("#team_number").val();
	if (team.length < 2) {
		alert("Invalid Team");
		return false;
	}
	return team + "_" + $("#time").val();
}
function read() {
    var teamnumber_time = getTeamnumberTime();
	if (teamnumber_time === false) {
		return;
	}
    setState("loading");
    var db = getDB();
    if (db[teamnumber_time]) {
        load(db[teamnumber_time]);
    } else {
        var obj = teamnumber_time.split("_");
        $.ajax({
            type: "POST",
            url: "read.php",
            data: {team: obj[0], time: obj[1]},
            success: function (msg) {
                load(msg);
            },
            error: function(){
                load({})
            },
            dataType: "json"
        });
    }
}
function getDB() {
    return JSON.parse(localStorage["unsavedactions"] || "{}");
}
function save() {
    if(getState() != "edit"){
        return;
    }
    var teamnumber_time = getTeamnumberTime();
	if (teamnumber_time === false) {
		return;
	}
    var now = new Date();
    var time = now.toTimeString().substr(0, 5);
    var date = now.getFullYear() + "-" + ((now.getMonth() + 1 < 10) ? "0" : "") + (now.getMonth() + 1) + "-" + ((now.getDate() < 10) ? "0" : "") + now.getDate() + " ";
    var data = {modified_on: date + time};
    $("input").each(function () {
	if ($(this).attr("type") == "text") {
            data[this.name] = $(this).val();
        } else if ($(this).attr("type") == "checkbox") {
            data[this.name] = ($(this).is(":checked") ? 1 : 0);
        }
    });
    data["time"] = $("#time").val();
    var db = getDB();
    db[teamnumber_time] = data;
    saveDB(db);
}
function saveDB(db) {
	var unsaved = 0;
	$.each(db, function() {
		if (this)
			unsaved++;
	});
	$(".save-indicator").html(unsaved).toggle(unsaved ? true : false);
    localStorage["unsavedactions"] = JSON.stringify(db);
}
function load(msg) {
    $("#edit input[type=text]").val(0);
    $("#edit input[type=checkbox]").each(function(){ this.checked=false; });
    $.each(msg, function (key, value) {
        $("input[type=text][name=" + key + "]").val(value);
        $("input[type=checkbox][name=" + key + "]").prop("checked", parseInt(value));
        $("input[type=checkbox][name=17]").prop("checked", 1)
    });
    setState("edit");
}
function send() {
	if (!navigator.onLine) {
		return;
	}
    var db = getDB();
    $.each(db, function (key, value) {
        jsonvalue = JSON.stringify(value);
        $.post("save.php", {info: jsonvalue},function(){
            var ldb = getDB();
            delete ldb[key];
            saveDB(ldb);
        });
    });
}
