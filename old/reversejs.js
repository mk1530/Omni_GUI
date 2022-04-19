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

function enableAll() {
    $('input, button, select').prop('disabled', false);
    $('.sorting').removeClass('disabled');

    $('#first_btn').prop('disabled', page == 1);
    $('#prev_btn').prop('disabled', page == 1);
    $('#next_btn').prop('disabled', page == pages);
    $('#last_btn').prop('disabled', page == pages);

    $('[name=database_filter]').prop('checked', false);
    for (var i = 0; i < database_filter.length; ++i) {
        $('[name=database_filter][value=' + database_filter[i] + ']').prop('checked', true);
    }
    $('[name=database_operator][value=' + database_operator + ']').prop('checked', true);
    $('[name=validation_filter][value=' + validation_filter + ']').prop('checked', true);
    $('[name=pubmed_filter][value=' + pubmed_filter + ']').prop('checked', true);

    if (selected.length == 0) {
        $('[name=download_radio][value=selected]').prop('disabled', true);
        $('[name=download_radio][value=partial]').prop('disabled', true);
        $('[name=download_radio][value=all]').prop('checked', true);
        $('[name=format_radio]').prop('disabled', false);
    }
    else {
        $('[name=download_radio][value=selected]').prop('disabled', false);
        $('[name=download_radio][value=partial]').prop('disabled', false);
    }

    if ($('[name=download_radio][value=selected]').is(':checked')) {
        $('[name=format_radio]').prop('disabled', true);
    }
    else {
        $('[name=format_radio]').prop('disabled', false);
    }
}

function search() {
    var gene = $('#gene').val();

    $('.searchbox > div').hide();
    if (jqxhr) {
        jqxhr.abort();
		jqxhr = null;
	}

    $('input, button, select').prop('disabled', true);

    $.post('reversesearch.php', {
            
            gene: gene,
            page: page,
            rows: rows,
            sort_dir: sort_dir,
            sort_col: sort_col,
            selected: JSON.stringify(selected),
            validation_filter: validation_filter,
            database_filter: JSON.stringify(database_filter),
            database_operator: database_operator,
            pubmed_filter: pubmed_filter
        })
         .done(function (json) {
			if(json.length == 0) {
				$('#content').hide();
                $('#result_controls').hide();
                $('#error_panel').html('<h2>Server Timeout</h2>').show();
				enableAll();
				return;
			}
            json = JSON.parse(json);
            if (json.success) {
                page = parseInt(json.page, 10);
                pages = parseInt(json.pages, 10);
                total = parseInt(json.total, 10);
                targets = json.targets;

                document.title = 'OmniSearch: ' + mirna;

                $('#page_span').text(page);
                $('#pages_span').text(pages);
                $('#total_span').text(total + ' Total Targets');
                $('#download_all_lbl').text(total);
                $('#table_div').html(json.html);
                $('#rna_central_link').attr('href', 'http://rnacentral.org/search?q=' + mirna + '%20AND%20TAXONOMY:%229606%22');

                $('#error_panel').hide();
                $('#result_controls').show();
                $('#content').show();
            }
            else {
                $('#content').hide();
                $('#result_controls').hide();
                $('#error_panel').html('<h2>' + json.error + '</h2>').show();
            }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            $('#content').hide();
			$('#result_controls').hide();
			$('#error_panel').html('<h2>Server Timeout</h2>').show();
        })
        .always(function () {
            enableAll();
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

    jqxhr = $.get('reversequery.php', {
            
            gene: $('#gene').val()
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