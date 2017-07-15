$(document).ready(function () {
    $(function () {
    
        $("#q").autocomplete({
            source: function (request, response) {
                $.ajax({
                    url: "autocomp.php?lookup=" + request.term,
                    type: "GET",
                    success: function (data) {
                        var suggestionList  = [];
                        parsedData = JSON.parse(data);
                        parsedData.forEach(function(item) {
                        	if(!/[@.~`_!#$%\^&*+=\-\[\]\\"\\'\;,\/{}|:<>\?]/g.test(item)) {
                        		suggestionList.push(item);
                        	}
                        });
                        response(suggestionList); 
                    }
                });
            },
            minLength: 1,
            select: function (event, ui) {
                var str1 = ui.item.label;
                ui.item.value = str1;
            },
            open: function () {
                $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
            },
            close: function () {
                $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
            }
        });
    });
});
