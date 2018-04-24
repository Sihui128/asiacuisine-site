<?php 
session_start();
$_SESSION['crrentpage']='';

############################################################
##                                                        ##
## we first have to figure out the user's address somehow ##
## if there is one, store it in  $_SESSION['useraddress'] ##
##                                                        ##
############################################################
$crruseraddress=NULL;
$cuid=get_current_user_id();

if($cuid){
  $crruseraddress=get_user_meta($cuid, '_user_address', 'true');
}

if(isset($_GET['addr']) && !empty($_GET['addr'])){	//如果用户输入了新地址

  if((isset($_SESSION['useraddress']) && $_SESSION['useraddress']!=$_GET['addr']) || (isset($crruseraddress) && $crruseraddress!=$_GET['addr']) || (isset($crruseraddress) && isset($_SESSION['useraddress']) && $crruseraddress!=$_SESSION['useraddress'])){		
	  // 如果用户输入的地址和之前的存储不一致，把之前的存储全部清除，重新再加上新值
		if(isset($_SESSION['userlocationzip'])) unset($_SESSION['userlocationzip']);
		if(isset($_SESSION['userlocationcoordinate'])) unset($_SESSION['userlocationcoordinate']);			
		if(isset($_SESSION['available_restaurants'])) unset($_SESSION['available_restaurants']);
		if(isset($_SESSION['on_map_restaurants'])) unset($_SESSION['on_map_restaurants']);
	}
	
  $_SESSION['useraddress']=$_GET['addr'];
	
	if($cuid){ //如果是注册的用户
	  		
	  $addresstosaveforuser=$_SESSION['useraddress'];
		
		if(empty($crruseraddress)){ //如果数据库中 ！没有 ！ 存储用户的地址			
			if(!empty($addresstosaveforuser)){
				add_user_meta( $cuid, '_user_address', $addresstosaveforuser);
				$crruseraddress=$addresstosaveforuser;
			}				
		}else{ //如果数据库中 ！已经有 ！存储用户的地址
			if($crruseraddress!=$_SESSION['useraddress']){ //如果用户新输入的地址和数据库中存储的地址不一样，执行更新操作
				if(!empty($addresstosaveforuser)){
			     update_user_meta( $cuid, '_user_address', $addresstosaveforuser);
				}
			}
		}
		
	} //END if($cuid){ } 结束是否注册用户的条件
} //END if(isset($_GET['addr'])){	


if(empty($_SESSION['useraddress'])){ //如果session中没有用户的地址存储，把数据库里调出来的地址存上
	if(!empty($crruseraddress)){
		$_SESSION['useraddress']=$crruseraddress;
	}
}


#########################################################################
##                                                                     ##
##    now $_SESSION['useraddress'] should have a value if it exists    ##
##                                                                     ##
#########################################################################


$page_id_of_crr_page = get_queried_object_id();
//273 is home page
if(1013==$page_id_of_crr_page){
    if(!is_user_logged_in()){
        header('Location: https://asiacuisine.be/my-account/');
		exit();
    }
}
if(273!=$page_id_of_crr_page && 367!=$page_id_of_crr_page && 87!=$page_id_of_crr_page && 625!=$page_id_of_crr_page && 976!=$page_id_of_crr_page && 1013!=$page_id_of_crr_page && 60!=$page_id_of_crr_page){
	//没有 客人的地址的session， 只能回首页，重新填写地址
	if(!isset($_SESSION['useraddress'])){
		header('Location: https://asiacuisine.be');
		exit();
	}
	
	
	//先用简单方式把数据库连上,以后只需直接调用 $conn
	$conn=dbConnect('write','pdo');
	$conn->query("SET NAMES 'utf8'");
	
	$perlink_full=get_page_link($page_id_of_crr_page);
	$perlink_no_base=str_replace('https://asiacuisine.be/', '', $perlink_full);
	$language_bases=array('en','nl','fr');
	$perlink_no_languagebase=str_replace($language_bases, '', $perlink_no_base);
	
	###########################
	#       $perlink_URI      #
	###########################
	$perlink_URI=str_replace('/', '', $perlink_no_languagebase);
  
	if('checkout'==$perlink_URI){
		$_SESSION['crrentpage']='checkout';
	}else if('restaurants-near-me'==$perlink_URI){
		$_SESSION['crrentpage']='restaurants-near-me';
	}
	
	############################
	#                          #
	#                          #
	#                          #
	############################
	//如果没有客人地址zip 的 session，要根据客人输入的地址产生该 session
	if(empty($_SESSION['userlocationzip']) || empty($_SESSION['userlocationcoordinate'])){
		// 000 address to coordinates
		##############################
		##############################
		##############################
		$useraddressinurl=urlencode($_SESSION['useraddress']);
		$req_url='https://maps.googleapis.com/maps/api/geocode/json?address='.$useraddressinurl.'&key=AIzaSyDlmvX9SxsiFvT9dIz_HanyllRXMZRGEDg';
		$curl = curl_init();
		curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $req_url,
				CURLOPT_USERAGENT => 'Codular Sample cURL Request'
		));
		$resp = curl_exec($curl);
		curl_close($curl);
		
		$obj = json_decode($resp);
		$lat=$obj->{'results'}[0]->{'geometry'}->{'location'}->{'lat'};//这个变量下面需要用
		$lng=$obj->{'results'}[0]->{'geometry'}->{'location'}->{'lng'};//这个变量下面需要用
		$addr_components=$obj->{'results'}[0]->{'address_components'};
		
		if(empty($addr_components)){
		    header('Location: https://chinafood.be?placefound=err');
			exit();
		}
		
		foreach($addr_components as $addr_ele){
			$crr_ele_title=$addr_ele->{'types'}[0];
			if('postal_code'==$crr_ele_title){
				$zip_code=$addr_ele->{'long_name'};
			}
		}
	
		
		if(empty($zip_code) || empty($lat) || empty($lng)){
			header('Location: https://chinafood.be?placefound=err');
			exit();
		}
		
		unset($curl);
		unset($resp);
		unset($obj);
				
		$_SESSION['userlocationcoordinate']=$lat.'|'.$lng;
		$_SESSION['userlocationzip']=$zip_code;
	
	}
	
	
	############################
	#                          #
	#                          #
	#                          #
	############################
	//如果没有下列几个session，要重新program生成下列session
	if(empty($_SESSION['available_restaurants']) || empty($_SESSION['on_map_restaurants'])){
		
		$userlocationzip=$_SESSION['userlocationzip'];
		
		$_SESSION['available_restaurants']='';		
		$_SESSION['on_map_restaurants']='';
		
		
		
		
		###############################################
		
		# use an array to save category names         #
		
		###############################################
		
		
		$_pf = new WC_Product_Factory();
					
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => 880
			);
		$loop = new WP_Query( $args );
		if ( $loop->have_posts() ) {
			while ( $loop->have_posts() ) : $loop->the_post();
			
				$crr_res_loc_lat_array=NULL;
				$crr_res_loc_lat=NULL;
				$crr_res_loc_lng_array=NULL;
				$crr_res_loc_lng=NULL;
				
				$crr_postID=get_the_ID();
				
				$crr_res_loc_lat_array=get_the_terms($crr_postID, 'pa_location-lat');				
				if(!empty($crr_res_loc_lat_array)){
					$crr_res_loc_lat=$crr_res_loc_lat_array[0]->{'name'};
				}
				
				$crr_res_loc_lng_array=get_the_terms($crr_postID, 'pa_location-lng');				
				if(!empty($crr_res_loc_lng_array)){
					$crr_res_loc_lng=$crr_res_loc_lng_array[0]->{'name'};
				}
				
				
				
				
				
				if(!empty($crr_res_loc_lat) && !empty($crr_res_loc_lng) ){
					
					$_product = $_pf->get_product($crr_postID);
					
					$averagerating = $_product->get_average_rating();
					
					$crr_res_deliver_zone_str=$_product->get_attribute( 'deliver-range-zip' );
					$crr_res_deliver_zone_array=explode(',',$crr_res_deliver_zone_str);
					
		
					
					$featured_image_array = wp_get_attachment_image_src( get_post_thumbnail_id( $crr_postID ), 'single-post-thumbnail' );
					$featured_image=$featured_image_array[0];
					
					$restaurantname=get_the_title( $crr_postID );
					
					$restaurantaddress= $_product->get_attribute( 'address' );
					$restaurantURLfix= $_product->get_attribute( 'url-slag' );   //pa_url-slag');			
					$restaurant_closeday= $_product->get_attribute( 'closing-date' ); //closing-date');		
					$restaurant_closehour= $_product->get_attribute( 'close-hour' ); ///close-hour');	
					$restaurant_deliverrange= $_product->get_attribute( 'delivery-range' );  //delivery-range');	
					$restaurant_minspend= $_product->get_attribute( 'min-spend-for-delivery' );  //min-spend-for-delivery');
					
					$openinghour=$_product->get_attribute('opening-hour');
					$openinghour_saturday=$_product->get_attribute('open-hour-saturday');
					$openinghour_sunday=$_product->get_attribute('open-hour-sunday');
					$deliver_service=$_product->get_attribute('deliver-service');
					
					$website=$_product->get_attribute('website');
					
					$sql='SELECT * FROM restaurant_status WHERE restaurant_uri = "'.$restaurantURLfix.'" ORDER BY updated_at DESC LIMIT 1';
					$stmt=$conn->query($sql);
					$found=$stmt->rowCount();
					if(!$found){
						$restaurant_open_status='normal';
					}else{
						foreach($stmt as $row){
							$restaurant_open_status=$row['crr_status'];
						}
					}
					
					
					
							
					##########################################
					#              给地图用的数据              #
					##########################################
					$todaydate=date("l");
					if($todaydate=='Saturday'){
						$openinghour=$openinghour_saturday;
					}else if($todaydate=='Sunday'){
						$openinghour=$openinghour_sunday;
					}
					
					if(!empty($_SESSION['on_map_restaurants'])){
						$_SESSION['on_map_restaurants'] .= '<*|*>';
					}
					if(in_array($userlocationzip, $crr_res_deliver_zone_array) && $deliver_service=='yes'){
						$deliveryavailability='deliver to '.$userlocationzip;
					}else{
						$deliveryavailability='out of delivery zone';
					}
					$_SESSION['on_map_restaurants'] .= $crr_res_loc_lat.'&=&'.$crr_res_loc_lng.'&=&'.$featured_image.'&=&'.$restaurantname.'&=&'.$restaurantaddress.'&=&'.$restaurantURLfix.'&=&'.$restaurant_closeday.'&=&'.$openinghour.'&=&'.$deliver_service.'&=&'.$restaurant_minspend.'&=&'.$restaurant_open_status.'&=&'.$website.'&=&'.$deliveryavailability;
					
				
				 //在送餐范围之内的餐馆都显示出来
				 if(in_array($userlocationzip, $crr_res_deliver_zone_array)){
					if(!empty($_SESSION['available_restaurants'])){
						$_SESSION['available_restaurants'].='|';
					}
					 $_SESSION['available_restaurants'].=$crr_postID;
					 
					 
				 }
					
				}
				
			endwhile;
		}
		
		
		
		wp_reset_postdata();
	}
	
}////==== end if home page condition. not execute this code to make home page load faster

?>

<!DOCTYPE html>
<!--[if IE 9 ]> <html <?php language_attributes(); ?> class="ie9 <?php flatsome_html_classes(); ?>"> <![endif]-->
<!--[if IE 8 ]> <html <?php language_attributes(); ?> class="ie8 <?php flatsome_html_classes(); ?>"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html <?php language_attributes(); ?> class="<?php flatsome_html_classes(); ?>"> <!--<![endif]-->
<!-- Header by Studio FIDES <?php echo $_SESSION['available_restaurants']; ?> -->
<head>
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-101951881-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-101951881-1');
</script>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

<?php wp_head(); ?>
</head>

<body <?php body_class(); // Body classes is added from inc/helpers-frontend.php ?>>

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'flatsome' ); ?></a>

<div id="wrapper">

<?php do_action('flatsome_before_header'); ?>

<header id="header" class="header <?php flatsome_header_classes();  ?>">
   <div class="header-wrapper">
	<?php
		get_template_part('template-parts/header/header', 'wrapper');
	?>
   </div><!-- header-wrapper-->
</header>

<?php do_action('flatsome_after_header'); ?>

<main id="main" class="<?php flatsome_main_classes();  ?>">
