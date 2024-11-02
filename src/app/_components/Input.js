const { forwardRef } = window.wp.element;
import { Input as BaseInput } from '@mui/base/Input';
import { styled } from '@mui/system';
import { CustomTheme } from "../Helpers";

const Input = forwardRef(function CustomInput(props, ref) {
    return (
        <BaseInput
            slots={{
                root: RootDiv,
                input: 'input',
                textarea: TextareaElement,
            }}
            {...props}
            ref={ref}
        />
    );
});

export default function InputMultiline(props) {
    return <Input multiline {...props} placeholder="Type something…" />;
}

const RootDiv = styled('div')`
  display: flex;
  max-width: 100%;
`;

const TextareaElement = styled('textarea', {
    shouldForwardProp: (prop) =>
        !['ownerState', 'minRows', 'maxRows'].includes(prop.toString()),
})(({ theme }) => `
    width: 100%;
    font-size: 0.875rem;
    font-weight: 400;
    line-height: 1.5rem;
    padding: 8px 12px;
    border-radius: 8px 8px 0 8px;
    color: ${CustomTheme.palette.text.primary};
    background: ${CustomTheme.palette.background.paper};
    border: 1px solid ${CustomTheme.palette.border.primary};
    resize: none;

    &:hover {
        border-color: ${CustomTheme.palette.primary.main};
    }

    &:focus-visible {
        outline: 0;
    }
`);