import { useEffect, useRef } from 'react';
import { createPortal } from 'react-dom';
import type { Strings } from '../lib/i18n';

type Props = {
	open: boolean;
	anchor: DOMRect | null;
	anchorEl: HTMLElement | null;
	value: string;
	defaultText: string;
	strings: Strings;
	onClose: () => void;
	onChange: ( value: string ) => void;
};

export function SubmitButtonContextMenu( {
	open,
	anchor,
	anchorEl,
	value,
	defaultText,
	strings,
	onClose,
	onChange,
}: Props ) {
	const menuRef = useRef<HTMLDivElement>( null );

	useEffect( () => {
		if ( ! open ) {
			return;
		}
		const onKeyDown = ( e: KeyboardEvent ) => {
			if ( e.key === 'Escape' ) {
				onClose();
			}
		};
		const onPointerDown = ( e: MouseEvent ) => {
			const target = e.target as Node;
			if ( menuRef.current?.contains( target ) ) {
				return;
			}
			if ( anchorEl?.contains( target ) ) {
				return;
			}
			onClose();
		};
		document.addEventListener( 'keydown', onKeyDown );
		document.addEventListener( 'mousedown', onPointerDown );
		return () => {
			document.removeEventListener( 'keydown', onKeyDown );
			document.removeEventListener( 'mousedown', onPointerDown );
		};
	}, [ open, onClose, anchorEl ] );

	if ( ! open || ! anchor ) {
		return null;
	}

	const top = anchor.top;
	const left = anchor.right + 8;
	const displayLabel = value.trim() || defaultText;

	return createPortal(
		<div
			ref={ menuRef }
			className="sobiforms-field-context-menu"
			role="dialog"
			aria-label={ strings.ctaSettings }
			style={ { top: `${ top }px`, left: `${ left }px` } }
		>
			<div className="sobiforms-field-context-menu__header">
				<span className="sobiforms-field-context-menu__header-label">{ displayLabel }</span>
				<span className="sobiforms-field-context-menu__header-type">{ strings.ctaButton }</span>
			</div>

			<div className="sobiforms-field-context-menu__section sobiforms-field-context-menu__cta-field">
				<label className="sobiforms-field-context-menu__cta-label" htmlFor="sobiforms-cta-text">
					{ strings.submitButtonLabel }
				</label>
				<input
					id="sobiforms-cta-text"
					type="text"
					className="sobiforms-field-context-menu__option-input sobiforms-field-context-menu__cta-input"
					value={ value }
					onChange={ ( e ) => onChange( e.target.value ) }
					placeholder={ defaultText }
					maxLength={ 100 }
				/>
			</div>
		</div>,
		document.body
	);
}
