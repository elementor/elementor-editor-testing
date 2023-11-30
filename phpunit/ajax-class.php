<?php
namespace ElementorEditorTesting;

use ElementorEditorTesting\Traits\Base_Elementor;
use ElementorEditorTesting\Traits\Extra_Assertions;
use ElementorEditorTesting\Traits\Kit_Trait;

abstract class Elementor_Test_AJAX extends \WP_Ajax_UnitTestCase {
	use Base_Elementor, Extra_Assertions, Kit_Trait;

	public function setUp() : void {
		parent::setUp();

		$this->create_default_kit();
	}

	public function define_doing_ajax() {
		if ( ! wp_doing_ajax() ) {
			define( 'DOING_AJAX', true );
		}
	}

	public function _handleAjaxAndDecode( $action ) {
		try {
			$this->_handleAjax( $action );
		} catch ( \WPAjaxDieContinueException $e ) {
			unset( $e );
		} catch ( \WPAjaxDieStopException $e ) {
			// Do nothing.
		}

		return json_decode( $this->_last_response, true );
	}
}