var page = 1;
var pages = 1;
var total = 0;
var rows = 100;
var jqxhr = null;

$('body').tooltip({
    selector: '[data-toggle=tooltip]'
});

$(document).on('mousedown', function () {
    $('.searchbox > div').hide();
    if (jqxhr) {
        jqxhr.abort();
		jqxhr = null;
	}
});

function search() {
    var gene = $('#gene').val();

    $('.searchbox > div').hide();
    if (jqxhr) {
        jqxhr.abort();
		jqxhr = null;
	}

    $('input, button, select').prop('disabled', true);

    $.post('reverse.php', {
            gene: gene,
            page: page,
            rows: rows
        })
        .done(function (json) {
			if(json.length == 0) {
                $('#error_panel').html('<h2>Server Timeout</h2>').show();
				return;
			}
            json = JSON.parse(json);
            if (json.success) {
                total = json.total;
                pages = json.pages;
                $('#total_span').text(json.total + ' Total miRNA');
                $('#pages_span').text(json.pages);
                $('#page_span').text(json.page);
                $('#table_div').html(json.html);
                $('#error_panel').hide();
                $('#content').show();
            }
            else {
                $('#content').hide();
                $('#error_panel').html('<h2>' + json.error + '</h2>').show();
            }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            $('#content').hide();
			$('#error_panel').html('<h2>Server Timeout</h2>').show();
        })
        .always(function () {
            $('input, button, select').prop('disabled', false);
        });
}

$('#search_form').on('submit', function (e) {
    e.preventDefault();
    $('#error_panel').html('<h2>Searching...</h2>').show();
    search();
});

$('#rows_select').on('change', function () {
    rows = $(this).val();
    search();
});

$('#goto_page_form').on('submit', function (e) {
    e.preventDefault();

    var input = $(this).find('input[type=text]');
    page = parseInt(input.val(), 10);
    input.val('');
    search();
});

$('#goto_page_form').find('input[type=text]').on('keydown', function (e) {
    var key = (96 <= e.keyCode && e.keyCode <= 105) ? e.keyCode - 48 : e.keyCode;
    if (key != 8 && key != 13 && (key < 48 || key > 57))
        return false;

    if (key != 8 && key != 13) {
        var n = parseInt($(this).val() + String.fromCharCode(key));
        if (n < 1 || n > pages)
            return false;
    }
});

function first() {
    page = 1;
    search();
}

function prev() {
    page = Math.max(1, page - 1);
    search();
}

function next() {
    page = Math.min(pages, page + 1);
    search();
}

function last() {
    page = pages;
    search();
}

$('.searchbox').on('mousedown', 'div', function (e) {
    e.stopPropagation();
});

$('.searchbox').on('blur', function() {
	if (jqxhr) {
        jqxhr.abort();
		jqxhr = null;
	}
});

$('.searchbox').on('click', 'div > ul > li', function () {
    $(this).parent().toggleClass('collapsed').find('ul').toggle();
});

$('.searchbox > div')
    .on('keydown', function (e) {
        e.preventDefault();

        var p = $(this).find('p:focus');

        if (e.which == 38) {
            var prev = p.prev('p');
            if (prev.length) {
                prev.focus();
            }
        }
        else if (e.which == 40) {
            var next = p.next('p');
            if (next.length) {
                next.focus();
            }
        }
    })
    .on('keydown', 'p', function (e) {
        if (e.which == 9 || e.which == 13) {
            $(this).closest('div').hide();
            $(this).closest('div').prev().val($(this).text()).focus();
            if (e.which == 9)
                $(this).closest('div').parent().parent().next().find('input, button').focus();
        }
    })
    .on('mousemove', 'p', function () {
        $(this).focus();
    })
    .on('mousedown', 'p', function () {
        $(this).closest('div').prev().val($(this).text());
        $(this).closest('div').hide();
    });

$('#gene')
    .on('input', function () {
        query(false);
    })
    .on('keydown', function (e) {
        if (e.which == 40) {
            if ($(this).next().is(':visible')) {
                $(this).next().find('p:first').focus();
            }
            else {
                query(true);
            }
            return false;
        }
    });

function query(focus) {
    if (focus)
        $('#gene').prop('disabled', true);

    $('#gene').next().html('<h5>Searching...</h5>').show();

    if (jqxhr) {
        jqxhr.abort();
		jqxhr = null;
	}

    jqxhr = $.post('reverse.php', {
            query: $('#gene').val()
        })
        .done(function (json) {
            if(json.length == 0) {
                $('#error_panel').html('<h2>Server Timeout</h2>').show();
				return;
			}
            json = JSON.parse(json);
            if (json.success && jqxhr) {
                $('#gene').next().html(json.html).show();
                $('#gene').next().find('p').on('click', function () {
                    $('#gene').val($(this).text());
                });
                if (focus) {
                    $('#gene').next().find('p:first').focus();
                }
            }
            else {
                $('#gene').next().html('<h5>' + json.error + '</h5>').show();
            }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            if (errorThrown != 'abort')
                alert(textStatus + ':' + errorThrown);
        })
        .always(function () {
            $('#gene').prop('disabled', false);
        });
    }

