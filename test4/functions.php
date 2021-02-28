<?php

// Enqueue parent theme style
function add_parent_style() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' ); 
}
add_action( 'wp_enqueue_scripts', 'add_parent_style');


// Enqueue child theme style
function add_child_style() {
    wp_enqueue_style( 'child-style', get_stylesheet_uri() );
}
add_action( 'wp_enqueue_scripts', 'add_child_style', 11 );

// Apply a selected coupon code only to matched brands
function apply_test4_coupon_to_selected_items() {
    
    $coupon_label = 'test4';
    
    // Get the ID of this coupon
    $args = array(
        'post_type'         => 'shop_coupon',
        'post_status'       => 'publish',
        'title'             => $coupon_label,
    );    
    $coupons = get_posts( $args );
    foreach($coupons as $coupon) {
        $coupon_ID = $coupon->ID;
    }

    // Get brands associated with this coupon
    $allowed_brands = [];
    $brands = get_fields($coupon_ID)['brand'];
    foreach ($brands as $brand) {
        array_push($allowed_brands, $brand->slug);
    }

    // Get all products with those same attributes
    $products = new WP_Query (array(
        'post_type'     => 'product',
        'post_status'   => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'pa_brands',
                'field'    => 'slug',
                'terms'    => $allowed_brands
            )
        )
    ));
    
    if($products->have_posts()) {
        while ($products->have_posts()) {
            $products->the_post();
            $product_ids[] = $products->post->ID;
        }
        wp_reset_postdata();
    }
    // Get an instance of the WC_Coupon object
    $wc_coupon = new WC_Coupon($coupon_label);

    // Update product limitation with the matched products.
    $wc_coupon->set_product_ids($product_ids);

    // Save the coupon
    $wc_coupon->save();
   
}
add_action('woocommerce_before_cart', 'apply_test4_coupon_to_selected_items', 999);