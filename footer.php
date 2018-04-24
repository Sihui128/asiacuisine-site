<?php
/**
 * The template for displaying the footer.
 *
 * @package flatsome
 */

global $flatsome_opt;
?>

</main><!-- #main -->

<footer id="footer" class="footer-wrapper">

	<?php do_action('flatsome_footer'); ?>

</footer><!-- .footer-wrapper -->

</div><!-- #wrapper -->

<?php 
wp_footer();

$billing_first_name='';
$billing_email='';
$billing_phone='';
$billing_address_1='';
$billing_postcode='';
$billing_city='';

$current_user = wp_get_current_user();
$current_user_id=$current_user->ID;
if ( 0 == $current_user_id ) {
    // Not logged in.
} else {
    // Logged in.
		
		$billing_first_name=get_user_meta( $current_user_id, 'billing_first_name', true );
		$billing_email=get_user_meta( $current_user_id, 'billing_email', true );
		$billing_phone=get_user_meta( $current_user_id, 'billing_phone', true );
		$billing_address_1=get_user_meta( $current_user_id, 'billing_address_1', true );
		$billing_postcode=get_user_meta( $current_user_id, 'billing_postcode', true );
		$billing_city=get_user_meta( $current_user_id, 'billing_city', true );
}
?>


<form id="restaurant-linker-form" action="" method="post" target="_blank">
<input type="hidden" name="billing_first_name" value="<?php echo $billing_first_name; ?>" />
<input type="hidden" name="billing_email" value="<?php echo $billing_email; ?>" />
<input type="hidden" name="billing_phone" value="<?php echo $billing_phone; ?>" />
<input type="hidden" name="billing_address_1" value="<?php echo $billing_address_1; ?>" />
<input type="hidden" name="billing_postcode" value="<?php echo $billing_postcode; ?>" />
<input type="hidden" name="billing_city" value="<?php echo $billing_city; ?>" />
</form>





<script type="text/javascript">
/* ---------------------------- */
// 如果是餐馆列表页面，下面的代码控制filter 
/* ---------------------------- */
if(jQuery('body').hasClass('page-id-275')){
	
	var crr_chosen_delivery_method='all';
	var crr_chosen_category='all';
	
	jQuery('a.ShowFreeDeliveryBTN').click(function(){		
		crr_chosen_delivery_method='deliver_yes';
		crr_chosen_category='all';
		jQuery('a.ShowFreeDeliveryBTN').removeClass('is-outline');
		jQuery('a.pickupBTN').addClass('is-outline');
		filter_restaurant_result();
	});
	jQuery('a.pickupBTN').click(function(){
		crr_chosen_delivery_method='deliver_no';
		crr_chosen_category='all';
		jQuery('a.ShowFreeDeliveryBTN').addClass('is-outline');
		jQuery('a.pickupBTN').removeClass('is-outline');
		filter_restaurant_result();		
	});
	jQuery('a.showAllAvailableRestaurantsBTN').click(function(){
		crr_chosen_delivery_method='all';
		crr_chosen_category='all';
		jQuery('a.ShowFreeDeliveryBTN, a.pickupBTN').addClass('is-outline');
		jQuery('a.categoryBTN').removeClass('crr-chosen-cate-btn');
		filter_restaurant_result();
	});
	jQuery('a.categoryBTN').click(function(){
		var crr_obj=jQuery(this);
		var clickedBTNcategoryTitle=crr_obj.attr('title');
		crr_chosen_category=clickedBTNcategoryTitle;
		jQuery('a.categoryBTN').removeClass('chosenKitchenCategory');
		crr_obj.addClass('chosenKitchenCategory');
		
		filter_restaurant_result();
	});
	
}


function filter_restaurant_result(){
	console.log('call the func');
	countRestaurantNumPerCategory();
	
	if(jQuery('p.noresultnoti').length){
		jQuery('p.noresultnoti').remove();
	}
	
	jQuery('div.restaurantcontainer').each(function(){
		var crr_obj=jQuery(this);		
		if((crr_chosen_delivery_method=="all" || crr_obj.hasClass(crr_chosen_delivery_method)) && (crr_chosen_category=='all' || crr_obj.hasClass('product_cat-'+crr_chosen_category))){
			crr_obj.removeClass('hidden_for_filter');
		}else{			
			crr_obj.addClass('hidden_for_filter');			
		}
		
	});
	
	if(jQuery('div.hidden_for_filter').length==jQuery('div.restaurantcontainer').length){
		jQuery('.restaurantlistcontainer div.products').append('<p class="noresultnoti">Sorry, no restaurant has been found according to the condition.</p>');
	}
}

function countRestaurantNum(){
	//var total_restaurants_num=0;
	var total_freedelivery_restaurants_num=0;
	jQuery('a.showAllAvailableRestaurantsBTN').append(' <span>('+jQuery('div.restaurantcontainer').length+')</span>');
	jQuery('a.ShowFreeDeliveryBTN').append(' <span>('+jQuery('div.deliver_yes').length+')</span>');
	jQuery('a.pickupBTN').append(' <span>('+jQuery('div.deliver_no').length+')</span>');
	
	countRestaurantNumPerCategory();
}


function countRestaurantNumPerCategory(){
	jQuery('a.categoryBTN').removeClass('count_as_0');
	if(crr_chosen_delivery_method=='all'){
		var objStr='div.product_cat-';
	}else{
		var objStr='div.'+crr_chosen_delivery_method+'.product_cat-';
	}
	jQuery('a.categoryBTN').each(function(){
		var crr_obj=jQuery(this);
		var crr_cat=crr_obj.attr('title');
		var crr_cat_num=jQuery(objStr+crr_cat).length;
		if(crr_cat_num==0){
			crr_obj.addClass('count_as_0');
		}
		crr_obj.find('span.filtered_num_category').remove();
		crr_obj.children('span').append('<span class="filtered_num_category">('+crr_cat_num+')</span>');
	});
}
/* ---------------------------- */
/* ---------------------------- */
/* ---------------------------- */


if(jQuery('.page-id-310').length>0){
	jQuery(window).resize(function(){
		themapsize();
	});
	themapsize();
}
function themapsize(){
	var crr_window_height=jQuery(window).height();
	if(crr_window_height<300){
		crr_window_height=300;
	}
	jQuery('div#map').height(crr_window_height-jQuery('header#header').height());
	google.maps.event.trigger(map, 'resize');
}

jQuery(document).ready(function(){
	if(jQuery('.single-product .reviews_tab > a').length>0){
		jQuery('.reviews_tab > a').click();
	}
	countRestaurantNum();
});

function viewMenu(theURL){
	jQuery('form#restaurant-linker-form').attr('action',theURL);
	jQuery('form#restaurant-linker-form').submit();
}

</script>
</body>
</html>
