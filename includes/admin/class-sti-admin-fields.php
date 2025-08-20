<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'STI_Admin_Fields' ) ) :

    /**
     * Class for plugin admin ajax hooks
     */
    class STI_Admin_Fields {

        /**
         * @var STI_Admin_Fields Current tab name
         */
        private $tab_name;

        /**
         * @var STI_Admin_Fields Options section name
         */
        private $section_name;

        /**
         * @var STI_Admin_Fields The array of options that is need to be generated
         */
        private $options_array;

        /**
         * @var STI_Admin_Fields Current plugin instance options
         */
        private $plugin_options;

        /*
         * Constructor
         */
        public function __construct( $tab_name, $plugin_options ) {

            $this->section_name = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'none';

            $options = STI_Admin_Options::options_array( $tab_name, $this->section_name );

            $this->tab_name = $tab_name;
            $this->options_array = $options[$tab_name];
            $this->plugin_options = $plugin_options;

            $this->generate_fields();

        }

        /*
         * Generate options fields
         */
        private function generate_fields() {

            if ( empty( $this->options_array ) ) {
                return;
            }

            $current_tab = empty( $_GET['tab'] ) ? 'buttons' : sanitize_text_field( $_GET['tab'] );
            $tab_visibility = $current_tab === $this->tab_name ? 'style="display:block;"' : 'style="display:none;"';

            echo '<table class="form-table" data-tab="' . $this->tab_name . '" ' . $tab_visibility . '>';

            echo '<colgroup>';
                echo '<col class="sti-col-25">';
                echo '<col class="sti-col-75">';
            echo '</colgroup>';

            echo '<tbody>';

            if ( $this->section_name !== 'none' ) {
                $tab = empty( $_GET['tab'] ) ? 'buttons' : sanitize_text_field( $_GET['tab'] );
                $back_link = admin_url( 'admin.php?page=sti-options&tab=' . $tab  );
                echo '<a class="button sti-back" href="' . esc_url( $back_link ) . '" title="' . esc_attr__( 'Back', 'share-this-image' ) . '">' . esc_html__( 'Back', 'share-this-image' ) . '</a>';
            }


            foreach ( $this->options_array as $k => $value ) {

                if ( isset( $value['depends'] ) && ! $value['depends'] ) {
                    continue;
                }

                $this->generate_field_types( $value );

            }

            echo '</tbody>';
            echo '</table>';

        }

        /*
         * Generate different options fields types
         */
        private function generate_field_types( $value ) {

            $plugin_options = $this->plugin_options;

            switch ( $value['type'] ) {

                case 'text': ?>
                    <?php $text_value = isset( $plugin_options[ $value['id'] ] ) ? esc_attr( stripslashes( $plugin_options[ $value['id'] ] ) ) : $value['value']; ?>
                    <?php $container = isset( $value['container'] ) && $value['container'] ? ' class="' . $value['container'] . '"' : ''; ?>
                    <tr valign="top" <?php echo $container; ?>>
                        <th scope="row"><?php echo esc_html( $value['name'] ); ?></th>
                        <td>
                            <input type="text" name="<?php echo esc_attr( $value['id'] ); ?>" class="regular-text" value="<?php echo $text_value; ?>">
                            <br><span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>
                        </td>
                    </tr>
                    <?php break;

                case 'image': ?>

                    <?php $image_value = isset( $plugin_options[ $value['id'] ] ) ? esc_attr( stripslashes( $plugin_options[ $value['id'] ] ) ) : $value['value']; ?>

                    <tr valign="top">
                        <th scope="row"><?php echo esc_html( $value['name'] ); ?></th>
                        <td>
                            <img class="image-preview" src="<?php echo esc_url( stripslashes( $plugin_options[ $value['id'] ] ) ); ?>"  />
                            <input type="hidden" size="40" name="<?php echo esc_attr( $value['id'] ); ?>" class="image-hidden-input" value="<?php echo $image_value; ?>" />
                            <input class="button image-upload-btn" type="button" value="<?php echo esc_attr__( 'Upload Image', 'share-this-image' ); ?>" data-size="<?php echo esc_attr( $value['size'] ); ?>" />
                            <input class="button image-remove-btn" type="button" value="<?php echo esc_attr__( 'Remove Image', 'share-this-image' ); ?>" />
                        </td>
                    </tr>

                    <?php

                    break;

                case 'number': ?>

                    <?php
                    $number_value = isset( $plugin_options[$value['id']] ) ? intval( esc_attr( stripslashes( $plugin_options[ $value['id'] ] ) ) ) : $value['value'];

                    $params = '';
                    $params .= isset( $value['step'] ) ? ' step="' . $value['step'] . '"' : '';
                    $params .= isset( $value['min'] ) ? ' min="' . $value['min'] . '"' : '';
                    $params .= isset( $value['max'] ) ? ' max="' . $value['max'] . '"' : '';
                    $params .= isset( $value['attr'] ) ? $value['attr'] : '';
                    $container = isset( $value['container'] ) && $value['container'] ? ' class="' . $value['container'] . '"' : '';
                    ?>

                    <tr valign="top"<?php echo $container; ?>>
                        <th scope="row"><?php echo esc_html( $value['name'] ); ?></th>
                        <td>
                            <input type="number" <?php echo $params; ?> name="<?php echo esc_attr( $value['id'] ); ?>" class="regular-text" value="<?php echo $number_value; ?>">
                            <br><span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>
                        </td>
                    </tr>
                    <?php break;

                case 'number_add': ?>
                    <?php
                    $number_value = isset( $plugin_options[$value['id']] ) ? esc_attr( stripslashes( $plugin_options[ $value['id'] ] ) ) : $value['value'];
                    $page_ids_val = isset( $plugin_options[ $value['id'] ] ) ? stripslashes( $plugin_options[ $value['id'] ] ) : '';
                    $page_ids_array = json_decode( $page_ids_val );
                    ?>
                    <tr valign="top">
                        <th scope="row"><?php echo $value['name']; ?></th>
                        <td data-container>

                            <ul data-add-number-list class="items-list clearfix">

                                <?php

                                if ( ! empty( $page_ids_array ) ) {

                                    foreach( $page_ids_array as $page_id ) {
                                        echo '<li class="item">';
                                        echo '<span data-name="' . esc_attr( $page_id ) . '" class="name">' . esc_attr( $page_id ) . '</span>';
                                        echo '<a data-remove-number-btn class="close">x</a>';
                                        echo '</li>';
                                    }

                                }
                                ?>

                            </ul>

                            <input data-add-number-val type="hidden" name="<?php echo esc_attr( $value['id'] ); ?>" value='<?php echo $number_value; ?>'>

                            <input data-add-number-name type="number" class="regular-text" value="">
                            <input data-add-number-btn type="submit" name="<?php echo esc_attr__( 'Add', 'share-this-image' ); ?>" class="button-primary" value="<?php echo esc_attr__( 'Add', 'share-this-image' ); ?>">
                            <br><span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>
                        </td>
                    </tr>
                    <?php break;

                case 'textarea': ?>
                    <?php $container = isset( $value['container'] ) && $value['container'] ? ' class="' . $value['container'] . '"' : ''; ?>
                    <tr valign="top"<?php echo $container; ?>>
                        <th scope="row"><?php echo esc_html( $value['name'] ); ?></th>
                        <td>
                            <?php $textarea_cols = isset( $value['cols'] ) ? $value['cols'] : "45"; ?>
                            <?php $textarea_rows = isset( $value['rows'] ) ? $value['rows'] : "3"; ?>
                            <?php $textarea_value = isset( $plugin_options[$value['id']] ) ? $plugin_options[ $value['id'] ] : $value['value']; ?>
                            <?php $textarea_output = isset( $value['allow_tags'] ) ? wp_kses( $textarea_value, STI_Admin_Helpers::get_kses( $value['allow_tags'] ) ) : esc_html( stripslashes( $textarea_value ) ); ?>
                            <textarea id="<?php echo esc_attr( $value['id'] ); ?>" name="<?php echo esc_attr( $value['id'] ); ?>" cols="<?php echo $textarea_cols; ?>" rows="<?php echo $textarea_rows; ?>"><?php print $textarea_output; ?></textarea>
                            <br><span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>
                        </td>
                    </tr>
                    <?php break;

                case 'checkbox': ?>
                    <?php $container = isset( $value['container'] ) && $value['container'] ? ' class="' . $value['container'] . '"' : ''; ?>
                    <tr valign="top"<?php echo $container; ?>>
                        <th scope="row"><?php echo esc_html( $value['name'] ); ?></th>
                        <td>
                            <?php $checkbox_options = isset( $plugin_options[ $value['id'] ] ) ? $plugin_options[ $value['id'] ] : 'false'; ?>
                            <?php foreach ( $value['choices'] as $val => $label ) { ?>
                                <input type="checkbox" name="<?php echo esc_attr( $value['id'] . '[' . $val . ']' ); ?>" id="<?php echo esc_attr( $value['id'] . '_' . $val ); ?>" value="1" <?php checked( $checkbox_options[$val], '1' ); ?>> <label for="<?php echo esc_attr( $value['id'] . '_' . $val ); ?>"><?php echo esc_html( $label ); ?></label><br>
                            <?php } ?>
                            <br><span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>
                        </td>
                    </tr>
                    <?php break;

                case 'toggler': ?>
                    <?php
                    $attr = isset( $value['attr'] ) && $value['attr'] ? $value['attr'] : '';
                    $toggle = isset( $value['toggle'] ) && $value['toggle'] ? ' data-toggle="' . $value['toggle'] . '" ' : '';
                    $container = isset( $value['container'] ) && $value['container'] ? ' class="' . $value['container'] . '"' : '';
                    ?>
                    <tr valign="top"<?php echo $container; ?>>
                        <th scope="row"><?php echo esc_html( $value['name'] ); ?></th>
                        <td>
                            <?php $checkbox_options = isset( $plugin_options[ $value['id'] ] ) ? $plugin_options[ $value['id'] ] : 'false'; ?>

                            <div class="sti-togglers">
                                <label data-toggle>
                                    <input <?php echo $attr . $toggle; ?> type="checkbox" name="<?php echo esc_attr( $value['id'] ); ?>" value="true" <?php checked( $checkbox_options, 'true' ); ?>>
                                    <span class="sti-toggle"></span>
                                </label>
                            </div>

                            <br><span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>
                        </td>
                    </tr>
                    <?php break;

                case 'color': ?>
                    <?php
                    $attr = isset( $value['attr'] ) && $value['attr'] ? $value['attr'] : '';
                    $container = isset( $value['container'] ) && $value['container'] ? ' class="' . $value['container'] . '"' : '';
                    $color_value = isset( $plugin_options[ $value['id'] ] ) ? esc_attr( stripslashes( $plugin_options[ $value['id'] ] ) ) : $value['value'];
                    ?>
                    <tr valign="top"<?php echo $container; ?>>
                        <th scope="row"><?php echo esc_html( $value['name'] ); ?></th>
                        <td>
                            <input <?php echo $attr; ?> type="text" class="sti-color-picker" name="<?php echo esc_attr( $value['id'] ); ?>" class="regular-text" value="<?php echo $color_value; ?>">
                            <br><span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>
                        </td>
                    </tr>
                    <?php break;

                case 'html': ?>
                    <?php
                    $container = isset( $value['container'] ) && $value['container'] ? ' class="' . $value['container'] . '"' : '';
                    ?>
                    <tr valign="top"<?php echo $container; ?>>
                        <th scope="row"><?php echo esc_html( $value['name'] ); ?></th>
                        <td>
                            <?php echo $value['html']; ?>
                            <br><span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>
                        </td>
                    </tr>
                    <?php break;

                case 'radio': ?>
                    <?php $radio_value = isset( $plugin_options[ $value['id'] ] ) ? $plugin_options[ $value['id'] ] : $value['value']; ?>
                    <?php $container = isset( $value['container'] ) && $value['container'] ? ' class="' . $value['container'] . '"' : ''; ?>
                    <tr valign="top"<?php echo $container; ?>>
                        <th scope="row"><?php echo esc_html( $value['name'] ); ?></th>
                        <td>
                            <?php foreach ( $value['choices'] as $val => $label ) { ?>
                                <input class="radio" type="radio" name="<?php echo esc_attr( $value['id'] ); ?>" id="<?php echo esc_attr( $value['id'].$val ); ?>" value="<?php echo esc_attr( $val ); ?>" <?php checked( $radio_value, $val ); ?>> <label for="<?php echo esc_attr( $value['id'].$val ); ?>"><?php echo esc_html( $label ); ?></label><br>
                            <?php } ?>
                            <br><span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>
                        </td>
                    </tr>
                    <?php break;

                case 'select': ?>
                    <?php $select_value = isset( $plugin_options[ $value['id'] ] ) ? $plugin_options[ $value['id'] ] : $value['value']; ?>
                    <?php $container = isset( $value['container'] ) && $value['container'] ? ' class="' . $value['container'] . '"' : ''; ?>
                    <tr valign="top"<?php echo $container; ?>>
                        <th scope="row"><?php echo esc_html( $value['name'] ); ?></th>
                        <td>
                            <select name="<?php echo esc_attr( $value['id'] ); ?>">
                                <?php foreach ( $value['choices'] as $val => $label ) { ?>
                                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $select_value, $val ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php } ?>
                            </select>
                            <br><span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>
                        </td>
                    </tr>
                    <?php break;

                case 'select_advanced': ?>
                    <?php $container = isset( $value['container'] ) && $value['container'] ? ' class="' . $value['container'] . '"' : ''; ?>
                    <tr valign="top"<?php echo $container; ?>>
                        <th scope="row"><?php echo esc_html( $value['name'] ); ?></th>
                        <td>
                            <select name="<?php echo esc_attr( $value['id'].'[]' ); ?>" multiple class="chosen-select">
                                <?php $values = isset( $plugin_options[ $value['id'] ] ) ? $plugin_options[ $value['id'] ] : $value['value']; ?>
                                <?php foreach ( $value['choices'] as $val => $label ) {  ?>
                                    <?php $selected = ( is_array( $values ) && in_array( $val, $values ) ) ? ' selected="selected" ' : ''; ?>
                                    <option value="<?php echo esc_attr( $val ); ?>"<?php echo $selected; ?>><?php echo esc_html( $label ); ?></option>
                                <?php } ?>
                            </select>
                            <br><span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>

                        </td>
                    </tr>
                    <?php break;

                case 'sortable': ?>
                    <?php $container = isset( $value['container'] ) && $value['container'] ? ' class="' . $value['container'] . '"' : ''; ?>
                    <tr valign="top"<?php echo $container; ?>>
                        <th scope="row"><?php echo esc_html( $value['name'] ); ?></th>
                        <td>


                            <script>
                                jQuery(document).ready(function() {

                                    jQuery( "#<?php echo esc_attr( $value['id'] ); ?>1, #<?php echo esc_attr( $value['id'] ); ?>2" ).sortable({
                                        connectWith: ".connectedSortable",
                                        placeholder: "highlight",
                                        update: function(event, ui){
                                            var serviceList = '';
                                            jQuery("#<?php echo esc_attr( $value['id'] ); ?>2 li").each(function(){

                                                serviceList = serviceList + ',' + jQuery(this).attr('id');

                                            });
                                            var serviceListOut = serviceList.substring(1);
                                            jQuery('#<?php echo esc_attr( $value['id'] ); ?>').attr('value', serviceListOut);
                                        }
                                    }).disableSelection();

                                })
                            </script>

                            <span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span><br><br>

                            <?php
                            $all_buttons = $value['choices'];
                            $active_buttons = explode( ',', $plugin_options[ $value['id'] ] );
                            $active_buttons_array = array();

                            if ( count( $active_buttons ) > 0 ) {
                                foreach ($active_buttons as $button) {
                                    $active_buttons_array[$button] = $all_buttons[$button];
                                }
                            }

                            $inactive_buttons = array_diff($all_buttons, $active_buttons_array);
                            ?>


                            <div class="sortable-container">

                                <div class="sortable-title">
                                    <?php esc_html_e( 'Active', 'share-this-image' ) ?><br>
                                    <?php esc_html_e( 'Change order by drag&drop', 'share-this-image' ) ?>
                                </div>

                                <ul id="<?php echo esc_attr( $value['id'] ); ?>2" class="sti-sortable enabled connectedSortable">
                                    <?php
                                    if ( count( $active_buttons_array ) > 0 ) {
                                        foreach ($active_buttons_array as $button_value => $button) {
                                            if ( ! $button ) continue;
                                            echo '<li id="' . esc_attr( $button_value ) . '" class="sti-btn sti-' . esc_attr( $button_value ) . '-btn">' . esc_html( $button ) . '</li>';
                                        }
                                    }
                                    ?>
                                </ul>

                            </div>

                            <div class="sortable-container">

                                <div class="sortable-title">
                                    <?php esc_html_e( 'Inactive', 'share-this-image' ) ?><br>
                                    <?php esc_html_e( 'Excluded from this option', 'share-this-image' ) ?>
                                </div>

                                <ul id="<?php echo $value['id']; ?>1" class="sti-sortable disabled connectedSortable">
                                    <?php
                                    if ( count( $inactive_buttons ) > 0 ) {
                                        foreach ($inactive_buttons as $button_value => $button) {
                                            echo '<li id="' . esc_attr( $button_value ) . '" class="sti-btn sti-' . esc_attr( $button_value ) . '-btn">' . esc_html( $button ) . '</li>';
                                        }
                                    }
                                    ?>
                                </ul>

                            </div>

                            <input type="hidden" id="<?php echo $value['id']; ?>" name="<?php echo esc_attr( $value['id'] ); ?>" value="<?php echo esc_attr( $plugin_options[ $value['id'] ] ); ?>" />

                        </td>
                    </tr>
                    <?php break;

                case 'sortable_table': ?>
                    <?php $buttons = $plugin_options[ $value['id'] ]; ?>
                    <?php $container = isset( $value['container'] ) && $value['container'] ? ' class="' . $value['container'] . '"' : ''; ?>
                    <tr valign="top"<?php echo $container; ?>>
                        <th scope="row"><?php echo esc_html( $value['name'] ); ?></th>
                        <td>

                            <span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>

                            <table class="sti-table sti-table-sortable widefat" cellspacing="0">

                                <thead>
                                <tr>
                                    <th class="sti-table-sort">&nbsp;</th>
                                    <th class="sti-table-btns"><?php esc_html_e( 'Social button', 'share-this-image' ) ?></th>
                                    <th class="sti-table-show"><?php esc_html_e( 'Desktop', 'share-this-image' ) ?></th>
                                    <th class="sti-table-show"><?php esc_html_e( 'Mobile', 'share-this-image' ) ?></th>
                                    <!--                                            <th class="sti-table-edit"></th>-->
                                </tr>
                                </thead>

                                <tbody>

                                <?php if ( $buttons && is_array( $buttons ) ): ?>
                                    <?php foreach( $buttons as $button_slug => $button_val ): ?>

                                        <?php

                                        $button_name = $value['choices'][$button_slug]['name'];
                                        $tab = empty( $_GET['tab'] ) ? 'buttons' : sanitize_text_field( $_GET['tab'] );
                                        $edit_link = admin_url( 'admin.php?page=sti-options&tab=' . $tab . '&section=edit_' . $button_slug );
                                        $svg_icon = STI_Helpers::get_svg( $button_slug );

                                        ?>

                                        <tr class="sti-table-button">
                                            <td class="sti-table-sort"></td>
                                            <td class="sti-table-btns">
                                                        <span class="sti-table-btns-inner">
                                                            <span class="sti-share-box">
                                                                <span class="sti-btn sti-<?php echo $button_slug; ?>-btn">
                                                                    <?php echo $svg_icon; ?>
                                                                </span>
                                                            </span>
                                                            <span class="sti-btn-name"><?php echo $button_name; ?></span>
                                                        </span>
                                                <input type="hidden" value="<?php echo $button_slug; ?>" name="<?php echo esc_attr( $value['id'] ) . '['.$button_slug.'][name]'; ?>">
                                            </td>
                                            <td class="sti-togglers">
                                                <label data-toggle>
                                                    <input type="checkbox" name="<?php echo esc_attr( $value['id'] ) . '['.$button_slug.'][desktop]'; ?>" value="true" <?php checked( $button_val['desktop'], 'true' ); ?>>
                                                    <span class="sti-toggle"></span>
                                                </label>
                                            </td>
                                            <td class="sti-togglers">
                                                <label data-toggle>
                                                    <input type="checkbox" name="<?php echo esc_attr( $value['id'] ) . '['.$button_slug.'][mobile]'; ?>" value="true" <?php checked( $button_val['mobile'], 'true' ); ?>>
                                                    <span class="sti-toggle"></span>
                                                </label>
                                            </td>
                                        </tr>

                                    <?php endforeach; ?>
                                <?php endif; ?>

                                </tbody>

                            </table>

                        </td>
                    </tr>
                    <?php break;

                case 'sharing_buttons': ?>

                    <?php $buttons = $plugin_options[ $value['id'] ]; ?>
                    <?php $container = isset( $value['container'] ) && $value['container'] ? ' class="' . $value['container'] . '"' : ''; ?>

                    <tr valign="top"<?php echo $container; ?>>
                        <th scope="row"><?php echo esc_html( $value['name'] ); ?></th>
                        <td>
                            <span class="description"><?php echo wp_kses_post( $value['desc'] ); ?></span>

                            <div class="sti-sbt">

                                <div class="sti-sbt-head">

                                    <div class="sort" style="width:10%;">
                                    </div>

                                    <div class="btns" style="width:60%;">
                                        <?php esc_html_e( 'Social button', 'share-this-image' ) ?>
                                    </div>

                                    <div class="show" style="width:15%;">
                                        <?php esc_html_e( 'Desktop', 'share-this-image' ) ?>
                                    </div>

                                    <div class="show" style="width:15%;">
                                        <?php esc_html_e( 'Mobile', 'share-this-image' ) ?>
                                    </div>

                                </div>

                                <div class="sti-sbt-body">

                                    <?php if ( $buttons && is_array( $buttons ) ): ?>
                                        <?php foreach( $buttons as $button_slug => $button_val ): ?>

                                            <?php

                                            $button_section_name = "edit_" . $button_slug;
                                            $button_name = $value['choices'][$button_slug]['name'];
                                            $svg_icon = STI_Helpers::get_svg( $button_slug );

                                            ?>


                                            <div class="sti-sbt-item sti-table-button">

                                                <div class="sti-table-sort" style="width:10%;">
                                                    <span class="dashicons dashicons-menu"></span>
                                                </div>

                                                <div class="sti-table-btns" style="width:60%;">
                                                        <span class="sti-table-btns-inner">
                                                            <span class="sti-share-box">
                                                                <span class="sti-btn sti-<?php echo $button_slug; ?>-btn">
                                                                    <?php echo $svg_icon; ?>
                                                                </span>
                                                            </span>
                                                            <span class="sti-btn-name"><?php echo $button_name; ?></span>
                                                        </span>
                                                    <input type="hidden" value="<?php echo $button_slug; ?>" name="<?php echo esc_attr( $value['id'] ) . '['.$button_slug.'][name]'; ?>">
                                                </div>

                                                <div class="sti-togglers" style="width:15%;">
                                                    <label data-toggle>
                                                        <input type="checkbox" name="<?php echo esc_attr( $value['id'] ) . '['.$button_slug.'][desktop]'; ?>" value="true" <?php checked( $button_val['desktop'], 'true' ); ?>>
                                                        <span class="sti-toggle"></span>
                                                    </label>
                                                </div>

                                                <div class="sti-togglers" style="width:15%;">
                                                    <label data-toggle>
                                                        <input type="checkbox" name="<?php echo esc_attr( $value['id'] ) . '['.$button_slug.'][mobile]'; ?>" value="true" <?php checked( $button_val['mobile'], 'true' ); ?>>
                                                        <span class="sti-toggle"></span>
                                                    </label>
                                                </div>

                                            </div>

                                        <?php endforeach; ?>
                                    <?php endif; ?>

                                </div>

                            </div>

                        </td>
                    </tr>

                    <?php break;

                case 'display_rules':

                    $rules_value = isset($plugin_options[$value['id']]) ? $plugin_options[$value['id']] : $value['value'];
                    $incorrect_rules = STI_Admin_Helpers::check_for_incorrect_display_rules( $rules_value );

                    $rules = STI_Admin_Options::include_rules();
                    $default_rule = new STI_Admin_Display_Rules( $rules['common'][0] );
                    $container = isset($value['container']) && $value['container'] ? ' class="' . $value['container'] . '"' : '';

                    $html = '<tr valign="top"' . $container . '>';

                    $html .= '<th scope="row">' . esc_html( $value['name'] ) . '</th>';

                    $html .= '<td>';

                    $html .= '<div class="sti-rules">';

                    $html .= '<script id="stiRulesTemplate" type="text/html">';
                    $html .= $default_rule->get_rule();
                    $html .= '</script>';

                    if ( $incorrect_rules ) {
                        $html .= '<div class="sti-rules-notices">';
                        $html .= __( 'Warning: you set some display rules incorrectly. You can\'t have several Image, Page or Post conditions inside one AND condition group.', 'share-this-image'  ) . '<br>';
                        $html .= __( 'Incorrect rules:', 'share-this-image' ) . '<br>';
                        $html .= '<code>'. $incorrect_rules .'</code>' . '<br>';
                        $html .= __( 'Divide them into several OR condition groups or just delete some.', 'share-this-image' );
                        $html .= '</div>';
                    }

                    $html .= '<div class="sti-rules-desc">';
                    $html .= wp_kses_post( $value['desc'] );
                    $html .= '</div>';

                    if ( $rules_value && ! empty( $rules_value )  ) {

                        foreach( $rules_value as $group_id => $group_rules ) {

                            $group_id = is_string( $group_id ) ? str_replace( 'group_', '', $group_id ) : $group_id;

                            $html .= '<table class="sti-rules-table" data-sti-group="' . esc_attr( $group_id ) . '">';
                            $html .= '<tbody>';

                            foreach( $group_rules as $rule_id => $rule_values ) {

                                $rule_id = is_string( $rule_id ) ? str_replace( 'rule_', '', $rule_id ) : $rule_id;

                                if ( isset( $rule_values['param'] ) ) {
                                    $current_rule = new STI_Admin_Display_Rules( STI_Admin_Options::include_rule_by_id( $rule_values['param'], STI_Admin_Options::include_rules() ), $group_id, $rule_id, $rule_values );
                                    $html .= $current_rule->get_rule();
                                }

                            }

                            $html .= '</tbody>';
                            $html .= '</table>';

                        }

                    } else {

                        $html .= '<table class="sti-rules-table" data-sti-group="1">';
                        $html .= '<tbody>';
                        $html .= $default_rule->get_rule();
                        $html .= '</tbody>';
                        $html .= '</table>';

                    }

                    $html .= '<a href="#" class="button add-rule-group" data-sti-add-group>' . __( "Add 'or' group", "share-this-image" ) . '</a>';

                    $html .= '</div>';

                    $html .= '</td>';

                    $html .= '</tr>';

                    echo $html;

                    break;


                case 'heading': ?>
                    <?php $tag = isset( $value['tag'] ) ? $value['tag'] : 'h3'; ?>
                    <?php $container = isset($value['container']) && $value['container'] ? ' ' . $value['container']: ''; ?>
                    <?php $desc = isset( $value['desc'] ) ? $value['desc'] : ''; ?>
                    <tr class="delimiter"></tr>
                    <tr valign="top" class="heading<?php echo $container; ?>">
                        <th scope="row"><<?php echo $tag; ?>><?php echo esc_html( $value['name'] ); ?></<?php echo $tag; ?>></th>
                        <td>
                            <span class="description"><?php echo wp_kses_post( $desc ); ?></span>
                            <?php if ( isset( $value['spoiler'] ) && $value['spoiler'] ): ?>
                            <span class="additional-info">
                                        <a href="#"><?php echo $value['spoiler']['title']; ?></a>
                                        <span class="info-spoiler"><?php echo stripslashes( $value['spoiler']['text'] ); ?></span>
                                    <span>
                                <?php endif; ?>
                        </td>
                    </tr>
                    <?php break;
            }

        }

    }

endif;