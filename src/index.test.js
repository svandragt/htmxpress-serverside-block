import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';

jest.mock( '@wordpress/blocks', () => ( {
	registerBlockType: jest.fn(),
} ) );

// index.js's real ./edit chain pulls in @wordpress/block-editor and
// @wordpress/server-side-render, which are irrelevant here and drag in ESM
// dependencies Jest can't parse without extra transform config.
jest.mock(
	'./edit',
	() =>
		function Edit() {
			return null;
		}
);

describe( 'index.js', () => {
	it( 'registers the block with its metadata name and an edit function', () => {
		require( './index' );

		expect( registerBlockType ).toHaveBeenCalledWith(
			metadata.name,
			expect.objectContaining( {
				edit: expect.any( Function ),
			} )
		);
		expect( registerBlockType ).toHaveBeenCalledWith(
			'create-block/htmx-server-block',
			expect.anything()
		);
	} );
} );
