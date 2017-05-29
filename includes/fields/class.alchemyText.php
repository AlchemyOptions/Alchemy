<?php
//no direct access allowed
if( ! defined( 'ALCHEMY_OPTIONS_VERSION' ) ) {
    exit;
}

if( ! class_exists( 'Alchemy_Text_Field' ) ) {

    class Alchemy_Text_Field extends Alchemy_Field {
        public function __construct() {
            parent::__construct();

            $this->template = '
                <div class="alchemy__field field field--{{TYPE}}" id="field--{{ID}}" data-alchemy=\'{"id":"{{ID}}","type":"{{TYPE}}"}\'>
                    <label class="field__label" for="{{ID}}">{{TITLE}}</label>
                    <input {{ATTRIBUTES}} />
                    <div class="field__description">
                        <p>{{DESCRIPTION}}</p>
                    </div>
                </div>
            ';
        }

        public function normalize_field_keys( $field ) {
            $field = parent::normalize_field_keys( $field );

            $field[ 'attributes' ] = $this->concat_attributes(array(
                'type' => $field[ 'type' ],
                'id' => $field[ 'id' ],
                'value' => $field[ 'value' ]
            ));

            return $field;
        }
    }
}