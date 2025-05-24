(function (wp) {
    const { registerFormatType, toggleFormat } = wp.richText;
    const { RichTextToolbarButton } = wp.blockEditor || wp.editor;
    const { createElement } = wp.element;

    registerFormatType('yipl/citation', {
        title: 'YIPL Citation',
        tagName: 'yipl_citation_placeholder',
        className: null,
        __experimentalShowInToolbar: true, // Force it into top toolbar
        edit({ isActive, value, onChange }) {
            return createElement(
                RichTextToolbarButton,
                {
                    icon: 'editor-ul',
                    title: 'YIPL Citation',
                    onClick: () => {
                        onChange(
                            toggleFormat(value, {
                                type: 'yipl/citation',
                            })
                        );
                    },
                    isActive: isActive,
                }
            );
        },
    });
})(window.wp);




/**
 * 
 */

jQuery(document).ready(function ($) {
    $('#add-repeater-group').on('click', function (e) {
        e.preventDefault();
        const $button = $(this);
        $button.prop('disabled', true); // Prevent rapid multiple clicks

        let ajax = $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'update_citation_fields',
                _nonce: ajax_object.nonce, // optional if nonce is used in PHP
                custom_data: 'example'
            }
        });

        ajax.done(function (response) {
            let row = $(response);
            $('#repeater-table tbody').append(row);

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

    $(document).on('click', '.remove-group', function () {
        $(this).closest('tr').remove();
    });

});
