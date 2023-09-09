<?php
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Get the Icons
 */
$no_icon='<span class="dashicons dashicons-dismiss" style="color:#ea1515;"></span>&nbsp;';
$yes_icon='<span class="dashicons dashicons-yes-alt" style="color:#18c01d;"></span>&nbsp;';
global $wp_version;
if( version_compare( $wp_version, '5.2.0' ) < 0 )
{
 	$yes_icon='<img src="'.QFI_ROOT_URL.'assets/images/tick_icon_green.png" style="float:left;" />&nbsp;';
}
unset( $wp_version );

/**
 *	Array format
 *	First 	: Feature
 *	Second 	: Basic availability. Supports: Boolean, Array(Boolean and String values), String
 *	Pro 	: Pro availability. Supports: Boolean, Array(Boolean and String values), String
 */
$comparison_data=array(

    array(
        __('Actions in Bulk Edit', 'quick-featured-images')
    ),
	array(
		__('Sets the selected image as new featured image', 'quick-featured-images'),
		true,
		true,
	),
	array(
		__('Sets multiple images randomly as featured images', 'quick-featured-images'),
		true,
		true,
	),
	array(
		__('Replaces featured images by the selected image', 'quick-featured-images'),
		true,
		true,
	),
	array(
		__('Removes the selected image as featured image', 'quick-featured-images'),
		true,
		true,
	),
	array(
		__('Removes any image as featured image', 'quick-featured-images'),
		true,
		true,
	),
	array(
		__('Remove all featured images without existing image files', 'quick-featured-images'),
		true,
		true,
	),
	array(
		__('Removes database entries of featured images without existing image files', 'quick-featured-images'),
		true,
		true,
	),
	array(
		__('Sets the first image as featured image', 'quick-featured-images'),
		false,
		true,
	),
	array(
		__('Sets the external image as a featured image (needs Featured Image By URL or Featured Image From URL)', 'quick-featured-images'),
		false,
		true,
	),
    array(
        __('Options in Bulk Edit', 'quick-featured-images')
    ),
    array(
		__('Overwrites featured images', 'quick-featured-images'),
        true,
		true,
	),
	array(
		__('Considers only posts without featured image', 'quick-featured-images'),
        true,
		true,
	),
	array(
		__('Removes the first embedded image from the post content after this image was set as featured image', 'quick-featured-images'),
		false,
		true,
	),
    array(
        __('Sets the first post image (if available in the media library) as featured image', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Sets the first post image under current domain as featured image', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Sets the first external post image as featured image	', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Sets the first attached image of a post as featured image', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Sets the first image of a WordPress standard gallery as featured image', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Sets the thumbnail of the first embedded content (e.g. YouTube, Vimeo, Instagram) as featured image', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Sets the first image of a WordPress standard gallery as the featured image', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Sets the first image of a NextGen Gallery as featured image (if the NextGen plugin is activated)', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('At multiple images: Uses each selected image only once', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('At multiple images: Removes excess featured images', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Attaches images to posts after set as featured images successfully', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Detaches images and posts after featured images are removed successfully', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Filter in Bulk Edit', 'quick-featured-images')
    ),
    array(
        __('Post Type Filter', 'quick-featured-images'),
        true,
        true,
    ),
    array(
        __('Category Filter', 'quick-featured-images'),
        true,
        true,
    ),
    array(
        __('Tag Filter', 'quick-featured-images'),
        true,
        true,
    ),
    array(
        __('Post Format Filter', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Multimedia File Filter', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Post Status Filter', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Search Filter', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Time Filter', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Author Filter', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Custom Taxonomy Filter', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Featured Image Size Filter', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Parent Page Filter', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Supported Post Types in Bulk Edit', 'quick-featured-images')
    ),
    array(
        __('Posts', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Pages', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Custom Post Types', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Audio Files', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Video Files', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Presets in Bulk Edit', 'quick-featured-images')
    ),
    array(
        __('Stores all settings of a process as a preset to recall them for recurring tasks', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Default Images', 'quick-featured-images')
    ),
    array(
        __('Overwrites existing featured images', 'quick-featured-images'),
        true,
        true,
    ),
    array(
        __('Takes first content image as featured image in new posts if image was uploaded to the media library', 'quick-featured-images'),
        true,
        true,
    ),
    array(
        __('Takes first content image as featured image in new posts if image is on an external server', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Takes one of multiple images randomly as featured image in new posts', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Takes the thumbnail of the first embedded content (e.g. YouTube, Vimeo, Instagram) as featured image in new posts', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Takes the external image as a featured image (needs Featured Image By URL or Featured Image From URL)', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Displays featured images randomly at each page load', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Removes the first content image automatically after the featured image was set successfully', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Supports WooCommerce products', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Exports and imports all rules', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Imports rules from the free version "Quick Featured Images"', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Supported Taxonomies in Default Images', 'quick-featured-images')
    ),
    array(
        __('Matches a standard WordPress post type (post or page)', 'quick-featured-images'),
        true,
        true,
    ),
    array(
        __('Matches a selected custom post type', 'quick-featured-images'),
        true,
        true,
    ),
    array(
        __('Matches a selected category', 'quick-featured-images'),
        true,
        true,
    ),
    array(
        __('Matches a selected tag', 'quick-featured-images'),
        true,
        true,
    ),
    array(
        __('Matches a selected author', 'quick-featured-images'),
        true,
        true,
    ),
    array(
        __('Matches a selected post format', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Matches a search term in post title', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Image Columns', 'quick-featured-images')
    ),
    array(
        __('Shows additional column of featured images for posts, pages and custom post types', 'quick-featured-images'),
        true,
        true,
    ),
    array(
        __('Provides action links to set, change, remove and edit the post’s image if the user is allowed to do it', 'quick-featured-images'),
        true,
        true,
    ),
    array(
        __('Shows assigned external featured images in the image column (needs Featured Image By URL or Featured Image From URL)', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Settings', 'quick-featured-images')
    ),
    array(
        __('Shows or hides the plugin based on the selected user role ‘Administrator’ or ‘Editor’', 'quick-featured-images'),
        true,
        true,
    ),
    array(
        __('Supported media file formats', 'quick-featured-images')
    ),
    array(
        __('Supported image formats: jpg, jpeg, jpe, gif, png', 'quick-featured-images'),
        true,
        true,
    ),
    array(
        __('Supported audio formats: mp3, ogg, wma, m4a, wav', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('Supported video formats: mp4, m4v, webm, ogv, wmv, flv', 'quick-featured-images'),
        false,
        true,
    ),
    array(
        __('In General', 'quick-featured-images')
    ),
    array(
        __('Premium support', 'quick-featured-images'),
        false,
        true,
    )
);

function qfi_free_vs_pro_column_vl( $vl, $yes_icon, $no_icon )
{
	if( is_array( $vl ) )
	{
		foreach ( $vl as $value )
		{
			if( is_bool( $value ) )
			{
				echo ( $value ? $yes_icon : $no_icon );
			}else {
				//string only
				echo $value;
			}
		}
	} else {
		if( is_bool( $vl ) )
		{
			echo ($vl ? $yes_icon : $no_icon);
		} else {
			//string only
			echo $vl;
		}
	}
}
?>
<div class="qfi_wrapper">
    <div class="qfi_main">
	<table class="qfi_freevs_pro">
	<tr>
		<td><?php _e('FEATURES', 'quick-featured-images'); ?></td>
		<td><?php _e('FREE', 'quick-featured-images'); ?></td>
		<td><?php _e('PREMIUM', 'quick-featured-images'); ?></td>
	</tr>
	<?php
	foreach ($comparison_data as $val_arr)
	{
		?>
		<tr>
            <?php if( 1 == count( $val_arr ) ): ?>
                <td colspan="3" class="headline"><?php echo $val_arr[0];?></td>
            <?php else: ?>
                <td><?php echo $val_arr[0];?></td>
                <td>
                    <?php
                    qfi_free_vs_pro_column_vl( $val_arr[1], $yes_icon, $no_icon );
                    ?>
                </td>
                <td>
                    <?php
                    qfi_free_vs_pro_column_vl( $val_arr[2], $yes_icon, $no_icon );
                    ?>
                </td>
            <?php endif; ?>
		</tr>
		<?php
	}
	?>
</table>
</div>
</div>
