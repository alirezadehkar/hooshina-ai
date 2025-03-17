import {createTheme} from "@mui/material";

const { useState, useEffect, useRef } = window.wp.element;

export function useMutationObserver(selector) {
    const [elements, setElements] = useState([]);
    const initialized = useRef(false);

    useEffect(() => {
        const updateElements = () => {
            const selectedElements = Array.from(document.querySelectorAll(selector));
            const newElementsData = selectedElements.map(el => el.id || el.className || el.tagName);
            
            const currentElementsData = elements.map(el => el.id || el.className || el.tagName);
            
            if (JSON.stringify(newElementsData) !== JSON.stringify(currentElementsData)) {
                setElements(selectedElements);
            }
        };

        const observer = new MutationObserver(updateElements);
        observer.observe(document.body, { childList: true, subtree: true });

        if (!initialized.current) {
            updateElements();
            initialized.current = true;
        }

        return () => observer.disconnect();
    }, [selector, elements]);

    return elements;
}

export function stripHtml(html) {
    return html.replace(/<[^>]*>?/gm, '');
}

export function AutoClicker(url, target = '_self'){
    const link = document.createElement('a');
    link.href = url;
    link.target = target;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

export const CustomTheme = createTheme({
    palette: {
        mode: 'dark',
        background: {
            paper: '#333',
        },
        text: {
            primary: '#ffffff',
            secondary: '#aaaaaa',
        },
        primary: {
            main: '#04b4cc',
        },
        secondary: {
            main: '#a92424',
        },
        border: {
            primary: '#555555',
        }
    },
    typography: {
        fontFamily: 'inherit',
        button: {
            textTransform: 'none',
            color: '#000'
        },
        allVariants: {
            fontFamily: 'inherit',
            color: '#fff'
        },
    },
    components: {
        MuiPopover: {
            styleOverrides: {
                root: {
                    zIndex: 130000,
                }
            }
        },
        MuiSnackbar: {
            styleOverrides: {
                root: {
                    zIndex: 131000,
                }
            }
        },
        MuiButton: {
            styleOverrides: {
                root: {
                    fontFamily: 'inherit',
                },
            }
        }
    }
});

export const HooshinaIcon = () => (<span className="hooshina-icon hai-icon-logo" />);

export const buttonActionTypes = {
    blockPostTitle: 'block-post-title',
    editorPostTitle: 'editor-post-title',
    postSeoTitle: 'post-seo-title',
    postSeoDescription: 'post-seo-description',
    postSeoKeyword: 'post-seo-keyword',
    productDescription: 'product-description',
    blockPostThumbnail: 'block-post-thumbnail',
    editorPostThumbnail: 'editor-post-thumbnail',
    editorProductGallery: 'editor-product-gallery',
    postExcerpt: 'post-excerpt',
    commentReply: 'comment-reply',
    productReviewReply: 'product-review-reply',
    elementorTextField: 'elementor-text-field',
    elementorTextareaField: 'elementor-textarea-field',
    mediasPage: 'medias-page',
    productReviewsSummary: 'product-reviews-summary'
}