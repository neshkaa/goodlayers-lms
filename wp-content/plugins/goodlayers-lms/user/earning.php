<h3 class="gdlr-lms-admin-head with-sub" ><?php esc_html_e('Earning', 'gdlr-lms'); ?></h3>
<h4 class="gdlr-lms-admin-sub-head" ><?php 
	global $gdlr_lms_option, $current_user;

	$commission_table = get_option('gdlr-lms-commission', array());
	esc_html_e('Your commission rate is', 'gdlr-lms'); 
	if(empty($commission_table[$current_user->ID])){
		$commission_rate = empty($gdlr_lms_option['default-instructor-commission'])? 100: $gdlr_lms_option['default-instructor-commission'];
	}else{
		$commission_rate = $commission_table[$current_user->ID];
	}
	echo ' ' . $commission_rate . '%';
?></h4>
<?php
	$start_date = empty($_GET['start-date'])? current_time('Y-m-01'): $_GET['start-date']; 
	$end_date = empty($_GET['end-date'])? current_time('Y-m-t'): $_GET['end-date']; 
?>
<form class="gdlr-lms-date-filter-form" method="GET" action="">
	<span class="gdlr-lms-head"><?php esc_html_e('Filter :', 'gdlr-lms'); ?></span>
	<input type="text" name="start-date" class="gdlr-lms-date-picker" placeholder="<?php esc_html_e('Start Date', 'gdlr-lms'); ?>" value="<?php echo esc_attr($start_date); ?>" />
	<i class="fa fa-calendar icon-calendar"></i>
	<i class="fa fa-long-arrow-right icon-long-arrow-right"></i>
	<input type="text" name="end-date" class="gdlr-lms-date-picker" placeholder="<?php esc_html_e('End Date', 'gdlr-lms'); ?>" value="<?php echo esc_attr($end_date); ?>" />
	<i class="fa fa-calendar icon-calendar"></i>
	<input type="hidden" name="type" value="earning" />
	<input type="submit" value="<?php esc_html_e('Filter!', 'gdlr-lms'); ?>" />
</form>

<table class="gdlr-lms-table">
<tr>
	<th><?php esc_html_e('Course', 'gdlr-lms'); ?></th>
	<th><?php esc_html_e('Revenue', 'gdlr-lms'); ?></th>
	<th><?php esc_html_e('My Earning', 'gdlr-lms'); ?></th>
</tr>
<?php 
	global $wpdb;
	
	$temp_sql  = "SELECT course_id, SUM(price) AS revenue FROM " . $wpdb->prefix . "gdlrpayment ";
	$temp_sql .= $wpdb->prepare("WHERE price != 0 AND payment_status = 'paid' AND author_id = %d ", $current_user->ID);
	$temp_sql .= $wpdb->prepare("AND payment_date >= cast(%s as DATETIME) ", $start_date);
	$temp_sql .= $wpdb->prepare("AND payment_date <= cast(%s as DATETIME) ", $end_date);
	$temp_sql .= "GROUP BY course_id";	
	
	$sum_price = 0;
	$results = $wpdb->get_results($temp_sql);
	foreach($results as $result){
		$sum_price += floatval($result->revenue);
		
		echo '<tr>';
		echo '<td>' . get_the_title($result->course_id) . '</td>';
		
		echo '<td>' . gdlr_lms_money_format(number_format_i18n($result->revenue, 2)) . '</td>';
		echo '<td>' . gdlr_lms_money_format(number_format_i18n(floatval($result->revenue) * floatval($commission_rate) / 100, 2)) . '</td>';
		echo '</tr>';
	}
	
	echo '<tr class="with-top-divider">';
	echo '<td>' . __('Total', 'gdlr-lms') . '</td>';
	echo '<td>' . gdlr_lms_money_format(number_format_i18n($sum_price, 2)) . '</td>';
	echo '<td>' . gdlr_lms_money_format(number_format_i18n($sum_price * floatval($commission_rate) / 100, 2)) . '</td>';
	echo '</tr>';
?>
</table>