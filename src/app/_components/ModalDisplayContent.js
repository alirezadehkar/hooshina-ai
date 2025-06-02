const { useEffect, useState } = window.wp.element;
import {
    Box, 
    Button, 
    FormControl, 
    InputLabel, 
    MenuItem, 
    Select, 
    CircularProgress,
    FormControlLabel,
    Checkbox,
    LinearProgress,
} from "@mui/material";
import InputMultiline from "./Input";
import axios from 'axios';
import { marked } from "marked";
import { buttonActionTypes, CustomTheme } from "../Helpers";
import { ApplyContentToEditor } from "../BlockEditorHelpers";
import { useSnackbar } from "../SnackbarContext";

const DataDisplay = ({
     data,
     contentLang,
     handleContentLangChange,
     contentTone,
     handleContentToneChange,
     contentType,
     handleContentTypeChange,
     imageStyle,
     handleImageStyleChange,
     imageSize,
     handleImageSizeChange,
     isOpen,
     onClose,
     selectedBlock,
     options,
}) => {
    const { showSnackbar } = useSnackbar();

    const [loading, setLoading] = useState(false);
    const [content, setContent] = useState(false);
    const [disabledContentType, setDisabledContentType] = useState(false);
    const [subject, setSubject] = useState('');

    const [imagePreview, setImagePreview] = useState(null);
    const [showImageSelector, setShowImageSelector] = useState(false);

    const [progress, setProgress] = useState(0);
    const [showProgress, setShowProgress] = useState(false);

    const getButtonAction = () => options.action || null;

    const isDiabledContentType = () => {
        let buttonAction = getButtonAction();
        let checkes = ['comment', 'thumbnail', 'title', 'gallery', 'medias'];
        if(buttonAction && checkes.some(check => (buttonAction.toLowerCase()).includes(check))){
            return true;
        }

        return false;
    }

    useEffect(() => {
        let subject = '';
        let editorContent = '';

        const textBlocks = ["core/paragraph", "core/heading", "core/quote", "core/list"];
            
        if (selectedBlock && selectedBlock?.name && textBlocks.includes(selectedBlock.name)) {
            const blockContent = selectedBlock.attributes.content;
            
            if (blockContent) {
                subject = blockContent.trim();
            }
        } else {
            const gutenbergTitle = document.querySelector('.editor-post-title__input');
            if (gutenbergTitle) {
                subject = gutenbergTitle.textContent.trim();
            } else {
                const classicTitle = document.getElementById('title');
                if (classicTitle) {
                    subject = classicTitle.value;
                }
            }
        }

        if(getButtonAction() == buttonActionTypes.postSeoKeyword){
            const blocks = wp.data.select('core/block-editor').getBlocks();
            if (blocks && blocks.length) {
                const textContent = blocks
                    .filter(block => textBlocks.includes(block.name))
                    .map(block => block.attributes.content || '')
                    .join(' ')
                    .trim();
                
                if (textContent) {
                    editorContent += textContent;
                }
            } else {
                const classicContent = document.getElementById('content');
                if (classicContent) {
                    editorContent += classicContent.value.trim();
                }
            }

            subject += subject ? "\n" + editorContent : editorContent;
        }

        if(subject){
            setSubject(subject);
        }

        setDisabledContentType(isDiabledContentType());
    });

    const openMediaUploader = () => {
        const frame = wp.media({
            title: hai_data.texts.select_image,
            library: { type: "image" },
            multiple: false,
            button: { text: hai_data.texts.select_image }
        });

        frame.on("select", function () {
            const attachment = frame.state().get("selection").first().toJSON();
            setImagePreview(attachment.url);
        });

        frame.open();
    };

    const handleCheckProductGenerator = (e) => {
        setShowImageSelector(e.target.checked);
    };

    const handleSubmit = async (event) => {
        event.preventDefault();

        const form = event.target;
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        setLoading(true);
        setContent(null);
    
        const formData = new FormData(event.target);
        const queryString = new URLSearchParams(formData).toString();
        const baseData = `action=hai_generate_content&nonce=${hai_data.nonce}&${queryString}&buttonAction=${options.action || null}&type=${contentType}`;
    
        const makeRequest = async (url, data) => {
            try {
                const response = await axios.post(url, data, {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/json',
                    },
                });

                return response;
            } catch (error) {
                showSnackbar(hai_data.texts.generate_error, 'error');
                console.error(error);
                throw error;
            }
        };
    
        const afterGenerate = (response, data) => {
            setContent(data);
            useGeneratedContent({ data, id: response?.data?.data?.id });
            showSnackbar(hai_data.texts.generate_success, 'success');
            setLoading(false);
            setProgress(100);
            setShowProgress(false);
        };
    
        try {
            const response = await makeRequest(hai_data.ajax_url, baseData);
            console.log(response.data);
            if (response.data.success) {
                const data = response?.data?.data?.content || null;

                if(response?.data?.data?.msg){
                    showSnackbar(response.data.data.msg, response.data.data.status);
                }
    
                if(data && response?.data?.data){
                    if (response?.data?.data?.status === 'done') {
                        afterGenerate(response, data);
                    } else if (response?.data?.data?.status && response?.data?.data?.status !== 'done') {
                        const contentId = response.data.data.content_id;
    
                        setProgress(0);
                        setShowProgress(true);
    
                        const progressInterval = setInterval(() => {
                            setProgress((prev) => {
                                if (prev >= 90) return prev;
                                return prev + 1;
                            });
                        }, 3000);
        
                        const checkImageStatusInterval = setInterval(async () => {
                            try {
                                const checkResponse = await makeRequest(
                                    hai_data.ajax_url,
                                    `action=hai_check_image_status&nonce=${hai_data.nonce}&content_id=${contentId}`
                                );
        
                                if (checkResponse.data.success) {
                                    const checkData = checkResponse?.data?.data?.content || null;
                                    if (checkData && checkResponse?.data?.data?.status === 'done') {
                                        afterGenerate(checkResponse, checkData);
                                        clearInterval(progressInterval);
                                        clearInterval(checkImageStatusInterval);
                                    }
                                }
                            } catch (error) {
                                clearInterval(checkImageStatusInterval);
                            }
                        }, 2000);
                    }
                }
            } else {
                throw new Error("Request failed.");
            }
        } catch (error) {
            console.error(error);
            setLoading(false);
        }
    };

    const useGeneratedContent = ({ data = null, id = null }) => {
        let stringData = data ? data : content;

        if (stringData) {
            if (contentType == 'text') {
                const htmlContent = marked(stringData);

                ApplyContentToEditor({
                    content: htmlContent, 
                    type: 'html', block: 
                    selectedBlock, 
                    options: {id: id, buttonAction: (options.action || null)}
                });
            } else if (contentType == 'image') {
                ApplyContentToEditor({
                    content: stringData, 
                    type: 'image', 
                    block: selectedBlock, 
                    options: {id: id, buttonAction: (options.action || null)}
                });
            }

            onClose();
        }
    }

    const generatorTypes = hai_data.generator.types ? Object.entries(hai_data.generator.types) : {};
    const contentTones = hai_data.generator.contentTones ? Object.entries(hai_data.generator.contentTones) : {};
    const imageStyles = hai_data.generator.imageStyles ? Object.entries(hai_data.generator.imageStyles) : {};
    const productImageStyles = hai_data.generator.productImageStyles ? Object.entries(hai_data.generator.productImageStyles) : {};
    const imageSizes = hai_data.generator.imageSizes ? Object.entries(hai_data.generator.imageSizes) : {};
    const languages = hai_data.generator.languages ? Object.entries(hai_data.generator.languages) : {};

    return (
        <Box sx={{
            width: '100%',
            p: 2,
        }}>
            <Box>
                <form method="post" onSubmit={handleSubmit}>
                    {contentType === "image" && showImageSelector && (
                        <Box
                            sx={{
                                border: "2px dashed #ccc",
                                borderRadius: "10px",
                                p: 2,
                                textAlign: "center",
                                cursor: "pointer",
                                mb: 2,
                                color: CustomTheme.palette.text.primary
                            }}
                            onClick={openMediaUploader}
                        >
                            <input
                                type="hidden"
                                name="original_image"
                                value={imagePreview}
                                required
                            />
                            {imagePreview ? (
                                <>
                                    <img
                                        src={imagePreview}
                                        style={{ width: '100%', maxHeight: '150px', objectFit: 'contain' }}
                                    />
                                </>
                            ) : (
                                hai_data.texts.click_select_image
                            )}
                        </Box>
                    )}

                    {!showImageSelector && (
                         <div style={{marginBottom: '20px'}}>
                            <InputMultiline name="subject" defaultValue={subject} required/>
                        </div>
                    )}

                    <FormControl fullWidth variant="outlined" sx={{mb: 2}}>
                        <InputLabel sx={{color: CustomTheme.palette.text.primary}}>{ hai_data.texts.type }</InputLabel>
                        <Select
                            value={contentType}
                            onChange={handleContentTypeChange}
                            color="primary"
                            sx={{color: CustomTheme.palette.text.primary}}
                            name="type"
                            disabled={disabledContentType}
                            required
                        >
                            {generatorTypes.map(([key, label]) => (
                                <MenuItem key={key} value={key}>
                                    {label}
                                </MenuItem>
                            ))}
                        </Select>
                    </FormControl>

                    {contentType === "image" && (
                        <Box sx={{ mb: 2 }}>
                            <FormControlLabel
                                control={
                                    <Checkbox
                                        checked={showImageSelector}
                                        onChange={(e) => handleCheckProductGenerator(e)}
                                    />
                                }
                                label={hai_data.texts.generate_product_image}
                            />
                        </Box>
                    )}

                    {contentType === 'image' ? (
                        <>
                            <FormControl fullWidth variant="outlined" sx={{ mb: 2 }}>
                                <InputLabel sx={{ color: CustomTheme.palette.text.primary }}>
                                    {hai_data.texts.style}
                                </InputLabel>
                                <Select
                                    value={showImageSelector ? hai_data.generator.defaults.product_image_style : imageStyle}
                                    onChange={handleImageStyleChange}
                                    color="primary"
                                    sx={{ color: CustomTheme.palette.text.primary }}
                                    name="style"
                                    required
                                >
                                    {(showImageSelector ? productImageStyles : imageStyles).map(([key, label]) => (
                                        <MenuItem key={key} value={key}>
                                            {label}
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>
                            
                            {!showImageSelector && (
                                <FormControl fullWidth variant="outlined" sx={{ mb: 2 }}>
                                    <InputLabel>{hai_data.texts.size}</InputLabel>
                                    <Select
                                        value={imageSize}
                                        onChange={handleImageSizeChange}
                                        color="primary"
                                        sx={{ color: CustomTheme.palette.text.primary }}
                                        name="size"
                                        required
                                    >
                                        {imageSizes.map(([key, label]) => (
                                            <MenuItem key={key} value={key}>
                                                {label}
                                            </MenuItem>
                                        ))}
                                    </Select>
                                </FormControl>
                            )}
                        </>
                    ) : (
                        <>
                            <FormControl fullWidth variant="outlined" sx={{ mb: 2 }}>
                                <InputLabel>{hai_data.texts.language}</InputLabel>
                                <Select
                                    value={contentLang}
                                    onChange={handleContentLangChange}
                                    color="primary"
                                    sx={{ color: CustomTheme.palette.text.primary }}
                                    name="lang"
                                    required
                                >
                                    {languages.map(([code, label]) => (
                                        <MenuItem key={code} value={code}>
                                            {typeof label === "string" ? label : code}
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>

                            <FormControl fullWidth variant="outlined" sx={{ mb: 2 }}>
                                <InputLabel>{hai_data.texts.tone}</InputLabel>
                                <Select
                                    value={contentTone}
                                    onChange={handleContentToneChange}
                                    color="primary"
                                    sx={{ color: CustomTheme.palette.text.primary }}
                                    name="tone"
                                    required
                                >
                                    {contentTones.map(([key, label]) => (
                                        <MenuItem key={key} value={key}>
                                            {label}
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>
                        </>
                    )}

                    {showProgress && (
                        <Box sx={{ width: '100%', my: 2 }}>
                            <LinearProgress variant="determinate" value={progress} />
                        </Box>
                    )}

                    <Button
                        type="submit"
                        variant="contained"
                        color="primary"
                        disabled={loading}
                        startIcon={loading ? <CircularProgress size={17} /> : null}
                    >
                        {loading ? hai_data.texts.doing : hai_data.texts.generate}
                    </Button>
                </form>
            </Box>
        </Box>
    );
}

export default DataDisplay;