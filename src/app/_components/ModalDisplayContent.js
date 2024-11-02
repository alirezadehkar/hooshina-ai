const { useState } = window.wp.element;
import {Box, Button, FormControl, InputLabel, MenuItem, Select, Skeleton, CircularProgress} from "@mui/material";
import InputMultiline from "./Input";
import axios from 'axios';
import ReactMarkdown from 'react-markdown';
import {marked} from "marked";
import { CustomTheme } from "../Helpers";
import { ApplyContentToEditor } from "../BlockEditorHelpers"

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
     handleImageSizeChange
}) => {
    const [loading, setLoading] = useState(false);
    const [content, setContent] = useState(false);

    const handleSubmit = async (event) => {
        event.preventDefault();
        setLoading(true);
        setContent(null);

        const formData = new FormData(event.target);
        const queryString = new URLSearchParams(formData).toString();

        try {
            const response = await axios.post(
                hai_data.ajax_url,
                `action=hai_generate_content&nonce=${hai_data.nonce}&${queryString}`,
                {
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'Accept': 'application/json'
                    }
                }
            );

            if(response.data.success){
                let data = response?.data?.data?.content ? response.data.data.content : null;
                if (data){
                    setContent(data);
                }
            }
        } catch (error) {
            console.error("Error submitting form:", error);
        } finally {
            setLoading(false);
        }
    };

    const renderContent = () => {
        if (content) {
            const handleButtonClick = () => {
                if (content) {
                    if (contentType === 'text') {
                        const htmlContent = marked(content);
                        ApplyContentToEditor({content: htmlContent, type: 'html'});
                    } else if (contentType === 'image') {
                        ApplyContentToEditor({content: content, type: 'image'});
                    }
                }
            }

            const commonButton = (
                <Button variant="outlined" sx={{mt: 1}} onClick={handleButtonClick}>I Use</Button>
            );

            if (content.match(/^https?:\/\/.*\.(jpeg|jpg|gif|png|webp|svg)$/)) {
                return (
                    <>
                        <img src={content} alt="Generated Content" style={{maxWidth: '100%', borderRadius: '8px'}}/>
                        {commonButton}
                    </>
                );
            } else {
                return (
                    <>
                        <Box
                            sx={{
                                maxHeight: '80vh',
                                overflowY: 'auto',
                                color: CustomTheme.palette.text.primary
                            }}
                        >
                            <ReactMarkdown>{content}</ReactMarkdown>
                            {commonButton}
                        </Box>
                    </>
                );
            }
        }
        return <Skeleton variant="rectangular" width="100%" height={360} sx={{ borderRadius: 2 }} />;
    };

    const generatorTypes = Object.entries(hai_data.generator.types);
    const contentTones = Object.entries(hai_data.generator.contentTones);
    const imageTones = Object.entries(hai_data.generator.imagesTones);
    const imageSizes = Object.entries(hai_data.generator.imageSizes);
    const languages = Object.entries(hai_data.generator.languages);

    return (
        <Box sx={{
            display: 'flex',
            flexDirection: 'row',
            gap: '15px',
            width: '100%',
            p: 2,
        }}>
            <Box sx={{width: { xs: '100%', md: '360px' }, minWidth: { xs: '100%', md: '360px' }}}>
                <form method="post" onSubmit={handleSubmit}>
                    <div style={{marginBottom: '20px'}}>
                        <InputMultiline name="subject"/>
                    </div>

                    <FormControl fullWidth variant="outlined" sx={{mb: 2}}>
                        <InputLabel sx={{color: CustomTheme.palette.text.primary}}>Type</InputLabel>
                        <Select
                            value={contentType}
                            onChange={handleContentTypeChange}
                            color="primary"
                            sx={{color: CustomTheme.palette.text.primary}}
                            name="type"
                        >
                            {generatorTypes.map(([key, label]) => (
                                <MenuItem key={key} value={key}>
                                    {label}
                                </MenuItem>
                            ))}
                        </Select>
                    </FormControl>

                    {contentType === 'image' ? (
                        <>
                            <FormControl fullWidth variant="outlined" sx={{mb: 2}}>
                                <InputLabel sx={{color: CustomTheme.palette.text.primary}}>Style</InputLabel>
                                <Select
                                    value={imageStyle}
                                    onChange={handleImageStyleChange}
                                    color="primary"
                                    sx={{color: CustomTheme.palette.text.primary}}
                                    name="style"
                                >
                                    {imageTones.map(([key, label]) => (
                                        <MenuItem key={key} value={key}>
                                            {label}
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>

                            <FormControl fullWidth variant="outlined" sx={{mb: 2}}>
                                <InputLabel>Size</InputLabel>
                                <Select
                                    value={imageSize}
                                    onChange={handleImageSizeChange}
                                    color="primary"
                                    sx={{color: CustomTheme.palette.text.primary}}
                                    name="size"
                                >
                                    {imageSizes.map(([key, label]) => (
                                        <MenuItem key={key} value={key}>
                                            {label}
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>
                        </>
                    ) : (
                        <>
                            <FormControl fullWidth variant="outlined" sx={{mb: 2}}>
                                <InputLabel>Language</InputLabel>
                                <Select
                                    value={contentLang}
                                    onChange={handleContentLangChange}
                                    color="primary"
                                    sx={{color: CustomTheme.palette.text.primary}}
                                    name="lang"
                                >
                                    {languages.map(([code, label]) => (
                                        <MenuItem key={code} value={code}>
                                            {label}
                                        </MenuItem>
                                    ))}
                                </Select>
                            </FormControl>

                            <FormControl fullWidth variant="outlined" sx={{mb: 2}}>
                                <InputLabel>Tone</InputLabel>
                                <Select
                                    value={contentTone}
                                    onChange={handleContentToneChange}
                                    color="primary"
                                    sx={{color: CustomTheme.palette.text.primary}}
                                    name="tone"
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

                    <Button
                        type="submit"
                        variant="contained"
                        color="primary"
                        disabled={loading}
                        startIcon={loading ? <CircularProgress size={17} /> : null}
                    >
                        {loading ? 'Doing...' : 'Generate'}
                    </Button>
                </form>
            </Box>

            <Box sx={{
                flexGrow: 1,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
            }}>
                {renderContent()}
            </Box>
        </Box>
    );
}

export default DataDisplay;