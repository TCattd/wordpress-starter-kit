<?php 
	function form_database_meta_box_piotnetforms() {
		add_meta_box( 'form-database-meta-box-piotnetforms', 'Form Database', 'form_database_meta_box_output_piotnetforms', 'piotnetforms-data' );
	}
	add_action( 'add_meta_boxes', 'form_database_meta_box_piotnetforms' );

	function form_database_meta_box_output_piotnetforms() {
		$database_id = get_the_ID();
		$all_meta = get_post_meta($database_id);
		$fields_database = get_post_meta($database_id, '_piotnetforms_fields_database', true);
		if ($fields_database) {
			$fields_database = json_decode($fields_database, true);
		}
		echo '<table class="piotnetforms-form-database">';
		if ($fields_database) {
			foreach ($fields_database as $key => $field) {
				if ($key != 'form_id_piotnetforms' && $key != 'post_id' && $key != '_edit_lock' && $key != '_piotnetforms_fields_database') {
					$label = !empty($field['label']) ? $field['label'] : $key;
					echo '<tr>';
						echo '<td>';
							echo $label;
						echo '</td>';
						echo '<td>';
							if ($key == 'payment_amount') {
								echo $field['value'] / 100;
							} else {
								echo nl2br($field['value']);
							}
						echo '</td>';
					echo '</tr>';
				}
			}
		} else {
			foreach ($all_meta as $key => $meta) {
				if ($key != 'form_id_piotnetforms' && $key != 'post_id' && $key != '_edit_lock' && $key != '_piotnetforms_fields_database') {
					$label = !empty($fields_database[$key]) ? $fields_database[$key]['label'] : $key;
					echo '<tr>';
						echo '<td>';
							echo $label;
						echo '</td>';
						echo '<td>';
							if ($key == 'payment_amount') {
								echo $meta[0] / 100;
							} else {
								echo nl2br($meta[0]);
							}
						echo '</td>';
					echo '</tr>';
				}
			}
		}
		
		echo '</table>';
	}
?>