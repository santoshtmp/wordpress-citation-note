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
