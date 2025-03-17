<div class="hai-option-field-wrap">
    <label for="default_content_tone"><?php esc_html_e('Default Content Tone', 'hooshina-ai') ?></label>
    <div class="hai-form-field">
        <select name="default_content_tone">
            <option value=""><?php esc_html_e('Select...', 'hooshina-ai') ?></option>
            <?php
                if($contentTones){
                    foreach($contentTones as $key => $title){ ?>
                        <option <?php selected($key, $defContentTone) ?> value="<?php echo esc_attr($key) ?>">
                            <?php echo esc_html($title) ?>
                        </option>
                    <?php }
                }
            ?>
        </select>
    </div>
</div>
<div class="hai-option-field-wrap">
    <label for="default_image_style"><?php esc_html_e('Default Image Style', 'hooshina-ai') ?></label>
    <div class="hai-form-field">
        <select name="default_image_style">
            <option value=""><?php esc_html_e('Select...', 'hooshina-ai') ?></option>
            <?php
                if($imageStyles){
                    foreach($imageStyles as $key => $title){ ?>
                        <option <?php selected($key, $defImageStyle) ?> value="<?php echo esc_attr($key) ?>">
                            <?php echo esc_html($title) ?>
                        </option>
                    <?php }
                }
            ?>
        </select>
    </div>
</div>
<div class="hai-option-field-wrap">
    <label for="default_product_image_style"><?php esc_html_e('Default Product Style', 'hooshina-ai') ?></label>
    <div class="hai-form-field">
        <select name="default_product_image_style">
            <option value=""><?php esc_html_e('Select...', 'hooshina-ai') ?></option>
            <?php
                if($productImageStyles){
                    foreach($productImageStyles as $key => $title){ ?>
                        <option <?php selected($key, $defProductImageStyle) ?> value="<?php echo esc_attr($key) ?>">
                            <?php echo esc_html($title) ?>
                        </option>
                    <?php }
                }
            ?>
        </select>
    </div>
</div>

<div class="hai-option-field-wrap">
    <label for="default_image_size"><?php esc_html_e('Default Image Size', 'hooshina-ai') ?></label>
    <div class="hai-form-field">
        <select name="default_image_size">
            <option value=""><?php esc_html_e('Select...', 'hooshina-ai') ?></option>
            <?php
                if($imageSizes){
                    foreach($imageSizes as $key => $title){ ?>
                        <option <?php selected($key, $defImageSize) ?> value="<?php echo esc_attr($key) ?>">
                            <?php echo esc_html($title) ?>
                        </option>
                    <?php }
                }
            ?>
        </select>
    </div>
</div>

<footer class="hai-options-foot">
    <button type="submit" class="hai-submit"><?php esc_html_e('Save Changes', 'hooshina-ai') ?></button>
</footer>