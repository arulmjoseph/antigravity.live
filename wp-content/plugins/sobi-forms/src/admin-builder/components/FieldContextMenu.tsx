import { ChevronRight, Copy, Trash2 } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { BuilderIcon } from './BuilderIcon';
import { ContextMenuToggle } from './ContextMenuToggle';
import { FileExtensionsPicker } from './FileExtensionsPicker';
import { resolveLayoutItems, resolveSlashItems } from '../lib/filterSlashItems';
import type { FileExtensionOption } from '../lib/fileExtensions';
import { defaultFileExtensions } from '../lib/fileExtensions';
import type { Strings } from '../lib/i18n';
import {
	Block,
	DEFAULT_TEXT_MAX_LENGTH,
	DEFAULT_TEXTAREA_MAX_LENGTH,
	FieldType,
	MAX_FIELD_OPTIONS,
	MAX_TEXTAREA_MAX_LENGTH,
	MAX_TEXT_MAX_LENGTH,
	isLayoutBlock,
	isScalarPrefillField,
	defaultOptions,
	blockHasChoiceOptions,
	patchTypeChange,
} from '../types/block';

type Props = {
	open: boolean;
	anchor: DOMRect | null;
	anchorEl: HTMLElement | null;
	block: Block;
	strings: Strings;
	serverMaxUploadMb: number;
	fileExtensionOptions: FileExtensionOption[];
	onClose: () => void;
	onUpdate: ( patch: Partial<Block> ) => void;
	onDuplicate: () => void;
	onDelete: () => void;
};

function typeLabel( type: FieldType, strings: Strings ): string {
	const map: Record<FieldType, string> = {
		text: strings.shortText,
		textarea: strings.longText,
		email: strings.email,
		url: strings.link,
		checkbox: strings.checkbox,
		phone: strings.phone,
		number: strings.number,
		select: strings.select,
		radio: strings.radio,
		title: strings.title,
		paragraph: strings.paragraph,
		file: strings.fileUpload,
	};
	return map[ type ];
}

function headerPlaceholder( block: Block, strings: Strings ): string {
	if ( block.type === 'paragraph' ) {
		return strings.paragraphPlaceholder;
	}
	if ( block.type === 'title' ) {
		return strings.titlePlaceholder;
	}
	return strings.questionPlaceholder;
}

export function FieldContextMenu( {
	open,
	anchor,
	anchorEl,
	block,
	strings,
	serverMaxUploadMb,
	fileExtensionOptions,
	onClose,
	onUpdate,
	onDuplicate,
	onDelete,
}: Props ) {
	const menuRef = useRef<HTMLDivElement>( null );
	const [ turnIntoOpen, setTurnIntoOpen ] = useState( false );
	const isLayout = isLayoutBlock( block.type );
	const types = useMemo( () => resolveSlashItems( strings ), [ strings ] );
	const layoutTypes = useMemo( () => resolveLayoutItems( strings ), [ strings ] );
	const options = block.options ?? defaultOptions();
	const hasTextareaMaxLength =
		block.type === 'textarea' && typeof block.maxLength === 'number' && block.maxLength > 0;
	const hasTextMaxLength =
		block.type === 'text' && typeof block.maxLength === 'number' && block.maxLength > 0;
	const hasMinLength =
		block.type === 'text' && typeof block.minLength === 'number' && block.minLength > 0;
	const hasNumberMin = block.type === 'number' && typeof block.min === 'number';
	const hasNumberMax = block.type === 'number' && typeof block.max === 'number';
	const fileExtensions =
		block.allowedExtensions && block.allowedExtensions.length > 0
			? block.allowedExtensions
			: defaultFileExtensions( fileExtensionOptions.length > 0 ? fileExtensionOptions : [] );
	const turnIntoTypes = useMemo(
		() =>
			isLayout
				? layoutTypes.filter( ( item ) => item.type !== block.type )
				: types.filter( ( item ) => item.type !== block.type ),
		[ isLayout, layoutTypes, types, block.type ]
	);

	const clampTextareaMaxLength = ( value: number ) =>
		Math.min( MAX_TEXTAREA_MAX_LENGTH, Math.max( 1, value ) );
	const clampFieldMaxLength = ( value: number ) =>
		Math.min( MAX_TEXT_MAX_LENGTH, Math.max( 1, value ) );

	useEffect( () => {
		if ( ! open ) {
			setTurnIntoOpen( false );
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

	const updateOption = ( index: number, value: string ) => {
		const next = [ ...options ];
		next[ index ] = value;
		onUpdate( { options: next } );
	};

	const minOptions = 2;

	const removeOption = ( index: number ) => {
		if ( options.length <= minOptions ) {
			return;
		}
		onUpdate( { options: options.filter( ( _, i ) => i !== index ) } );
	};

	const addOption = () => {
		if ( options.length >= MAX_FIELD_OPTIONS ) {
			return;
		}
		onUpdate( { options: [ ...options, `Option ${ options.length + 1 }` ] } );
	};

	const placeholder = headerPlaceholder( block, strings );

	return createPortal(
		<div
			ref={ menuRef }
			className="sobiforms-field-context-menu"
			role="dialog"
			aria-label={ strings.fieldSettings }
			style={ { top: `${ top }px`, left: `${ left }px` } }
		>
			<div className="sobiforms-field-context-menu__header">
				<div
					className={
						'sobiforms-field-context-menu__header-title' +
						( isLayout
							? ' sobiforms-field-context-menu__header-title--readonly'
							: ' sobiforms-field-context-menu__header-title--editable' )
					}
				>
					{ block.type === 'paragraph' ? (
						<span className="sobiforms-field-context-menu__header-text">
							{ block.content || placeholder }
						</span>
					) : block.type === 'title' ? (
						<span className="sobiforms-field-context-menu__header-text">
							{ block.label || placeholder }
						</span>
					) : (
						<input
							type="text"
							className="sobiforms-field-context-menu__header-input"
							value={ block.label }
							onChange={ ( e ) => onUpdate( { label: e.target.value } ) }
							placeholder={ placeholder }
							aria-label={ placeholder }
						/>
					) }
				</div>
				<span className="sobiforms-field-context-menu__header-type">{ typeLabel( block.type, strings ) }</span>
			</div>

			{ ! isLayout && (
			<div className="sobiforms-field-context-menu__section">
				<ContextMenuToggle
					label={ strings.required }
					checked={ block.required }
					onChange={ ( checked ) => onUpdate( { required: checked } ) }
				/>
				{ ( block.type !== 'checkbox' || blockHasChoiceOptions( block ) ) && (
					<ContextMenuToggle
						label={ strings.showLabel }
						checked={ block.showLabel !== false }
						onChange={ ( checked ) => onUpdate( { showLabel: checked } ) }
					/>
				) }
				{ block.type === 'number' && (
					<>
						<ContextMenuToggle
							label={ strings.minNumber }
							checked={ hasNumberMin }
							onChange={ ( checked ) =>
								onUpdate( checked ? { min: block.min ?? 0 } : { min: undefined } )
							}
						/>
						{ hasNumberMin && (
							<div className="sobiforms-field-context-menu__number-wrap">
								<input
									type="number"
									className="sobiforms-field-context-menu__number-input"
									step={ 1 }
									value={ block.min ?? 0 }
									onChange={ ( e ) => {
										const parsed = parseInt( e.target.value, 10 );
										onUpdate( {
											min: Number.isFinite( parsed ) ? parsed : 0,
										} );
									} }
									aria-label={ strings.minNumber }
								/>
							</div>
						) }
						<ContextMenuToggle
							label={ strings.maxNumber }
							checked={ hasNumberMax }
							onChange={ ( checked ) =>
								onUpdate( checked ? { max: block.max ?? 100 } : { max: undefined } )
							}
						/>
						{ hasNumberMax && (
							<div className="sobiforms-field-context-menu__number-wrap">
								<input
									type="number"
									className="sobiforms-field-context-menu__number-input"
									step={ 1 }
									value={ block.max ?? 100 }
									onChange={ ( e ) => {
										const parsed = parseInt( e.target.value, 10 );
										onUpdate( {
											max: Number.isFinite( parsed ) ? parsed : 100,
										} );
									} }
									aria-label={ strings.maxNumber }
								/>
							</div>
						) }
					</>
				) }
				{ block.type === 'file' && (
					<>
						<FileExtensionsPicker
							options={ fileExtensionOptions }
							selected={ fileExtensions }
							strings={ strings }
							onChange={ ( allowedExtensions ) => onUpdate( { allowedExtensions } ) }
						/>
						<div className="sobiforms-field-context-menu__file-max">
							<label className="sobiforms-field-context-menu__file-max-label">
								<span className="sobiforms-field-context-menu__file-max-text">
									{ strings.fileMaxSizeMb }
								</span>
								<input
									type="number"
									className="sobiforms-field-context-menu__number-input sobiforms-field-context-menu__file-max-input"
									min={ 1 }
									max={ serverMaxUploadMb }
									step={ 1 }
									value={ block.maxSizeMb ?? 5 }
									onChange={ ( e ) => {
										const parsed = parseInt( e.target.value, 10 );
										const value = Number.isFinite( parsed ) ? parsed : 5;
										onUpdate( {
											maxSizeMb: Math.min(
												serverMaxUploadMb,
												Math.max( 1, value )
											),
										} );
									} }
									aria-label={ strings.fileMaxSizeMb }
								/>
							</label>
						</div>
					</>
				) }
				{ block.type === 'text' && (
					<>
						<ContextMenuToggle
							label={ strings.minCharacters }
							checked={ hasMinLength }
							onChange={ ( checked ) =>
								onUpdate(
									checked ? { minLength: block.minLength ?? 1 } : { minLength: undefined }
								)
							}
						/>
						{ hasMinLength && (
							<div className="sobiforms-field-context-menu__number-wrap">
								<input
									type="number"
									className="sobiforms-field-context-menu__number-input"
									min={ 0 }
									max={ MAX_TEXT_MAX_LENGTH }
									step={ 1 }
									value={ block.minLength ?? 1 }
									onChange={ ( e ) => {
										const parsed = parseInt( e.target.value, 10 );
										onUpdate( {
											minLength: Number.isFinite( parsed ) ? parsed : 1,
										} );
									} }
									aria-label={ strings.minCharacters }
								/>
							</div>
						) }
						<ContextMenuToggle
							label={ strings.maxCharacters }
							checked={ hasTextMaxLength }
							onChange={ ( checked ) =>
								onUpdate(
									checked
										? {
												maxLength:
													block.maxLength ?? DEFAULT_TEXT_MAX_LENGTH,
										  }
										: { maxLength: undefined }
								)
							}
						/>
						{ hasTextMaxLength && (
							<div className="sobiforms-field-context-menu__number-wrap">
								<input
									type="number"
									className="sobiforms-field-context-menu__number-input"
									min={ 1 }
									max={ MAX_TEXT_MAX_LENGTH }
									step={ 1 }
									value={ block.maxLength ?? DEFAULT_TEXT_MAX_LENGTH }
									onChange={ ( e ) => {
										const parsed = parseInt( e.target.value, 10 );
										onUpdate( {
											maxLength: clampFieldMaxLength(
												Number.isFinite( parsed )
													? parsed
													: DEFAULT_TEXT_MAX_LENGTH
											),
										} );
									} }
									aria-label={ strings.maxCharacters }
								/>
							</div>
						) }
					</>
				) }
				{ block.type === 'textarea' && (
					<>
						<ContextMenuToggle
							label={ strings.allowResize }
							checked={ block.resizable !== false }
							onChange={ ( checked ) => onUpdate( { resizable: checked } ) }
						/>
						<ContextMenuToggle
							label={ strings.maxCharacters }
							checked={ hasTextareaMaxLength }
							onChange={ ( checked ) =>
								onUpdate(
									checked
										? {
												maxLength:
													block.maxLength ?? DEFAULT_TEXTAREA_MAX_LENGTH,
										  }
										: { maxLength: undefined }
								)
							}
						/>
						{ hasTextareaMaxLength && (
							<div className="sobiforms-field-context-menu__number-wrap">
								<input
									type="number"
									className="sobiforms-field-context-menu__number-input"
									min={ 1 }
									max={ MAX_TEXTAREA_MAX_LENGTH }
									step={ 1 }
									value={ block.maxLength ?? DEFAULT_TEXTAREA_MAX_LENGTH }
									onChange={ ( e ) => {
										const parsed = parseInt( e.target.value, 10 );
										onUpdate( {
											maxLength: clampTextareaMaxLength(
												Number.isFinite( parsed )
													? parsed
													: DEFAULT_TEXTAREA_MAX_LENGTH
											),
										} );
									} }
									aria-label={ strings.maxCharacters }
								/>
							</div>
						) }
					</>
				) }
				{ isScalarPrefillField( block.type ) && (
					<>
						<ContextMenuToggle
							label={ strings.prefillKey }
							checked={ block.urlPrefill === true }
							onChange={ ( checked ) => onUpdate( { urlPrefill: checked } ) }
						/>
						{ block.urlPrefill && (
							<div className="sobiforms-field-context-menu__toggle-detail">
								<input
									type="text"
									className="sobiforms-field-context-menu__option-input"
									value={ block.prefillKey ?? '' }
									onChange={ ( e ) => {
										const next = e.target.value.trim();
										onUpdate( { prefillKey: next || undefined } );
									} }
									placeholder={ strings.prefillKeyPlaceholder }
								/>
							</div>
						) }
					</>
				) }
			</div>
			) }

			{ block.type === 'select' && (
				<>
					<div className="sobiforms-field-context-menu__divider" />
					<div className="sobiforms-field-context-menu__section sobiforms-field-context-menu__options">
						<div className="sobiforms-field-context-menu__turn-label">{ strings.fieldOptions }</div>
						{ options.map( ( option, index ) => (
							<div key={ index } className="sobiforms-field-context-menu__option-row">
								<input
									type="text"
									className="sobiforms-field-context-menu__option-input"
									value={ option }
									onChange={ ( e ) => updateOption( index, e.target.value ) }
									aria-label={ `${ strings.fieldOptions } ${ index + 1 }` }
								/>
								<button
									type="button"
									className="sobiforms-field-context-menu__option-remove"
									onClick={ () => removeOption( index ) }
									disabled={ options.length <= minOptions }
									aria-label={ strings.removeOption }
								>
									{ strings.removeOption }
								</button>
							</div>
						) ) }
						<button
							type="button"
							className="sobiforms-field-context-menu__option-add"
							onClick={ addOption }
							disabled={ options.length >= MAX_FIELD_OPTIONS }
						>
							{ strings.addOption }
						</button>
					</div>
				</>
			) }

			<div className="sobiforms-field-context-menu__divider" />

			<div className="sobiforms-field-context-menu__section">
				<button
					type="button"
					className="sobiforms-field-context-menu__action sobiforms-field-context-menu__action--danger"
					onClick={ () => {
						onDelete();
						onClose();
					} }
				>
					<span className="sobiforms-field-context-menu__action-icon" aria-hidden="true">
						<BuilderIcon icon={ Trash2 } size={ 14 } />
					</span>
					<span>{ strings.delete }</span>
				</button>
				<button
					type="button"
					className="sobiforms-field-context-menu__action"
					onClick={ () => {
						onDuplicate();
						onClose();
					} }
				>
					<span className="sobiforms-field-context-menu__action-icon" aria-hidden="true">
						<BuilderIcon icon={ Copy } size={ 14 } />
					</span>
					<span>{ strings.duplicate }</span>
				</button>
			</div>

			<div className="sobiforms-field-context-menu__divider" />

			<div
				className={ `sobiforms-field-context-menu__submenu-wrap${
					turnIntoOpen ? ' is-open' : ''
				}` }
			>
				<button
					type="button"
					className="sobiforms-field-context-menu__action sobiforms-field-context-menu__submenu-trigger"
					onClick={ () => setTurnIntoOpen( ( open ) => ! open ) }
					aria-expanded={ turnIntoOpen }
					aria-haspopup="menu"
				>
					<span className="sobiforms-field-context-menu__submenu-trigger-label">
						<span>{ strings.turnInto }</span>
					</span>
					<span className="sobiforms-field-context-menu__submenu-chevron" aria-hidden="true">
						<BuilderIcon icon={ ChevronRight } size={ 14 } />
					</span>
				</button>
				{ turnIntoOpen && (
					<div className="sobiforms-field-context-menu__submenu" role="menu">
						{ turnIntoTypes.map( ( item ) => (
							<button
								key={ item.type }
								type="button"
								role="menuitem"
								className="sobiforms-field-context-menu__type"
								onClick={ () => {
									onUpdate( patchTypeChange( block, item.type ) );
									setTurnIntoOpen( false );
								} }
							>
								<span>{ item.label }</span>
							</button>
						) ) }
					</div>
				) }
			</div>
		</div>,
		document.body
	);
}
