import {
    useState,
    useEffect,
    useTransition,
    Suspense,
} from '@wordpress/element';
import {
    Button,
    Modal,
    Box,
    Typography,
    Skeleton,
    LinearProgress,
    ThemeProvider,
    Divider,
    CircularProgress
} from "@mui/material";
import axios from 'axios';
import qs from 'qs';
import AutoAwesomeIcon from '@mui/icons-material/AutoAwesome';
import { buttonActionTypes, CustomTheme } from "../Helpers";
import DataDisplay from "./ModalDisplayContent";
import CloseIcon from '@mui/icons-material/Close';
import { SnackbarProvider } from "../SnackbarContext";
import CommentReplyDisplay from "./CommentReplyDisplay";

export function OpenGeneratorModal({ isOpen, onClose, type = 'text', selectedBlock = null, options = {} }) {
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(false);
    const [data, setData] = useState(null);
    const [contentType, setContentType] = useState(type);

    const [imageStyle, setImageStyle] = useState('');
    const [imageSize, setImageSize] = useState('');
    const [contentLang, setContentLang] = useState('');
    const [contentTone, setContentTone] = useState('');
    const [balanceValue, setBalanceValue] = useState(0);
    const [loadingBalance, setLoadingBalance] = useState(true);

    const [showCommentReply, setShowCommentReply] = useState(false);

    const [showReviewsSummary, setShowReviewsSummary] = useState(false);

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

    const fetchBalanceValue = async () => {
        try {
            const response = await axios.post(hai_data.ajax_url, qs.stringify({
                action: 'hai_account_balance',
                nonce: hai_data.nonce
            }));

            if (response.data.success) {
                return response;
            }
        } catch {
            setBalanceValue(0);
        }
    }

    useEffect(() => {
        if (isOpen) {
            setLoading(true);
            setError(false);
            setData(null);

            setContentType(type || 'text');
            
            setImageStyle(hai_data.generator.defaults.image_style);
            setImageSize(hai_data.generator.defaults.image_size);
            setContentLang(hai_data.generator.defaults.content_lang);
            setContentTone(hai_data.generator.defaults.content_tone); 
            
            setShowCommentReply(options?.action == buttonActionTypes.commentReply || options?.action == buttonActionTypes.productReviewReply);

            setShowReviewsSummary(options?.action == buttonActionTypes.productReviewsSummary);

            let checkConnection = setInterval(() => {
                fetchData().then(res => {
                    if(!res){
                        setError(true);
                        setLoadingBalance(false);
                    } else {
                        setError(false);

                        fetchBalanceValue().then(res => {
                            if(res){
                                setBalanceValue(res.data.data.htmlValue);
                                setLoadingBalance(false);
                            }
                        });

                        clearInterval(checkConnection);
                    }
                });
            }, 2500);
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
            <SnackbarProvider>
                <Modal open={isOpen} aria-labelledby="hai-generator-modal" sx={{ zIndex: 130000 }}>
                    <Box sx={{
                        width: '550px',
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
                            <Box sx={{ 
                                    display: 'flex', 
                                    alignItems: 'center',
                                    gap: 2,
                                    backgroundColor: '#1c1c1c', 
                                    padding: '4px 7px', 
                                    borderRadius: '7px'
                                }}>
                                <Typography id="modal-title" variant="h6">
                                    { hai_data.texts.modal_heading }
                                </Typography>

                                <Divider orientation="vertical" variant="middle" flexItem />

                                <Box sx={{ display: 'flex', gap: 2, alignItems: 'center', color: 'text.secondary' }}>
                                    {loadingBalance ? (<CircularProgress size="20px" />) : (balanceValue ? balanceValue : '')}

                                    <Button href={hai_data.chargePageUrl} target="_blank" sx={{ padding: '4px 11px' }}>
                                        {hai_data.texts.charge_account}
                                    </Button>
                                </Box>
                            </Box>
                        
                            <Button onClick={onClose} sx={{ minWidth: 'auto', color: 'text.secondary' }}>
                                <CloseIcon />
                            </Button>
                        </Box>

                        {loading ? (
                            <Box sx={{ p: 2, textAlign: 'center' }}>
                                <LinearProgress />
                            </Box>
                        ) : error ? (
                            <Box sx={{ p: 2, textAlign: 'center' }}>
                                <AutoAwesomeIcon sx={{ fontSize: 40, color: 'text.primary', mb: 2 }} />
                                <Typography variant="h6" color="text.primary">
                                    { hai_data.texts.modal_license_err_primary }
                                </Typography>
                                <Typography variant="body2" color="text.secondary" sx={{ mb: 2 }}>
                                    { hai_data.texts.modal_license_err_secondary }
                                </Typography>
                                <Button href={hai_data.pluginOptionsUrl} target="_blank" variant="contained" color="primary">
                                    { hai_data.texts.connect_button }
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
                                {showCommentReply || showReviewsSummary ? (
                                    <CommentReplyDisplay
                                        data={data}
                                        contentType={contentType}
                                        isOpen={isOpen}
                                        onClose={onClose}
                                        selectedBlock={selectedBlock}
                                        options={{ isReviewsSummary: showReviewsSummary }}
                                    />
                                ) : (
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
                                        isOpen={isOpen}
                                        onClose={onClose}
                                        selectedBlock={selectedBlock}
                                        options={options}
                                    />
                                )}
                            </Suspense>
                        )}
                    </Box>
                </Modal>
            </SnackbarProvider>
        </ThemeProvider>
    );
}