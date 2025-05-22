(function (wp) {
    const { registerFormatType, toggleFormat } = wp.richText;
    const { RichTextToolbarButton } = wp.blockEditor || wp.editor;
    const { createElement } = wp.element;

    registerFormatType('yipl/citation', {
        title: 'YIPL Citation',
        tagName: 'yipl_citation_slug',
        className: null,
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
