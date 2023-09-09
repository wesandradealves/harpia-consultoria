<?php
namespace AIOSEO\Plugin\Pro\Schema;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema as CommonSchema;

/**
 * Builds our schema.
 *
 * @since 4.0.13
 */
class Schema extends CommonSchema\Schema {
	/**
	 * The Pro subdirectories that contain graph classes.
	 *
	 * @since 4.2.5
	 *
	 * @var array
	 */
	private $proGraphSubDirectories = [
		'Music',
		'Product'
	];

	/**
	 * Instance of the FAQPage class.
	 *
	 * @since 4.2.6
	 *
	 * @var Graphs\FAQPage
	 */
	private $faqPageInstance;

	/**
	 * The user-defined FAQPage graph (if there is one).
	 *
	 * @since 4.2.6
	 *
	 * @var Object
	 */
	private $faqPageGraphData;

	/**
	 * Buffer to store FAQPage pairs before we output them under one main entity.
	 *
	 * @since 4.2.6
	 *
	 * @var array
	 */
	private $faqPages = [];

	/**
	 * Generates the JSON schema after the graphs/context have been determined.
	 *
	 * @since 4.2.5
	 *
	 * @param  array  $graphs       The graphs from the schema validator.
	 * @param  array  $customGraphs The graphs from the schema validator.
	 * @param  object $default      The default graph data.
	 * @param  bool   $isValidator  Whether the current call is for the validator.
	 * @return string               The JSON schema output.
	 */
	protected function generateSchema( $graphs = [], $customGraphs = [], $default = null, $isValidator = false ) {
		// Now, filter the graphs.
		$this->graphs = apply_filters(
			'aioseo_schema_graphs',
			array_unique( array_filter( array_values( $this->graphs ) ) )
		);

		if ( empty( $this->graphs ) ) {
			return '';
		}

		// Check if a WebPage graph is included. Otherwise add the default one.
		$webPageGraphFound = false;
		foreach ( $this->graphs as $graphName ) {
			if ( in_array( $graphName, $this->webPageGraphs, true ) ) {
				$webPageGraphFound = true;
				break;
			}
		}

		$post       = aioseo()->helpers->getPost();
		$metaData   = aioseo()->meta->metaData->getMetaData( $post );
		$postGraphs = ! empty( $graphs ) ? $graphs : [];
		if ( ! empty( $metaData->schema->graphs ) ) {
			$postGraphs = $metaData->schema->graphs;
		}

		if ( ! empty( $metaData->schema->default ) ) {
			$default = $metaData->schema->default;
		}

		foreach ( $postGraphs as $graphData ) {
			$graphData = (object) $graphData;

			if ( in_array( $graphData->graphName, $this->webPageGraphs, true ) ) {
				$webPageGraphFound = true;
				break;
			}
		}

		if (
			! empty( $default->isEnabled ) &&
			! empty( $default->graphName ) &&
			in_array( $default->graphName, [ 'FAQPage', 'WebPage' ], true )
		) {
			$webPageGraphFound = true;
		}

		if ( ! $webPageGraphFound ) {
			$this->graphs[] = 'WebPage';
		}

		// Now that we've determined the graphs, start generating their data.
		$schema = [
			'@context' => 'https://schema.org',
			// Let's first grab all the user-defined graphs (Schema Generator + blocks) if this a post.
			// We want to do this before we get the regular smart graphs since we want to give the user-defined graphs a chance to "enqueue" any smart graphs they might require.
			'@graph'   => $this->getUserDefinedGraphs( $graphs, $customGraphs, $default )
		];

		// By determining the length of the array after every iteration, we are able to add additional graphs during runtime.
		// e.g. The Article graph may require a Person graph to be output for the author.
		for ( $i = 0; $i < count( $this->graphs ); $i++ ) {
			$namespace = $this->getGraphNamespace( $this->graphs[ $i ] );
			if ( $namespace ) {
				$schema['@graph'][] = ( new $namespace() )->get();
				continue;
			}

			// If we still haven't found a graph, check the addons (e.g. Local Business).
			$graphData = $this->getAddonGraphData( $this->graphs[ $i ] );
			if ( ! empty( $graphData ) ) {
				$schema['@graph'][] = $graphData;
				continue;
			}
		}

		return aioseo()->schema->helpers->getOutput( $schema, $isValidator );
	}

	/**
	 * Gets the relevant namespace for the given graph.
	 *
	 * @since 4.2.5
	 *
	 * @param  string $graphName The graph name.
	 * @return string            The namespace.
	 */
	protected function getGraphNamespace( $graphName ) {
		// Check if a Pro graph exists.
		// We must do this before we check in the Common graphs in case we override one.
		$namespace = "\AIOSEO\Plugin\Pro\Schema\Graphs\\${graphName}";
		if ( class_exists( $namespace ) ) {
			return $namespace;
		}

		// If we can't find it in the root dir, check if we can find it in a sub dir.
		foreach ( $this->proGraphSubDirectories as $dirName ) {
			$namespace = "\AIOSEO\Plugin\Pro\Schema\Graphs\\{$dirName}\\{$graphName}";
			if ( class_exists( $namespace ) ) {
				return $namespace;
			}
		}

		$namespace = "\AIOSEO\Plugin\Common\Schema\Graphs\\{$graphName}";
		if ( class_exists( $namespace ) ) {
			return $namespace;
		}

		// If we can't find it in the root dir, check if we can find it in a sub dir.
		foreach ( $this->graphSubDirectories as $dirName ) {
			$namespace = "\AIOSEO\Plugin\Common\Schema\Graphs\\{$dirName}\\{$graphName}";
			if ( class_exists( $namespace ) ) {
				return $namespace;
			}
		}

		return '';
	}

	/**
	 * Returns the output for the user-defined graphs (Schema Generator + blocks).
	 *
	 * @since 4.2.5
	 *
	 * @param  array $graphs       The graphs from the validator.
	 * @param  array $customGraphs The custom graphs from the validator.
	 * @param  array $default      The default graph data.
	 * @return array               The graphs.
	 */
	private function getUserDefinedGraphs( $graphs = [], $customGraphs = [], $default = [] ) {
		// Get individual value.
		$post     = aioseo()->helpers->getPost();
		$metaData = aioseo()->meta->metaData->getMetaData( $post );
		if ( ! is_a( $post, 'WP_Post' ) || empty( $metaData->post_id ) ) {
			return [];
		}

		$graphs            = ! empty( $graphs ) ? $graphs : $metaData->schema->graphs;
		$userDefinedGraphs = [];
		foreach ( $graphs as $graphData ) {
			$graphData = (object) $graphData;

			if (
				empty( $graphData->id ) ||
				empty( $graphData->graphName ) ||
				empty( $graphData->properties )
			) {
				continue;
			}

			// If the graph has a subtype, this is the place where we need to replace the main graph name with the one of the subtype.
			if ( ! empty( $graphData->properties->type ) ) {
				$graphData->graphName = $graphData->properties->type;
			}

			switch ( $graphData->graphName ) {
				case 'FAQPage':
					if ( null === $this->faqPageInstance ) {
						$this->faqPageInstance = new Graphs\FAQPage();
					}

					// FAQ pages need to be collected first and added later because they should be nested under a parent graph.
					// We'll also store the data since we need it for the name/description properties.
					$this->faqPageGraphData = $graphData;
					$this->faqPages         = array_merge( $this->faqPages, $this->faqPageInstance->get( $graphData ) );
					break;
				default:
					$namespace = $this->getGraphNamespace( $graphData->graphName );
					if ( $namespace ) {
						$userDefinedGraphs[] = ( new $namespace() )->get( $graphData );
					}
					break;
			}
		}

		$customGraphs = ! empty( $customGraphs ) ? $customGraphs : $metaData->schema->customGraphs;
		foreach ( $customGraphs as $customGraphData ) {
			$customGraphData = (object) $customGraphData;

			if ( empty( $customGraphData->schema ) ) {
				continue;
			}

			$customSchema = json_decode( $customGraphData->schema, true );
			if ( ! empty( $customSchema ) ) {
				if ( isset( $customSchema['@graph'] ) && is_array( $customSchema['@graph'] ) ) {
					foreach ( $customSchema['@graph'] as $graph ) {
						$userDefinedGraphs[] = $graph;
					}
				} else {
					$userDefinedGraphs[] = $customSchema;
				}
			}
		}

		$default = ! empty( $default ) ? $default : $metaData->schema->default;
		if ( ! empty( $default->isEnabled ) && ! empty( $default->graphName ) ) {
			$graphData = ! empty( $default->data->{$default->graphName} ) ? $default->data->{$default->graphName} : [];
			$namespace = $this->getGraphNamespace( $default->graphName );

			switch ( $default->graphName ) {
				case 'FAQPage':
					if ( null === $this->faqPageInstance ) {
						$this->faqPageInstance = new Graphs\FAQPage();
					}

					// FAQ pages need to be collected first and added later because they should be nested under a parent graph.
					// We'll also store the data since we need it for the name/description properties.
					$graphData              = $default->data->FAQPage;
					$this->faqPageGraphData = $graphData;
					$this->faqPages         = array_merge( $this->faqPages, $this->faqPageInstance->get( $graphData ) );
					break;
				default:
					$namespace = $this->getGraphNamespace( $default->graphName );
					if ( $namespace ) {
						$userDefinedGraphs[] = ( new $namespace() )->get( $graphData );
					}
					break;
			}
		}

		$userDefinedGraphs = array_merge( $userDefinedGraphs, $this->getBlockGraphs() );

		$this->faqPages = array_filter( $this->faqPages );
		if ( ! empty( $this->faqPages ) && $this->faqPageInstance ) {
			$userDefinedGraphs[] = $this->faqPageInstance->getMainGraph( $this->faqPages, $this->faqPageGraphData );
		}

		return $userDefinedGraphs;
	}

	/**
	 * Returns the schema for all the schema supported blocks that are embedded into the post.
	 *
	 * @since 4.2.3
	 *
	 * @return array The schema graph data.
	 */
	private function getBlockGraphs() {
		$post = aioseo()->helpers->getPost();
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return [];
		}

		$metaData = aioseo()->meta->metaData->getMetaData( $post );
		if ( empty( $metaData->schema->blockGraphs ) ) {
			return [];
		}

		$graphs = [];
		foreach ( $metaData->schema->blockGraphs as $blockGraphData ) {
			// If the type isn't set for whatever reason, then bail.
			if ( empty( $blockGraphData->type ) ) {
				continue;
			}

			$type = strtolower( $blockGraphData->type );
			switch ( $type ) {
				case 'aioseo/faq':
					if ( null === $this->faqPageInstance ) {
						$this->faqPageInstance = new Graphs\FAQPage();
					}

					// FAQ pages need to be collected first and added later because they should be nested under a parent graph.
					$this->faqPages[] = $this->faqPageInstance->get( $blockGraphData, true );
					break;
				default:
					break;
			}
		}

		return $graphs;
	}

	/**
	 * Determines the smart graphs that need to be build, as well as the current context for the breadcrumbs.
	 *
	 * This can't run in the constructor since the queried object needs to be available first.
	 *
	 * @since 4.2.5
	 *
	 * @param  bool $isValidator Whether the current call is for the validator.
	 * @return void
	 */
	protected function determineSmartGraphsAndContext( $isValidator = false ) {
		parent::determineSmartGraphsAndContext( $isValidator );

		$loadedAddons = aioseo()->addons->getLoadedAddons();
		if ( empty( $loadedAddons ) ) {
			return;
		}

		// Check if our addons need to register graphs.
		foreach ( $loadedAddons as $loadedAddon ) {
			if ( ! empty( $loadedAddon->schema ) && method_exists( $loadedAddon->schema, 'determineGraphsAndContext' ) ) {
				$this->graphs = array_values( array_merge( $this->graphs, $loadedAddon->schema->determineGraphsAndContext() ) );
			}
		}
	}

	/**
	 * Determines the smart graphs and context for singular pages.
	 *
	 * @since 4.2.6
	 *
	 * @param  Context $contextInstance The Context class instance.
	 * @param  bool    $isValidator     Whether we're getting the output for the validator.
	 * @return void
	 */
	protected function determineContextSingular( $contextInstance, $isValidator ) { // phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
		// Check if we're on a BuddyPress member page.
		if ( function_exists( 'bp_is_user' ) && bp_is_user() ) {
			$this->graphs[] = 'ProfilePage';
		}

		$this->context = $contextInstance->post();
	}

	/**
	 * Returns the default graph for the current post.
	 *
	 * @since 4.2.6
	 *
	 * @return string The default graph.
	 */
	public function getDefaultPostGraph() {
		$metaData = aioseo()->meta->metaData->getMetaData();
		if ( isset( $metaData->schema->default->graphName ) ) {
			return $metaData->schema->default->graphName;
		}

		return $this->getDefaultPostTypeGraph();
	}

	/**
	 * Gets the graph data from our addons.
	 *
	 * @since 4.0.17
	 *
	 * @param  string $graphName The graph name.
	 * @return array             The graph data.
	 */
	public function getAddonGraphData( $graphName ) {
		$loadedAddons = aioseo()->addons->getLoadedAddons();
		if ( empty( $loadedAddons ) ) {
			return [];
		}

		foreach ( $loadedAddons as $loadedAddon ) {
			if ( ! empty( $loadedAddon->schema ) && method_exists( $loadedAddon->schema, 'get' ) ) {
				$graphData = $loadedAddon->schema->get( $graphName );
				if ( ! empty( $graphData ) ) {
					return $graphData;
				}
			}
		}

		return [];
	}

	/**
	 * Returns the simulated schema output for the Schema Validator in the post editor.
	 *
	 * @since 4.2.5
	 *
	 * @param  int    $postId       The post ID.
	 * @param  array  $graphs       The graphs from the schema validator.
	 * @param  array  $customGraphs The custom graphs from the schema validator.
	 * @param  array  $default      The default graph data.
	 * @return string               The JSON schema output.
	 */
	public function getValidatorOutput( $postId, $graphs, $customGraphs, $default ) {
		$postObject = aioseo()->helpers->getPost( $postId );
		if ( ! is_a( $postObject, 'WP_Post' ) ) {
			return '';
		}

		global $wp_query, $post;
		$originalQuery = is_object( $wp_query ) ? clone $wp_query : $wp_query;
		$originalPost  = is_object( $post ) ? clone $post : $post;
		$isNewPost     = ! empty( $originalPost ) && ! $originalPost->post_title && ! $originalPost->post_name && 'auto-draft' === $originalPost->post_status;

		// Only modify the query if there is no post on it set yet.
		// Otherwise page builders like Divi and Elementor can't seem to load their visual builder.
		if ( empty( $originalQuery->post ) ) {
			$post                        = $postObject;
			$wp_query->post              = $postObject;
			$wp_query->posts             = [ $postObject ];
			$wp_query->post_count        = 1;
			$wp_query->queried_object    = $postObject;
			$wp_query->queried_object_id = $postId;
			$wp_query->is_single         = true;
			$wp_query->is_singular       = true;
		}

		$this->determineSmartGraphsAndContext();

		$output = $this->generateSchema( $graphs, $customGraphs, $default, true );

		// Reset the global objects.
		if ( empty( $originalQuery->post ) ) {
			$wp_query = $originalQuery;
			$post     = $originalPost;
		}

		// We must reset the title for new posts because they will be given a "Auto Draft" one due to the schema class determining the schema output for the validator.
		if ( $isNewPost ) {
			$post->post_title = '';
		}

		return $output;
	}
}