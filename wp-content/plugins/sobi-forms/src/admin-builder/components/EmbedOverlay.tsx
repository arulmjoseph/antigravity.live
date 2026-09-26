import { useEffect, useRef } from 'react';
import { createPortal } from 'react-dom';
import type { Strings } from '../lib/i18n';
import { InstallPanel } from './InstallPanel';

type Props = {
	open: boolean;
	onClose: () => void;
	strings: Strings;
	formId: number;
	slug: string;
	returnFocusRef: React.RefObject<HTMLElement | null>;
};

export function EmbedOverlay( {
	open,
	onClose,
	strings,
	formId,
	slug,
	returnFocusRef,
}: Props ) {
	const panelRef = useRef<HTMLDivElement>( null );

	useEffect( () => {
		if ( ! open ) {
			return;
		}

		const previousFocus = document.activeElement as HTMLElement | null;

		const onKeyDown = ( e: KeyboardEvent ) => {
			if ( e.key === 'Escape' ) {
				onClose();
			}
		};

		document.addEventListener( 'keydown', onKeyDown );
		window.setTimeout( () => {
			const focusable = panelRef.current?.querySelector<HTMLElement>(
				'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
			);
			focusable?.focus();
		}, 0 );

		return () => {
			document.removeEventListener( 'keydown', onKeyDown );
			const target = returnFocusRef.current ?? previousFocus;
			target?.focus();
		};
	}, [ open, onClose, returnFocusRef ] );

	if ( ! open ) {
		return null;
	}

	return createPortal(
		<div className="sobiforms-embed-overlay" role="presentation">
			<button
				type="button"
				className="sobiforms-embed-overlay__backdrop"
				aria-label={ strings.closeOverlay }
				onClick={ onClose }
			/>
			<div
				ref={ panelRef }
				className="sobiforms-embed-overlay__panel"
				role="dialog"
				aria-modal="true"
				aria-labelledby="sobiforms-embed-overlay-title"
			>
				<div className="sobiforms-embed-overlay__header">
					<h2 id="sobiforms-embed-overlay-title" className="sobiforms-embed-overlay__title">
						{ strings.embedForm }
					</h2>
					<button
						type="button"
						className="sobiforms-embed-overlay__close"
						onClick={ onClose }
						aria-label={ strings.closeOverlay }
					>
						×
					</button>
				</div>
				<div className="sobiforms-embed-overlay__body">
					<InstallPanel strings={ strings } formId={ formId } slug={ slug } variant="overlay" />
				</div>
			</div>
		</div>,
		document.body
	);
}
