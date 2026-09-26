import { useEffect } from 'react';

function shouldInterceptLink( anchor: HTMLAnchorElement ): boolean {
	if ( anchor.target === '_blank' || anchor.hasAttribute( 'download' ) ) {
		return false;
	}

	const href = anchor.getAttribute( 'href' );
	if ( ! href || href.startsWith( '#' ) || href.startsWith( 'javascript:' ) ) {
		return false;
	}

	let url: URL;
	try {
		url = new URL( anchor.href );
	} catch {
		return false;
	}

	const current = new URL( window.location.href );
	if ( url.origin !== current.origin ) {
		return true;
	}

	if ( url.pathname === current.pathname && url.search === current.search ) {
		return false;
	}

	return url.href !== current.href;
}

/**
 * Warn when leaving the form builder with unsaved changes.
 */
export function useUnsavedChangesWarning( isDirty: boolean, confirmMessage: string ) {
	useEffect( () => {
		if ( ! isDirty ) {
			return;
		}

		const onBeforeUnload = ( event: BeforeUnloadEvent ) => {
			event.preventDefault();
			event.returnValue = '';
		};

		const onDocumentClick = ( event: MouseEvent ) => {
			if ( event.defaultPrevented ) {
				return;
			}

			if ( event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button !== 0 ) {
				return;
			}

			const target = event.target;
			if ( ! ( target instanceof Element ) ) {
				return;
			}

			const anchor = target.closest( 'a' );
			if ( ! ( anchor instanceof HTMLAnchorElement ) || ! shouldInterceptLink( anchor ) ) {
				return;
			}

			if ( ! window.confirm( confirmMessage ) ) {
				event.preventDefault();
				event.stopPropagation();
			}
		};

		window.addEventListener( 'beforeunload', onBeforeUnload );
		document.addEventListener( 'click', onDocumentClick, true );

		return () => {
			window.removeEventListener( 'beforeunload', onBeforeUnload );
			document.removeEventListener( 'click', onDocumentClick, true );
		};
	}, [ confirmMessage, isDirty ] );
}
