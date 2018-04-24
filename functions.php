<?php
// Add custom Theme Functions here
function wpb_woo_my_account_order() {
 $myorder = array(
 'edit-account' => __( 'Change My Details', 'woocommerce' ),
 'edit-address' => __( 'Addresses', 'woocommerce' ),
 'customer-logout' => __( 'Logout', 'woocommerce' ),
 );
 return $myorder;
}
add_filter ( 'woocommerce_account_menu_items', 'wpb_woo_my_account_order' );






add_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_single_excerpt', 5);

#######################################################################
#######################################################################
#######################################################################

function show_user_current_address_func($atts){
	
	$page_id_of_crr_page = get_queried_object_id();
	if(!empty($page_id_of_crr_page)){
		if(273!=$page_id_of_crr_page){
			if(isset($_SESSION['useraddress'])){
				$the_user_address_raw=$_SESSION['useraddress'];
				
				$addresswordtoreplace=array(', BELGIUM',',BELGIUM',', Belgium',',Belgium',', belgium',',belgium',', België',',België', ', BELGIË', ',BELGIË');
				$the_user_address = str_replace($addresswordtoreplace, "", $the_user_address_raw);
			}
		}
	}
	if(empty($the_user_address)){
		$crr_user_language=qtranxf_getLanguage();
		if($crr_user_language=='nl'){
		  $the_user_address='Antwerpen België';
		}else{
			$the_user_address='Antwerp Belgium';
		}
	}
	return $the_user_address;
}
add_shortcode( 'show_user_current_address', 'show_user_current_address_func' );




#######################################################################
#######################################################################
#######################################################################

function show_close_by_restaurants_func($atts){
	 if(empty($_SESSION['available_restaurants'])){
		 return '<p>Sorry, there is no Chinese restaurant registered in our system in this neighborhood.</p>';
	 }else{
		 $available_restaurants=explode('|',$_SESSION['available_restaurants']);
		 
		 
		 $theshortcode='[products ids="';//"]';
     
		 foreach($available_restaurants as $ar){
			 if($theshortcode!='[products ids="'){
				 $theshortcode.=',';
			 }
			 $theshortcode.=$ar;
		 }
		 $theshortcode.='"]';
		 
     return $theshortcode;
	}
}
add_shortcode( 'showrestaurantsinneighborhood', 'show_close_by_restaurants_func' );








#######################################################################
#######################################################################
#######################################################################

function showRestaurantCategories(){
	 $args = array(
			 'taxonomy'     => 'product_cat'
		);
		
		$all_categories_obj = get_categories( $args );
		
		$txt='';
		
		
		foreach($all_categories_obj as $cateobj){
			//$all_categories_array[$cateobj->name]=0;
			
			$cate_name=$cateobj->name;
			$cate_slug=$cateobj->slug;
			$txt.='<a href="#kitchencategory" target="_self" class="button primary is-link is-small lowercase expand categoryBTN" title="'.$cate_slug.'"><span>'.$cate_name.'</span></a>';
			
		}
		
		return $txt;
}
add_shortcode( 'show_restaurants_categories', 'showRestaurantCategories');








#######################################################################
#######################################################################
#######################################################################

function show_detail_restaurant_func($atts){
	
	
	$atts = shortcode_atts( array(
		'idofthepost' => ''
	), $atts);
	
	$R_id_todisplay=$atts['idofthepost'];
	
	$toreturn='';
	
	/**/
	$args = array(
		//'post_id' => $R_id_todisplay
		'post_type' => 'product',
		'posts_per_page' => 880,
		);
	$loop = new WP_Query( $args );
	if ( $loop->have_posts() ) {
		while ( $loop->have_posts() ) : $loop->the_post();
		//wc_get_template_part( 'content', 'single-product' );
		//$a=wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) );
		//return $a;
		
		$crrid=get_the_ID();
		$toreturn.='+++'.$crrid;
		
		endwhile;
	}
	wp_reset_postdata();
	
	
	return $toreturn;
}
add_shortcode( 'showrestaurantdetail', 'show_detail_restaurant_func' );









#######################################################################
#######################################################################
#######################################################################

function show_restaurants_onmap_func($atts){
	if(!isset($_SESSION['on_map_restaurants']) || !isset($_SESSION['userlocationcoordinate'])){
		return;
	}
	
	$user_coo_array=explode('|',$_SESSION['userlocationcoordinate']);
	
	$_a_restaurants=explode('<*|*>', $_SESSION['on_map_restaurants']);
	
	$todaydate=date("l");
	$crrhour=intval(current_time( 'H' ))+(intval(current_time( 'i' ))/60);
	if($crrhour<=3){
		$crrhour+=24;
	}
	
		
	$theJScode="function initMap() {
  var crrUadd = {lat: ".$user_coo_array[0].", lng: ".$user_coo_array[1]."};
  var map = new google.maps.Map(document.getElementById('map'), {
    zoom: 14,
    center: crrUadd
  });
  
  var lastOpenWindow_obj=null;
  ";
	
	foreach($_a_restaurants as $rs){
		
		$rstrt_info=explode('&=&',$rs);
		
		$url=$rstrt_info[5];
		$lat=$rstrt_info[0];
		$lng=$rstrt_info[1];
		$pic=$rstrt_info[2];
		$name=$rstrt_info[3];
		$addr=$rstrt_info[4];
		$closingdate=$rstrt_info[6];
		$openinghour=$rstrt_info[7];
		$deliver_service=$rstrt_info[8];
		$min_spend=$rstrt_info[9];
		
		$crr_open_status=$rstrt_info[10];
		$website=$rstrt_info[11];
		$deliveryavailability=$rstrt_info[12];
		
		
		$deli_words=$deliveryavailability;
		$openwords=$openinghour;
		
		
		
		$theJScode.="
	var p$url={lat: $lat, lng: $lng};
  var contentString$url = '<div class=\"r_onmap_content\"><a href=\"$website\" target=\"_blank\"><img style=\"max-width:180px;\" class=\"restaurantpiconmap\" src=\"$pic\" /></a><p style=\"margin:0 0 5px 0; font-size:14px;\"><a href=\"$website\"><span class=\"firstHeading restaurantnameonmap\">$name</span></a></p><p style=\"margin:0;\">$deli_words | $openwords</p></div>';

  var infowindow$url = new google.maps.InfoWindow({
    content: contentString$url  });

  var marker$url = new google.maps.Marker({
    position: p$url,
    map: map,
    title: '$name'
  });
  marker$url.addListener('click', function() {
    if(lastOpenWindow_obj!=null){
      lastOpenWindow_obj.close();
    }
    lastOpenWindow_obj=infowindow$url;
    infowindow$url.open(map, marker$url);
  });
	";
	}
  
  $theJScode .= '
  }
  ';
  
	$stringtoReturn='<div id="map" style="height:450px;"></div>	
	<script>
	'.$theJScode.'
	</script>
  <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyA4exOxSV3l044-q6THGmU8a7RsXbc5lE8&callback=initMap" type="text/javascript"></script>
	';

	return $stringtoReturn;

}
add_shortcode( 'showrestaurantsonmap', 'show_restaurants_onmap_func' );






#######################################################################
#######################################################################
#######################################################################
function linkBTNRestaurant_func(){
	global $product;
	if(empty($product)){
		return;
	}
	$website=$product->get_attribute('website');
	if(empty($website)){
		return;
	}
	$txttoreturn='<div class="title-wrapper"><button class="viewmenubtn" onclick="viewMenu('."'".$website."'".')">'.__('view menu').' »'.'</button></div>';
	return $txttoreturn;
}
add_shortcode( 'linkBTNRestaurant', 'linkBTNRestaurant_func' );

#######################################################################
#######################################################################
#######################################################################
function google_addr_search_bar_func( $atts ){
	
	if(isset($_SESSION['useraddress'])){
		$useraddress=$_SESSION['useraddress'];
	}else{
		$useraddress='';
	}
	
	$crr_user_language=qtranxf_getLanguage();
	if($crr_user_language=='nl'){
		$enteraddressplaceholderstr='Vul uw adres in';
		$showbtnwords='Zoek';
	}else{
		$enteraddressplaceholderstr='Enter your address';
		$showbtnwords='Show';
	}
	
	return <<<HTML
    <div id="locationField" class="bySigway">
      <input id="autocomplete" placeholder="$enteraddressplaceholderstr" onFocus="geolocate()" type="text" value="$useraddress">
			</input><button class="button primary" style="border-radius:8px; position:relative;" type="button" onclick="submitsearchform()" id="thesearchbtn">
			<span style="display:inline-block;" id="searchrestaurantsbtntxt">$showbtnwords</span>
			<span id="searchinginprogressindicontainer" style="visibility:hidden;">
			<img style="position:relative; height:20px; width:auto; top:4px;" src="https://asiacuisine.be/wp-content/uploads/2017/07/loading-chinafoodbe.gif" />
			</span>
			</button>
    </div>
		<img style="display:none;" src="https://asiacuisine.be/wp-content/uploads/2017/07/loading-chinafoodbe.gif" />

<form action="/restaurants-near-me/" method="get" id="addressform">
<input type="hidden" value="" id="theaddressfieldtopost" name="addr" />
</form>
    <script>
		//google address autocomplete
      // This example requires the Places library. Include the libraries=places
      // parameter when you first load the API. For example:

      var placeSearch, autocomplete;
      var componentForm = {
        street_number: 'short_name',
        route: 'long_name',
        locality: 'long_name',
        administrative_area_level_1: 'short_name',
        country: 'long_name',
        postal_code: 'short_name'
      };

      function initAutocomplete() {
        // Create the autocomplete object, restricting the search to geographical
        // location types.
        autocomplete = new google.maps.places.Autocomplete(
            /** @type {!HTMLInputElement} */(document.getElementById('autocomplete')),
            {types: ['geocode'],
						componentRestrictions: {country: 'be'}
						}
						
						
						);

        // When the user selects an address from the dropdown, populate the address
        // fields in the form.
        autocomplete.addListener('place_changed', fillInAddress);
      }

      function fillInAddress() {
        // Get the place details from the autocomplete object.
        var place = autocomplete.getPlace();
				var address=place.formatted_address;
				
				//console.log(place.formatted_address);
				if(typeof address != 'undefined'){
				  jQuery('input#theaddressfieldtopost').val(address);
				  jQuery('form#addressform').submit();
				}
        
      }

      // Bias the autocomplete object to the user's geographical location,
      // as supplied by the browser's 'navigator.geolocation' object.
      function geolocate() {
        if (navigator.geolocation) {
          navigator.geolocation.getCurrentPosition(function(position) {
            var geolocation = {
              lat: position.coords.latitude,
              lng: position.coords.longitude
            };
            var circle = new google.maps.Circle({
              center: geolocation,
              radius: position.coords.accuracy
            });
						/*
            autocomplete.setBounds(circle.getBounds());
						console.log("navigator.geolocation:"+position);
						*/
						
						var geocoder = new google.maps.Geocoder;
						
						geocoder.geocode({'location': geolocation}, function(results, status) {
							if (status === 'OK') {
								if (results[1]) {
									if(jQuery.trim(jQuery('input#autocomplete').val())==''){
										jQuery('input#autocomplete').val(results[1].formatted_address);
									}
								} else {
									console.log('No results found');
								}
							} else {
								console.log('Geocoder failed due to: ' + status);
							}
						});
						
          });
        }
      }
			
			function submitsearchform(){
				var address=jQuery('input#autocomplete').val();
				
				//console.log(place.formatted_address);
				if(typeof address != 'undefined' && jQuery.trim(address)!=''){
					jQuery('#searchrestaurantsbtntxt').css('opacity','0.1');
					jQuery('#searchinginprogressindicontainer').removeAttr('style');
				  jQuery('input#theaddressfieldtopost').val(address);
				  jQuery('form#addressform').submit();
				}else{
					alert('Please choose a valid address!');
				}
			}
			if(jQuery.trim(jQuery('input#autocomplete').val())==''){
			  geolocate();
			}
    </script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAawJs_oDXSUUQ_L0Ssr_UDwcB--5jGC24&libraries=places&callback=initAutocomplete" async defer></script>

HTML;
}
add_shortcode( 'googleaddresssearchfield', 'google_addr_search_bar_func' );


function distance($lat1, $lon1, $lat2, $lon2, $unit) {

	$theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
	$dist = acos($dist);
	$dist = rad2deg($dist);
	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);

	if ($unit == "K") {
		return ($miles * 1.609344);
	} else if ($unit == "N") {
			return ($miles * 0.8684);
		} else {
				return $miles;
			}
}




function foodCategoryNameInCertainLanguage($catename, $l){
	if($l=='nl'){
		switch ($catename) {
				case 'soup':
						$the_cateName='soep';
						break;
				case 'starter':
						$the_cateName='voorgerecht';
						break;
				case 'snack':
						$the_cateName='hapje';
						break;
				case 'beef':
						$the_cateName='rundvlees';
						break;
				case 'chicken':
						$the_cateName='kippenvlees';
						break;
				case 'dessert':
						$the_cateName='dessert';
						break;
				case 'drink':
						$the_cateName='drink';
						break;
				case 'duck':
						$the_cateName='eendsvlees';
						break;
				case 'soup':
						$the_cateName='soep';
						break;
				case 'fish':
						$the_cateName='vis';
						break;
				case 'fried-noodles':
						$the_cateName='gebakken noedels';
						break;
				case 'noodles':
						$the_cateName='noedels';
						break;
				case 'package-menu':
						$the_cateName='menu';
						break;
				case 'pork':
						$the_cateName='varkensvlees';
						break;
				case 'squids':
						$the_cateName='inktvis';
						break;
				case 'vegetable':
						$the_cateName='groenten';
						break;
				default:
				    $the_cateName=str_replace("-"," ", $catename);
		}
	}else{
		$the_cateName=str_replace("-"," ", $catename);
	}
	return $the_cateName;
}






###########################
# register  form ##########
###########################

function wooc_extra_register_fields() {
?>
<p class="form-row form-row-first">
<label for="reg_billing_first_name"><?php _e( 'First name', 'woocommerce' ); ?><span class="required">*</span></label>
<input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
</p>


<p class="form-row form-row-last">
<label for="reg_billing_phone"><?php _e( 'Phone', 'woocommerce' ); ?></label>
<input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php esc_attr_e( $_POST['billing_phone'] ); ?>" />
</p>

<p class="form-row form-row-wide">
<label for="reg_billing_address_1"><?php _e( 'Address', 'woocommerce' ); ?><span class="required">*</span></label>
<input type="text" class="input-text" name="billing_address_1" id="reg_billing_address_1" value="<?php if ( ! empty( $_POST['billing_address_1'] ) ) esc_attr_e( $_POST['billing_address_1'] ); ?>" />
</p>

<p class="form-row form-row-first">
<label for="reg_billing_city"><?php _e( 'City', 'woocommerce' ); ?><span class="required">*</span></label>
<input type="text" class="input-text" name="billing_city" id="reg_billing_city" value="<?php if ( ! empty( $_POST['billing_city'] ) ) esc_attr_e( $_POST['billing_city'] ); ?>" />
</p>


<p class="form-row form-row-last">
<label for="reg_billing_address_1"><?php _e( 'Post code', 'woocommerce' ); ?><span class="required">*</span></label>
<input type="text" class="input-text" name="billing_postcode" id="reg_billing_postcode" value="<?php if ( ! empty( $_POST['billing_postcode'] ) ) esc_attr_e( $_POST['billing_postcode'] ); ?>" />
</p>
<div class="clear"></div>
<?php
}
add_action( 'woocommerce_register_form_start', 'wooc_extra_register_fields' );


/**

* register fields Validating.

*/

function wooc_validate_extra_register_fields( $username, $email, $validation_errors ) {
	if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {	
		$validation_errors->add( 'billing_first_name_error', __( '<strong>Error</strong>: First name is required!', 'woocommerce' ) );	
	}
	if ( isset( $_POST['billing_phone'] ) && empty( $_POST['billing_phone'] ) ) {	
		$validation_errors->add( 'billing_phone_error', __( '<strong>Error</strong>: Phone number is required!.', 'woocommerce' ) );	
	}
	if ( isset( $_POST['billing_address_1'] ) && empty( $_POST['billing_address_1'] ) ) {	
		$validation_errors->add( 'billing_address_1_error', __( '<strong>Error</strong>: Billing address is required!.', 'woocommerce' ) );	
	}
	if ( isset( $_POST['billing_city'] ) && empty( $_POST['billing_city'] ) ) {	
		$validation_errors->add( 'billing_city_error', __( '<strong>Error</strong>: City is required!.', 'woocommerce' ) );	
	}
	if ( isset( $_POST['billing_postcode'] ) && empty( $_POST['billing_postcode'] ) ) {	
		$validation_errors->add( 'billing_postcode_error', __( '<strong>Error</strong>: Post code is required!.', 'woocommerce' ) );	
	}
  return $validation_errors;
}
add_action( 'woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3 );


/**
* Below code save extra fields.
*/
function wooc_save_extra_register_fields( $customer_id ) {
	if ( isset( $_POST['billing_phone'] ) ) {
		// Phone input filed which is used in WooCommerce
		update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
	}
	if ( isset( $_POST['billing_first_name'] ) ) {
		//First name field which is by default
		update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
		// First name field which is used in WooCommerce
		update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
	}
	/* *********** */
	if ( isset( $_POST['billing_address_1'] ) ) {
		update_user_meta( $customer_id, 'billing_address_1', sanitize_text_field( $_POST['billing_address_1'] ) );
	}
	if ( isset( $_POST['billing_city'] ) ) {
		update_user_meta( $customer_id, 'billing_city', sanitize_text_field( $_POST['billing_city'] ) );
	}
	if ( isset( $_POST['billing_postcode'] ) ) {
		update_user_meta( $customer_id, 'billing_postcode', sanitize_text_field( $_POST['billing_postcode'] ) );
	}

}
add_action( 'woocommerce_created_customer', 'wooc_save_extra_register_fields' );


###########################
# END register  form ######
###########################




function dbConnect($usertype='write', $connectionType = 'pdo') {
  $host = 'localhost';
  $db = 'chinafoo_db';
  if ($usertype  == 'read') {
  $user = 'chinafoo_user';
  $pwd = 'Sihui8mp5e878';
  } elseif ($usertype == 'write') {
  $user = 'chinafoo_user';
  $pwd = 'Sihui8mp5e878';
  } else {
  exit('Unrecognized connection type');
  }
  if ($connectionType == 'mysqli') {
  return new mysqli($host, $user, $pwd, $db) or die ('Cannot open database');
  } else {
    try {
      return new PDO("mysql:host=$host;dbname=$db", $user, $pwd);
    } catch (PDOException $e) {
      echo 'Cannot connect to database';
      exit;
    }
  }
}