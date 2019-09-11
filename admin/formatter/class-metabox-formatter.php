<?php
/**
 * WPSEO plugin file.
 *
 * @package WPSEO\Admin\Formatter
 */

/**
 * This class forces needed methods for the metabox localization.
 */
class WPSEO_Metabox_Formatter {

	/**
	 * Object that provides formatted values.
	 *
	 * @var WPSEO_Metabox_Formatter_Interface
	 */
	private $formatter;

	/**
	 * Setting the formatter property.
	 *
	 * @param WPSEO_Metabox_Formatter_Interface $formatter Object that provides the formatted values.
	 */
	public function __construct( WPSEO_Metabox_Formatter_Interface $formatter ) {
		$this->formatter = $formatter;
	}

	/**
	 * Returns the values.
	 *
	 * @return array
	 */
	public function get_values() {
		$defaults = $this->get_defaults();
		$values   = $this->formatter->get_values();

		return ( $values + $defaults );
	}

	/**
	 * Returns array with all the values always needed by a scraper object.
	 *
	 * @return array Default settings for the metabox.
	 */
	private function get_defaults() {
		$analysis_seo         = new WPSEO_Metabox_Analysis_SEO();
		$analysis_readability = new WPSEO_Metabox_Analysis_Readability();

		return array(
			'language'                  => WPSEO_Language_Utils::get_site_language_name(),
			'settings_link'             => $this->get_settings_link(),
			'search_url'                => '',
			'post_edit_url'             => '',
			'base_url'                  => '',
			'contentTab'                => __( 'Readability', 'wordpress-seo' ),
			'keywordTab'                => __( 'Keyphrase:', 'wordpress-seo' ),
			'removeKeyword'             => __( 'Remove keyphrase', 'wordpress-seo' ),
			'contentLocale'             => get_locale(),
			'userLocale'                => WPSEO_Language_Utils::get_user_locale(),
			'translations'              => $this->get_translations(),
			'keyword_usage'             => array(),
			'title_template'            => '',
			'metadesc_template'         => '',
			'contentAnalysisActive'     => $analysis_readability->is_enabled() ? 1 : 0,
			'keywordAnalysisActive'     => $analysis_seo->is_enabled() ? 1 : 0,
			'cornerstoneActive'         => WPSEO_Options::get( 'enable_cornerstone_content', false ) ? 1 : 0,
			'intl'                      => $this->get_content_analysis_component_translations(),
			'isRtl'                     => is_rtl(),
			'isPremium'                 => WPSEO_Utils::is_yoast_seo_premium(),
			'addKeywordUpsell'          => $this->get_add_keyword_upsell_translations(),
			'wordFormRecognitionActive' => ( WPSEO_Language_Utils::get_language( get_locale() ) === 'en' ),
			'siteIconUrl'               => get_site_icon_url(),
			'postImageUrl'				=> $this->get_post_image_url(),

			/**
			 * Filter to determine if the markers should be enabled or not.
			 *
			 * @param bool $showMarkers Should the markers being enabled. Default = true.
			 */
			'show_markers'              => apply_filters( 'wpseo_enable_assessment_markers', true ),
			'publish_box'               => array(
				'labels' => array(
					'content' => array(
						'na'   => sprintf(
							/* translators: %1$s expands to the opening anchor tag, %2$s to the closing anchor tag, %3$s to the readability score. */
							__( '%1$sReadability%2$s: %3$s', 'wordpress-seo' ),
							'<a href="#yoast-readability-analysis-collapsible-metabox">',
							'</a>',
							'<strong>' . __( 'Not available', 'wordpress-seo' ) . '</strong>'
						),
						'bad'  => sprintf(
							/* translators: %1$s expands to the opening anchor tag, %2$s to the closing anchor tag, %3$s to the readability score. */
							__( '%1$sReadability%2$s: %3$s', 'wordpress-seo' ),
							'<a href="#yoast-readability-analysis-collapsible-metabox">',
							'</a>',
							'<strong>' . __( 'Needs improvement', 'wordpress-seo' ) . '</strong>'
						),
						'ok'   => sprintf(
							/* translators: %1$s expands to the opening anchor tag, %2$s to the closing anchor tag, %3$s to the readability score. */
							__( '%1$sReadability%2$s: %3$s', 'wordpress-seo' ),
							'<a href="#yoast-readability-analysis-collapsible-metabox">',
							'</a>',
							'<strong>' . __( 'OK', 'wordpress-seo' ) . '</strong>'
						),
						'good' => sprintf(
							/* translators: %1$s expands to the opening anchor tag, %2$s to the closing anchor tag, %3$s to the readability score. */
							__( '%1$sReadability%2$s: %3$s', 'wordpress-seo' ),
							'<a href="#yoast-readability-analysis-collapsible-metabox">',
							'</a>',
							'<strong>' . __( 'Good', 'wordpress-seo' ) . '</strong>'
						),
					),
					'keyword' => array(
						'na'   => sprintf(
							/* translators: %1$s expands to the opening anchor tag, %2$s to the closing anchor tag, %3$s to the SEO score. */
							__( '%1$sSEO%2$s: %3$s', 'wordpress-seo' ),
							'<a href="#yoast-seo-analysis-collapsible-metabox">',
							'</a>',
							'<strong>' . __( 'Focus Keyphrase not set', 'wordpress-seo' ) . '</strong>'
						),
						'bad'  => sprintf(
							/* translators: %1$s expands to the opening anchor tag, %2$s to the closing anchor tag, %3$s to the SEO score. */
							__( '%1$sSEO%2$s: %3$s', 'wordpress-seo' ),
							'<a href="#yoast-seo-analysis-collapsible-metabox">',
							'</a>',
							'<strong>' . __( 'Needs improvement', 'wordpress-seo' ) . '</strong>'
						),
						'ok'   => sprintf(
							/* translators: %1$s expands to the opening anchor tag, %2$s to the closing anchor tag, %3$s to the SEO score. */
							__( '%1$sSEO%2$s: %3$s', 'wordpress-seo' ),
							'<a href="#yoast-seo-analysis-collapsible-metabox">',
							'</a>',
							'<strong>' . __( 'OK', 'wordpress-seo' ) . '</strong>'
						),
						'good' => sprintf(
							/* translators: %1$s expands to the opening anchor tag, %2$s to the closing anchor tag, %3$s to the SEO score. */
							__( '%1$sSEO%2$s: %3$s', 'wordpress-seo' ),
							'<a href="#yoast-seo-analysis-collapsible-metabox">',
							'</a>',
							'<strong>' . __( 'Good', 'wordpress-seo' ) . '</strong>'
						),
					),
				),
			),
			'markdownEnabled'           => $this->is_markdown_enabled(),
			'analysisHeadingTitle'      => __( 'Analysis', 'wordpress-seo' ),
		);
	}

	/**
	 * Returns the url of the posts' or terms' main image.
	 * If the featured image is not set (post), returns the first image in the post or term.
	 * If there are also no images in the post or term, returns null.
	 *
	 * @return string The posts' or terms' main image url.
	 */
	private function get_post_image_url() {
		$post_id = get_the_ID();
		$term_id = filter_input( INPUT_GET, 'tag_ID' );

		if ( has_post_thumbnail( $post_id ) ) {
			$featured_image_info = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'single-post-thumbnail' );
			return $featured_image_url = $featured_image_info[0];
		}

		if ( $post_id ) {
			return WPSEO_Image_Utils::get_first_usable_content_image_for_post( $post_id );
		}

		if ( $term_id ){
			return WPSEO_Image_Utils::get_first_usable_description_image_for_term( $term_id );
		}

		return null;
	}

	/**
	 * Returns a link to the settings page, if the user has the right capabilities.
	 * Returns an empty string otherwise.
	 *
	 * @return string The settings link.
	 */
	private function get_settings_link() {
		if ( current_user_can( 'manage_options' ) ) {
			return admin_url( 'options-general.php' );
		}

		return '';
	}

	/**
	 * Returns required yoast-component translations.
	 *
	 * @return array
	 */
	private function get_content_analysis_component_translations() {
		// Esc_html is not needed because React already handles HTML in the (translations of) these strings.
		return array(
			'locale'                                         => WPSEO_Language_Utils::get_user_locale(),
			'content-analysis.language-notice-link'          => __( 'Change language', 'wordpress-seo' ),
			'content-analysis.errors'                        => __( 'Errors', 'wordpress-seo' ),
			'content-analysis.problems'                      => __( 'Problems', 'wordpress-seo' ),
			'content-analysis.improvements'                  => __( 'Improvements', 'wordpress-seo' ),
			'content-analysis.considerations'                => __( 'Considerations', 'wordpress-seo' ),
			'content-analysis.good'                          => __( 'Good results', 'wordpress-seo' ),
			'content-analysis.language-notice'               => __( 'Your site language is set to {language}.', 'wordpress-seo' ),
			'content-analysis.language-notice-contact-admin' => __( 'Your site language is set to {language}. If this is not correct, contact your site administrator.', 'wordpress-seo' ),
			'content-analysis.highlight'                     => __( 'Highlight this result in the text', 'wordpress-seo' ),
			'content-analysis.nohighlight'                   => __( 'Remove highlight from the text', 'wordpress-seo' ),
			'content-analysis.disabledButton'                => __( 'Marks are disabled in current view', 'wordpress-seo' ),
			'a11yNotice.opensInNewTab'                       => __( '(Opens in a new browser tab)', 'wordpress-seo' ),
		);
	}

	/**
	 * Returns the translations for the Add Keyword modal.
	 *
	 * These strings are not escaped because they're meant to be used with React
	 * which already takes care of that. If used in PHP, they should be escaped.
	 *
	 * @return array Translated text strings for the Add Keyword modal.
	 */
	public function get_add_keyword_upsell_translations() {
		return array(
			'title'                    => __( 'Would you like to add more than one keyphrase?', 'wordpress-seo' ),
			'intro'                    => sprintf(
				/* translators: %s expands to a 'Yoast SEO Premium' text linked to the yoast.com website. */
				__( 'Great news: you can, with %s!', 'wordpress-seo' ),
				'{{link}}Yoast SEO Premium{{/link}}'
			),
			'link'                     => WPSEO_Shortlinker::get( 'https://yoa.st/pe-premium-page' ),
			'other'                    => sprintf(
				/* translators: %s expands to 'Yoast SEO Premium'. */
				__( 'Other benefits of %s for you:', 'wordpress-seo' ),
				'Yoast SEO Premium'
			),
			'buylink'                  => WPSEO_Shortlinker::get( 'https://yoa.st/add-keywords-popup' ),
			'buy'                      => sprintf(
				/* translators: %s expands to 'Yoast SEO Premium'. */
				__( 'Get %s', 'wordpress-seo' ),
				'Yoast SEO Premium'
			),
			'small'                    => __( '1 year free support and updates included!', 'wordpress-seo' ),
			'a11yNotice.opensInNewTab' => __( '(Opens in a new browser tab)', 'wordpress-seo' ),
		);
	}

	/**
	 * Returns Jed compatible YoastSEO.js translations.
	 *
	 * @return array
	 */
	private function get_translations() {
		$locale = WPSEO_Language_Utils::get_user_locale();

		$file = plugin_dir_path( WPSEO_FILE ) . 'languages/wordpress-seo-' . $locale . '.json';
		if ( file_exists( $file ) ) {
			$file = file_get_contents( $file );
			if ( is_string( $file ) && $file !== '' ) {
				return json_decode( $file, true );
			}
		}

		return array();
	}

	/**
	 * Checks if Jetpack's markdown module is enabled.
	 * Can be extended to work with other plugins that parse markdown in the content.
	 *
	 * @return boolean
	 */
	private function is_markdown_enabled() {
		$is_markdown = false;

		if ( class_exists( 'Jetpack' ) && method_exists( 'Jetpack', 'get_active_modules' ) ) {
			$active_modules = Jetpack::get_active_modules();

			// First at all, check if Jetpack's markdown module is active.
			$is_markdown = in_array( 'markdown', $active_modules, true );
		}

		/**
		 * Filters whether markdown support is active in the readability- and seo-analysis.
		 *
		 * @since 11.3
		 *
		 * @param array $is_markdown Is markdown support for Yoast SEO active.
		 */
		return apply_filters( 'wpseo_is_markdown_enabled', $is_markdown );
	}
}
