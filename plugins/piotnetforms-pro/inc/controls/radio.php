<?php

namespace Controls_Piotnetforms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class piotnetforms_Control_Radio extends piotnetforms_Base_Control {
	public function get_type() {
		return 'radio';
	}

	public function get_control_template() {
		?>
			<div class="piotnet-control__field-radio">
				<% for ( var key in data.options ) { %>
				<label class="piotnet-control__field-label">
					<input type="radio" class="<%= data.classes %>" name="<%= data.name %>" value="<%- key %>" <%= (key == data.value) ? "checked" : "" %> <%= data_type_html(data) %> <%= data.attr %>>
					<span class="piotnet-control__field-text"><%- data.options[key] %></span>
				</label>
				<% } %>
			</div>
		<?php
	}
}
