<?php
namespace ElementorEditorTesting\Traits;

use Elementor\Core\Wp_Api;
use Elementor\Plugin;
use ElementorEditorTesting\Factories\Factory;

trait Base_Elementor {

	/**
	 * @var Plugin
	 */
	private static $elementor;

	/**
	 * @param $name
	 *
	 * @return Plugin|Factory
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'elementor':
				return self::elementor();
		}
	}

	/**
	 * Extends the wordpress factory.
	 *
	 * @return Factory
	 */
	protected static function factory() {
		static $factory = null;
		if ( ! $factory ) {
			$factory = new Factory();
		}

		return $factory;
	}

	/**
	 * @return Plugin
	 */
	protected static function elementor() {
		if ( ! self::$elementor ) {
			self::$elementor = Plugin::instance();
		}

		return self::$elementor;
	}

	/**
	 * @param array $methods
	 *
	 * @return \PHPUnit\Framework\MockObject\MockObject
	 */
	protected function mock_wp_api( array $methods = [] ) {
		$mock = $this->getMockBuilder( Wp_Api::class )->setMethods( array_keys( $methods ) )->getMock();

		foreach ( $methods as $method_name => $method_return ) {
			$mock->method( $method_name )->willReturn( $method_return );
		}

		Plugin::$instance->wp = $mock;

		return $mock;
	}
}
