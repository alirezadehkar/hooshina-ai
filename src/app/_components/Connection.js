const { useState } = window.wp.element;
import { Button, CircularProgress, ThemeProvider } from '@mui/material';
import axios from 'axios';
import qs from 'qs';
import {AutoClicker, CustomTheme} from "../Helpers";

const isConnected = async function () {
    try {
        const response = await axios.post(hai_data.ajax_url, qs.stringify({
            action: 'hai_check_is_connected',
            nonce: hai_data.nonce,
        }));

        if (response.data.success) {
            return response;
        } else {
            console.error('Request failed.');
        }
    } catch (error) {
        console.log(error)
    }
}

export function ConnectingButton() {
    const [loading, setLoading] = useState(false);
    const [disabled, setDisabled] = useState(false);
    const [connected, setConnected] = useState(false);

    isConnected().then(res => {
        if(res){
            setConnected(true);
        }
    });

    const handleClickConnect = async () => {
        setLoading(true);
        setDisabled(true);

        try {
            const response = await axios.post(hai_data.ajax_url, qs.stringify({
                action: 'hai_connect_to_api',
                nonce: hai_data.nonce,
            }));

            if (response.data.success) {
                AutoClicker(response.data.data.redirect);
            } else {
                setDisabled(false);
                setLoading(false);
            }
        } catch (error) {
            setDisabled(false);
            setLoading(false);
        }
    };

    const handleClickDisconnect = async () => {
        setLoading(true);
        setDisabled(true);

        try {
            const response = await axios.post(hai_data.ajax_url, qs.stringify({
                action: 'hai_disconnect_to_api',
                nonce: hai_data.nonce,
            }));

            if (response.data.success) {
                location.reload();
            } else {
                setDisabled(false);
                setLoading(false);
            }
        } catch (error) {
            setDisabled(false);
            setLoading(false);
        }
    };

    return (
        <ThemeProvider theme={CustomTheme}>
            <Button
                variant="contained"
                color={connected ? 'secondary' : 'primary'}
                onClick={connected ? handleClickDisconnect : handleClickConnect}
                disabled={disabled}
                endIcon={loading ? <CircularProgress size={17} color="inherit" /> : null}
            >
                {loading ? 'Doing...' : (connected ? 'Revoke Connection' : 'Connect to Hooshina')}
            </Button>
        </ThemeProvider>
    );
}