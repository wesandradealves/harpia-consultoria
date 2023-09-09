<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs as CommonGraphs;

/**
 * Recipe graph class.
 *
 * @since 4.0.13
 */
class Recipe extends CommonGraphs\Graph {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.0.13
	 *
	 * @param  Object $graphData The graph data.
	 * @return array             The parsed graph data.
	 */
	public function get( $graphData = null ) {
		$data = [
			'@type'              => 'Recipe',
			'@id'                => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#recipe',
			'name'               => ! empty( $graphData->properties->name ) ? $graphData->properties->name : get_the_title(),
			'description'        => ! empty( $graphData->properties->description ) ? $graphData->properties->description : '',
			'author'             => [
				'@type' => 'Person',
				'name'  => ! empty( $graphData->properties->author ) ? $graphData->properties->author : get_the_author_meta( 'display_name' )
			],
			'image'              => ! empty( $graphData->properties->image ) ? $this->image( $graphData->properties->image ) : $this->getFeaturedImage(),
			'recipeCategory'     => ! empty( $graphData->properties->dishType ) ? $graphData->properties->dishType : '',
			'recipeCuisine'      => ! empty( $graphData->properties->cuisineType ) ? $graphData->properties->cuisineType : '',
			'prepTime'           => '',
			'cookTime'           => '',
			'totalTime'          => '',
			'recipeYield'        => ! empty( $graphData->properties->nutrition->servings ) ? $graphData->properties->nutrition->servings : '',
			'nutrition'          => [],
			'recipeIngredient'   => [],
			'recipeInstructions' => [],
			'keywords'           => ''
		];

		if ( ! empty( $graphData->properties->timeRequired->preparation ) && ! empty( $graphData->properties->timeRequired->cooking ) ) {
			$data['prepTime']  = aioseo()->helpers->timeToIso8601DurationFormat( 0, 0, $graphData->properties->timeRequired->preparation );
			$data['cookTime']  = aioseo()->helpers->timeToIso8601DurationFormat( 0, 0, $graphData->properties->timeRequired->cooking );

			$totalTime         = (int) $graphData->properties->timeRequired->preparation + (int) $graphData->properties->timeRequired->cooking;
			$data['totalTime'] = aioseo()->helpers->timeToIso8601DurationFormat( 0, 0, $totalTime );
		}

		if ( ! empty( $graphData->properties->nutrition->servings ) && ! empty( $graphData->properties->nutrition->calories ) ) {
			$data['nutrition'] = [
				'@type'    => 'NutritionInformation',
				'calories' => $graphData->properties->nutrition->calories . ' ' . __( 'Calories', 'aioseo-pro' )
			];
		}

		if ( ! empty( $graphData->properties->keywords ) ) {
			$keywords = json_decode( $graphData->properties->keywords, true );
			$keywords = array_map( function ( $keywordObject ) {
				return $keywordObject['value'];
			}, $keywords );
			$data['keywords'] = implode( ', ', $keywords );
		}

		if ( ! empty( $graphData->properties->ingredients ) ) {
			$ingredients = json_decode( $graphData->properties->ingredients, true );
			$ingredients = array_map( function ( $ingredientObject ) {
				return $ingredientObject['value'];
			}, $ingredients );
			$data['recipeIngredient'] = $ingredients;
		}

		if ( ! empty( $graphData->properties->instructions ) ) {
			foreach ( $graphData->properties->instructions as $instructionData ) {
				if ( empty( $instructionData->text ) ) {
					continue;
				}

				$data['recipeInstructions'][] = [
					'@type' => 'HowToStep',
					'name'  => $instructionData->name,
					'text'  => $instructionData->text,
					'image' => $instructionData->image
				];
			}
		}

		return $data;
	}
}