import { useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { store as editorStore } from '@wordpress/editor';

/**
 * Taken from WordPress PostURLCheck (private)
 * @wordpress/editor
 * and adapted for post type config from sppp
 *
 */
const Check = ( { children } ) => {
	const { isPostTypeVisible, postType, isPostPrivate } = useSelect(
		( select ) => {
			let isPostTypeVisible = false;
			const postTypeSlug = select( editorStore ).getCurrentPostType();
			const postType = select( coreStore ).getPostType( postTypeSlug );
			const link = select( editorStore ).getEditedPostAttribute( 'link' );
			const editedPostVisibility =
				select( editorStore ).getEditedPostVisibility();

			if ( postType?.viewable ) {
				isPostTypeVisible = true;
			}

			if ( link ) {
				isPostTypeVisible = true;
			}

			const permalinkParts = select( editorStore ).getPermalinkParts();
			if ( permalinkParts ) {
				isPostTypeVisible = true;
			}

			return {
				isPostTypeVisible,
				postType: postTypeSlug,
				isPostPrivate:
					editedPostVisibility === 'private' ||
					editedPostVisibility === 'password',
			};
		},
		[]
	);

	const isEnabledForPostType =
		window.sppp && window.sppp.settings.postTypes.includes( postType );
	const hasPermissions = window.sppp && window.sppp.settings.hasPermissions;

	if (
		! hasPermissions ||
		! isPostTypeVisible ||
		! isEnabledForPostType ||
		! isPostPrivate
	) {
		return null;
	}

	return children;
};

export default Check;
