import { describe, expect, it, vi } from 'vitest';
import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';

vi.mock( '@wordpress/blocks', () => ( {
	registerBlockType: vi.fn(),
} ) );

// index.js's real ./edit chain pulls in @wordpress/block-editor and
// @wordpress/server-side-render, which are irrelevant here and drag in ESM
// dependencies Node can't load without extra config.
vi.mock( './edit', () => ( {
	default: function Edit() {
		return null;
	},
} ) );

describe( 'index.js', () => {
	it( 'registers the block with its metadata name and an edit function', async () => {
		await import( './index' );

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
