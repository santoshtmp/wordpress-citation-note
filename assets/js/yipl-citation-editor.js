/**
 * https://developer.wordpress.org/block-editor/how-to-guides/format-api/
 */
if (typeof ajax_object === 'undefined' || ajax_object.allow_citation) {
    (
        function (wp) {
            const { registerFormatType, toggleFormat } = wp.richText;
            const { BlockControls } = wp.blockEditor || wp.editor;
            // const { RichTextToolbarButton } = wp.blockEditor || wp.editor;
            const { ToolbarGroup, ToolbarButton } = wp.components;
            const { createElement, Fragment } = wp.element;

            const YiplCitationButton = ({ isActive, onChange, value }) => {
                // return createElement(
                //     RichTextToolbarButton,
                //     {
                //         icon: 'editor-ul',
                //         title: 'YIPL Citation',
                //         onClick: () => {
                //             onChange(toggleFormat(value, { type: 'yipl/citation', })
                //             );
                //         },
                //         isActive: isActive,
                //     }
                // );
                return createElement(
                    Fragment,
                    null,
                    createElement(
                        BlockControls,
                        null,
                        createElement(
                            ToolbarGroup,
                            null,
                            createElement(ToolbarButton, {
                                // icon: 'editor-ol',
                                icon: wp.element.createElement(
                                    'svg',
                                    { width: 20, height: 20, viewBox: '0 0 24 24' },
                                    wp.element.createElement('text', {
                                        x: '2',
                                        y: '16',
                                        fontSize: '14',
                                        fontFamily: 'Arial',
                                    }, '123')
                                ),
                                label: 'YIPL Citation',
                                title: 'YIPL Citation',
                                onClick: () => {
                                    onChange(
                                        toggleFormat(value, {
                                            type: 'yipl/citation',
                                        })
                                    );
                                },
                                isActive: isActive,
                            })
                        )
                    )
                );
            };

            registerFormatType('yipl/citation', {
                title: 'YIPL Citation',
                tagName: 'yipl_citation_placeholder',
                className: null,
                edit: YiplCitationButton,
            });

        }
    )(window.wp);
}



/**
 * 
 */
jQuery(document).ready(function ($) {
    $('#yipl-citation-add-repeater-group').on('click', function (e) {
        e.preventDefault();
        const $button = $(this);
        $button.prop('disabled', true); // Prevent rapid multiple clicks

        let ajax = $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: ajax_object.action_yipl_citation_fields,
                _nonce: ajax_object.nonce,
                row_number: $('#yipl-citation-repeater-table tbody tr').length + 1,
            }
        });

        ajax.done(function (response) {
            let row = $(response);
            $('#yipl-citation-repeater-table tbody').append(row);

            // Reinitialize TinyMCE if the row contains an editor
            row.find('textarea').each(function () {
                const editorId = $(this).attr('id');
                if (editorId && typeof tinymce !== 'undefined') {
                    tinymce.execCommand('mceAddEditor', false, editorId);
                }
            });
        });
        ajax.fail(function (response) {
            console.error('Error:', response.responseText);
        });
        ajax.always(function (response) {
            // console.log(response);
            $button.prop('disabled', false);
        });
    });

    // Remove group button
    // This will remove the group from the table
    $(document).on('click', '.yipl-citation-remove-group', function () {
        $(this).prop('disabled', true);
        $(this).closest('tr').remove();
        $(this).prop('disabled', false);
    });


    //
    $(document).on('blur', '#yipl-citation-repeater-table input[type="number"]', function () {
        var $input = $(this);
        var index = $input.data('index');
        var value = $input.val();

        // Check for duplicates
        var isDuplicate = false;
        $('input[type="number"]').not($input).each(function () {
            if ($(this).val().trim() === value) {
                isDuplicate = true;
                return false; // break loop
            }
        });
        // If the value is empty, we don't consider it a duplicate
        if (isDuplicate) {
            $input.val('');
            $input.css('border', '2px solid red');
            $('.yi_citation_' + index).text('Duplicate!   ' + 'yi_citation_' + value);
        } else {
            $input.css('border', '');
            $('.yi_citation_' + index).text('yi_citation_' + value);
        }
        // 
    });


});
