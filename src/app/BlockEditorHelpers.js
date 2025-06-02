import { buttonActionTypes, stripHtml } from "./Helpers";
const { insertBlocks } = wp.data.dispatch('core/block-editor');

export function ApplyContentToEditor({content, type, block = null, options}) {     
    const getEditorContext = () => {
        if (block && block.name) {
            return {
                type: 'gutenberg-block',
                element: block
            };
        }

        const selectedGutenbergBlock = wp.data.select('core/block-editor')?.getSelectedBlock();
        if (selectedGutenbergBlock) {
            return {
                type: 'gutenberg-block',
                element: selectedGutenbergBlock
            };
        }

        const gutenbergEditor = document.querySelector('.block-editor-page');
        if (gutenbergEditor && !(block instanceof Element)) {
            return {
                type: 'gutenberg-editor',
                element: gutenbergEditor
            };
        }

        const classicEditorContent = block.querySelector('.wp-editor-area') || block?.parentNode?.querySelector('.wp-editor-area') || block?.parentNode?.parentNode?.querySelector('.wp-editor-area') || block?.parentNode?.parentNode?.parentNode?.querySelector('.wp-editor-area');
        if (classicEditorContent) {
            return {
                type: 'classic-editor',
                element: classicEditorContent
            };
        }

        const activeElement = block.querySelector('input, textarea');
        if (activeElement && 
            (activeElement.tagName === 'INPUT' || 
             activeElement.tagName === 'TEXTAREA')) {
            return {
                type: 'input-textarea',
                element: activeElement
            };
        }

        const editableElements = [
            '.wp-block-post-title',
        ];

        for (let selector of editableElements) {
            const editableElement = block.parentNode.querySelector(selector);
            if (editableElement) {
                return {
                    type: 'editable-area',
                    element: editableElement
                };
            }
        }

        return {
            type: 'unknown',
            element: null
        };
    };

    const setSeoData = () => {
        const yoastStore = wp.data.select('yoast-seo/editor');

        const yoastEditor = wp.data.dispatch('yoast-seo/editor');

        const rankMathStore = wp.data.select('rank-math');

        const rankMathEditorController = wp.data.dispatch('rank-math');

        const wpBlockEditor = wp.data.dispatch('core/editor');

        const inYoastEditor = block instanceof Element ? block?.classList?.contains('yoast') || block?.closest('.yoast') : false;

        const refreshRankMathEditor = () => {
            if(typeof rankMathEditor === 'object'){
                rankMathEditor.refresh();
            }
            rankMathEditorController.refreshResults();
        }

        if(options?.buttonAction == buttonActionTypes.blockPostTitle){
            wpBlockEditor.editPost({ title: stripHtml(content) });

            return true;
        } else if(options?.buttonAction == buttonActionTypes.editorPostTitle){
            let element = document.querySelector('#titlewrap #title');
            element.value = stripHtml(content);
            element.dispatchEvent(new Event("change", { bubbles: true }));

            return true;
        } else if(options?.buttonAction == buttonActionTypes.postExcerpt){
            wpBlockEditor.editPost({ excerpt: stripHtml(content) });

            return true;
        } else if(options?.buttonAction == buttonActionTypes.postSeoTitle){
            if (inYoastEditor && yoastStore) {
                if(yoastEditor){
                    yoastEditor.updateData({
                        title: stripHtml(content),
                    });
                    yoastEditor.refreshSnippetEditor();
                }

                return true;
            } else if(rankMathStore){
                if(rankMathEditorController){
                    rankMathEditorController.updateTitle(stripHtml(content));
                    rankMathEditorController.updateSerpTitle(stripHtml(content));

                    refreshRankMathEditor();
                }

                return true;
            }
        } else if(options?.buttonAction == buttonActionTypes.postSeoDescription){
            if (inYoastEditor && yoastStore) {
                if(yoastEditor){
                    yoastEditor.updateData({
                        description: stripHtml(content),
                    });
                    yoastEditor.refreshSnippetEditor();
                }

                return true;
            } else if(rankMathStore){
                if(rankMathEditorController){
                    rankMathEditorController.updateDescription(stripHtml(content));
                    rankMathEditorController.updateSerpDescription(stripHtml(content));

                    refreshRankMathEditor();
                }

                return true;
            }
        } else if(options?.buttonAction == buttonActionTypes.postSeoKeyword){
            if (inYoastEditor && yoastStore) {
                if(yoastEditor){
                    yoastEditor.setFocusKeyword(stripHtml(content));
                    yoastEditor.refreshSnippetEditor();
                }

                return true;
            } else if(rankMathStore){
                if(rankMathEditorController){
                    const keyword = stripHtml(content);
                    rankMathEditorController.updateKeywords(keyword);

                    const addKeywordToRankMathTagify = (keyword) => {
                        try {
                            const tagifyComponent = window.rankMathEditor?.focusKeywordField?.tagifyField?.current?.component;
                    
                            if (tagifyComponent) {
                                const currentKeywordsArray = tagifyComponent.value ? JSON.parse(tagifyComponent.value) : [];
                    
                                if (!Array.isArray(currentKeywordsArray)) {
                                    console.error("Rank Math tagifyComponent.value did not parse to an array:", tagifyComponent.value);
                                    const tempValue = tagifyComponent.tagifyValue;
                                     if (tempValue && Array.isArray(tempValue)) {
                                         currentKeywordsArray = tempValue;
                                     } else {
                                        currentKeywordsArray = [];
                                     }
                                }
                    
                                const keywordExists = currentKeywordsArray.some(item => item.value === keyword);
                    
                                if (!keywordExists) {
                                    currentKeywordsArray.push({ value: keyword });
                    
                                    tagifyComponent.value = JSON.stringify(currentKeywordsArray);
                                    
                                    const inputElement = tagifyComponent.DOM.input;
                                    if (inputElement) {
                                         inputElement.dispatchEvent(new Event('input', { bubbles: true }));
                                         inputElement.dispatchEvent(new Event('change', { bubbles: true }));
                                    }
                                }
                            } else {
                                console.warn("Rank Math Tagify component not found.");
                            }
                        } catch (e) {
                            console.error("Error adding keyword to Rank Math Tagify:", e);
                        }
                    };
                    
                    addKeywordToRankMathTagify(keyword);

                    refreshRankMathEditor();
                }
                
                return true;
            }
        }

        return false;
    };

    const simulateTyping = (fullText, callback, speed = 4) => {
        let currentText = '';
        let currentIndex = 0;
    
        const adjustedSpeed = fullText.length > 200 ? 2 : speed;
    
        const typingInterval = setInterval(() => {
            currentText += fullText.charAt(currentIndex);
            currentIndex++;
    
            if (callback) {
                callback(currentText);
            }
    
            if (currentIndex >= fullText.length) {
                clearInterval(typingInterval);
            }
        }, adjustedSpeed);
    };

    const insertContent = () => {
        const context = getEditorContext();

        if(setSeoData() == true){
            return false;
        }

        switch (context.type) {
            case 'gutenberg-block':
                if (type === 'html') {
                    const blocks = wp.blocks.rawHandler({
                        HTML: content,
                        mode: 'BLOCKS',
                        canUserUseUnfilteredHTML: true
                    });

                    if (blocks && blocks.length) {
                        wp.data.dispatch('core/block-editor').replaceBlock(
                            context.element.clientId, 
                            blocks
                        );
                        
                        setTimeout(() => {
                            let delay = 0;
                            
                            blocks.forEach(block => {
                                const blockClientId = block.clientId;
                                const blockElement = document.querySelector(`[data-block="${blockClientId}"]`);
                                
                                if (blockElement) {
                                    blockElement.style.opacity = '0';
                                    blockElement.style.transform = 'translateY(40px)';
                                    blockElement.style.transition = 'opacity 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94), transform 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
                                    blockElement.style.position = 'relative';
                                    
                                    setTimeout(() => {
                                        blockElement.style.opacity = '1';
                                        blockElement.style.transform = 'translateY(0)';
                                        blockElement.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
                                        
                                        setTimeout(() => {
                                            blockElement.style.boxShadow = 'none';
                                            blockElement.style.transition = 'box-shadow 0.3s ease';
                                        }, 800);
                                    }, delay);
                                    
                                    delay += 250;
                                }
                            });
                        }, 250);
                    } else {
                        simulateTyping(content, (text) => {
                            wp.data.dispatch('core/block-editor').updateBlockAttributes(
                                context.element.clientId,
                                { content: text }
                            );
                        });
                    }
                } else if (type === 'image') {
                    if (context.element.name === 'core/gallery') {
                        const currentImages = context.element.attributes.images || [];
                        
                        const newImages = [
                            ...currentImages, 
                            { 
                                url: content, 
                                id: null,
                                alt: '' ,
                                link: '',
                                caption: ''
                            }
                        ];
            
                        wp.data.dispatch('core/block-editor').updateBlockAttributes(
                            context.element.clientId, 
                            { 
                                images: newImages
                            }
                        );
                    } else {
                        if (context.element?.name != 'core/image') {
                            const newImageBlock = wp.blocks.createBlock('core/image', {
                                url: content,
                                align: 'center',
                            });

                            console.log(newImageBlock);

                            insertBlocks(newImageBlock);
                        } else {
                            wp.data.dispatch('core/block-editor').updateBlock(
                                context.element.clientId, 
                                { attributes: { url: content, align: 'center' } }
                            );
                        }
                    }
                }
                break;

            case 'gutenberg-editor':
                if (type === 'html') {
                    const blocks = wp.blocks.rawHandler({
                        HTML: content,
                        mode: 'BLOCKS',
                        canUserUseUnfilteredHTML: true
                    });

                    if (blocks && blocks.length) {
                        wp.data.dispatch('core/block-editor').insertBlocks(blocks);
                    } else {
                        const block = wp.blocks.createBlock('core/paragraph', { content: '' });
                        wp.data.dispatch('core/block-editor').insertBlocks(block);
                        
                        setTimeout(() => {
                            simulateTyping(content, (text) => {
                                wp.data.dispatch('core/block-editor').updateBlock(
                                    block.clientId, 
                                    { attributes: { content: text } }
                                );
                            });
                        }, 100);
                    }
                } else if (type === 'image') {
                    const imageBlock = wp.blocks.createBlock('core/image', { 
                        url: content, 
                        align: 'center' 
                    });
                    wp.data.dispatch('core/block-editor').insertBlocks(imageBlock);
                }
                break;

            case 'classic-editor':
                const editorId = context.element?.id;
                if (!editorId) return;

                const editor = tinyMCE.get(editorId);
                
                if (editor) {
                    if (type == 'html') {
                        const bookmark = editor.selection.getBookmark();
                        
                        editor.selection.setContent('<span id="typing-cursor"></span>');
                        
                        simulateTyping(content, (text) => {
                            const tempElement = editor.dom.get('typing-cursor');
                            if (tempElement) {
                                tempElement.innerHTML = text;
                                
                                if (text === content) {
                                    editor.dom.remove(tempElement, true);
                                    editor.selection.moveToBookmark(bookmark);
                                    //editor.insertContent(content);
                                }
                            }
                        });
                    } else if (type == 'image') {
                        editor.insertContent(`<img src="${content}" />`);
                    }
                } else {
                    simulateTyping(content, (text) => {
                        context.element.value = text;
                        context.element.dispatchEvent(new Event("input", { bubbles: true }));
                        if (text === content) {
                            context.element.dispatchEvent(new Event("change", { bubbles: true }));
                        }
                    });
                }
                break;

            case 'input-textarea':
                simulateTyping(stripHtml(content), (text) => {
                    context.element.value = text;
                    context.element.dispatchEvent(new Event("input", { bubbles: true }));
                    if (text === stripHtml(content)) {
                        context.element.dispatchEvent(new Event("change", { bubbles: true }));
                    }
                });
                break;

            case 'input':
                simulateTyping(stripHtml(content), (text) => {
                    context.element.value = text;
                    context.element.dispatchEvent(new Event("input", { bubbles: true }));
                    if (text === stripHtml(content)) {
                        context.element.dispatchEvent(new Event("change", { bubbles: true }));
                    }
                });
                break;

            case 'editable-area':
                if (context.element.tagName === 'TEXTAREA') {
                    simulateTyping(stripHtml(content), (text) => {
                        context.element.value = text;
                        context.element.dispatchEvent(new Event("input", { bubbles: true }));
                        if (text === stripHtml(content)) {
                            context.element.dispatchEvent(new Event("change", { bubbles: true }));
                        }
                    });
                } else {
                    simulateTyping(content, (text) => {
                        context.element.innerHTML = text;
                        context.element.dispatchEvent(new Event("input", { bubbles: true }));
                        if (text === content) {
                            context.element.dispatchEvent(new Event("change", { bubbles: true }));
                        }
                    });
                }
                break;

            default:
                console.log('Unable to insert content - no suitable editor found');
        }
    };

    const applyMedia = () => {
        let postThumbnailImg = block.querySelector('#set-post-thumbnail img');
        let postThumbnailInput = block.querySelector('#_thumbnail_id');

        switch (options.buttonAction) {
            case buttonActionTypes.mediasPage:
                if (wp && wp.media && wp.media.frame && wp.media.frame.content) {
                    const mediaCollection = wp.media.frame.content.get().collection;
                    if (mediaCollection && typeof mediaCollection._requery === "function") {
                        mediaCollection._requery(true);
                    } else {
                        location.reload();
                    }
                } else {
                    location.reload();
                }
            break;

            case buttonActionTypes.blockPostThumbnail:
                    wp.data.dispatch('core/editor').editPost({ featured_media: options.id });
                break;

            case buttonActionTypes.editorPostThumbnail:
                if(postThumbnailImg) {
                    postThumbnailImg.src = content;
                    postThumbnailImg.srcset = '';
                } else {
                    const thumbnailContainer = block.querySelector('#set-post-thumbnail');
                    if(thumbnailContainer) {
                        if(thumbnailContainer.classList.contains('no-thumbnail') || !thumbnailContainer.querySelector('img')) {
                            thumbnailContainer.classList.remove('no-thumbnail');
                            
                            const img = document.createElement('img');
                            img.src = content;
                            img.style.width = '100%';
                            
                            thumbnailContainer.innerHTML = '';
                            thumbnailContainer.appendChild(img);
                            
                            const removeLink = document.createElement('a');
                            removeLink.href = '#';
                            removeLink.className = 'remove-post-thumbnail';
                            removeLink.textContent = hai_data.texts.delete_thumb;
                            removeLink.style.color = '#b11b1b';
                            removeLink.onclick = function(e) {
                                e.preventDefault();
                                thumbnailContainer.innerHTML = hai_data.texts.add_thumb;
                                thumbnailContainer.classList.add('no-thumbnail');
                                if(postThumbnailInput) {
                                    postThumbnailInput.value = '-1';
                                }
                                
                                if(wp && wp.data && wp.data.dispatch) {
                                    wp.data.dispatch('core/editor').editPost({ featured_media: 0 });
                                }
                            };
                            
                            thumbnailContainer.appendChild(removeLink);
                        }
                    }
                }
                if(options?.id && postThumbnailInput){
                    postThumbnailInput.value = options.id;
                }
                break;

            case buttonActionTypes.editorProductGallery:
                const galleryInput = block.querySelector('#product_image_gallery');
                const galleryContainer = block.querySelector('.product_images');

                if (galleryInput && options?.id) {
                    const currentGalleryIds = galleryInput.value ? galleryInput.value.split(',') : [];
                    
                    if (!currentGalleryIds.includes(options.id.toString())) {
                        currentGalleryIds.push(options.id);
                        galleryInput.value = currentGalleryIds.join(',');
                        
                        const changeEvent = new Event('change', { bubbles: true });
                        galleryInput.dispatchEvent(changeEvent);
                    }
                }

                if (galleryContainer && content) {
                    const imageHtml = `
                        <li class="image" data-attachment_id="${options.id}">
                            <img src="${content}" alt="">
                            <ul class="actions">
                                <li><a href="#" class="delete">×</a></li>
                            </ul>
                        </li>
                    `;
                    galleryContainer.insertAdjacentHTML('beforeend', imageHtml);
                    
                    const newImage = galleryContainer.querySelector(`[data-attachment_id="${options.id}"]`);
                    if (newImage) {
                        const deleteBtn = newImage.querySelector('.delete');
                        if (deleteBtn) {
                            deleteBtn.addEventListener('click', function(e) {
                                e.preventDefault();
                                removeGalleryImage(options.id, newImage);
                            });
                        }
                    }
                }
                break;
        }
    };

    let inMedias = [
        buttonActionTypes.mediasPage,
        buttonActionTypes.blockPostThumbnail,
        buttonActionTypes.editorPostThumbnail,
        buttonActionTypes.editorProductGallery
    ];
    
    if (options?.id && options?.buttonAction && inMedias.includes(options.buttonAction)) {
        applyMedia();
    } else {
        insertContent();
    }
}