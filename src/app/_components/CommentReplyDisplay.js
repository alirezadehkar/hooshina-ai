import { 
    Box, 
    LinearProgress, 
    Typography ,
    Button,
    Radio
} from "@mui/material";
import { useState, useEffect } from "@wordpress/element";
import axios from 'axios';
import qs from 'qs';
import InputMultiline from "./Input";
import { useSnackbar } from "../SnackbarContext";
import { stripHtml } from "../Helpers";

const CommentReplyDisplay = ({data, contentType, isOpen, onClose, selectedBlock, options}) => {
    const { showSnackbar } = useSnackbar();
    
    const [loading, setLoading] = useState(true);
    const [submitProcess, setSubmitProcess] = useState(false);
    const [commentAnswers, setCommentAnswers] = useState([]);
    const [selectedAnswer, setSelectedAnswer] = useState(-1);

    const comment = selectedBlock ? selectedBlock.closest('.comment') : null;

    const isReviewsSummary = options?.isReviewsSummary == true;

    let objectId = null;
    let commentText = null;

    if(comment){
        objectId = parseInt(comment.querySelector('.reply .button-link')?.dataset?.commentId);
        commentText = comment.querySelector('p').innerHTML;
    } else {
        let urlParams = new URLSearchParams(window.location.search);

        let postId = urlParams.get('post');
        objectId = postId;
        commentText = null;
    }

    const answerCount = 3;

    const generateCommentAnswer = async () => {
        const promises = [];

        for (let i = 0; i < answerCount; i++) {
            promises.push(
                axios.post(hai_data.ajax_url, qs.stringify({
                    action: 'hai_generate_comment_answer',
                    objectId: objectId,
                    nonce: hai_data.nonce,
                    isReviewsSummary: isReviewsSummary
                }))
            );
        }

        try {
            const responses = await Promise.all(promises);
            let error = false;
            return responses.map(response => {
                if(!response.data.data?.content){
                    if(!error){
                        showSnackbar(response.data.data.msg, 'error');
                    }
                    error = true;
                    onClose();
                    return null;
                }

                return stripHtml(response.data.data.content);
            });
        } catch {
            setLoading(false);
            return [];
        }
    };

    const handleSubmitComment = async () => {
        if (!Array.isArray(commentAnswers) || !commentAnswers[selectedAnswer]) {
            showSnackbar(hai_data.texts.select_answer_warning, 'error');
            return;
        }

        setSubmitProcess(true);

        setLoading(true);

        try {
            const response = await axios.post(hai_data.ajax_url, qs.stringify({
                action: isReviewsSummary ? 'hai_submit_product_reviews_summary' : 'hai_submit_comment_answer',
                objectId: objectId,
                answer: commentAnswers[selectedAnswer],
                nonce: hai_data.nonce,
            }));

            if(response?.data?.data?.msg){
                showSnackbar(response.data.data.msg, response.data.data.status);
            }

            if (response.data.success) {
                setTimeout(() => {
                    location.reload();
                    onClose();
                }, 1500);
            }
        } catch {
            setLoading(false);
            setSubmitProcess(false);
        }
    };

    const onChangeAnswer = (index, value) => {
        const updatedAnswers = [...commentAnswers];
        updatedAnswers[index] = value;
        setCommentAnswers(updatedAnswers);
    }

    const onSelectAnswer = (index) => {
        if(selectedAnswer != index){
            setSelectedAnswer(index);
        }
    }

    useEffect(() => {
        setLoading(true);

        generateCommentAnswer().then(res => {
            setLoading(false);

            if (res && res.length > 0) {    
                setCommentAnswers(res);            
            }
        });
    }, [selectedBlock]);

    return (
        <Box sx={{ p: 2 }}>
            {loading ? (
                <>
                    {!submitProcess && 
                    (
                        <Box sx={{ mb: 2 }}>
                            <Typography variant="h6">
                                { hai_data.texts.wait_for_answer }
                            </Typography>
                        </Box>
                    )}
                    <Box sx={{ textAlign: 'center' }}>
                        <LinearProgress />
                    </Box>
                </>
            ) : (
                <Box>
                    {!isReviewsSummary && 
                        <Box>
                            <Typography variant="h6" sx={{ mb: 2 }}>
                                { hai_data.texts.answer_for }
                            </Typography>
                            <Box sx={{ color: 'text.primary' }} dangerouslySetInnerHTML={{ __html: commentText }} />
                        </Box>
                    }
                    

                    <Box sx={{my: 2}}>
                        <Typography variant="h6" sx={{ mb: 2 }}>
                            { hai_data.texts.ai_answer }
                        </Typography>
                        {commentAnswers.map((answer, index) => (
                            <Box sx={{ display: 'flex', alignItems: 'center', mb: 2 }} key={index}>
                                <Radio 
                                    checked={selectedAnswer === index}
                                    onClick={(e) => onSelectAnswer(index)}
                                    value={index}
                                    sx={{ flexShrink: 0 }}
                                    name="comment-ai-answers"
                                />
                                <Box sx={{ width: '100%' }}>
                                    <InputMultiline 
                                        name={`answer_${index}`}
                                        value={answer}
                                        onChange={(e) => onChangeAnswer(index, e.target.value)}
                                        rows={3}
                                        fullWidth
                                    />
                                </Box>
                            </Box>
                        ))}
                    </Box>

                    <Button
                        type="submit"
                        variant="contained"
                        color="primary"
                        disabled={loading}
                        startIcon={loading ? <CircularProgress size={17} /> : null}
                        onClick={handleSubmitComment}
                    >
                        {loading ? hai_data.texts.doing : hai_data.texts.submit}
                    </Button>
                </Box>
            )}
        </Box>
    );
}

export default CommentReplyDisplay;
