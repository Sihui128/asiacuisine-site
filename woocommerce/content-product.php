<?php
/**
 * The template for displaying product content within loops.
 *
 * Override this template by copying it to yourtheme/woocommerce/content-product.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product, $lat, $lng;

// Ensure visibility
if ( empty( $product ) || ! $product->is_visible() ) {
  return;
}

// Check stock status
$out_of_stock = get_post_meta($post->ID, '_stock_status',true) == 'outofstock';

// Extra post classes
$classes = array();
$classes[] = 'product-small';
$classes[] = 'col';
$classes[] = 'has-hover';

if($out_of_stock) $classes[] = 'out-of-stock';



$address=$product->get_attribute( 'address' );
$closing_date=$product->get_attribute('closing date');
$location_lat=$product->get_attribute('location lat');
$location_lng=$product->get_attribute('location lng');
$url_slag=$product->get_attribute('url slag');
$website=$product->get_attribute('website');
$close_hour=$product->get_attribute('close hour');
$min_spend_for_delivery=$product->get_attribute('min spend for delivery');

$openinghour=$product->get_attribute('opening-hour');
$openinghour_saturday=$product->get_attribute('open-hour-saturday');
$openinghour_sunday=$product->get_attribute('open-hour-sunday');
$deliver_service=$product->get_attribute('deliver-service');


$foodtype='';


$terms = get_the_terms ( $post->ID, 'product_cat' );
foreach ( $terms as $term ) {
	if(!empty($foodtype)){
		$foodtype.=', ';
	}
	$foodtype.=$term->name;
}



if(!empty($website)){
	$webpagelink=$website;
}else{
	$webpagelink='/'.$url_slag;
}

if(!isset($conn)){
	$conn=dbConnect('write','pdo');
	$conn->query("SET NAMES 'utf8'");
}

$sql='SELECT * FROM restaurant_status WHERE restaurant_uri = "'.$url_slag.'" ORDER BY updated_at DESC LIMIT 1';
$stmt=$conn->query($sql);
$found=$stmt->rowCount();
if(!$found){
	$restaurant_open_status='normal';
}else{
	foreach($stmt as $row){
		$restaurant_open_status=$row['crr_status'];
	}
}


switch ($close_hour) {
		case "22:00":
		$close_hour_number=22;
		break;
		case "21:30":
		$close_hour_number=21.5;
		break;
		case "22:30":
		$close_hour_number=22.5;
		break;
		case "23:00":
		$close_hour_number=23;
		break;
		case "23:30":
		$close_hour_number=23.5;
		break;
		case "00:00":
		$close_hour_number=24;
		break;
		case "00:30":
		$close_hour_number=24.5;
		break;
		case "01:00":
		$close_hour_number=25;
		break;
		case "01:30":
		$close_hour_number=25.5;
		break;
		case "02:00":
		$close_hour_number=26;
		break;
		case "21:00":
		$close_hour_number=21;
		break;
		case "20:30":
		$close_hour_number=20.5;
		break;
		default:
		$close_hour_number=21.5;
}


$todaydate=date("l");
$crrhour=intval(current_time( 'H' ))+(intval(current_time( 'i' ))/60);
if($crrhour<=3){
	$crrhour+=24;
}

//echo $todaydate;

if($todaydate=='Saturday'){
	$openinghour=$openinghour_saturday;
}else if($todaydate=='Sunday'){
	$openinghour=$openinghour_sunday;
}

if(!empty($address)){
	$classes[] = 'restaurantcontainer';
	
	$classes[]='deliver_'.$deliver_service;
	
	
	
######################################################
######################################################
######### 显示餐馆列表                     #############
######################################################
######################################################

  if($_SESSION['crrentpage']=='Restaurant'){
	  #############################################
		#
		#            餐馆菜单页面, 显示餐馆详情 
		#
		#############################################	
			// 没有餐馆详情	
?>
<p>this page can not be found.</p>
<?php
  }else{		
		#############################################
		#
		#            餐馆列表，显示所有餐馆
		#
		#############################################			
?>

  <div <?php post_class( $classes ); ?>>
    <!-- <a class="col-inner" target="_blank" href="<?php echo $webpagelink; ?>" title="<?php  echo $product->get_title(); ?>"> -->
    <div  class="col-inner" title="<?php  echo $product->get_title(); ?>">
    <?php do_action( 'woocommerce_before_shop_loop_item' ); ?>
    <div class="product-small box <?php echo flatsome_product_box_class(); ?>">
      <div class="box-image">
        <div class="<?php echo flatsome_product_box_image_class(); ?>">
        <?php			
        /**
         *
         * @hooked woocommerce_get_alt_product_thumbnail - 11
         * @hooked woocommerce_template_loop_product_thumbnail - 10
         */
        do_action( 'flatsome_woocommerce_shop_loop_images' );
        ?>
          
        </div>
        
      
        <?php if($out_of_stock) { ?><div class="out-of-stock-label"><?php _e( 'Out of stock', 'woocommerce' ); ?></div><?php }?>
      </div><!-- box-image -->
  
      <div class="box-text <?php echo flatsome_product_box_text_class(); ?> restaurant-info-box">
        <?php
          do_action( 'woocommerce_before_shop_loop_item_title' );
          echo '<p class="foodtype">'.$foodtype.'</p>';
          echo '<div class="title-wrapper">';
          //do_action( 'woocommerce_shop_loop_item_title' );
          echo $product->get_title();
          echo '</div>';
  
	
	         ?>
            <p class="restaurant-info">
            
            <?php
            echo '<!-- now time: '.$crrhour.' closing time: '.$close_hour_number.' -->';
            //if($todaydate==$closing_date || $crrhour>$close_hour_number || $restaurant_open_status=='holidays' || $restaurant_open_status=='out-of-service'){
            if($todaydate==$closing_date){  
              ?>
              <span class="openstatus closed">Closed today</span>
              <?php
            }else{
              ?>
              <span class="openstatus open">Open today</span> 
							<span class="openhour"><i class="fa fa-clock-o" aria-hidden="true"></i><?php echo $openinghour; ?></span>
              <?php
            }
						if($deliver_service=='yes'){
            ?>            
            <span class="restaurant-info min-amount"><img width="16" height="16" class="delivery-icon" src="/wp-content/uploads/2017/12/delivery-chinafoodicon-black.png" /> Min. € <?php echo $min_spend_for_delivery; ?>,00</span>
            </p>
            <?php
						}else if($deliver_service=='no'){
							$pickup_word='Pick-Up Only';
						?>            
            <span class="restaurant-info min-amount">
            <?php echo $pickup_word; ?>
            </span>
            </p>
            <?php
						}
  
          echo '<div class="price-wrapper">';
          do_action( 'woocommerce_after_shop_loop_item_title' );
          echo '</div>';
          
          
            echo '<div class="title-wrapper">';
						?>
            <button class="viewmenubtn" onclick="viewMenu('<?php echo $webpagelink; ?>')">view menu &raquo;</button>
            <?php
						echo '</div>';
            ?>
            
      </div><!-- box-text -->
    </div><!-- box -->
    <?php do_action( 'woocommerce_after_shop_loop_item' ); ?>
    <!--</a> .col-inner --></div>
  </div><!-- col -->
<?php
	
	}








}else{
######################################################
######################################################
######### 显示菜列表                     #############
######################################################
######################################################
// empty here
?>
<p>this page can not be found.</p>
<?php
}
?>
