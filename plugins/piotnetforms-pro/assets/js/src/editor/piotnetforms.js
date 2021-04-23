import ObjectUtil from "../util/object-util";

const CONTROL_WIDGETS = "control_widgets";
const CSS_WIDGETS = "css_widgets";
const WIDGET_INFOS = "widget_infos";
const LIBS = "libs";
const SETTINGS = "settings";
const TEMPLATES = "templates";
const WIDGET_STRUCTURES = "widget_structures";
const BREAKPOINTS = "breakpoints";
const RESPONSIVE = "responsive";
const DEPENDENCIES = "dependencies";
const STYLE_CONTROLS = "style_controls";

export default class piotnetforms {
    constructor() {
        const master = {}
        master[CONTROL_WIDGETS] = {};
        master[CSS_WIDGETS] = {};
        master[WIDGET_INFOS] = {};
        master[LIBS] = {};
        master[SETTINGS] = {
            'widgets': {},
            'tree': {}
        };
        master[TEMPLATES] = {};
        master[WIDGET_STRUCTURES] = {};
        master[BREAKPOINTS] = {};
        master[RESPONSIVE] = "desktop";
        master[DEPENDENCIES] = {};
        master[STYLE_CONTROLS] = {};
        this.master = master;
    }

    get_template(template_id) {
        return this.master[TEMPLATES][template_id];
    }

    set_template(template_id, $template) {
        this.master[TEMPLATES][template_id] = $template;
    }

    get_setting_widgets() {
        return this.master[SETTINGS]['widgets'];
    }

    set_setting_widgets(setting_widgets) {
        this.master[SETTINGS]['widgets'] = setting_widgets;
    }

    get_setting_widget(widget_id) {
        return this.get_setting_widgets()[widget_id];
    }

    set_setting_widget(widget_id, setting_widget) {
        this.get_setting_widgets()[widget_id] = setting_widget;
    }

    remove_setting_widget(widget_id) {
        delete this.master[SETTINGS]['widgets'][widget_id];
    }

    get_tree_setting_widgets() {
        return this.master[SETTINGS]['tree'];
    }

    set_tree_setting_widgets(tree_setting_widgets) {
        this.master[SETTINGS]['tree'] = tree_setting_widgets;
    }

    get_tree_setting_widget(widget_id, elements) {
        if (!elements) {
            elements = this.master[SETTINGS]['tree'];
        }
        for (let i = 0; i < elements.length; i++) {
            const widget = elements[i];
            if (widget['id'] === widget_id) {
                return widget;
            }

            const sub_elements = widget['elements'];
            if (sub_elements && sub_elements.length > 0) {
                const sub_widget = this.get_tree_setting_widget(widget_id, sub_elements);
                if (sub_widget) {
                    return sub_widget;
                }
            }
        }
        return null;
    }

    get_css_widgets() {
        return this.master[CSS_WIDGETS];
    }

    get_css_widget(widget_id) {
        return this.get_css_widgets()[widget_id];
    }

    set_css_widget(widget_id, css) {
        this.get_css_widgets()[widget_id] = css;
    }

    get_control_widgets() {
        return this.master[CONTROL_WIDGETS];
    }

    get_control_widget(widget_id) {
        return this.get_control_widgets()[widget_id];
    }

    set_control_widget(widget_id, control_widget) {
        this.get_control_widgets()[widget_id] = control_widget;
    }

    get_widget_structures() {
        return this.master[WIDGET_STRUCTURES];
    }

    get_widget_structure(widget_id) {
        return this.get_widget_structures()[widget_id];
    }

    build_widget_structure(widget_id, fields) {
        const setting = this.get_setting_widget(widget_id);
        if (!fields) {
            fields = setting['fields'];
        }

        const widget_type = setting['type'];
        const structure = this.get_widget_info(widget_type)['structure'];
        if (structure) {
            const widget_structure = ObjectUtil.clone(structure);
            return this.fill_values(widget_structure, fields);
        }
        return null;
    }

    fill_values(controls_widget, fields) {
        const tab_indexes = Object.keys(controls_widget);
        for (const tab_index in tab_indexes) {
            const tab = controls_widget[tab_index];
            const sections = tab['sections'];
            const section_indexes = Object.keys(sections);
            for (const section_index in section_indexes) {
                const section = sections[section_index];
                const controls = section['controls'];
                const control_indexes = Object.keys(controls);
                for (const control_index in control_indexes) {
                    controls[control_index] = this.fill_values_control(controls[control_index], fields);
                }
            }
        }
        return controls_widget;
    }

    fill_values_control(control, fields) {
        const name = control['name'];
        const field = fields[name];

        let is_skip_fill_controls = false;
        if (Array.isArray(field) && control.type === "repeater") {
            is_skip_fill_controls = true;
            for (const repeater_item of field) {
                // Copy controls from repeater item 0
                let new_control = this.fill_values_control(ObjectUtil.clone(control.controls[0]), repeater_item);
                control['controls'].push(new_control);
            }
        } else if (control.type === 'switch' && !field) {
            control['value'] = '';
        } else if (field) {
            control['value'] = field;
        }

        const controls = control['controls'];
        if (controls && !is_skip_fill_controls) {
            const control_indexes = Object.keys(controls);
            for (const control_index in control_indexes) {
                controls[control_index] = this.fill_values_control(controls[control_index], fields);
            }
        }
        return control;
    }

    set_widget_structure(widget_id, widget_structure) {
        this.get_widget_structures()[widget_id] = widget_structure;
    }

    get_widget_infos() {
        return this.master[WIDGET_INFOS];
    }

    set_widget_infos(widget_infos) {
        this.master[WIDGET_INFOS] = widget_infos;
    }

    get_widget_info(name) {
        return this.get_widget_infos()[name];
    }

    get_libs() {
        return this.master[LIBS];
    }

    set_libs(libs) {
        this.master[LIBS] = libs;
    }

    set_breakpoint(breakpoint_id, value) {
        this.master[BREAKPOINTS][breakpoint_id] = value;
    }

    get_breakpoint(breakpoint_id) {
        return this.master[BREAKPOINTS][breakpoint_id];
    }

    set_responsive(responsive) {
        this.master[RESPONSIVE] = responsive;
    }

    get_responsive() {
        return this.master[RESPONSIVE];
    }

    set_dependencies(widget_type, dependencies) {
        this.master[DEPENDENCIES][widget_type] = dependencies;
    }

    get_dependencies(widget_type) {
        return this.master[DEPENDENCIES][widget_type];
    }

    set_style_controls(widget_type, style_controls) {
        this.master[STYLE_CONTROLS][widget_type] = style_controls;
    }

    get_style_controls(widget_type) {
        return this.master[STYLE_CONTROLS][widget_type];
    }
}
