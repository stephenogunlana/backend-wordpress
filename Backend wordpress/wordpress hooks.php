<?php
/*
This code is meant to be a wordpress hook that is added to the functions.php page.

User Information Retrieval: It fetches the current logged-in user's details such as first name, last name, address, and role. The user information is used to generate JavaScript variables that can be passed to an external script via AJAX.

AJAX Functionality: It listens for a change in the payment method ('#payment_method_purchase_order') and triggers an AJAX call to an external PHP script hosted at 'https://generalequip.lvaiproofs.com/creditsafe_integration_script.php'. It sends user data (name and address) and processes the response to update the payment method display.

Frontend Modifications: It adds HTML elements for the payment method (a radio button for 'Purchase Order') and a display area for text, which gets updated based on the AJAX response.

Additional JavaScript Functionality: An extra script is added to modify the structure of a related products section on the frontend.

This code integrates the WooCommerce payment system with an external credit processing script, potentially for user verification or credit-related functionalities. It also customizes the user interface in the WooCommerce checkout page for specific user roles.
*/


$current_user = wp_get_current_user();
    $user_id = $current_user->ID;

//     // Retrieve the user's name and address fields
//     $user_info['user_first_name'] = $current_user->first_name;
//     $user_info['user_last_name'] = $current_user->last_name;
//     $user_info['address'] = get_user_meta($user_id, 'address', true);

//     // Create JavaScript variables to pass user data to the script
//     echo $user_full_name = $user_info['user_first_name'] . ' ' . $user_info['user_last_name'];
//     echo $billing_address = $user_info['address'];

// 	$user_role = $current_user->roles[0]; // Assumes the user has only one role

//     // Print the user role
//     echo 'User Role: ' . $user_role;
	

function add_custom_ajax_script_to_footer() {
	
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    // Retrieve the user's name and address fields
    $user_info['user_first_name'] = $current_user->first_name;
    $user_info['user_last_name'] = $current_user->last_name;
    $user_info['address'] = get_user_meta($user_id, 'address', true);

    // Create JavaScript variables to pass user data to the script
//     echo $user_full_name = $user_info['user_first_name'] . ' ' . $user_info['user_last_name'];
//     echo $billing_address = $user_info['address'];
	


    // Output the JavaScript code
    ?>
   <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Add a flag to track whether the payment method has been changed
        // Listen for changes to the payment method
        $('#payment_method_purchase_order').on('change', function() {
  
            // Data to be sent to the external PHP script
            $('.display_radio_text').text("Give us a minute while we process your credit.....");
			$('input[type="radio"]#payment_method_purchase_order').attr('style','background-color: green');
			

//             var dataToSend = {
//                 user_full_name: '<?php echo $user_full_name; ?>',
//                 billing_address: '<?php echo $billing_address; ?>'
//             };
// 			var user_full_name = <?php echo $user_full_name; ?>;
// 			var billing_address = <?php echo $billing_address; ?>;

// 			$('.display_radio_text').text('test');
// 			$('.display_radio_text').text(billing_address);
			
            $.ajax({
                type: 'POST',
                url: 'https://generalequip.lvaiproofs.com/creditsafe_integration_script.php',
                data: dataToSend,
                success: function(response) {
                    if (parseInt(response) === 1) {
                        // Your if block
                        $('#payment_method_woocommerce_gateway_purchase_order').prop('checked', true);
                        $('.display_radio_text').text("Credit Successfully processed....");
						$('#payment_method_purchase_order').attr('style', 'color: #007bff');
                        $('li.payment_method_woocommerce_gateway_purchase_order').attr('style', 'display: block !important');
					
					}else if (response == 'Not found') {
						// Your else if block
						// Code for condition2
						$('.display_radio_text').text("Sorry, we could not find your credit data. Please call 506-434-3344 ");
					} else {
                        // Your else block
                        // You can add alternative actions here
                        $('.display_radio_text').text("Sorry, we can't process your order at this point. Please call 506-434-3344" + response);
                    }
                },
                error: function(xhr, status, error) {
                    $('.purchase_order_label').text('E:' + status + ' and error: ' + error);
                }
            });

        });
    });
</script>

    <?php
}

add_action('wp_footer', 'add_custom_ajax_script_to_footer');




function add_popup_content_to_payment_and_review() {
    echo '<div id="popup-overlay">
    <div id="popup-content">
        <span id="popup-close">Close</span>
        <p>Your popup content goes here.</p>
    </div>
</div>';
}
add_action('woocommerce_review_order_before_payment', 'add_popup_content_to_payment_and_review');


function add_html_content_to_payment_and_review() {
		// Get the current user's information
		$current_user = wp_get_current_user();

		// Check if the user has one of the specified roles
		if (in_array('um_dealer', $current_user->roles) || in_array('um_contractor', $current_user->roles) || in_array('administrator', $current_user->roles)) {
			echo '
				<div class="payment_method_purchase_order_div">
					<input type="radio" name="payment_method" id="payment_method_purchase_order" value="purchase_order" checked>
					<label for="payment_method_purchase_order" class="radio-button-label">Purchase Order</label>
					<p class="display_radio_text"></p>
				</div>
			';
		}

}

add_action('woocommerce_review_order_before_payment', 'add_html_content_to_payment_and_review');

function add_custom_script() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        // Select the element with the specified class and add an id to it
        $("section.related.products.wt-related-products").attr("id", "wt-related-products");
    });
    </script>
    <?php
}
add_action('wp_footer', 'add_custom_script');


?>