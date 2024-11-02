const { createElement, useState, useEffect, useRef, createPortal } = window.wp.element;
import { useMutationObserver } from "../Helpers";
import { HooshinaOpenGeneratorModal } from "./GeneratorModal";
import { Button } from "@mui/material";

function HooshinaLargeButton({ containerSelector, onClick = () => {} }) {
    const containers = useMutationObserver(containerSelector);

    return (
        <>
            {containers.map((container, index) =>
                createPortal(
                    <Button color="primary" class="button button-primary button-large" onClick={onClick}>
                        Hooshina
                    </Button>,
                    container
                )
            )}
        </>
    );
}

function HooshinaTextButton({ containerSelector, onClick = () => {} }) {
    const containers = useMutationObserver(containerSelector);

    const handleClick = (event) => {
        event.preventDefault();
        onClick();
    };

    return (
        <>
            {containers.map((container, index) =>
                createPortal(
                    <a href="#" onClick={handleClick}>
                        Generate with Hooshina
                    </a>,
                    container
                )
            )}
        </>
    );
}

export function ButtonsInitialize() {
    const [isOpen, setIsOpen] = useState(false);
    const handleOpen = () => setIsOpen(true);
    const handleClose = () => setIsOpen(false);

    return (
        <>
            <HooshinaLargeButton onClick={handleOpen} containerSelector=".editor-document-tools" />
            <HooshinaTextButton onClick={handleOpen} containerSelector=".editor-post-featured-image" />

            <HooshinaOpenGeneratorModal isOpen={isOpen} onClose={handleClose} />
        </>
    );
}
