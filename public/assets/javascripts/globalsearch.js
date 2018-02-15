/*jslint browser: true, unparam: true, nomen: true */
/*global jQuery, STUDIP, _ */
(function ($, STUDIP, _) {
    'use strict';

    STUDIP.GlobalSearch = {
        lastSearch: null,

        /**
         * Toggles visibility of search input field and hints.
         * @param visible boolean indicating whether shown or not
         * @param cleanup boolean whether to clear search term and results
         * @returns {boolean}
         */
        toggleSearchBar: function (visible, cleanup) {
            $('#globalsearch-searchbar').toggleClass('is-visible', visible);

            if (!visible && cleanup) {
                $('#globalsearch-searchbar').removeClass('has-value');
                $('#globalsearch-results').html('');
                $('#globalsearch-input').blur().val('');
            }

            return false;
        },

        /**
         * Performs the actual search.
         */
        doSearch: function () {
            var searchterm      = $('#globalsearch-input').val().trim(),
                hasValue        = searchterm.length >= 3,
                resultsDiv      = $('#globalsearch-results'),
                resultsPerType  = resultsDiv.data('results-per-type'),
                moreResultsText = resultsDiv.data('more-results'),
                wrapper         = $('#globalsearch-searchbar');
            if (searchterm === '') {
                return;
            }

            wrapper.toggleClass('has-value', hasValue);

            if (!hasValue || STUDIP.GlobalSearch.lastSearch === searchterm) {
                return;
            }

            STUDIP.GlobalSearch.lastSearch = searchterm;

            // Display spinner symbol, user should always see something is happening.
            wrapper.addClass('is-searching');

            // Call AJAX endpoint and get search results.
            $.getJSON(STUDIP.URLHelper.getURL('dispatch.php/globalsearch/find'), {
                search: searchterm
            }).done(function (json) {
                resultsDiv.html('');

                // No results found...
                if (!$.isPlainObject(json) || $.isEmptyObject(json)) {
                    wrapper.removeClass('is-searching');
                    resultsDiv.html(resultsDiv.data('no-result'));
                    return;
                }

                // Iterate over each result category.
                $.each(json, function (name, value) {
                    // Create an <article> for category.
                    var category = $('<article id="globalsearch-' + name + '">'),
                        header   = $('<header>').appendTo(category),
                        counter  = 0;

                    // Create header name
                    $('<a href="#">').text(value.name)
                        .wrap('<div class="globalsearch-category">')
                        .parent() // Element is now the wrapper
                        .data('category', name)
                        .appendTo(header);

                    // We have more search results than shown, provide link to
                    // full search if available.
                    if (value.more && value.fullsearch !== '') {
                        $('<a>').attr('href',  value.fullsearch)
                            .text(moreResultsText)
                            .wrap('<div class="globalsearch-more-results">')
                            .parent() // Element is now the wrapper
                            .appendTo(header);
                    }

                    // Process results and create corresponding entries.
                    $.each(value.content, function (index, result) {
                        // Create single result entry.
                        var single  = $('<section>'),
                            data    = $('<div class="globalsearch-result-data">'),
                            details = $('<div class="globalsearch-result-details">');

                        if (counter >= resultsPerType) {
                            single.addClass('globalsearch-extended-result');
                        }

                        var link = $('<a href="' + result.url + '">')
                            .appendTo(single);

                        // Optional image...
                        if (result.img !== null) {
                            $('<img src="' + result.img + '">')
                                .wrap('<div class="globalsearch-result-img">')
                                .parent() // Element is now the wrapper
                                .appendTo(link);
                        }

                        link.append(data);

                        // Name/title
                        $('<div class="globalsearch-result-title">')
                            .html($.parseHTML(result.name))
                            .appendTo(data);

                        // Details: Descriptional text
                        if (result.description !== null) {
                            $('<div class="globalsearch-result-description">')
                                .html($.parseHTML(result.description))
                                .appendTo(details);
                        }

                        // Details: Additional information
                        if (result.additional !== null) {
                            $('<div class="globalsearch-result-additional">')
                                .html($.parseHTML(result.additional))
                                .appendTo(details);
                        }

                        data.append(details);

                        // Date/Time of entry
                        if (result.date !== null) {
                            $('<div class="globalsearch-result-time">')
                                .html($.parseHTML(result.date))
                                .appendTo(link);
                        }

                        // "Expand" attribute for further, result-related search
                        // (e.g. search in course of found forum entry)
                        if (result.expand !== null && result.expand !== value.fullsearch && value.more) {
                            $('<a href="' + result.expand + '" title="' + result.expandtext + '">')
                                .wrap('<div class="globalsearch-result-expand">')
                                .parent() // Element is now the wrapper
                                .appendTo(single);
                        }
                        category.append(single);

                        counter += 1;
                    });
                    resultsDiv.append(category);
                });

                wrapper.removeClass('is-searching');
            }).fail(function (xhr, status, error) {
                window.alert(error);
            });
        },

        /**
         * Clear search term and remove results for previous search term.
         */
        resetSearch: function () {
            STUDIP.GlobalSearch.lastSearch = null;

            $('#globalsearch-searchbar').removeClass('is-visible has-value');
            $('#globalsearch-input').val('');
            $('#globalsearch-results').html('');
            $('#globalsearch-input').focus();
        },

        /**
         * Expand a single category, showing more results, and hide other
         * categories.
         * @param category
         * @returns {boolean}
         */
        expandCategory: function (category) {
            // Hide other categories.
            $('#globalsearch-results article:not([id="globalsearch-' + category + '"])').hide();
            // Show all results.
            $('#globalsearch-' + category + ' section.globalsearch-extended-result').
                removeClass('globalsearch-extended-result');
            $('article#globalsearch-' + category).get(0).scrollIntoView();
            // Reassign category click to closing extended view.
            $('#globalsearch-results article#globalsearch-' + category + ' header a').
                off('click').
                on('click', function () {
                    STUDIP.GlobalSearch.showAllCategories(category);
                    return false;
                });
            return false;
        },

        /**
         * Close expanded view of a single category, showing normal view with
         * all categories again.
         * @param currentCategory
         */
        showAllCategories: function (currentCategory) {
            $('#globalsearch-results article#globalsearch-' + currentCategory + ' header a').
                off('click').
                on('click', function () {
                    STUDIP.GlobalSearch.expandCategory(currentCategory);
                    return false;
                });
            var resultCount = $('#globalsearch-results').data('results-per-type') - 1;
            $('#globalsearch-' + currentCategory + ' section:gt(' + resultCount + ')').
                addClass('globalsearch-extended-result');
            $('#globalsearch-results').children('article:not([id="globalsearch-' + currentCategory + '"])').show();
            return false;
        }
    };

    $(function () {
        // Clear search term
        $('#globalsearch-clear').on('click', function () {
            STUDIP.GlobalSearch.resetSearch();
            return false;
        });

        // Bind icon click to performing search.
        $('#globalsearch-icon').on('click', function () {
            STUDIP.GlobalSearch.doSearch();
            return false;
        });

        // Enlarge search input on focus and show hints.
        $('#globalsearch-input').on('focus', function () {
            STUDIP.GlobalSearch.toggleSearchBar(true, false);
        });

        // Start search on Enter
        $('#globalsearch-input').on('keypress', function (e) {
            if (e.which === 13) {
                STUDIP.GlobalSearch.doSearch();
                return false;
            }
        });

        // Close search on click on page.
        $('div#flex-header, div#layout_page, div#layout_footer').on('click', function () {
            if (!$('#globalsearch-input').hasClass('hidden-js')) {
                STUDIP.GlobalSearch.toggleSearchBar(false, false);
            }
        });

        // Show/hide hints on click.
        $('#globalsearch-togglehints').on('click', function () {
            var toggle      = $('#globalsearch-togglehints'),
                currentText = toggle.text();

            toggle.text(toggle.data('toggle-text').trim());
            toggle.data('toggle-text', currentText);

            toggle.toggleClass('open');
        });

        // Delegate events on result container so we don't have to bind them
        // one by one
        $('#globalsearch-results').on('click', '.globalsearch-category a', function () {
            var category = $(this).closest('.globalsearch-category').data('category');
            STUDIP.GlobalSearch.expandCategory(category);
            return false;
        });

        // Key bindings.
        $(document).keydown(function (e) {
            // ctrl + space
            if (e.which === 32 && e.ctrlKey && !e.altKey && !e.metaKey && !e.shiftKey) {
                e.preventDefault();
                if ($('#globalsearch-searchbar').hasClass('is-visible')) {
                    STUDIP.GlobalSearch.toggleSearchBar(false, false);
                    $('#globalsearch-input').blur();
                } else {
                    $('#globalsearch-input').focus();
                }
            // escape
            } else if (e.which === 27 && !e.ctrlKey && !e.altKey && !e.metaKey && !e.shiftKey) {
                e.preventDefault();
                STUDIP.GlobalSearch.toggleSearchBar(false, true);
            }
        });

        // Start searching 750 ms after user stopped typing.
        $('#globalsearch-input').keyup(_.debounce(function () {
            STUDIP.GlobalSearch.doSearch();
        }, 750));
    });

}(jQuery, STUDIP, _));
