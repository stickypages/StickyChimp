<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="wrap">
	<h1><?php echo __('StickyChimp', 'sticky-chimp'); ?></h1>
</div>

<?php

if( isset($_POST['sticky_chimp_api_key']) && !empty($_POST['sticky_chimp_api_key']) ) {
	update_option( 'sticky_chimp_api_key', $_POST['sticky_chimp_api_key'] );
	update_option( 'sticky_chimp_list_id', $_POST['sticky_chimp_list_id'] );
	update_option( 'sticky_chimp_options', $_POST['sticky_chimp_options'] );
	update_option( 'sticky_chimp_on_actions', $_POST['sticky_chimp_on_actions'] );

	// Saved
	sticky_chimp_notice_content_saved();
}

$sticky_chimp_api_key = get_option("sticky_chimp_api_key");
$sticky_chimp_list_id = get_option("sticky_chimp_list_id");
$sticky_chimp_options = get_option("sticky_chimp_options");
$sticky_chimp_on_actions = get_option("sticky_chimp_on_actions");

$lists = null;
if(!empty($sticky_chimp_api_key)) {
	$StickyChimp = new StickyChimp($sticky_chimp_api_key);
	$response = $StickyChimp->method( "GET" )->request();
	$lists = $StickyChimp->response_body;

	/**
	 * CREATE A CUSTOM FIELD IN MAILCHIMP
     * "PRODUCT" and store products from woocommerce there
	 */
    if(!empty($sticky_chimp_list_id)) {
	    if ( !get_option( "sticky_chimp_field_product_id" ) ) {
		    echo "Created Field, 'PRODUCT'";
		    $StickyChimpField = new StickyChimp( $sticky_chimp_api_key, $sticky_chimp_list_id );
		    $StickyChimpField->create_field( "PRODUCT", "text" );
		    if ( $StickyChimpField->response_code === 200 ) {
			    update_option( 'sticky_chimp_field_product_id', $StickyChimpField->response_body->merge_id );
		    }
	    }
    }
}


$sticky_on_actions = array(
    'woocommerce_thankyou'  => 'WooCommerce Thank You Order Page',
);


?>

<form name="form1" method="post" action="options-general.php?page=sticky-chimp">

    <table>
        <tr>
            <td><?php _e("API Key", 'sticky-chimp' ); ?></td><td><input type="text" name="sticky_chimp_api_key" value="<?php echo $sticky_chimp_api_key; ?>" size="60" /></td>
        </tr>

        <?php if(!empty($sticky_chimp_api_key)) { ?>
            <tr>
                <td><?php _e("List", 'sticky-chimp' ); ?></td>
                <td>
                    <select name="sticky_chimp_list_id">
		                <?php if(!empty($lists)) {
			                foreach($lists->lists as $list) {
				                echo '<option value="'.$list->id.'" '. selected( $list->id, $sticky_chimp_list_id ) .'>'.$list->name.'</option>';
			                }
		                } ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?php _e("On What Action", 'sticky-chimp' ); ?></td>
                <td>
                    <select name="sticky_chimp_on_actions[]" multiple="multiple" required="required">
				        <?php if(!empty($sticky_on_actions)) {
					        foreach($sticky_on_actions as $action => $action_name) {
					            $selected = null;
					            if(in_array($action, $sticky_chimp_on_actions)) {
					                $selected = "selected";
                                }
						        echo '<option value="'.$action.'" '. $selected .'>'.$action_name.'</option>';
					        }
				        } ?>
                    </select>
                </td>
            </tr>
        <?php } // EO api key ?>
    </table>
    <hr />

    <h2><?php echo __('Options', 'sticky-chimp'); ?></h2>
    <div class="sticky_chimp_options">
        <ul>
            <li><label><input type="checkbox" name="sticky_chimp_options[enable_woocommerce_checkout]" <?php @checked($sticky_chimp_options['enable_woocommerce_checkout'], 1) ?> value="1"> Enable at WooCommerce Checkout, will join the list selected</label></li>
            <li><label><input type="checkbox" name="sticky_chimp_options[remove_on_cancelled_subscription]" <?php @checked($sticky_chimp_options['remove_on_cancelled_subscription'], 1) ?> value="1"> Remove from list selected on cancelled subscription</label></li>
        </ul>
    </div>

	<p class="submit">
        <input type="hidden" name="page" value="sticky-chimp" />
		<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes') ?>" />
	</p>

</form>
