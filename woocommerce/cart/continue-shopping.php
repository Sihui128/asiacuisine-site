<?php
$gobackURI='/restaurants-near-me/';
if(isset($_SESSION['crrrestaurantdetail'])){
	$therestaurdetailarray=explode('|',$_SESSION['crrrestaurantdetail']);
	$gobackURI='/'.$therestaurdetailarray[5].'/';
}
$crr_user_language=qtranxf_getLanguage();
$ordermoretxt='Order more';
if($crr_user_language=='nl'){
	$ordermoretxt='Verder bestellen';
}
?>
<div class="continue-shopping pull-left text-left">
    <a class="button-continue-shopping button primary is-outline"  href="<?php echo $gobackURI; ?>">
        &#8592; <?php echo $ordermoretxt;  ?>
    </a>
</div>
