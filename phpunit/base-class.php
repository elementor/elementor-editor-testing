<?php
namespace ElementorEditorTesting;

use Elementor\Core\Wp_Api;
use Elementor\Plugin;
use ElementorEditorTesting\Traits\Auth_Helpers;
use ElementorEditorTesting\Traits\Base_Elementor;
use ElementorEditorTesting\Traits\Extra_Assertions;
use ElementorEditorTesting\Traits\Kit_Trait;

abstract class Elementor_Test_Base extends \WP_UnitTestCase {

	use Base_Elementor, Extra_Assertions, Auth_Helpers, Kit_Trait;

	public function setUp(): void {
		parent::setUp();

		$this->create_default_kit();

		set_current_screen( 'dashboard' );
	}

	public function tearDown(): void {
		parent::tearDown();

		Plugin::$instance->editor->set_edit_mode( false );
		Plugin::$instance->documents->restore_document();
		Plugin::$instance->editor->set_edit_mode( false );
		Plugin::$instance->wp = new Wp_Api();
	}
}
