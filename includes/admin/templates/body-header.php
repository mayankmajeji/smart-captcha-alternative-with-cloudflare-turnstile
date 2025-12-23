<?php
if ( ! defined('WPINC') ) {
	die;
}
// Expected (optional) inputs:
// - $twp_title (string)
// - $twp_desc (string)
?>
<div class="twp-body-header">
	<div class="twp-bh-left">
		<img src="<?php echo esc_url(TURNSTILEWP_PLUGIN_URL . 'assets/images/favicon.svg'); ?>" alt="Smart Cloudflare Turnstile" />
	</div>
	<div class="twp-bh-right">
		<h1><?php echo esc_html(isset($twp_title) && $twp_title !== '' ? $twp_title : get_admin_page_title()); ?></h1>
		<?php if ( ! empty($twp_desc) ) : ?>
			<p class="twp-page-desc"><?php echo esc_html($twp_desc); ?></p>
		<?php endif; ?>
	</div>
</div>
