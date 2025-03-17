document.addEventListener("DOMContentLoaded", () => {
    let productReviewsListWrap = document.querySelector('#reviews'),
        hooshinaReviewsSummary = document.querySelector('.hooshina-ai-product-summary');

    if(productReviewsListWrap && hooshinaReviewsSummary){
        productReviewsListWrap.prepend(hooshinaReviewsSummary);
        hooshinaReviewsSummary.style.display = '';
    }
});