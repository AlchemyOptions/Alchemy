<?php

if( ! function_exists( 'alch_get_option' ) ) {
    function alch_get_option( $id, $default = '' ) {
        return alch_get_prepared_value(
            get_option( $id ), $id, $default
        );
    }
}

if ( ! function_exists( 'alch_get_post_meta' ) ) {
    function alch_get_post_meta( $postID, $metaID, $default = '' ) {
        return alch_get_prepared_value(
            get_post_meta( $postID, $metaID, true ), $metaID, $default
        );
    }
}

if( ! function_exists( 'alch_get_prepared_value' ) ) {
    function alch_get_prepared_value( $saved, $id, $default ) {
        if( empty( $saved ) ) {
            return $default;
        }

        $repeater = Alchemy\Options::get_repeater_id_details( $saved['type'] );
        $valueType = $repeater ? 'repeater' : $saved['type'];

        $savedValue = apply_filters( "alch_prepare_{$valueType}_value", $saved['value'], $id );

        if( ! empty( $savedValue ) ) {
            return $savedValue;
        }

        return $default;
    }
}

if( ! function_exists( 'alch_admin_get_saved_option' ) ) {
    function alch_admin_get_saved_option( $id, $default = '' ) {
        $savedOption = get_option( $id );
        $value = $default;

        if( ! empty( $savedOption ) ) {
            $value = $savedOption['value'];
        }

        return $value;
    }
}

if( ! function_exists( 'alch_admin_get_saved_network_option' ) ) {
    function alch_admin_get_saved_network_option( $id, $default = '' ) {
        $savedOption = get_site_option( $id );
        $value = $default;

        if( ! empty( $savedOption ) ) {
            $value = $savedOption['value'];
        }

        return $value;
    }
}

if( ! function_exists( 'alch_admin_get_saved_meta' ) ) {
    function alch_admin_get_saved_meta( $postID, $id, $default = '' ) {
        $savedValue = get_post_meta( $postID, $id, true );
        $value = $default;

        if( ! empty( $savedValue ) ) {
            $value = $savedValue['value'];
        }

        return $value;
    }
}

if( ! function_exists( 'alch_admin_delete_option' ) ) {
    function alch_admin_delete_option( $id ) {
        return update_option( $id, '' );
    }
}

if( ! function_exists( 'alch_admin_get_field_label' ) ) {
    function alch_admin_get_field_label( $data, $useLabel ) {
        if( $useLabel ) {
            return sprintf( '<label class="field__label" for="%1$s">%2$s</label>', $data['id'], $data['title'] );
        }

        return sprintf( '<h3 class="field__label">%s</h3>', $data['title'] );
    }
}

if( ! function_exists( 'alch_admin_get_field_description' ) ) {
    function alch_admin_get_field_description( $text ) {
        return sprintf( '<div class="field__description">%s</div>', $text );
    }
}

if( ! function_exists( 'alch_admin_get_field_sidebar' ) ) {
    function alch_admin_get_field_sidebar( $data, $useLabel = true ) {
        $html = '';

        if( ! empty( $data['title'] ) || ! empty( $data['desc'] ) ) {
            $html .= '<div class="field__sidebar">';

            if( ! empty( $data['title'] ) ) {
                $html .= alch_admin_get_field_label( $data, $useLabel );
            }

            if( ! empty( $data['desc'] ) ) {
                $html .= alch_admin_get_field_description( $data['desc'] );
            }

            $html .= '</div>';
        }

        return $html;
    }
}

if( ! function_exists( 'alch_get_validation_tooltip' ) ) {
    function alch_get_validation_tooltip() {
        return '<span class="tooltip jsAlchemyValidationTooltip" role="tooltip"><span class="jsAlchemyTooltipText"></span><span class="tooltip__arrow" data-popper-arrow></span></span>';
    }
}