<?php
	function piotnetforms_template_form_booking($settings, $element_id, $post_id, $date = '') {
?>
<?php if (!empty($settings['piotnetforms_booking_field_label_show'])) : ?>
<label class="piotnetforms-field-label"><?php echo $settings['piotnetforms_booking_field_label']; ?></label>
<?php endif; ?>
<form class="piotnetforms-booking__inner">
<div data-piotnetforms-required></div>
<?php 
	foreach ( $settings['piotnetforms_booking'] as $key => $item ) :
		$item['piotnetforms_booking_id'] =  $settings['piotnetforms_booking_id'];
		$item['piotnetforms_booking_date'] = $date;
		$item['piotnetforms_booking_element_id'] = $element_id;
		$item['piotnetforms_booking_post_id'] = $post_id;

		if (empty($settings['piotnetforms_booking_field_allow_multiple']) && !empty($settings['piotnetforms_booking_slot_quantity_field'])) {
			$item['piotnetforms_booking_slot_quantity_field'] =  $settings['piotnetforms_booking_slot_quantity_field'];
		}

		if ($settings['piotnetforms_booking_date_type'] == 'specify_date') {
			$date = date( "Y-m-d", strtotime( $settings['piotnetforms_booking_date'] ) );
			$item['piotnetforms_booking_date'] = $date;
		} else {
			$item['piotnetforms_booking_date_field'] = $settings['piotnetforms_booking_date_field'];
			
			if (empty($date)) {
				$date = date( "Y-m-d", strtotime("now") );
			} else {
				$date = date( "Y-m-d", strtotime($date) );
			}
		}
		$slot_availble = 0;
		$slot = $item['piotnetforms_booking_slot'];
		$slot_query = new WP_Query(array(  
			'posts_per_page' => -1 , 
			'post_type' => 'piotnetforms-book',
			'meta_query' => array(                  
		       'relation' => 'AND',                 
			        array(
			            'key' => 'piotnetforms_booking_id',                
			            'value' => $item['piotnetforms_booking_id'],                  
			            'type' => 'CHAR',
			            'compare' => '=',
			        ),
			        array(
			            'key' => 'piotnetforms_booking_slot_id',                  
			            'value' => $item['piotnetforms_booking_slot_id'],                  
			            'type' => 'CHAR',
			            'compare' => '=',                  
			        ),
			        array(
			            'key' => 'piotnetforms_booking_date',                  
			            'value' => $date,                  
			            'type' => 'CHAR',
			            'compare' => '=',                
			        ),
			        array(
			            'key' => 'payment_status',                  
			            'value' => 'succeeded',                  
			            'type' => 'CHAR',                  
			            'compare' => '=',                
			        ),
			),	
		));

		$num = 0;

		if ($slot_query->have_posts()) {
			while($slot_query->have_posts()) {
				$slot_query->the_post();
				$num += intval( get_post_meta(get_the_ID(), 'piotnetforms_booking_quantity', true) );
			}
		}

		wp_reset_postdata();

		$slot_availble = $slot - $num;
?>	
	<div class="piotnetforms-booking__item<?php if(empty($slot_availble)) { echo ' piotnetforms-booking__item--disabled'; } ?>">
		<div class="piotnetforms-booking__item-inner">
			<input type="checkbox"<?php if(empty($slot_availble)) { echo ' disabled'; } ?> value="<?php echo $item['piotnetforms_booking_title']; ?>" data-value="<?php echo $item['piotnetforms_booking_title']; ?>" id="form-field-<?php echo $item['piotnetforms_booking_id']; ?>-<?php echo $key; ?>" name="form_fields[<?php echo $item['piotnetforms_booking_id']; ?>][]" data-piotnetforms-default-value="<?php echo $item['piotnetforms_booking_title']; ?>" data-piotnetforms-booking-price="<?php echo $item['piotnetforms_booking_price']; ?>" data-piotnetforms-id="<?php echo $settings['piotnetforms_booking_form_id']; ?>" data-piotnetforms-booking-item data-piotnetforms-booking-item-options='<?php echo json_encode( $item, JSON_UNESCAPED_UNICODE ); ?>'<?php if(empty($settings['piotnetforms_booking_field_allow_multiple'])) { echo ' data-piotnetforms-booking-item-radio'; } ?> data-piotnetforms-booking-availble="<?php echo $slot_availble; ?>">
			<?php if (!empty($item['piotnetforms_booking_title'])) : ?>
				<div class="piotnetforms-booking__title"><?php echo $item['piotnetforms_booking_title']; ?></div>
			<?php endif; ?>
			<?php if (!empty($settings['piotnetforms_booking_field_slot_show'])) : ?>
				<div class="piotnetforms-booking__slot" data-piotnetforms-booking-slot>
					<?php if (!empty($slot_availble) || empty($settings['piotnetforms_booking_sold_out_text'])) : ?>
						<span class="piotnetforms-booking__slot-before">
							<?php if(!empty($settings['piotnetforms_booking_before_number_of_slot'])) {
							         echo $settings['piotnetforms_booking_before_number_of_slot'];
							      } 
							?>
						</span>
						<span class="piotnetforms-booking__slot-number"><?php echo $slot_availble; ?></span>
						<span class="piotnetforms-booking__slot-after">
							<?php if(!empty($settings['piotnetforms_booking_after_number_of_slot'])) {
							         echo $settings['piotnetforms_booking_after_number_of_slot'];
							      } 
							?>			
						</span>
					<?php else : ?>
						<span class="piotnetforms-booking__slot-sold-out"><?php echo $settings['piotnetforms_booking_sold_out_text']; ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if (!empty($settings['piotnetforms_booking_field_price_show']) && !empty($item['piotnetforms_booking_price_text'])) : ?>
				<div class="piotnetforms-booking__price">
					<?php echo $item['piotnetforms_booking_price_text']; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
<?php endforeach; ?>	
</form>

<?php
	}
?>