<?php
/**
 * WPSEO plugin file.
 *
 * @package Yoast\WP\SEO\Presentations\Generators\Schema
 */

namespace Yoast\WP\SEO\Presentations\Generators\Schema;

use Yoast\WP\SEO\Context\Meta_Tags_Context;
use Yoast\WP\SEO\Helpers\Schema\HTML_Helper;
use Yoast\WP\SEO\Helpers\Schema\Image_Helper;
use Yoast\WP\SEO\Helpers\Post_Helper;

/**
 * Returns schema FAQ data.
 *
 * @since 11.5
 */
class HowTo extends Abstract_Schema_Piece {

	/**
	 * @var HTML_Helper
	 */
	private $html;

	/**
	 * @var Image_Helper
	 */
	private $image;

	/**
	 * The post helper.
	 *
	 * @var Post_Helper
	 */
	private $post;

	/**
	 * HowTo constructor.
	 *
	 * @param HTML_Helper  $html  The HTML helper.
	 * @param Image_Helper $image The schema image helper.
	 * @param Post_Helper  $post  The post helper.
	 */
	public function __construct(
		HTML_Helper $html,
		Image_Helper $image,
		Post_Helper $post
	) {
		$this->html  = $html;
		$this->image = $image;
		$this->post  = $post;
	}

	/**
	 * Determines whether or not a piece should be added to the graph.
	 *
	 * @param Meta_Tags_Context $context The meta tags context.
	 *
	 * @return bool
	 */
	public function is_needed( Meta_Tags_Context $context ) {
		return ! empty( $context->blocks['yoast/how-to-block'] );
	}

	/**
	 * Renders a list of questions, referencing them by ID.
	 *
	 * @param Meta_Tags_Context $context The meta tags context.
	 *
	 * @return array $data Our Schema graph.
	 */
	public function generate( Meta_Tags_Context $context ) {
		$graph = [];

		foreach ( $context->blocks['yoast/how-to-block'] as $index => $block ) {
			$data = [
				'@type'            => 'HowTo',
				'@id'              => $context->canonical . '#howto-' . ( $index + 1 ),
				'name'             => $this->html->smart_strip_tags( $this->post->get_post_title_with_fallback( $context->id ) ),
				'mainEntityOfPage' => [ '@id' => $context->main_schema_id ],
				'description'      => '',
			];

			if ( isset( $block['attrs']['jsonDescription'] ) ) {
				$data['description'] = $this->html->sanitize( $block['attrs']['jsonDescription'] );
			}

			$this->add_duration( $data, $block['attrs'] );
			$this->add_steps( $data, $block['attrs']['steps'], $context );

			$graph[] = $data;
		}

		return $graph;
	}

	/**
	 * Adds the duration of the task to the Schema.
	 *
	 * @param array $data       Our How-To schema data.
	 * @param array $attributes The block data attributes.
	 */
	private function add_duration( &$data, $attributes ) {
		if ( empty( $attributes['hasDuration'] ) ) {
			return;
		}

		$days    = empty( $attributes['days'] ) ? 0 : $attributes['days'];
		$hours   = empty( $attributes['hours'] ) ? 0 : $attributes['hours'];
		$minutes = empty( $attributes['minutes'] ) ? 0 : $attributes['minutes'];

		if ( ( $days + $hours + $minutes ) > 0 ) {
			$data['totalTime'] = esc_attr( 'P' . $days . 'DT' . $hours . 'H' . $minutes . 'M' );
		}
	}

	/**
	 * Adds the steps to our How-To output.
	 *
	 * @param array             $data    Our How-To schema data.
	 * @param array             $steps   Our How-To block's steps.
	 * @param Meta_Tags_Context $context The meta tags context.
	 */
	private function add_steps( &$data, $steps, Meta_Tags_Context $context ) {
		foreach ( $steps as $step ) {
			$schema_id   = $context->canonical . '#' . esc_attr( $step['id'] );
			$schema_step = [
				'@type' => 'HowToStep',
				'url'   => $schema_id,
			];

			if ( isset( $step['jsonText'] ) ) {
				$json_text = $this->html->sanitize( $step['jsonText'] );
			}

			if ( isset( $step['jsonName'] ) ) {
				$json_name = $this->html->smart_strip_tags( $step['jsonName'] );
			}

			if ( empty( $json_name ) ) {
				if ( empty( $step['text'] ) ) {
					continue;
				}

				$schema_step['text'] = '';

				$this->add_step_image( $schema_step, $step, $context );

				// If there is no text and no image, don't output the step.
				if ( empty( $json_text ) && empty( $schema_step['image'] ) ) {
					continue;
				}

				if ( ! empty( $json_text ) ) {
					$schema_step['text'] = $json_text;
				}
			}

			elseif ( empty( $json_text ) ) {
				$schema_step['text'] = $json_name;
			}
			else {
				$schema_step['name'] = $json_name;

				$this->add_step_description( $schema_step, $step );
				$this->add_step_image( $schema_step, $step, $context );
			}

			$data['step'][] = $schema_step;
		}
	}

	/**
	 * Checks if we have a step description, if we do, add it.
	 *
	 * @param array $schema_step Our Schema output for the Step.
	 * @param array $step        The step block data.
	 */
	private function add_step_description( &$schema_step, $step ) {
		$json_text = $this->html->sanitize( $step['jsonText'] );

		if ( empty( $json_text ) ) {
			return;
		}

		$schema_step['itemListElement'] = [
			[
				'@type' => 'HowToDirection',
				'text'  => $json_text,
			],
		];
	}

	/**
	 * Checks if we have a step image, if we do, add it.
	 *
	 * @param array             $schema_step Our Schema output for the Step.
	 * @param array             $step        The step block data.
	 * @param Meta_Tags_Context $context     The meta tags context.
	 */
	private function add_step_image( &$schema_step, $step, Meta_Tags_Context $context ) {
		foreach ( $step['text'] as $line ) {
			if ( \is_array( $line ) && isset( $line['type'] ) && $line['type'] === 'img' ) {
				$schema_step['image'] = $this->get_image_schema( esc_url( $line['props']['src'] ), $context );
			}
		}
	}

	/**
	 * Generates the image schema from the attachment $url.
	 *
	 * @param string            $url     Attachment url.
	 * @param Meta_Tags_Context $context The meta tags context.
	 *
	 * @return array Image schema.
	 *
	 * @codeCoverageIgnore
	 */
	protected function get_image_schema( $url, Meta_Tags_Context $context ) {
		$schema_id = $context->canonical . '#schema-image-' . \md5( $url );

		return $this->image->generate_from_url( $schema_id, $url );
	}
}
