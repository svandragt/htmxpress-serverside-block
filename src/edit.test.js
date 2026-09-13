import { act } from 'react';
import { createRoot } from 'react-dom/client';
import Edit from './edit';

jest.mock( '@wordpress/block-editor', () => ( {
	useBlockProps: jest.fn( () => ( {
		className: 'wp-block-create-block-htmx-server-block',
	} ) ),
} ) );

jest.mock( '@wordpress/server-side-render', () => ( props ) => (
	<div data-testid="server-side-render" data-block={ props.block } />
) );

describe( 'Edit', () => {
	let container;

	beforeAll( () => {
		// React 18's act() checks this flag; the wp-scripts jsdom preset
		// doesn't set it since it predates react-dom/client usage here.
		globalThis.IS_REACT_ACT_ENVIRONMENT = true;
	} );

	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'renders without crashing, wraps in block props, and renders ServerSideRender for the right block', () => {
		act( () => {
			createRoot( container ).render( <Edit /> );
		} );

		const wrapper = container.firstChild;
		expect( wrapper.className ).toBe(
			'wp-block-create-block-htmx-server-block'
		);

		const serverSideRender = container.querySelector(
			'[data-testid="server-side-render"]'
		);
		expect( serverSideRender ).not.toBeNull();
		expect( serverSideRender.dataset.block ).toBe(
			'create-block/htmx-server-block'
		);
	} );
} );
