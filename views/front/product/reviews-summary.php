<div class="hooshina-ai hooshina-ai-product-summary" style="display:none">
    <div class="hooshina-ai-summary-head">
        <div class="hai-sicon">
            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="100" height="100" viewBox="0 0 48 48">
                <defs>
                    <radialGradient id="animatedGradient" cx="24" cy="24" r="24" gradientUnits="userSpaceOnUse">
                    <stop offset="0%" stop-color="#1ba1e3">
                        <animate attributeName="stop-color" 
                                values="#1ba1e3; #5489d6; #9b72cb; #d96570;#b7e7ff; #1ba1e3" 
                                dur="10s" 
                                repeatCount="indefinite" />
                    </stop>
                    <stop offset="50%" stop-color="#5489d6">
                        <animate attributeName="stop-color" 
                                values="#5489d6; #9b72cb; #d96570; #f49c46; #1ba1e3; #5489d6" 
                                dur="10s" 
                                repeatCount="indefinite" />
                    </stop>
                    <stop offset="100%" stop-color="#f49c46">
                        <animate attributeName="stop-color" 
                                values="#f49c46; #1ba1e3; #5489d6; #9b72cb; #d96570; #f49c46" 
                                dur="10s" 
                                repeatCount="indefinite" />
                    </stop>
                    <animateTransform attributeName="gradientTransform"
                                        type="rotate"
                                        from="0 24 24"
                                        to="360 24 24"
                                        dur="15s"
                                        repeatCount="indefinite"/>
                    </radialGradient>
                </defs>
                
                <path fill="url(#animatedGradient)" d="M22.882,31.557l-1.757,4.024c-0.675,1.547-2.816,1.547-3.491,0l-1.757-4.024 c-1.564-3.581-4.378-6.432-7.888-7.99l-4.836-2.147c-1.538-0.682-1.538-2.919,0-3.602l4.685-2.08 c3.601-1.598,6.465-4.554,8.002-8.258l1.78-4.288c0.66-1.591,2.859-1.591,3.52,0l1.78,4.288c1.537,3.703,4.402,6.659,8.002,8.258 l4.685,2.08c1.538,0.682,1.538,2.919,0,3.602l-4.836,2.147C27.26,25.126,24.446,27.976,22.882,31.557z"></path>
                
                <path fill="url(#animatedGradient)" d="M39.21,44.246l-0.494,1.132 c-0.362,0.829-1.51,0.829-1.871,0l-0.494-1.132c-0.881-2.019-2.467-3.627-4.447-4.506l-1.522-0.676 c-0.823-0.366-0.823-1.562,0-1.928l1.437-0.639c2.03-0.902,3.645-2.569,4.511-4.657l0.507-1.224c0.354-0.853,1.533-0.853,1.886,0 l0.507,1.224c0.866,2.088,2.481,3.755,4.511,4.657l1.437,0.639c0.823,0.366,0.823,1.562,0,1.928l-1.522,0.676 C41.677,40.619,40.091,42.227,39.21,44.246z"></path>
            </svg>
        </div>
        <div>
            <h5><?php echo esc_html__('Summary of Customer Reviews', 'hooshina-ai') ?></h5>
            <p><?php echo esc_html__('Generated with Ai', 'hooshina-ai') ?></p>
        </div>
    </div>
    <div class="hooshina-ai-summary-content-wrap">
        <div class="hooshina-ai-summary-content">
            <p><?php echo esc_html($reviewsSummary) ?></p>
        </div>
        
        <div class="hooshina-ai-summary-content-foot">
            <?php echo esc_html__('This summary may not be accurate.', 'hooshina-ai') ?>
        </div>
    </div>
</div>