<?php

namespace Controls_Piotnetforms;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class piotnetforms_Base_Control {
	abstract public function get_type();

	abstract public function get_control_template();

	public function get_template() {
		?>
		<script type="text/html" data-piotnetforms-template id="piotnetforms-<?php echo esc_attr( $this->get_type() ); ?>-control-template">
			<%
				let field_group_class = "piotnet-control__field-group";
				if (data.separator) {
					field_group_class += " piotnet-control__field-group--separator-" + data.separator;
				}

				field_group_class += " piotnet-control__field-group--" + data.type;

				const field_group_attributes = ["data-piotnet-control"];
				if (data.responsive) {
					field_group_attributes.push('data-piotnet-responsive="' + data.responsive + '"');
				}

				if (data.label_block) {
					field_group_attributes.push("data-piotnet-control-label-block");
				}

				if ( data.conditions ) {
					field_group_attributes.push("data-piotnet-control-conditions='" + JSON.stringify(data.conditions) + "'");
				}

				let control_description = data.description ? '<span data-piotnet-control-tooltip><i class="far fa-question-circle"></i><span style="display: none;" data-piotnet-control-tooltip-content>' + data.description + '</span></span>' : '';

				if ( data.dynamic_field ) {
					field_group_attributes.push("data-piotnet-control-has-dynamic-field");
				}

				if ( !data.value && data.name.includes('__dynamic__') ) {
					field_group_attributes.push("style='display:none'");
				}

				if ( data.get_fields && data.dynamic ) {
					field_group_attributes.push("data-piotnetforms-get-fields-dynamic");
				}

				if ( data.get_metadata && data.dynamic ) {
					field_group_attributes.push("data-piotnetforms-get-metadata-dynamic");
				}

			%>
			<div class="<%= field_group_class %>"<% _.each(field_group_attributes, function(field_group_attribute) { %><%= " " + field_group_attribute %><% }); %>>
				<%= data.label ? '<label class="piotnet-control__label">' + data.label + control_description + '</label>' : "" %>
				<div class='piotnet-control__field'<%= data.field_width ? ' style="width:' + data.field_width + '!important"' : "" %>>
					<% if (data.responsive) { %>
					<div class="piotnet-control__responsive">
						<span class="piotnet-control__responsive-item active" data-piotnet-control-responsive="desktop">Desktop</span>
						<span class="piotnet-control__responsive-item" data-piotnet-control-responsive="tablet">Tablet</span>
						<span class="piotnet-control__responsive-item" data-piotnet-control-responsive="mobile">Mobile</span>
					</div><% } %>
					<% if (data.dynamic) { %>
						<div class="piotnet-control-dynamic-value" data-piotnet-control-dynamic-value title="Dynamic Tags"><i class="fas fa-bolt"></i></div>
					<% } %>
					<% if (data.name.includes('__dynamic__')) { %>
						<div class="piotnet-control-dynamic-value piotnet-control-dynamic-value--remove" data-piotnet-control-dynamic-value-remove title="Remove Dynamic Tags"><i class="fas fa-times"></i></div>
					<% } %>
					<?php $this->get_control_template(); ?>
				</div>
			</div>
		</script>
		<?php
	}
}
