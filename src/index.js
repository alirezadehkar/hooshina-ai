import {ButtonsInitialize} from "./app/_components/Buttons"
import {ConnectingButton} from "./app/_components/Connection";

document.addEventListener("DOMContentLoaded", () => {
    const appContainer = document.createElement("div");
    document.body.appendChild(appContainer);
    wp.element.render(<ButtonsInitialize />, appContainer);

    const addConnectingButtonToForm = () => {
        const formElement = document.querySelector('.hai-options form');
        if (formElement) {
            const buttonContainer = document.createElement('div');
            buttonContainer.setAttribute('id', 'connecting-button-container');
            formElement.appendChild(buttonContainer);
            wp.element.render(<ConnectingButton />, buttonContainer);
        }
    };

    addConnectingButtonToForm();
});