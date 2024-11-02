import {createTheme} from "@mui/material";

const { createElement, useState, useEffect, useRef, createPortal } = window.wp.element;

export function useMutationObserver(selector) {
    const [elements, setElements] = useState([]);

    useEffect(() => {
        const updateElements = () => {
            const selectedElements = document.querySelectorAll(selector);
            setElements(Array.from(selectedElements));
        };

        const observer = new MutationObserver(updateElements);
        observer.observe(document.body, {
            childList: true,
            subtree: true,
        });

        updateElements();

        return () => observer.disconnect();
    }, [selector]);

    return elements;
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
});