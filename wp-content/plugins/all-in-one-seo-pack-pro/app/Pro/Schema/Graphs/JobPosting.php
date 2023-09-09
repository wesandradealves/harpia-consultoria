<?php
namespace AIOSEO\Plugin\Pro\Schema\Graphs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use AIOSEO\Plugin\Common\Schema\Graphs as CommonGraphs;

/**
 * JobPosting graph class.
 *
 * @since 4.2.5
 */
class JobPosting extends CommonGraphs\Graph {
	/**
	 * Returns the graph data.
	 *
	 * @since 4.2.5
	 *
	 * @param  Object $graphData The graph data.
	 * @return array             The parsed graph data.
	 */
	public function get( $graphData = null ) {
		if ( ! aioseo()->license->hasCoreFeature( 'schema', 'job-posting' ) ) {
			return [];
		}

		$data = [
			'@type'                         => 'JobPosting',
			'@id'                           => ! empty( $graphData->id ) ? aioseo()->schema->context['url'] . $graphData->id : aioseo()->schema->context['url'] . '#jobPosting',
			'title'                         => ! empty( $graphData->properties->name ) ? $graphData->properties->name : get_the_title(),
			'description'                   => ! empty( $graphData->properties->description ) ? $graphData->properties->description : aioseo()->schema->context['description'],
			'employmentType'                => ! empty( $graphData->properties->employmentType ) ? $graphData->properties->employmentType : '',
			'jobLocationType'               => ! empty( $graphData->properties->remote ) ? 'TELECOMMUTE' : '',
			'datePosted'                    => '',
			'validThrough'                  => '',
			'hiringOrganization'            => [],
			'jobLocation'                   => [],
			'applicantLocationRequirements' => [],
			'baseSalary'                    => [],
			'educationRequirements'         => [],
			'experienceRequirements'        => [],
			'experienceInPlaceOfEducation'  => ! empty( $graphData->properties->requirements->experienceInsteadOfEducation )
				? $graphData->properties->requirements->experienceInsteadOfEducation
				: false
		];

		if ( ! empty( $graphData->properties->dates ) ) {
			$post = aioseo()->helpers->getPost();

			$data['datePosted'] = ! empty( $graphData->properties->dates->datePosted )
				? mysql2date( DATE_W3C, $graphData->properties->dates->datePosted, false )
				: mysql2date( DATE_W3C, $post->post_date_gmt, false );

			$data['validThrough'] = ! empty( $graphData->properties->dates->dateExpires )
				? mysql2date( DATE_W3C, $graphData->properties->dates->dateExpires, false )
				: '';
		}

		if ( ! empty( $graphData->properties->hiringOrganization ) ) {
			$data['hiringOrganization'] = [
				'@type'  => 'Organization',
				'name'   => ! empty( $graphData->properties->hiringOrganization->name ) ? $graphData->properties->hiringOrganization->name : '',
				'sameAs' => ! empty( $graphData->properties->hiringOrganization->url ) ? $graphData->properties->hiringOrganization->url : '',
				'logo'   => ! empty( $graphData->properties->hiringOrganization->image ) ? $this->image( $graphData->properties->hiringOrganization->image ) : ''
			];

			// If name is empty, fall back to the global one.
			if (
				empty( $graphData->properties->hiringOrganization->name ) &&
				'organization' === aioseo()->options->searchAppearance->global->schema->siteRepresents
			) {
				$homeUrl                    = trailingslashit( home_url() );
				$data['hiringOrganization'] = [
					'@type' => 'Organization',
					'@id'   => $homeUrl . '#organization',
				];
			}
		}

		if ( ! empty( $graphData->properties->remote ) && ! empty( $graphData->properties->locations ) ) {
			foreach ( $graphData->properties->locations as $location ) {
				if ( empty( $location->type ) || empty( $location->name ) ) {
					continue;
				}

				$data['applicantLocationRequirements'][] = [
					'@type' => $location->type,
					'name'  => $location->name
				];
			}
		}

		if ( empty( $graphData->properties->remote ) && ! empty( $graphData->properties->location ) ) {
			$data['jobLocation'] = [
				'@type'   => 'Place',
				'address' => [
					'streetAddress'   => ! empty( $graphData->properties->location->streetAddress ) ? $graphData->properties->location->streetAddress : '',
					'addressLocality' => ! empty( $graphData->properties->location->locality ) ? $graphData->properties->location->locality : '',
					'postalCode'      => ! empty( $graphData->properties->location->postalCode ) ? $graphData->properties->location->postalCode : '',
					'addressRegion'   => ! empty( $graphData->properties->location->region ) ? $graphData->properties->location->region : '',
					'addressCountry'  => ! empty( $graphData->properties->location->country ) ? $graphData->properties->location->country : ''
				]
			];
		}

		if (
			! empty( $graphData->properties->salary->minimum ) &&
			! empty( $graphData->properties->salary->maximum ) &&
			! empty( $graphData->properties->salary->interval )
		) {
			$data['baseSalary'] = [
				'@type'    => 'MonetaryAmount',
				'currency' => ! empty( $graphData->properties->salary->currency ) ? $graphData->properties->salary->currency : '',
				'value'    => [
					'@type'    => 'QuantitativeValue',
					'minValue' => ! empty( $graphData->properties->salary->minimum ) ? $graphData->properties->salary->minimum : 0,
					'maxValue' => ! empty( $graphData->properties->salary->maximum ) ? $graphData->properties->salary->maximum : 0,
					'unitText' => ! empty( $graphData->properties->salary->interval ) ? $graphData->properties->salary->interval : ''
				]
			];
		}

		if ( ! empty( $graphData->properties->requirements->experience ) ) {
			$data['experienceRequirements'] = [
				'@type'              => 'OccupationalExperienceRequirements',
				'monthsOfExperience' => $graphData->properties->requirements->experience
			];
		}

		if ( ! empty( $graphData->properties->requirements->degree ) ) {
			$data['educationRequirements'] = [
				'@type'              => 'EducationalOccupationalCredential',
				'credentialCategory' => $graphData->properties->requirements->degree
			];
		}

		return $data;
	}
}