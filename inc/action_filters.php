<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sticky_chimp_options = get_option("sticky_chimp_options");

function sticky_chimp_notice_content_saved() {
	?>
	<div class="notice notice-success is-dismissible">
		<p><?php _e( 'Done!', 'sticky-chimp' ); ?></p>
	</div>
	<?php
}


if($sticky_chimp_options['enable_woocommerce_checkout']) {
    add_action('woocommerce_after_checkout_billing_form', function() {
        $out = "<div class='form-row form-row-wide sticky-chimp-newsletter'>";
        $out .= "<label>";
	    $out .= '   <input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="sticky-chimp-join-newsletter" type="checkbox" checked="checked" name="sticky-chimp-join-newsletter" >';
	    $out .= "   <span>Subscribe to our newsletter!</span>";
	    $out .= "</label>";
        $out .= "</div>";
	    $out .= '<div class="clear"></div>';

        echo $out;
    });

    /**
     * Sample Testing to review order
     * /checkout/order-received/66563/?key=wc_order_5aeb9267de265
     * $order = new WC_Order( 66563 );
     * $key   = $order->get_order_key();
     */
	add_action('woocommerce_thankyou', function($order_id) {
	    if(!empty($order_id)) {
		    $order       = new WC_Order( $order_id );
		    $StickyChimp = new StickyChimp(get_option("sticky_chimp_api_key"), get_option("sticky_chimp_list_id"));

		    $first_name = $order->get_billing_first_name();
		    $last_name  = $order->get_billing_last_name();
		    $email      = $order->get_billing_email();

		    $product = "";
		    foreach ($order->get_items() as $item_id => $item_data) {
			    $products = $item_data->get_product();
			    $product .= $products->get_name() ." | ";
		    }
		    $product_field = "MMERGE".get_option( "sticky_chimp_field_product_id" );

		    $StickyChimp->create_subscriber($email, array("merge_fields"=> array("FNAME"=>$first_name, "LNAME"=>$last_name, $product_field=>$product)) );
	    }
	});
}

if($sticky_chimp_options['remove_on_cancelled_subscription']) {
    add_action('woocommerce_subscription_status_cancelled', function($subscription) {
	    $order = new WC_Order( $subscription->id );
	    $email = $order->get_billing_email();

	    $StickyChimp = new StickyChimp(get_option("sticky_chimp_api_key"), get_option("sticky_chimp_list_id"));
	    $StickyChimp->remove_subscriber($email, 'unsubscribe');
    });
}


