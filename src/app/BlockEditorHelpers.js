export function ApplyContentToEditor({content, type}){
    if(type === 'html'){
        function convertHtmlToBlocks(content) {
            const { select, dispatch } = wp.data;
            const { insertBlocks } = dispatch('core/block-editor');

            const blocks = wp.blocks.rawHandler({
                HTML: content,
                mode: 'BLOCKS',
                canUserUseUnfilteredHTML: true
            });

            if (blocks && blocks.length) {
                insertBlocks(blocks);
                return true;
            }
            return false;
        }

        function htmlToParagraphBlock(html) {
            const { createBlock } = wp.blocks;
            return createBlock('core/paragraph', {
                content: html
            });
        }

        function addHtmlContentToEditor(htmlContent) {
            const success = convertHtmlToBlocks(htmlContent);

            if (!success) {
                const { dispatch } = wp.data;
                const block = htmlToParagraphBlock(htmlContent);
                dispatch('core/block-editor').insertBlocks(block);
            }
        }

        addHtmlContentToEditor(content);
    } else if(type === 'image'){
        wp.data.dispatch('core/block-editor').insertBlock(
            wp.blocks.createBlock('core/image', { url: content })
        );
    }
}