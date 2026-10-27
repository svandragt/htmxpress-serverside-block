import { act } from 'react';
import {
	afterEach,
	beforeAll,
	beforeEach,
	describe,
	expect,
	it,
	vi,
} from 'vitest';
import { createRoot } from 'react-dom/client';
import Edit from './edit';

vi.mock( '@wordpress/block-editor', () => ( {
	useBlockProps: vi.fn( () => ( {
		className: 'wp-block-create-block-htmx-server-block',
	} ) ),
} ) );

vi.mock( '@wordpress/server-side-render', () => ( {
	default: ( props ) => (
		<div data-testid="server-side-render" data-block={ props.block } />
	),
} ) );

describe( 'Edit', () => {
	let container;

	beforeAll( () => {
		// React 18's act() checks this flag and nothing sets it in Vitest.
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
