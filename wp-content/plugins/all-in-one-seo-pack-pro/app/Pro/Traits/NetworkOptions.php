<?php
namespace AIOSEO\Plugin\Pro\Traits;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NetworkOptions trait.
 *
 * @since 4.0.0
 */
trait NetworkOptions {
	/**
	 * Initialize the options.
	 *
	 * @since 4.2.5
	 *
	 * @return void
	 */
	protected function init() {
		parent::init();

		if ( ! is_multisite() ) {
			return;
		}

		aioseo()->helpers->switchToBlog( $this->helpers->getNetworkId() );

		$dbOptions = $this->getDbOptions( $this->optionsName . '_pro' );

		// Refactor options.
		$this->defaultsMerged = aioseo()->helpers->arrayReplaceRecursive( $this->defaults, $this->proDefaults );

		$mergedDefaults = aioseo()->helpers->arrayReplaceRecursive(
			$this->proDefaults,
			$this->addValueToValuesArray( $this->proDefaults, $dbOptions )
		);

		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );
		$dbOptions     = aioseo()->helpers->arrayReplaceRecursive(
			$cachedOptions,
			$mergedDefaults
		);

		aioseo()->core->optionsCache->setOptions( $this->optionsName, $dbOptions );

		aioseo()->helpers->restoreCurrentBlog();
	}

	/**
	 * Merge defaults with proDefaults.
	 *
	 * @since 4.2.5
	 *
	 * @return array An array of dafults.
	 */
	public function getDefaults() {
		if ( ! is_multisite() ) {
			return [];
		}

		aioseo()->helpers->switchToBlog( $this->helpers->getNetworkId() );

		$defaults = aioseo()->helpers->arrayReplaceRecursive( parent::getDefaults(), $this->proDefaults );

		aioseo()->helpers->restoreCurrentBlog();

		return $defaults;
	}

	/**
	 * Updates the options in the database.
	 *
	 * @since 4.2.5
	 *
	 * @param  string     $optionsName An optional option name to update.
	 * @param  string     $defaults    The defaults to filter the options by.
	 * @param  array|null $options     An optional options array.
	 * @return void
	 */
	public function update( $optionsName = null, $defaults = null, $options = null ) {
		if ( ! is_multisite() ) {
			return;
		}

		aioseo()->helpers->switchToBlog( $this->helpers->getNetworkId() );

		$optionsName = empty( $optionsName ) ? $this->optionsName . '_pro' : $optionsName;
		$defaults    = empty( $defaults ) ? $this->proDefaults : $defaults;

		// We're creating a new array here because it was setting it by reference.
		$cachedOptions = aioseo()->core->optionsCache->getOptions( $this->optionsName );
		$optionsBefore = json_decode( wp_json_encode( $cachedOptions ), true );

		parent::update( $this->optionsName, $options );
		parent::update( $optionsName, $defaults, $optionsBefore );

		aioseo()->helpers->restoreCurrentBlog();
	}

	/**
	 * Updates the options in the database.
	 *
	 * @since 4.2.5
	 *
	 * @param  boolean $force       Whether or not to force an immediate save.
	 * @param  string  $optionsName An optional option name to update.
	 * @param  string  $defaults    The defaults to filter the options by.
	 * @return void
	 */
	public function save( $force = false, $optionsName = null, $defaults = null ) {
		if ( ! is_multisite() ) {
			return;
		}

		if ( ! $this->shouldSave && ! $force ) {
			return;
		}

		aioseo()->helpers->switchToBlog( $this->helpers->getNetworkId() );

		$optionsName = empty( $optionsName ) ? $this->optionsName . '_pro' : $optionsName;
		$defaults    = empty( $defaults ) ? $this->proDefaults : $defaults;

		parent::save( $force, $this->optionsName );
		parent::save( $force, $optionsName, $defaults );

		aioseo()->helpers->restoreCurrentBlog();
	}
}