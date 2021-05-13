<?php
namespace ElementorTesting;

use Elementor\Plugin;
use Elementor\Core\Wp_Api;
use ElementorTesting\Traits\Auth_Helpers;
use ElementorTesting\Traits\Base_Elementor;
use ElementorTesting\Traits\Extra_Assertions;

abstract class Elementor_Test_Base extends \WP_UnitTestCase {
	use Base_Elementor, Extra_Assertions, Auth_Helpers;

	public function setUp() {
		parent::setUp();

		set_current_screen( 'dashboard' );
	}

	public function tearDown() {
		parent::tearDown();

		Plugin::$instance->editor->set_edit_mode( false );
		Plugin::$instance->documents->restore_document();
		Plugin::$instance->editor->set_edit_mode( false );
		Plugin::$instance->wp = new Wp_Api();
	}
}
