(function(blocks, editor, element, components, _) {
    var el = element.createElement;
    var RichText = editor.RichText;
	var AlignmentToolbar = editor.AlignmentToolbar;
    var BlockControls = editor.BlockControls;
    var Fragment = element.Fragment;

/*---------创建第一个自定义块---------*/
    blocks.registerBlockType('reilve/quote', {
        title: 'DUXQ引言',
		category: 'layout',
        icon: {
            src: 'format-quote',
            foreground: '#f85253'
        },
        description: '几种不同的引言框',
        attributes: {   //设定自定义属性
            content: {
                type: 'array',
                source: 'children',
                selector: 'span',
            },
            typeClass: {
                source: 'attribute',
                selector: '.quote_q',
                attribute: 'class',
            }
        },
        edit: function(props) {   //编辑器函数
            var content = props.attributes.content, 
            typeClass = props.attributes.typeClass || 'quote_q qe_wzk_lan',
            isSelected = props.isSelected;
            function onChangeContent(newContent) {
                props.setAttributes({
                    content: newContent
                })
            }
            function changeType(event) {
                var type = event.target.className;
                props.setAttributes({
                    typeClass: 'quote_q ' + type
                })
            }
            var richText = el(RichText, {   //内容输入框
                tagName: 'p',
                onChange: onChangeContent,
                value: content,
                isSelected: props.isSelected,
                placeholder: '请输入...'
            });
            var outerHtml = el('div', {
                className: typeClass
            },
            richText);
            var selector = el('div', {
                className: 'duxq anz'
            },
            //在此添加4个可点击的按钮
            [el('button', {className: 'qe_wzk_lan',onClick: changeType}), 
            el('button', {className: 'qe_wzk_lv',onClick: changeType}), 
            el('button', {className: 'qe_wzk_hui',onClick: changeType}),
            el('button', {className: 'qe_wzk_hong',onClick: changeType})]
            );
            return el('div', {},[outerHtml, isSelected && selector])},
        save: function(props) {   //保存的函数
            var content = props.attributes.content,
            typeClass = props.attributes.typeClass || 'quote_q qe_wzk_lan';
            var outerHtml = el('div', {
                className: typeClass
            },
            el('i', {
                className: 'fa fa-quote-left '
            }), el('span', {},
            content));
            return el('div', {},
            outerHtml)
        },
    });

/*---------创建第二个自定义块---------*/
    blocks.registerBlockType('aduxq/button', {
        title: 'UDXQ 按钮',
		category: 'layout',
        icon: {
            src: 'marker',
            foreground: '#f85253'
        },
        attributes: {
            content: {
                type: 'array',
                source: 'children',
                selector: 'span',
            },
            alignment: {
                type: 'string',
            },
            typeClass: {
                source: 'attribute',
                selector: '.an_q',
                attribute: 'class',
            }
        },
        edit: function(props) {
            var content = props.attributes.content,
            typeClass = props.attributes.typeClass || 'qe_fxan b1',
            alignment = props.attributes.alignment,
            isSelected = props.isSelected;
            function onChangeContent(newContent) {
                props.setAttributes({
                    content: newContent
                })
            }
            function changeType(event) {
                var type = event.target.className;
                props.setAttributes({
                    typeClass: 'an_q ' + type
                })
            }
            function onChangeAlignment(newAlignment) {
                props.setAttributes({
                    alignment: newAlignment
                })
            }
            var richText = el(RichText, {
                tagName: 'span',
                onChange: onChangeContent,
                value: content,
                isSelected: props.isSelected,
                placeholder: '按钮'
            });
            var outerHtml1 = el('div', {
                className: typeClass
            },
            richText);
            var outerHtml = (el(element.Fragment, null, el(BlockControls, null, el(AlignmentToolbar, {
                value: alignment,
                onChange: onChangeAlignment,
            })), outerHtml1));
            var selector = el('div', {
                className: 'duxq anz'
            },
            [el('button', {
                className: 'qe_fxan b1',
                onClick: changeType
            },
            ''), el('button', {
                className: 'qe_fxan b2',
                onClick: changeType
            },
            ''), el('button', {
                className: 'qe_fxan b3',
                onClick: changeType
            },
            ''), el('button', {
                className: 'qe_fxan b4',
                onClick: changeType
            },
            ''), el('button', {
                className: 'qe_fxan b5',
                onClick: changeType
            },
            ''), ]);
            return el('div', {
                style: {
                    textAlign: alignment
                }
            },
            [outerHtml, isSelected && selector])
        },
        save: function(props) {
            var content = props.attributes.content,
            alignment = props.attributes.alignment,
            typeClass = props.attributes.typeClass || 'qe_fxan b1';
            if (alignment) {
                var outerHtml = el('div', {
                    style: {
                        textAlign: alignment
                    }
                },
                el('span', {
                    className: typeClass
                },
                content))
            } else {
                var outerHtml = el('span', {
                    className: typeClass
                },
                content)
            }
            return outerHtml
        },
    })
})(window.wp.blocks, window.wp.editor, window.wp.element, window.wp.components, window._, );