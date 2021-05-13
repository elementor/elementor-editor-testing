<?php
namespace ElementorTesting\Traits;

trait Mock_Data {

	private static $classes = [];

	public function mock_get_default_item( $class_name ) {
		return $this->get_class( $class_name )->get_default_item();
	}

	public function mock_get_default_items( $class_name, $count = 1 ) {
		$mock_class = $this->get_class( $class_name );
		$items = $mock_class->get_default_items();

		if ( ! empty( $items ) ) {
			return $items;
		}

		$default_item = $this->mock_get_default_item( $class_name );

		for ( $i = 0; $i !== $count; ++$i ) {
			$items [] = $default_item;
		}

		return $items;
	}

	/**
	 * @param string $class_name
	 *
	 * @return \ElementorTesting\Mock\Mock_Base
	 */
	private function get_class( $class_name ) {
		if ( in_array( $class_name, self::$classes ) ) {
			return self::$classes [ $class_name ];
		}

		self::$classes [ $class_name ] = new $class_name;

		return self::$classes [ $class_name ];
	}
}
