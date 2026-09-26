import { X } from 'lucide-react';
import { useEffect, useMemo } from 'react';
import { BuilderIcon } from './BuilderIcon';
import { resolveLayoutItems, resolveSlashItems } from '../lib/filterSlashItems';
import type { Strings } from '../lib/i18n';
import { FieldType } from '../types/block';

type Props = {
	open: boolean;
	strings: Strings;
	onSelect: ( type: FieldType ) => void;
	onClose: () => void;
};

export function FieldPickerOverlay( { open, strings, onSelect, onClose }: Props ) {
	const layoutItems = useMemo( () => resolveLayoutItems( strings ), [ strings ] );
	const items = useMemo( () => resolveSlashItems( strings ), [ strings ] );

	useEffect( () => {
		if ( ! open ) {
			return;
		}
		const onKeyDown = ( e: KeyboardEvent ) => {
			if ( e.key === 'Escape' ) {
				onClose();
			}
		};
		document.addEventListener( 'keydown', onKeyDown );
		return () => document.removeEventListener( 'keydown', onKeyDown );
	}, [ open, onClose ] );

	if ( ! open ) {
		return null;
	}

	return (
		<div
			className="sobiforms-field-picker-overlay"
			role="presentation"
			onClick={ onClose }
		>
			<div
				className="sobiforms-field-picker-modal"
				role="dialog"
				aria-modal="true"
				aria-label={ strings.pickFieldType }
				onClick={ ( e ) => e.stopPropagation() }
			>
				<div className="sobiforms-field-picker-modal__header">
					<h3 className="sobiforms-field-picker-modal__title">{ strings.pickFieldType }</h3>
					<button
						type="button"
						className="sobiforms-field-picker-modal__close"
						onClick={ onClose }
						aria-label={ strings.closePicker }
					>
						<BuilderIcon icon={ X } />
					</button>
				</div>
				<div className="sobiforms-field-picker-modal__list" role="listbox">
					<div className="sobiforms-field-picker-modal__category">{ strings.layout }</div>
					{ layoutItems.map( ( item ) => (
						<button
							key={ item.type }
							type="button"
							role="option"
							className="sobiforms-field-picker-modal__item"
							onClick={ () => onSelect( item.type ) }
						>
							<span className="sobiforms-field-picker-modal__item-label">{ item.label }</span>
							<span className="sobiforms-field-picker-modal__item-desc">{ item.description }</span>
						</button>
					) ) }
					<div className="sobiforms-field-picker-modal__category">{ strings.questions }</div>
					{ items.map( ( item ) => (
						<button
							key={ item.type }
							type="button"
							role="option"
							className="sobiforms-field-picker-modal__item"
							onClick={ () => onSelect( item.type ) }
						>
							<span className="sobiforms-field-picker-modal__item-label">{ item.label }</span>
							<span className="sobiforms-field-picker-modal__item-desc">{ item.description }</span>
						</button>
					) ) }
				</div>
			</div>
		</div>
	);
}
