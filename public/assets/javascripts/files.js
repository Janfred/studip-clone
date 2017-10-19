/*jslint browser: true, unparam: true */
/*global jQuery, STUDIP */

(function ($, STUDIP) {
    'use strict';

    STUDIP.Files = {
        openAddFilesWindow: function (folder_id) {
            if ($('.files_source_selector').length > 0) {
                STUDIP.Dialog.show($('.files_source_selector').html(), {
                    title: 'Datei hinzufügen'.toLocaleString(),
                    size: 'auto'
                });
            } else {
                STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL('dispatch.php/file/add_files_window/' + folder_id), {
                    title: 'Datei hinzufügen'.toLocaleString(),
                    size: 'auto'
                });
            }
        },
        validateUpload: function (file) {
            if (!STUDIP.Files.uploadConstraints) {
                return true;
            }
            if (file.size > STUDIP.Files.uploadConstraints.filesize) {
                return false;
            }
            var ending = file.name.lastIndexOf('.') !== -1
                       ? file.name.substr(file.name.lastIndexOf('.') + 1)
                       : '';

            if (STUDIP.Files.uploadConstraints.type === 'allow') {
                return $.inArray(ending, STUDIP.Files.uploadConstraints.file_types) === -1;
            }

            return $.inArray(ending, STUDIP.Files.uploadConstraints.file_types) !== -1;
        },
        upload: function (filelist) {
            var files = 0,
                folder_id = $('.files_source_selector').data('folder_id'),
                data = new FormData();

            //Open upload-dialog
            $('.file_upload_window .filenames').html('');
            $('.file_upload_window .errorbox').hide();
            $.each(filelist, function (index, file) {
                if (file.size > 0) {
                    if (STUDIP.Files.validateUpload(file)) {
                        data.append('file[]', file);
                        $('<li/>').text(file.name).appendTo('.file_upload_window .filenames');
                        files += 1;
                    } else {
                        $('.file_upload_window .errorbox').show();
                        $('.file_upload_window .errorbox .errormessage').text('Datei ist zu groß oder hat eine nicht erlaubte Endung.'.toLocaleString());
                    }
                }
            });
            if ($('.file_uploader').length > 0) {
                STUDIP.Dialog.show($('.file_uploader').html(), {
                    title: 'Datei hochladen'.toLocaleString()
                });
            } else {
                STUDIP.Dialog.fromURL(STUDIP.URLHelper.getURL('dispatch.php/file/upload_window'), {
                    title: 'Datei hochladen'.toLocaleString()
                });
            }

            //start upload
            $('.documents[data-folder_id] tbody > tr.dragover').removeClass('dragover');
            if (files > 0) {
                $('.file_upload_window .uploadbar').show().css('background-size', '0% 100%');
                $.ajax({
                    url: STUDIP.URLHelper.getURL('dispatch.php/file/upload/' + folder_id),
                    data: data,
                    cache: false,
                    contentType: false,
                    processData: false,
                    type: 'POST',
                    xhr: function () {
                        var xhr = $.ajaxSettings.xhr();
                        if (xhr.upload) {
                            xhr.upload.addEventListener('progress', function (event) {
                                var percent = 0,
                                    position = event.loaded || event.position,
                                    total = event.total;
                                if (event.lengthComputable) {
                                    percent = Math.ceil(position / total * 100);
                                }
                                //Set progress
                                $('.file_upload_window .uploadbar').css('background-size', percent + '% 100%');
                            }, false);
                        }
                        return xhr;
                    }
                }).done(function (json) {
                    $('.file_upload_window .uploadbar').css('background-size', '100% 100%');
                    if (json.redirect) {
                        STUDIP.Dialog.fromURL(json.redirect, {
                            title: json.window_title
                                || (json.new_html.length > 1 ? 'Lizenz auswählen: %s Dateien'.toLocaleString().replace('%s', json.new_html.length) : 'Lizenz auswählen'.toLocaleString())
                        });
                    } else if (json.message) {
                        $('.file_uploader .uploadbar').hide().parent().append(json.message);
                    } else {
                        $.each(json.new_html, function (index, tr) {
                            STUDIP.Files.addFile(tr, index * 200);
                        });
                        STUDIP.Dialog.close();
                    }
                });
            } else {
                $('.file_upload_window .uploadbar').hide();
            }
        },
        addFile: function (html, delay) {
            if (delay === undefined) {
                delay = 0;
            }
            var redirect = true;
            if (html.hasOwnProperty('html') && html.html !== undefined) {
                redirect = html.redirect;
                html = html.html;
            }

            if (!redirect) {
                window.setTimeout(STUDIP.Dialog.close, 20);
            } else {
                STUDIP.Dialog.fromURL(redirect);
            }

            if (typeof html !== 'array' && typeof html !== 'object') {
                html = [html];
            }

            $.each(html, function (i, value) {
                var tr = $(value).attr('id');
                if ($(document.getElementById(tr)).length > 0) {
                    $(document.getElementById(tr)).replaceWith(value);
                } else {
                    $(value).hide().appendTo('.documents[data-folder_id] tbody.files').delay(500 + delay + i * 200).fadeIn(300);
                }
            });

            $('.subfolders .empty').hide('fade');

            // update tablesorter cache
            $('table.documents').trigger('update');
            $('table.documents').trigger('sorton', [
                $('table.documents').get(0).config.sortList
            ]);
        },
        removeFile: function (fileref_id) {
            $.post(STUDIP.URLHelper.getURL('dispatch.php/file/delete/' + fileref_id))
                .done(function () {
                    $('.documents tbody.files > tr#fileref_' + fileref_id).fadeOut(300, function () {
                        $(this).remove();
                        if ($('.subfolders > *').length + $('.files > *').length < 2) {
                            $('.subfolders .empty').show('fade');
                        }
                    });
                });
        },
        reloadPage: function () {
            STUDIP.Dialog.close();
            location.reload();
        },
        getFolders: function (name) {
            var element_name = 'folder_select_' + name,
                context =  $('#' + element_name + '-destination').val(),
                range = null;

            if ($.inArray(context, ['courses']) > -1) {
                range = $('#' + element_name + '-range-course > div > input').first().val();
            } else if ($.inArray(context, ['institutes']) > -1) {
                range = $('#' + element_name + '-range-inst > div > input').first().val();
            } else if ($.inArray(context, ['myfiles']) > -1) {
                range = $('#' + element_name + '-range-user_id').val();
            }

            if (range !== null) {
                $.post(STUDIP.URLHelper.getURL('dispatch.php/file/getFolders'), {range: range}, function (data) {
                    if (data) {
                        $('#' + element_name + '-subfolder select').empty();
                        $.each(data, function (index, value) {
                            $.each(value, function (label, folder_id) {
                                $('#' + element_name + '-subfolder select').append('<option value="' + folder_id + '">' + label + '</option>');
                            });
                        });
                    }
                }, 'json').done(function () {
                    $('#' + element_name + '-subfolder').show();
                });
            }
        },

        changeFolderSource: function (name) {
            var element_name = 'folder_select_' + name,
                elem = $('#' + element_name + '-destination');

            $('#' + element_name + '-range-course').hide();
            $('#' + element_name + '-range-inst').hide();
            $('#' + element_name + '-subfolder').hide();
            $('#' + element_name + '-subfolder select').empty();

            if ($.inArray(elem.val(), ['courses']) > -1) {
                $('#' + element_name + '-range-course').show();
            } else if ($.inArray(elem.val(), ['institutes']) > -1) {
                $('#' + element_name + '-range-inst').show();
            } else if ($.inArray(elem.val(), ['myfiles']) > -1) {
                $('#' + element_name + '-subfolder').show();
                STUDIP.Files.getFolders(name);
            }
        },

        toggleBulkButtons: function () {
            //At the bottom of each file list there are buttons for bulk actions.
            //These have to be activated when at least one element is checked.
            var buttons = $('table.documents tfoot .multibuttons .button'),
                //The bulk checkbox wasn't clicked: check each of the elements:
                total_elements = $('table.documents tbody tr[role=row] td input'),
                checked_elements = $('table.documents tbody tr[role=row] td input:checked');

            if (checked_elements.length > 0) {
                //at least one element is checked: activate buttons
                $(buttons).removeAttr('disabled');
                //...and set the "select-all-checkbox" in the third state (undefined),
                //if not all elements are checked:


                if (checked_elements.length < total_elements.length) {
                    //not all elements checked
                    $('table.documents thead th input[data-proxyfor]').prop('indeterminate', true);
                } else {
                    //all elements checked
                    $('table.documents thead th input[data-proxyfor]').prop('indeterminate', null);
                    $('table.documents thead th input[data-proxyfor]').prop('checked', true);
                }

            } else {
                //no element is checked: deactivate buttons
                $(buttons).attr('disabled', 'disabled');
                //... and uncheck "select-all-checkbox"
                $('table.documents thead th input[data-proxyfor]').prop('indeterminate', null);
                $('table.documents thead th input[data-proxyfor]').prop('checked', false);
            }
        },
        updateTermsOfUseDescription: function (e) {
            //make all descriptions invisible:
            $('div.terms_of_use_description_container > section').addClass('invisible');

            var selected_id = $(this).val();

            $('#terms_of_use_description-' + selected_id).removeClass('invisible');
        }
    };

    $(function () {
        $('.documents[data-folder_id] tbody > tr')
            .on('dragover dragleave', function (event) {
                $(this).toggleClass('dragover', event.type === 'dragover');
                return false;
            });
        $('.documents[data-folder_id]').on('drop', function (event) {
            event.preventDefault();

            var filelist = event.originalEvent.dataTransfer.files || {};
            STUDIP.Files.upload(filelist);
        });

        $(document).on('change', 'table.documents :checkbox', STUDIP.Files.toggleBulkButtons);

        // workaround to wait for tables.js to be executed first
        $(function () {
            if ($('table.documents').length > 0) {
                $('table.documents').data('tablesorter').widgets = ['filter'];
                $('table.documents').data('tablesorter').widgetOptions = {
                    filter_columnFilters: false,
                    filter_saveFilters: true,
                    filter_reset: '.reset',
                    filter_ignoreCase: true,
                    filter_startsWith: false
                };
                $('table.documents.flat').trigger('applyWidgets');
                $.tablesorter.filter.bindSearch($('table.documents'), $('.tablesorterfilter'));
            }
        });

        $(document).on('click', '#file_license_chooser_1 > input[type=radio]', STUDIP.Files.updateTermsOfUseDescription);
    });

}(jQuery, STUDIP));
