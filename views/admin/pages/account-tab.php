<div class="hai-option-field-wrap">
    <h2 class="hai-section-head"><?php esc_html_e('Activation Hooshina Ai', 'hooshina-ai') ?></h2>
    <div class="connection-button-wrap"></div>
</div>

<?php 
if($accountBalance): 
    $isLtr = \HooshinaAi\App\Helper::get_locale() == 'en';

    $balanceValue = $isLtr ? $accountBalance['USD'] : $accountBalance['IRT'];

    $htmlValue = sprintf("%s %s", number_format($balanceValue, ($isLtr ? 3 : 0)), ($isLtr ? __('Usd', 'hooshina-ai') : __('Toman', 'hooshina-ai')));
?>
    <div class="hai-option-field-wrap">
        <h2 class="hai-section-head"><?php esc_html_e('Account Balance', 'hooshina-ai') ?></h2>
        <div class="hai-account-balance">
            <span><?php echo esc_html($htmlValue) ?></span>

            <a href="<?php echo esc_attr(HooshinaAi\App\Connection::get_charge_page_url()) ?>" target="_blank"><?php esc_html_e('Charge Account', 'hooshina-ai') ?></a>
        </div>
    </div>
<?php endif ?>