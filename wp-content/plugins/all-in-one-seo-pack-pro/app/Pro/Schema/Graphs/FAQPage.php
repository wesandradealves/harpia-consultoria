<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs as CommonGraphs;

/**
 * FAQPage graph class.
 *
 * @since 4.0.13
 */
class FAQPage {
	/**
	 * Returns the subgraph(s)' data.
	 * We only return the subgraphs since all FAQ pages need to be grouped under a single main entity.
	 * We'll group them later on right before we return the schema as JSON.
	 *
	 * @since 4.0.13
	 *
	 * @param  Object $graphData The graph data.
	 * @param  bool   $isBlock   Whether the graph data is coming from a block.
	 * @return array             The parsed graph data.
	 */
	public function get( $graphData = null, $isBlock = false ) {
		if ( $isBlock ) {
			if ( ! empty( $graphData->question ) && ! empty( $graphData->answer ) ) {
				return [
					'@type'          => 'Question',
					'name'           => $graphData->question,
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => $graphData->answer
					]
				];
			}

			return [];
		}

		$faqPages = [];
		if ( ! empty( $graphData->properties->questions ) ) {
			foreach ( $graphData->properties->questions as $data ) {
				if ( empty( $data->question ) || empty( $data->answer ) ) {
					continue;
				}

				$faqPages[] = [
					'@type'          => 'Question',
					'name'           => $data->question,
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => $data->answer
					]
				];
			}
		}

		return $faqPages;
	}

	/**
	 * Returns the main FAQ graph with all its subgraphs (questions/answers).
	 *
	 * @since 4.2.3
	 *
	 * @param  array  $subGraphs The subgraphs.
	 * @param  Object $graphData The graph data (optional).
	 * @return array             The main graph data.
	 */
	public function getMainGraph( $subGraphs = [], $graphData = null ) {
		if ( empty( $subGraphs ) ) {
			return [];
		}

		return [
			'@type'       => 'FAQPage',
			'@id'         => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#faq',
			'name'        => ! empty( $graphData->properties->name ) ? $graphData->properties->name : '',
			'description' => ! empty( $graphData->properties->description ) ? $graphData->properties->description : '',
			'url'         => aioseo()->schema->context['url'],
			'mainEntity'  => $subGraphs,
			'inLanguage'  => get_bloginfo( 'language' ),
			'isPartOf'    => empty( $graphData ) ? [ '@id' => trailingslashit( home_url() ) . '#website' ] : '',
			'breadcrumb'  => [ '@id' => aioseo()->schema->context['url'] . '#breadcrumblist' ]
		];
	}
}