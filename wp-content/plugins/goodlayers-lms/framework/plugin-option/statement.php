<?php
	/*	
	*	Goodlayers Statement File
	*/
	
	function gdlr_lms_statement_option(){
	$year = empty($_GET['year'])? current_time('Y'): $_GET['year']; 
	$start_date = empty($_GET['start-date'])? current_time('Y-m-01'): $_GET['start-date']; 
	$end_date = empty($_GET['end-date'])? current_time('Y-m-t'): $_GET['end-date']; 
	$_GET['type'] = empty($_GET['type'])? 'course': $_GET['type'];
?>
<div class="wrap">
<h2><?php esc_html_e('Statement', 'gdlr-lms'); ?></h2>

<!-- changing section -->
<div class="gdlr-lms-statement-button-wrapper">
<a class="gdlr-lms-statement-button" href="<?php echo esc_url(add_query_arg(array('type'=>'overall', 'page'=>'lms-statement'))); ?>">Overall</a>
<a class="gdlr-lms-statement-button" href="<?php echo esc_url(add_query_arg(array('type'=>'course', 'page'=>'lms-statement'))); ?>">Course</a>
<a class="gdlr-lms-statement-button" href="<?php echo esc_url(add_query_arg(array('type'=>'instructor', 'page'=>'lms-statement'))); ?>">Instructor</a>
</div>

<!-- form filter -->
<?php if($_GET['type'] == 'overall'){ ?>
	<form class="gdlr-lms-statement-form" method="GET" action="">
		<span class="gdlr-lms-head"><?php esc_html_e('Select Year :', 'gdlr-lms'); ?></span>
		<div class="gdlr-combobox-wrapper">
			<select name="year" >
				<option value="2014" <?php echo ($year=='2014')? 'selected': ''; ?> >2014</option>
				<option value="2015" <?php echo ($year=='2015')? 'selected': ''; ?> >2015</option>
				<option value="2016" <?php echo ($year=='2016')? 'selected': ''; ?> >2016</option>
				<option value="2017" <?php echo ($year=='2017')? 'selected': ''; ?> >2017</option>
			</select>
			<input type="hidden" name="page" value="lms-statement" />
		</div>
		<div class="clear"></div>
	</form>
<?php }else{ ?>
	<form class="gdlr-lms-statement-form" method="GET" action="">
		<span class="gdlr-lms-head"><?php esc_html_e('Filter :', 'gdlr-lms'); ?></span>
		<input type="text" name="start-date" class="gdlr-lms-date-picker" placeholder="<?php esc_html_e('Start Date', 'gdlr-lms'); ?>" value="<?php echo esc_attr($start_date); ?>" />
		<i class="fa fa-calendar icon-calendar"></i>
		<i class="fa fa-long-arrow-right icon-long-arrow-right"></i>
		<input type="text" name="end-date" class="gdlr-lms-date-picker" placeholder="<?php esc_html_e('End Date', 'gdlr-lms'); ?>" value="<?php echo esc_attr($end_date); ?>" />
		<i class="fa fa-calendar icon-calendar"></i>
		<input type="hidden" name="page" value="lms-statement" />
		<input type="submit" value="<?php esc_html_e('Filter!', 'gdlr-lms'); ?>" />
	</form>
<?php } ?>

<!-- query form -->
<table class="gdlr-lms-table">
<tr>	
	<?php if($_GET['type'] == 'overall'){ ?>
		<th class="gdlr-left-aligned"><?php esc_html_e('Instructor', 'gdlr-lms'); ?></th>
		<th><?php esc_html_e('Revenue', 'gdlr-lms'); ?></th>
	<?php }else if($_GET['type'] == 'course'){ ?>
		<th class="gdlr-left-aligned"><?php esc_html_e('Course', 'gdlr-lms'); ?></th>
		<th><?php esc_html_e('Revenue', 'gdlr-lms'); ?></th>
	<?php }else{ ?>
		<th class="gdlr-left-aligned"><?php esc_html_e('Instructor', 'gdlr-lms'); ?></th>
		<th><?php esc_html_e('Course', 'gdlr-lms'); ?></th>
		<th><?php esc_html_e('Revenue', 'gdlr-lms'); ?></th>
		<th><?php esc_html_e('Commission (%)', 'gdlr-lms'); ?></th>
		<th><?php esc_html_e('Earning', 'gdlr-lms'); ?></th>
	<?php } ?>
	
</tr>
<?php 
	global $wpdb, $gdlr_lms_option;
	$gdlr_lms_commission = get_option('gdlr-lms-commission', array()); 
	$gdlr_lms_option['default-instructor-commission'] = empty($gdlr_lms_option['default-instructor-commission'])? 100: $gdlr_lms_option['default-instructor-commission'];
	
	if($_GET['type'] == 'overall'){
		$temp_sql  = "SELECT MONTH(payment_date) as month, SUM(price) as revenue FROM " . $wpdb->prefix . "gdlrpayment ";
		$temp_sql .= "WHERE price != 0 AND payment_status = 'paid' ";
		$temp_sql .= $wpdb->prepare("AND payment_date >= cast(%s as DATETIME) ", $start_date . '-01-01');
		$temp_sql .= $wpdb->prepare("AND payment_date <= cast(%s as DATETIME) ", $year . '-12-31');
		$temp_sql .= "GROUP BY month";			
	}else if($_GET['type'] == 'course'){
		$temp_sql  = "SELECT course_id, SUM(price) AS revenue FROM " . $wpdb->prefix . "gdlrpayment ";
		$temp_sql .= "WHERE price != 0 AND payment_status = 'paid' ";
		$temp_sql .= $wpdb->prepare("AND payment_date >= cast(%s as DATETIME) ", $start_date);
		$temp_sql .= $wpdb->prepare("AND payment_date <= cast(%s as DATETIME) ", $end_date);
		$temp_sql .= "GROUP BY course_id";	
	}else{
		$temp_sql  = "SELECT author_id, COUNT(DISTINCT course_id) AS course_num, SUM(price) AS revenue FROM " . $wpdb->prefix . "gdlrpayment ";
		$temp_sql .= "WHERE price != 0 AND payment_status = 'paid' ";
		$temp_sql .= $wpdb->prepare("AND payment_date >= cast(%s as DATETIME) ", $start_date);
		$temp_sql .= $wpdb->prepare("AND payment_date <= cast(%s as DATETIME) ", $end_date);
		$temp_sql .= "GROUP BY author_id";		
	}
	
	$sum_revenue = 0;
	$results = $wpdb->get_results($temp_sql);
	foreach($results as $result){
		echo '<tr>';
		if($_GET['type'] == 'overall'){
			echo '<td class="gdlr-left-aligned">' . date_i18n("F", mktime(0, 0, 0, $result->month, 10)) . '</td>';
		}else if($_GET['type'] == 'course'){
			echo '<td class="gdlr-left-aligned">' . get_the_title($result->course_id) . '</td>';
		}else{
			echo '<td class="gdlr-left-aligned">' . get_user_meta($result->author_id, 'first_name', true) . ' ' . get_user_meta($result->author_id, 'last_name', true) . '</td>';
			echo '<td>' . $result->course_num . '</td>';
		}
		echo '<td>' . gdlr_lms_money_format(number_format_i18n($result->revenue, 2)) . '</td>';
		
		if($_GET['type'] == 'instructor'){
			$commission_rate = empty($gdlr_lms_commission[$result->author_id])? $gdlr_lms_option['default-instructor-commission']: $gdlr_lms_commission[$result->author_id];
			echo '<td>' . $commission_rate . '%</td>';
			echo '<td>' . gdlr_lms_money_format(number_format_i18n(floatval($result->revenue) * floatval($commission_rate) / 100, 2)) . '</td>';
		}
		echo '</tr>';
		
		$sum_revenue += floatval($result->revenue);
	}
	echo '<tr>';
	echo '<td class="gdlr-left-aligned gdlr-top-border">' . esc_html__('Total', 'gdlr-lms') . '</td>';
	echo ($_GET['type'] == 'instructor')? '<td class="gdlr-top-border"></td>': '';
	echo '<td class="gdlr-top-border">' . gdlr_lms_money_format(number_format_i18n($sum_revenue, 2)) . '</td>';
	echo '</tr>';
?>
</table>
</div>
<?php
	}
?>