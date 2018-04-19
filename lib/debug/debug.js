function appExpandTabs(act, key) {
	var arrDebugTabs = ["General", "Params", "Globals", "Queries", "SqlTrace"];

	keyTab = (key == null)
		? "General"
		: key;

	for (var i = 0; i < arrDebugTabs.length; i++) {
		if (act == "min" || arrDebugTabs[i] != keyTab) {
			$("#content" + arrDebugTabs[i]).css("display", "none");
			$("#tab" + arrDebugTabs[i]).css("color", "#bbb")
		}
	}
	if (act != "min") {
		$("#content" + keyTab).css("display", "");
		$("#content" + keyTab).css({
			"overflow-y": "auto"
		});
		$("#tab" + keyTab).css("color", "#222")
	}

	$("#debug-panel").css("opacity", (act == "min") ? "0.9" : "1");
};


function appTabsHide()
{
	$('#debug-panel-legend span a').css("color", "#bbb");
	$("#debug-panel .items").hide();
}