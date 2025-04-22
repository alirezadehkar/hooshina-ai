import {createTheme} from "@mui/material";

const { useState, useEffect, useRef } = window.wp.element;

export function useMutationObserver(
    selector,
    insideIframe = false,
    iframeSelector = null
) {
    const [elements, setElements] = useState([]);

    useEffect(() => {
        let observer;
        let iframeObserver;

        const startObserve = (targetDocument) => {
            const update = () => {
                const found = Array.from(targetDocument.querySelectorAll(selector));
                setElements(prev =>
                    prev.length === found.length && prev.every((n, i) => n === found[i])
                        ? prev
                        : found
                );
            };

            observer = new MutationObserver(update);
            observer.observe(targetDocument.body, { childList: true, subtree: true });
            update();        
        };

        if (!insideIframe) {
            startObserve(document);
            return () => observer?.disconnect();
        }

        const iframeEl = document.querySelector(iframeSelector);

        if (!iframeEl) {
            return;
        }        

        const handleIframeReady = () => {
            const iframeDoc = iframeEl?.contentDocument;
            if (!iframeDoc) return;

            iframeObserver = new MutationObserver((muts, obs) => {
                if (iframeDoc.body) {
                    obs.disconnect();   
                    startObserve(iframeDoc);
                }
            });
            iframeObserver.observe(iframeDoc, { childList: true, subtree: true });

            if (iframeDoc.body) {
                iframeObserver.disconnect();
                startObserve(iframeDoc);
            }
        };

        if (iframeEl?.contentDocument?.readyState === 'complete') {
            handleIframeReady();
        } else {
            iframeEl.addEventListener('load', handleIframeReady, { once: true });
        }

        return () => {
            observer?.disconnect();
            iframeObserver?.disconnect();
        };
    }, [selector, insideIframe, iframeSelector]);

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