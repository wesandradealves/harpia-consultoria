<?php
/**
 * This is the output for migrating SEO options from one site to another at the network level.
 *
 * @since 4.2.5
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$sites = aioseo()->helpers->getSites();
if ( ! count( $sites ) ) {
	return;
}

?>

<table class="form-table" role="presentation">
	<tbody>
		<tr class="form-field form-required">
			<th scope="row">
				<label for="seo-settings"><?php esc_html_e( 'SEO Settings', 'aioseo-pro' ); ?></label>
			</th>
			<td>
				<select
					id="seo-settings"
					name="aioseo-import-site"
				>
					<option value=""></option>
					<?php foreach ( $sites['sites'] as $site ) : ?>
						<option value="<?php echo intval( $site->blog_id ); ?>"><?php echo esc_html( $site->domain ); ?><?php echo esc_html( $site->path ); ?></option>
					<?php endforeach; ?>
				</select>

				<p id="site-admin-email"><?php esc_html_e( 'Choose a site to import SEO settings from.', 'aioseo-pro' ); ?></p>
			</td>
		</tr>
	</tbody>
</table>