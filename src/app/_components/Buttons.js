import { useState, useEffect, createPortal, useMemo, Fragment } from '@wordpress/element';
import { BlockControls, MediaReplaceFlow } from '@wordpress/block-editor';
import { addFilter } from '@wordpress/hooks';
import { useSelect } from '@wordpress/data';
import { registerPlugin } from '@wordpress/plugins';
import { PluginBlockSettingsMenuItem } from '@wordpress/edit-post';
import { createHigherOrderComponent } from '@wordpress/compose';

import { Button as WpButton, ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { Button as MuiButton, ThemeProvider } from '@mui/material';

import { useMutationObserver, CustomTheme, buttonActionTypes, HooshinaIcon } from '../Helpers';
import { OpenGeneratorModal } from './GeneratorModal';

export const ButtonsInitialize = () => {
    const [isButtonsLoaded, setIsButtonsLoaded] = useState(false);
    const [isOpen, setIsOpen] = useState(false);
    const [selectedBlock, setSelectedBlock] = useState(null);
    const [modalType, setModalType] = useState('text');
    const [buttonAction, setButtonAction] = useState(null);

    const createPortalButton = ({
        Container,
        onClick,
        text,
        type = 'text',
        className = 'wp-block',
        isTextButton = false,
        action = null,
        position = null,
    }) => {
        type = !type ? 'text' : type;
        let priClassName = `hai-button-wrap hai-${type}-type-button-wrap`;
        className = !className ? priClassName + ' wp-block' : priClassName + ' ' + className;
    
        const handleButtonOnClick = (e) => {
            setModalType(type);
            setButtonAction(action);
            if (typeof onClick === 'function') {
                onClick(e);
            }
            handleOpen(e);
        };
    
        const buttonElement = isTextButton ? (
            <div className={'hai-text-button-wrap ' + className}>
                <MuiButton
                    href="#"
                    onClick={(e) => {
                        e.preventDefault();
                        handleButtonOnClick(e);
                    }}
                    startIcon={<HooshinaIcon />}
                >
                    {text || hai_data.texts.text_button}
                </MuiButton>
            </div>
        ) : (
            <div className={'hai-large-button-wrap ' + className}>
                <MuiButton
                    color="primary"
                    className="button button-primary button-large"
                    onClick={(e) => handleButtonOnClick(e)}
                    startIcon={<HooshinaIcon />}
                >
                    {text || hai_data.texts.text_button}
                </MuiButton>
            </div>
        );
    
        return Container.map((selector) => {
            if (!selector || !(selector instanceof Node)) return null;
    
            const existingPortal = selector.parentNode.querySelector('[data-hai-portal]');
            let tempContainer = existingPortal;
    
            if (!tempContainer) {
                tempContainer = document.createElement('div');
                tempContainer.setAttribute('data-hai-portal', 'true');
                tempContainer.className = 'hai-button-portal';
                tempContainer.style.display = 'none';
                selector.appendChild(tempContainer);
            }
    
            const portal = createPortal(buttonElement, tempContainer);
    
            if (position && selector.parentNode) {
                if (position === 'before') {
                    selector.parentNode.insertBefore(tempContainer, selector);
                } else if (position === 'after') {
                    selector.parentNode.insertBefore(tempContainer, selector.nextSibling);
                }
                tempContainer.style.display = '';
            } else {
                tempContainer.style.display = '';
            }
    
            return portal;
        }).filter(Boolean);
    };

    const LargeButton = ({ selector, text, onClick, type = null, className = null, action = null, position = null }) => {
        const containers = useMutationObserver(selector);
        return useMemo(() => createPortalButton({
            Container: containers, 
            onClick: onClick, 
            type: type || 'text', 
            text: text || hai_data.texts.large_button, 
            className: className,
            action: action,
            position: position
        }), [containers, text, onClick, type, className, action, position]);
    };
    
    const TextButton = ({ selector, text, onClick, type = null, className= null, action = null, position = null }) => {
        const containers = useMutationObserver(selector);
        return useMemo(() => createPortalButton({
            Container: containers, 
            onClick: onClick, 
            type: type || 'text', 
            text: text || hai_data.texts.large_button, 
            className: className,
            isTextButton: true,
            action: action,
            position: position
        }), [containers, text, onClick, type, className, action, position]);
    };

    const buttonConfigs = [
        {
            selector: "[class*='editor__post-title-wrapper']",
            text: hai_data.texts.title_generate,
            isTextButton: false,
            type: "text",
            action: buttonActionTypes.blockPostTitle
        },
        {
            selector: "#titlewrap",
            text: hai_data.texts.title_generate,
            isTextButton: true,
            type: "text",
            action: buttonActionTypes.editorPostTitle
        },
        {
            selector: ".block-editor-block-list__empty-block-inserter .block-editor-inserter",
            className: 'hooshina-editor-inserter-button',
            type: "text",
        },
        {
            selector: ".wp-block-image .components-placeholder__fieldset", // .wp-block-gallery .components-placeholder__fieldset
            text: hai_data.texts.image_generate,
            type: "image",
            className: "wp-hooshina-generator-button wp-hooshina-generate-image",
            isTextButton: false
        },
        {
            selector: ":not(#wp-excerpt-media-buttons).wp-media-buttons",
            text: hai_data.texts.text_button,
            className: "wp-hooshina-generator-button wp-hooshina-generate-image",
            isTextButton: false,
            type: "text",
            action: (document.querySelector('body[class*="woocommerce-"]') ? buttonActionTypes.productDescription : null)
        },
        {
            selector: ".post-type-attachment #wp-media-grid .page-title-action",
            position: 'after',
            type: "image",
            text: hai_data.texts.image_generate,
            className: "wp-hooshina-generator-button wp-hooshina-generate-image",
            isTextButton: false,
            action: buttonActionTypes.mediasPage
        },
        {
            selector: "#wp-excerpt-wrap .wp-media-buttons",
            text: hai_data.texts.text_button,
            type: "text",
            className: "wp-hooshina-generator-button wp-hooshina-generate-product-excerpt",
            isTextButton: false
        },
        {
            selector: "label[for=rank-math-editor-title]",
            text: hai_data.texts.title_generate,
            position: 'after',
            type: "text",
            isTextButton: true,
            action: buttonActionTypes.postSeoTitle
        },
        {
            selector: ".rank-math-description-variables",
            text: hai_data.texts.description_generate,
            isTextButton: true,
            type: "text",
            action: buttonActionTypes.postSeoDescription
        },
        {
            selector: ".rank-math-tabs .below-focus-keyword, .block-editor-page .rank-math-tab-content-general .rank-math-focus-keyword",
            text: hai_data.texts.keyword_generate,
            position: 'after',
            isTextButton: true,
            type: "text",
            action: buttonActionTypes.postSeoKeyword
        },
        {
            selector: "#focus-keyword-input-metabox",
            text: hai_data.texts.keyword_generate,
            position: 'after',
            isTextButton: true,
            type: "text",
            action: buttonActionTypes.postSeoKeyword
        },
        {
            selector: ".yst-replacevar__buttons button[id*='title-metabox']",
            text: hai_data.texts.title_generate,
            position: 'after',
            isTextButton: true,
            type: "text",
            action: buttonActionTypes.postSeoTitle
        },
        {
            selector: ".yst-replacevar__buttons button[id*='description-metabox']",
            text: hai_data.texts.description_generate,
            position: 'after',
            isTextButton: true,
            type: "text",
            action: buttonActionTypes.postSeoDescription
        },
        {
            selector: ".editor-post-featured-image",
            text: hai_data.texts.image_generate,
            type: "image",
            isTextButton: true,
            action: buttonActionTypes.blockPostThumbnail
        },
        {
            selector: "#postimagediv .postbox-header",
            text: hai_data.texts.image_generate,
            type: "image",
            isTextButton: true,
            position: "after",
            className: 'hai-post-thumb-wrap',
            action: buttonActionTypes.editorPostThumbnail
        },
        {
            selector: "#woocommerce-product-images .postbox-header",
            text: hai_data.texts.image_generate,
            type: "image",
            isTextButton: true,
            position: "after",
            className: 'hai-post-thumb-wrap',
            action: buttonActionTypes.editorProductGallery
        },
        {
            selector: ".editor-post-excerpt",
            text: hai_data.texts.excerpt_generate,
            isTextButton: true,
            type: "text",
            action: buttonActionTypes.postExcerpt
        },
        {
            selector: "#comments-form .comment .row-actions span.reply, #reviews-filter .comment .row-actions span.reply",
            text: hai_data.texts.comment_reply,
            className: "hooshina-ai-comment-reply-button-wrap",
            isTextButton: true,
            type: "text",
            action: document.querySelector('.product-reviews') ? buttonActionTypes.productReviewReply : buttonActionTypes.commentReply
        },
        {
            selector: ".elementor-control-type-text .elementor-control-field",
            text: hai_data.texts.text_button,
            isTextButton: true,
            type: "text",
            action: buttonActionTypes.elementorTextField
        },
        {
            selector: ".elementor-control-type-textarea .elementor-control-field",
            text: hai_data.texts.text_button,
            isTextButton: true,
            type: "text",
            action: buttonActionTypes.elementorTextareaField
        },
        {
            selector: ".woocommerce_options_panel .form-field[class*=product_reviews_summary]",
            text: hai_data.texts.product_review_generate,
            type: "text",
            action: buttonActionTypes.productReviewsSummary
        }
    ];
    
    const ToolbarButtonIcon = () => (
        <span className="dashicon dashicons hooshina-ai-icon" />
    );

    const registerHooshinaToolbarButton = (BlockEdit) => (props) => {
        const supportedBlocks = ['core/paragraph', 'core/heading'];

        if (!supportedBlocks.includes(props.name)) {
            return <BlockEdit {...props} />;
        }

        return (
            <Fragment>
                <BlockControls>
                    <ToolbarGroup>
                        <ToolbarButton
                            icon={<ToolbarButtonIcon />}
                            label={hai_data.texts.toolbar_button}
                            onClick={(e) => handleOpen(e, 'text')}
                        />
                    </ToolbarGroup>
                </BlockControls>
                <BlockEdit {...props} />
            </Fragment>
        );
    };

    const registerHooshinaImageReplacementButton = createHigherOrderComponent((BlockEdit) => {
        return (props) => {
            if (props.name !== 'core/image' || !props.isSelected) {
                return <BlockEdit {...props} />;
            }
        
            return (
                <Fragment>
                    <BlockEdit {...props} />
                    <BlockControls>
                        <ToolbarGroup>
                            <ToolbarButton
                                icon={<ToolbarButtonIcon />}
                                label={hai_data.texts.image_replace_button}
                                onClick={(e) => handleOpen(e, 'image')}
                            />
                        </ToolbarGroup>
                    </BlockControls>
                </Fragment>
            );
        };
    }, 'registerHooshinaImageReplacementButton');

    useEffect(() => {
        addFilter(
            'editor.BlockEdit',
            'hooshina-ai/ai-toolbar-button',
            registerHooshinaToolbarButton
        );

        addFilter(
            'editor.BlockEdit',
            'hooshina-ai/with-image-replace-button',
            registerHooshinaImageReplacementButton
        );
    }, []);

    
    const block = useSelect(
        (select) => select("core/block-editor").getSelectedBlock(), 
        []
    );

    useEffect(() => {
        setSelectedBlock(block);
    }, [block]);
    

    const handleOpen = (event, type= null) => {
        const targetEl = event.target;

        if(type){
            setModalType(type);
        }
    
        if (targetEl.closest('.block-editor-block-popover')) {
            const currentBlock = wp.data.select('core/block-editor').getSelectedBlock();
    
            if (currentBlock) {
                setSelectedBlock(currentBlock);
                setIsOpen(true);
                //console.log("🔹 Selected Block from Editor:", currentBlock);
                return;
            }
        }

        if (targetEl.closest('.elementor-control-field')) {
            const currentBlock = targetEl.closest('.elementor-control-field');
    
            if (currentBlock) {
                setSelectedBlock(currentBlock);
                setIsOpen(true);
                //console.log("🔹 Selected Block from Elementor:", currentBlock);
                return;
            }
        }
    
        const closestBlock = targetEl.closest('.postbox') || targetEl.closest('div')?.parentElement;

        if (closestBlock) {
            setSelectedBlock(closestBlock);
            setIsOpen(true);
            //console.log("🔹 Selected Block from DOM:", closestBlock);
        }
    };

    const handleClose = () => setIsOpen(false);

    useEffect(() => {
        const timer = setTimeout(() => {
            setIsButtonsLoaded(true);
        }, 300);

        return () => clearTimeout(timer);
    }, []);

    if (!isButtonsLoaded) {
        return null;
    }

    return (
        <>
            <ThemeProvider theme={CustomTheme}>
                {
                    buttonConfigs.map(config => (
                        config.isTextButton ? <TextButton {...config} /> : <LargeButton {...config} />
                    ))
                }
            
                <OpenGeneratorModal 
                    isOpen={isOpen}
                    onClose={handleClose} 
                    selectedBlock={selectedBlock}
                    type={modalType}
                    options={{action: buttonAction}}
                />
            </ThemeProvider>
        </>
    );
};