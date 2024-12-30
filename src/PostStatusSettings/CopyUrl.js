import { Button, Flex, TextControl } from '@wordpress/components';
import { useCopyToClipboard } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { addQueryArgs, safeDecodeURIComponent } from '@wordpress/url';

import CONSTANTS from './constants';

const { META_KEY, QUERY_PARAM } = CONSTANTS;

/**
 * Taken from PostPublishPanelPostPublish (@wordpress/editor)
 */

const CopyButton = ( { text, onCopy, children, ...props } ) => {
	const ref = useCopyToClipboard( text, onCopy );
	return (
		<Button variant="secondary" ref={ ref } { ...props }>
			{ children }
		</Button>
	);
};

class CopyUrl extends Component {
	constructor() {
		super( ...arguments );
		this.state = {
			showCopyConfirmation: false,
		};
		this.onCopy = this.onCopy.bind( this );
		this.onSelectInput = this.onSelectInput.bind( this );
	}

	componentWillUnmount() {
		clearTimeout( this.dismissCopyConfirmation );
	}

	onCopy() {
		this.setState( {
			showCopyConfirmation: true,
		} );

		clearTimeout( this.dismissCopyConfirmation );
		this.dismissCopyConfirmation = setTimeout( () => {
			this.setState( {
				showCopyConfirmation: false,
			} );
		}, 4000 );
	}

	onSelectInput( event ) {
		event.target.select();
	}

	render() {
		const url = addQueryArgs( this.props.postLink, {
			[ QUERY_PARAM ]: this.props.secretKey,
		} );
		return (
			<div>
				<label htmlFor="sppp-link-field" className="screen-reader-text">
					{ __(
						'Share this URL',
						'sharable-password-protected-posts'
					) }
				</label>
				<Flex className="sppp__link-copy">
					<TextControl
						id="sppp-link-field"
						className="sppp__link-field"
						readOnly
						label={ __(
							'Secret URL:',
							'sharable-password-protected-posts'
						) }
						value={ safeDecodeURIComponent( url ) }
						onFocus={ this.onSelectInput }
					/>
					<CopyButton
						text={ url }
						onCopy={ this.onCopy }
						className="sppp__copy-button"
					>
						{ this.state.showCopyConfirmation
							? __(
									'Copied!',
									'sharable-password-protected-posts'
							  )
							: __(
									'Copy',
									'sharable-password-protected-posts'
							  ) }
					</CopyButton>
				</Flex>
			</div>
		);
	}
}

export default withSelect( ( select ) => {
	const { getEditedPostAttribute, getPermalink } = select( editorStore );

	return {
		postLink: getPermalink(),
		secretKey: getEditedPostAttribute( 'meta' )[ META_KEY ],
	};
} )( CopyUrl );
