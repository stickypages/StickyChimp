<div class="wrap">
	<h1><?php echo __('Sticky', 'sticky-chimp'); ?></h1>
</div>

<?php
include 'action_filters.php';
$sticky_chimp_api_key = get_option("sticky_chimp_api_key");

if( isset($_POST['sticky_chimp_api_key']) && !empty($_POST['sticky_chimp_api_key']) ) {
	echo "SAVEEEE";
	update_option( 'sticky_chimp_api_key', $_POST['sticky_chimp_api_key'] );
	// Saved
	add_action( 'admin_notices', 'sticky_chimp_content_saved' );
}

?>

<form name="form1" method="post">

	<p><?php _e("API Key:", 'sticky-chimp' ); ?>
		<input type="text" name="sticky_chimp_api_key" value="<?php echo $sticky_chimp_api_key; ?>" size="40">
	</p><hr />

	<p class="submit">
		<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
	</p>

</form>
