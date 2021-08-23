<?php
namespace ElementorEditorTesting;

use Elementor\Core\Utils\Collection;
use Elementor\Tracker;
use JsonSchema\Constraints\Factory;
use JsonSchema\Exception\ValidationException;
use JsonSchema\SchemaStorage;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\Validator;

abstract class Base_Schema extends Elementor_Test_Base {

	const HTTP_USER_AGENT = 'test-agent';

	/**
	 * @var UriRetriever
	 */
	public $uriRetriever;

	/**
	 * @var SchemaStorage
	 */
	public $refResolver;

	public function __construct( $name = null, array $data = [], $dataName = '' ) {
		parent::__construct( $name, $data, $dataName );

		$this->uriRetriever = new UriRetriever();
		$this->refResolver = new SchemaStorage( $this->uriRetriever );
	}

	public function setUp() {
		parent::setUp();

		// Required by `Tracker::get_tracking_data`.
		$_SERVER['HTTP_USER_AGENT'] = self::HTTP_USER_AGENT;
	}

	protected function validate_against_schema( $data_for_validation, $schema_file ) {
		// Since the usage system represents objects as array instead of stdClass.
		$data_for_validation = json_decode( json_encode( $data_for_validation ) );

		// Validate
		$validator = new Validator( new Factory( $this->refResolver ) );
		$validator->validate( $data_for_validation, $this->get_schema_by_file( $schema_file ) );

		if ( ! $validator->isValid() ) {
			$error_message = 'JSON does not validate. Violations:' . PHP_EOL;
			foreach ( $validator->getErrors() as $error ) {
				$error_message .= sprintf( '[%s] %s' . PHP_EOL, $error['property'], $error['message'] );
			}

			throw new ValidationException( $error_message );
		}

		return true;
	}

	protected function validate_current_tracking_data_against_schema( $schema_file ) {
		return $this->validate_against_schema( Tracker::get_tracking_data(), $schema_file );
	}

	protected function assert_schema_has_no_additional_properties( $schema_file ) {
		$json_schema_object = $this->get_schema_by_file( $schema_file );

		$schema_storage = new SchemaStorage();
		$schema_storage->addSchema( 'validation', $json_schema_object );

		$properties_all_objects_recursive = function ( $node, callable $callback ) use ( &$properties_all_objects_recursive ) {
			if ( ! ( $node instanceof \stdClass ) ) {
				return;
			}

			if ( isset( $node->properties ) || isset( $node->patternProperties ) ) {
				$callback( $node );
			}

			foreach ( $node as $part ) {
				$properties_all_objects_recursive( $part, $callback );
			}
		};

		// Act.
		$usage_schema = $schema_storage->getSchema( 'validation' );

		// Assert.
		$properties_all_objects_recursive( $usage_schema, function ( $node ) {
			$id = $node->{'$id'};
			$this->assertTrue( isset( $node->additionalProperties ), "Ensure node: '$id' 'additionalProperties' exists" );
			$this->assertFalse( $node->additionalProperties, "Ensure node: '$id' 'additionalProperties' is false" );
		} );
	}

	protected function get_schema_by_file( $schema_file ) {
		$schema = $this->refResolver->resolveRef( 'file://' . $schema_file );

		if ( ! empty( $schema->{'$merge'} ) ) {
			$original_schema = json_decode( file_get_contents( $schema_file ), JSON_OBJECT_AS_ARRAY );
			$schema_to_merge = json_decode( file_get_contents( $schema->{'$merge'} ), JSON_OBJECT_AS_ARRAY );

			$merged_dst = get_temp_dir() . basename( $schema_file );

			file_put_contents( $merged_dst, json_encode( $this->custom_merge_recursive( $original_schema, $schema_to_merge ) ) );

			$schema = $this->refResolver->resolveRef( 'file://' . $merged_dst );
		}

		return $schema;
	}

	/**
	 * TODO: Optimize.
	 *
	 * @param array $original_schema
	 * @param array $schema_to_merge
	 *
	 * @return array
	 */
	private function custom_merge_recursive( $original_schema, $schema_to_merge ) {
		$get_value_by_path = function ( $path, $data ) {
			$current = $data;

			foreach ( $path as $key ) {
				if ( ! isset( $current[ $key ] ) ) {
					return null;
				}
				$current = $current[ $key ];
			}

			return $current;
		};

		$map_custom_recursive = function ( $callback, $needle ) use ( &$map_custom_recursive ) {
			$result = [];
			static $path = [];

			foreach ( $needle as $key => $value ) {
				$path [] = $key;

				$result[ $key ] = $callback( $value, $path );

				if ( is_array( $value ) ) {
					$map_custom_recursive( $callback, $value );
				}

				array_pop( $path );
			}

			return $result;
		};

		return $map_custom_recursive( function ( $value, $path ) use ( $original_schema, $get_value_by_path ) {
			$original_value = $get_value_by_path( $path, $original_schema );

			if ( null === $original_value ) {
				return $value;
			}

			if ( is_array( $original_value ) ) {
				return array_merge_recursive( $original_value, $value );
			}

			return $original_value;
		}, $schema_to_merge );
	}
}
