const {
    useState,
    useEffect,
    useTransition,
    Suspense,
    lazy
} = window.wp.element;
import {
    Button,
    Modal,
    Box,
    Typography,
    Skeleton,
    LinearProgress,
    ThemeProvider,
    createTheme,
} from "@mui/material";
import axios from 'axios';
import qs from 'qs';
import AutoAwesomeIcon from '@mui/icons-material/AutoAwesome';
import { CustomTheme } from "../Helpers";

const DataDisplay = lazy(() => import('./ModalDisplayContent'));

export function HooshinaOpenGeneratorModal({ isOpen, onClose }) {
    const [isPending, startTransition] = useTransition();
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(false);
    const [data, setData] = useState(null);
    const [contentType, setContentType] = useState('text');
    const [imageStyle, setImageStyle] = useState('');
    const [imageSize, setImageSize] = useState('');
    const [contentLang, setContentLang] = useState('');
    const [contentTone, setContentTone] = useState('');

    const fetchData = async () => {
        try {
            const response = await axios.post(hai_data.ajax_url, qs.stringify({
                action: 'hai_check_is_connected',
                nonce: hai_data.nonce
            }));

            if (!response.data.success) {
                setError(true);
                setData(response.data.data);
            } else {
                return response;
            }
        } catch {
            setError(true);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (isOpen) {
            setLoading(true);
            setError(false);
            setData(null);
            fetchData().then(res => {
                if(!res){
                    setError(true);
                }
            });
        }
    }, [isOpen]);

    const handleContentTypeChange = (event) => {
        setContentType(event.target.value);
    };

    const handleImageStyleChange = (event) => {
        setImageStyle(event.target.value);
    };

    const handleImageSizeChange = (event) => {
        setImageSize(event.target.value);
    };

    const handleContentLangChange = (event) => {
        setContentLang(event.target.value);
    };

    const handleContentToneChange = (event) => {
        setContentTone(event.target.value);
    };

    return (
        <ThemeProvider theme={CustomTheme}>
            <Modal open={isOpen} onClose={onClose} aria-labelledby="modal-title">
                <Box sx={{
                    width: '1000px',
                    margin: 'auto',
                    marginTop: '5%',
                    bgcolor: 'background.paper',
                    borderRadius: 1,
                    boxShadow: 24,
                    p: 0,
                    position: 'relative',
                    '*': {
                        boxSizing: 'border-box',
                    },
                }}>
                    <Box display="flex" justifyContent="space-between" alignItems="center" sx={{
                        p: 2,
                        borderBottom: "1px solid " + CustomTheme.palette.border.primary
                    }}>
                        <Typography id="modal-title" variant="h6">Hooshina Ai</Typography>
                        <Button onClick={onClose} sx={{ minWidth: 'auto', color: 'text.secondary' }}>×</Button>
                    </Box>

                    {loading ? (
                        <Box sx={{ p: 2, textAlign: 'center' }}>
                            <LinearProgress />
                        </Box>
                    ) : error ? (
                        <Box sx={{ p: 2, textAlign: 'center' }}>
                            <AutoAwesomeIcon sx={{ fontSize: 40, color: 'text.primary', mb: 2 }} />
                            <Typography variant="h6" color="text.primary">
                                Stay a few steps ahead of the rest with Hooshina Ai.
                            </Typography>
                            <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                                You can intelligently generate text and images.
                            </Typography>
                            <Button href={hai_data.pluginOptionsUrl} target="_blank" variant="contained" color="primary">
                                Connect to Hooshina
                            </Button>
                        </Box>
                    ) : (
                        <Suspense fallback={
                            <Box sx={{p: 4}}>
                                <Skeleton animation="wave" width="100%"/>
                                <Skeleton animation="wave" width="100%"/>
                                <Skeleton animation="wave" width="100%"/>
                            </Box>
                        }>
                            <DataDisplay
                                data={data}
                                contentType={contentType}
                                handleContentTypeChange={handleContentTypeChange}
                                imageStyle={imageStyle}
                                handleImageStyleChange={handleImageStyleChange}
                                imageSize={imageSize}
                                handleImageSizeChange={handleImageSizeChange}
                                contentLang={contentLang}
                                handleContentLangChange={handleContentLangChange}
                                contentTone={contentTone}
                                handleContentToneChange={handleContentToneChange}
                            />
                        </Suspense>
                    )}
                </Box>
            </Modal>
        </ThemeProvider>
    );
}