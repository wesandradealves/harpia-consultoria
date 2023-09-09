<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs as CommonGraphs;

/**
 * Event class.
 *
 * @since 4.2.5
 */
class Event extends CommonGraphs\Graph {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.2.5
	 *
	 * @param  Object $graphData The graph data.
	 * @return array             The parsed graph data.
	 */
	public function get( $graphData = null ) {
		if ( ! aioseo()->license->hasCoreFeature( 'schema', 'event' ) ) {
			return [];
		}

		$data = [
			'@type'               => ! empty( $graphData->properties->eventType ) ? $graphData->properties->eventType : 'Event',
			'@id'                 => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#event',
			'name'                => ! empty( $graphData->properties->name ) ? $graphData->properties->name : get_the_title(),
			'description'         => ! empty( $graphData->properties->description ) ? $graphData->properties->description : '',
			'eventStatus'         => ! empty( $graphData->properties->status ) ? 'https://schema.org/' . $graphData->properties->status : 'https://schema.org/EventScheduled',
			'eventAttendanceMode' => ! empty( $graphData->properties->attendanceMode ) ? 'https://schema.org/' . $graphData->properties->attendanceMode : 'https://schema.org/OfflineEventAttendanceMode', // phpcs:ignore Generic.Files.LineLength.MaxExceeded
			'startDate'           => ! empty( $graphData->properties->dates->startDate ) ? mysql2date( DATE_W3C, $graphData->properties->dates->startDate, false ) : '',
			'endDate'             => ! empty( $graphData->properties->dates->endDate ) ? mysql2date( DATE_W3C, $graphData->properties->dates->endDate, false ) : '',
			'location'            => [],
			'offers'              => [],
			'organizer'           => [],
			'performer'           => [],
			'image'               => []
		];

		if ( ! empty( $graphData->properties->location ) ) {
			$data['location'] = [
				'@type'   => 'Place',
				'name'    => ! empty( $graphData->properties->location->name ) ? $graphData->properties->location->name : '',
				'url'     => ! empty( $graphData->properties->location->url ) ? $graphData->properties->location->url : '',
				'address' => [
					'streetAddress'   => ! empty( $graphData->properties->location->streetAddress ) ? $graphData->properties->location->streetAddress : '',
					'addressLocality' => ! empty( $graphData->properties->location->locality ) ? $graphData->properties->location->locality : '',
					'postalCode'      => ! empty( $graphData->properties->location->postalCode ) ? $graphData->properties->location->postalCode : '',
					'addressRegion'   => ! empty( $graphData->properties->location->region ) ? $graphData->properties->location->region : '',
					'addressCountry'  => ! empty( $graphData->properties->location->country ) ? $graphData->properties->location->country : ''
				]
			];
		}

		if ( ! empty( $graphData->properties->offer ) ) {
			$data['offers'] = [
				'@type'         => 'Offer',
				'url'           => ! empty( $graphData->properties->offer->url ) ? $graphData->properties->offer->url : '',
				'price'         => ! empty( $graphData->properties->offer->price ) ? (float) $graphData->properties->offer->price : 0,
				'priceCurrency' => ! empty( $graphData->properties->offer->currency ) ? $graphData->properties->offer->currency : '',
				'validFrom'     => ! empty( $graphData->properties->offer->validFrom )
					? aioseo()->helpers->dateToIso8601( $graphData->properties->offer->validFrom )
					: '',
				'availability'  => ! empty( $graphData->properties->offer->availability ) ? $graphData->properties->offer->availability : 'https://schema.org/InStock'
			];
		}

		if ( ! empty( $graphData->properties->organizer->type ) ) {
			$data['organizer'] = [
				'@type' => $graphData->properties->organizer->type,
				'name'  => $graphData->properties->organizer->name,
				'url'   => $graphData->properties->organizer->url
			];

			// If name is empty and the type is organization, fall back to the global one.
			if (
				empty( $graphData->properties->organizer->name ) &&
				'Organization' === $graphData->properties->organizer->type &&
				'organization' === aioseo()->options->searchAppearance->global->schema->siteRepresents
			) {
				$homeUrl        = trailingslashit( home_url() );
				$data['organizer'] = [
					'@type' => 'Organization',
					'@id'   => $homeUrl . '#organization',
				];
			}
		}

		if ( ! empty( $graphData->properties->performer->type ) && ! empty( $graphData->properties->performer->name ) ) {
			$data['performer'] = [
				'@type' => $graphData->properties->performer->type,
				'name'  => $graphData->properties->performer->name,
				'url'   => $graphData->properties->performer->url
			];
		}

		if ( ! empty( $graphData->properties->images ) ) {
			foreach ( $graphData->properties->images as $image ) {
				if ( empty( $image->url ) ) {
					continue;
				}

				$data['image'][] = $this->image( $image->url );
			}
		}

		return $data;
	}
}