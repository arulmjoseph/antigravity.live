import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import { memo, useCallback, useRef, useState } from 'react';
import type { Strings } from '../lib/i18n';
import {
	Block,
	choiceBadgeLetter,
	blockHasChoiceOptions,
	defaultOptions,
	isLayoutBlock,
	MAX_FIELD_OPTIONS,
} from '../types/block';
import type { FileExtensionOption } from '../lib/fileExtensions';
import { GripVertical, Settings, Trash2 } from 'lucide-react';
import { BuilderIcon } from './BuilderIcon';
import { AutoResizeTextarea } from './AutoResizeTextarea';
import { FieldContextMenu } from './FieldContextMenu';

type Props = {
	block: Block;
	strings: Strings;
	serverMaxUploadMb: number;
	fileExtensionOptions: FileExtensionOption[];
	onUpdateBlock: ( id: string, patch: Partial<Block> ) => void;
	onRemoveBlock: ( id: string ) => void;
	onDuplicateBlock: ( id: string ) => void;
};

export const BlockRow = memo( function BlockRow( {
	block,
	strings,
	serverMaxUploadMb,
	fileExtensionOptions,
	onUpdateBlock,
	onRemoveBlock,
	onDuplicateBlock,
}: Props ) {
	const [ hovered, setHovered ] = useState( false );
	const [ menuOpen, setMenuOpen ] = useState( false );
	const [ menuAnchor, setMenuAnchor ] = useState<DOMRect | null>( null );
	const settingsRef = useRef<HTMLButtonElement>( null );

	const { attributes, listeners, setNodeRef, transform, transition, isDragging } = useSortable( {
		id: block.id,
	} );

	const style = {
		transform: CSS.Transform.toString( transform ),
		transition,
		opacity: isDragging ? 0.5 : 1,
	};

	const showHandles = hovered || menuOpen;
	const isLayout = isLayoutBlock( block.type );
	const showsLabel = ! isLayout && block.showLabel !== false;
	const requiredOnField = block.required && ! showsLabel;
	const options = block.options ?? defaultOptions();
	const isMultiCheckbox = block.type === 'checkbox' && blockHasChoiceOptions( block );

	const openMenu = () => {
		if ( settingsRef.current ) {
			setMenuAnchor( settingsRef.current.getBoundingClientRect() );
		}
		setMenuOpen( true );
	};

	const closeMenu = useCallback( () => {
		setMenuOpen( false );
		setMenuAnchor( null );
	}, [] );

	const onUpdate = useCallback(
		( patch: Partial<Block> ) => onUpdateBlock( block.id, patch ),
		[ block.id, onUpdateBlock ]
	);

	const onRemove = useCallback( () => onRemoveBlock( block.id ), [ block.id, onRemoveBlock ] );

	const onDuplicate = useCallback(
		() => onDuplicateBlock( block.id ),
		[ block.id, onDuplicateBlock ]
	);

	const fieldPreviewClass = 'sobiforms-block-row__field-preview';

	const updateOption = ( index: number, value: string ) => {
		const next = [ ...options ];
		next[ index ] = value;
		onUpdate( { options: next } );
	};

	const addPreviewOption = () => {
		if ( options.length >= MAX_FIELD_OPTIONS ) {
			return;
		}
		onUpdate( { options: [ ...options, `Option ${ options.length + 1 }` ] } );
	};

	const minPreviewOptions = isMultiCheckbox ? 1 : 2;

	const removePreviewOption = ( index: number ) => {
		if ( options.length <= minPreviewOptions ) {
			return;
		}
		onUpdate( { options: options.filter( ( _, i ) => i !== index ) } );
	};

	return (
		<div
			ref={ setNodeRef }
			style={ style }
			className="sobiforms-block-row group relative flex items-start gap-3"
			onMouseEnter={ () => setHovered( true ) }
			onMouseLeave={ () => setHovered( false ) }
		>
			<div
				className={ `sobiforms-block-row__handles${ showHandles ? ' is-visible' : '' }` }
				aria-hidden={ ! showHandles }
			>
				<button
					ref={ settingsRef }
					type="button"
					onClick={ () => ( menuOpen ? closeMenu() : openMenu() ) }
					className={ `sobiforms-block-row__handle sobiforms-handle-tooltip${ menuOpen ? ' is-active' : '' }` }
					data-tooltip={ strings.fieldSettings }
					aria-label={ strings.fieldSettings }
					aria-expanded={ menuOpen }
				>
						<BuilderIcon icon={ Settings } />
				</button>
				<button
					type="button"
					className="sobiforms-block-row__handle sobiforms-block-row__handle--drag sobiforms-handle-tooltip"
					data-tooltip={ strings.move }
					aria-label={ strings.move }
					{ ...attributes }
					{ ...listeners }
				>
					<BuilderIcon icon={ GripVertical } />
				</button>
			</div>

			<div className="sobiforms-block-row__content relative min-w-0 flex-1 rounded-lg px-2 py-2 transition hover:bg-neutral-100">
				<div className="space-y-2">
					{ block.type === 'title' && (
						<input
							type="text"
							value={ block.label }
							onChange={ ( e ) => onUpdate( { label: e.target.value } ) }
							placeholder={ strings.titlePlaceholder }
							className="sobiforms-block-row__title-input min-w-0 w-full border-0 bg-transparent outline-none"
							aria-label={ strings.titlePlaceholder }
						/>
					) }
					{ block.type === 'paragraph' && (
						<AutoResizeTextarea
							value={ block.content ?? '' }
							onChange={ ( e ) => onUpdate( { content: e.target.value } ) }
							placeholder={ strings.paragraphPlaceholder }
							className="sobiforms-block-row__paragraph-input min-w-0 w-full border-0 bg-transparent outline-none"
							aria-label={ strings.paragraphPlaceholder }
						/>
					) }
					{ showsLabel && (
						<div className="sobiforms-block-row__label-row flex items-baseline gap-1">
							<input
								type="text"
								value={ block.label }
								onChange={ ( e ) => onUpdate( { label: e.target.value } ) }
								placeholder={ strings.questionPlaceholder }
								className="sobiforms-block-row__label-input min-w-0 flex-1 border-0 bg-transparent outline-none"
								aria-label={ strings.questionPlaceholder }
							/>
							{ block.required && (
								<span className="sobiforms-block-row__required" aria-hidden="true">*</span>
							) }
						</div>
					) }
					{ ! isLayout && block.type === 'textarea' && (
						<div
							className={
								'sobiforms-block-row__field-wrap' +
								( requiredOnField ? ' sobiforms-block-row__field-wrap--required-slot' : '' )
							}
						>
							<AutoResizeTextarea
								value={ block.placeholder }
								onChange={ ( e ) => onUpdate( { placeholder: e.target.value } ) }
								placeholder={ strings.answerPlaceholder }
								className={ `sobiforms-block-row__field-preview sobiforms-block-row__textarea-preview` }
							/>
							{ requiredOnField && (
								<span
									className="sobiforms-block-row__required sobiforms-block-row__required--on-field"
									aria-hidden="true"
								>
									*
								</span>
							) }
						</div>
					) }
					{ ! isLayout && block.type === 'radio' && (
						<div className="sobiforms-builder-choice-list">
							{ options.map( ( option, index ) => (
								<div key={ index } className="sobiforms-builder-choice-tile">
									<span className="sobiforms-builder-choice-tile__badge" aria-hidden="true">
										{ choiceBadgeLetter( index ) }
									</span>
									<input
										type="text"
										className="sobiforms-builder-choice-tile__input"
										value={ option }
										onChange={ ( e ) => updateOption( index, e.target.value ) }
										placeholder={ strings.answerPlaceholder }
										aria-label={ `${ strings.fieldOptions } ${ index + 1 }` }
									/>
									<button
										type="button"
										className="sobiforms-builder-choice-tile__remove"
										onClick={ () => removePreviewOption( index ) }
										disabled={ options.length <= minPreviewOptions }
										aria-label={ strings.removeOption }
									>
										<BuilderIcon icon={ Trash2 } size={ 14 } />
									</button>
								</div>
							) ) }
							<button
								type="button"
								className="sobiforms-builder-choice-add"
								onClick={ addPreviewOption }
								disabled={ options.length >= MAX_FIELD_OPTIONS }
							>
								<span
									className="sobiforms-builder-choice-tile__badge sobiforms-builder-choice-tile__badge--ghost"
									aria-hidden="true"
								>
									+
								</span>
								<span>{ strings.addOption }</span>
							</button>
						</div>
					) }
					{ ! isLayout && isMultiCheckbox && (
						<div className="sobiforms-builder-checkbox-list">
							{ options.map( ( option, index ) => (
								<div key={ index } className="sobiforms-builder-checkbox-row">
									<span className="sobiforms-builder-checkbox-box" aria-hidden="true" />
									<input
										type="text"
										className="sobiforms-builder-checkbox-row__input"
										value={ option }
										onChange={ ( e ) => updateOption( index, e.target.value ) }
										placeholder={ strings.answerPlaceholder }
										aria-label={ `${ strings.fieldOptions } ${ index + 1 }` }
									/>
									<button
										type="button"
										className="sobiforms-builder-checkbox-row__remove"
										onClick={ () => removePreviewOption( index ) }
										disabled={ options.length <= minPreviewOptions }
										aria-label={ strings.removeOption }
									>
										<BuilderIcon icon={ Trash2 } size={ 14 } />
									</button>
								</div>
							) ) }
							<button
								type="button"
								className="sobiforms-builder-checkbox-add"
								onClick={ addPreviewOption }
								disabled={ options.length >= MAX_FIELD_OPTIONS }
							>
								<span className="sobiforms-builder-checkbox-box sobiforms-builder-checkbox-box--ghost" aria-hidden="true" />
								<span>{ strings.addOption }</span>
							</button>
						</div>
					) }
					{ ! isLayout &&
						block.type !== 'checkbox' &&
						block.type !== 'textarea' &&
						block.type !== 'radio' &&
						block.type !== 'file' && (
						<div
							className={
								'sobiforms-block-row__field-wrap' +
								( requiredOnField ? ' sobiforms-block-row__field-wrap--required-slot' : '' )
							}
						>
							<input
								type="text"
								value={ block.placeholder }
								onChange={ ( e ) => onUpdate( { placeholder: e.target.value } ) }
								placeholder={ strings.answerPlaceholder }
								className={ fieldPreviewClass }
							/>
							{ requiredOnField && (
								<span
									className="sobiforms-block-row__required sobiforms-block-row__required--on-field"
									aria-hidden="true"
								>
									*
								</span>
							) }
						</div>
					) }
					{ ! isLayout && block.type === 'checkbox' && ! isMultiCheckbox && (
						<div className="sobiforms-builder-checkbox-row flex items-center gap-2 text-sm text-neutral-600">
							<span className="sobiforms-builder-checkbox-box" aria-hidden="true" />
							{ strings.preview }
						</div>
					) }
					{ ! isLayout && block.type === 'file' && (
						<div
							className={
								'sobiforms-block-row__field-wrap' +
								( requiredOnField ? ' sobiforms-block-row__field-wrap--required-slot' : '' )
							}
						>
							<div className="sobiforms-block-row__file-preview" aria-hidden="true">
								{ strings.fileUploadPreview }
							</div>
							{ requiredOnField && (
								<span
									className="sobiforms-block-row__required sobiforms-block-row__required--on-field"
									aria-hidden="true"
								>
									*
								</span>
							) }
						</div>
					) }
				</div>
			</div>

			{ menuOpen && menuAnchor && (
				<FieldContextMenu
					open={ menuOpen }
					anchor={ menuAnchor }
					anchorEl={ settingsRef.current }
					block={ block }
					strings={ strings }
					serverMaxUploadMb={ serverMaxUploadMb }
					fileExtensionOptions={ fileExtensionOptions }
					onClose={ closeMenu }
					onUpdate={ onUpdate }
					onDuplicate={ onDuplicate }
					onDelete={ onRemove }
				/>
			) }
		</div>
	);
} );
