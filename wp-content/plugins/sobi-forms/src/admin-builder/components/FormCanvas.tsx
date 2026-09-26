import {
	DndContext,
	DragEndEvent,
	KeyboardSensor,
	PointerSensor,
	closestCenter,
	useSensor,
	useSensors,
} from '@dnd-kit/core';
import {
	SortableContext,
	sortableKeyboardCoordinates,
	verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { memo, useCallback, useMemo, useState, Fragment, type Dispatch } from 'react';
import type { Block } from '../types/block';
import { BuilderAction, BuilderState } from '../hooks/useBlocksReducer';
import type { Strings } from '../lib/i18n';
import { AddFieldSlot } from './AddFieldSlot';
import { BlockRow } from './BlockRow';
import { FieldPickerOverlay } from './FieldPickerOverlay';
import { SubmitButtonRow } from './SubmitButtonRow';
import { FieldType } from '../types/block';
import type { FileExtensionOption } from '../lib/fileExtensions';

type Props = {
	blocks: BuilderState['blocks'];
	strings: Strings;
	dispatch: Dispatch<BuilderAction>;
	submitButtonText: string;
	defaultSubmitButtonText: string;
	onSubmitButtonTextChange: ( value: string ) => void;
	serverMaxUploadMb: number;
	fileExtensionOptions: FileExtensionOption[];
	defaultFileExtensions: string[];
};

export const FormCanvas = memo( function FormCanvas( {
	blocks,
	strings,
	dispatch,
	submitButtonText,
	defaultSubmitButtonText,
	onSubmitButtonTextChange,
	serverMaxUploadMb,
	fileExtensionOptions,
	defaultFileExtensions,
}: Props ) {
	const [ pickerIndex, setPickerIndex ] = useState<number | null>( null );

	const sensors = useSensors(
		useSensor( PointerSensor, { activationConstraint: { distance: 6 } } ),
		useSensor( KeyboardSensor, { coordinateGetter: sortableKeyboardCoordinates } )
	);

	const sortableIds = useMemo( () => blocks.map( ( b ) => b.id ), [ blocks ] );

	const updateBlock = useCallback(
		( id: string, patch: Partial<Block> ) => {
			dispatch( { type: 'update_block', id, patch } );
		},
		[ dispatch ]
	);

	const removeBlock = useCallback(
		( id: string ) => {
			dispatch( { type: 'remove_block', id } );
		},
		[ dispatch ]
	);

	const duplicateBlock = useCallback(
		( id: string ) => {
			dispatch( { type: 'duplicate_block', id } );
		},
		[ dispatch ]
	);

	const handleDragEnd = useCallback(
		( event: DragEndEvent ) => {
			const { active, over } = event;
			if ( ! over || active.id === over.id ) {
				return;
			}
			const from = blocks.findIndex( ( b ) => b.id === active.id );
			const to = blocks.findIndex( ( b ) => b.id === over.id );
			if ( from >= 0 && to >= 0 ) {
				dispatch( { type: 'reorder', from, to } );
			}
		},
		[ blocks, dispatch ]
	);

	const openPicker = useCallback( ( index: number ) => {
		setPickerIndex( index );
	}, [] );

	const closePicker = useCallback( () => {
		setPickerIndex( null );
	}, [] );

	const handlePick = useCallback(
		( type: FieldType ) => {
			if ( pickerIndex === null ) {
				return;
			}
			dispatch( {
				type: 'add_block',
				blockType: type,
				index: pickerIndex,
				defaultFileExtensions: type === 'file' ? defaultFileExtensions : undefined,
			} );
			setPickerIndex( null );
		},
		[ defaultFileExtensions, dispatch, pickerIndex ]
	);

	return (
		<div className="sobiforms-builder-app__canvas">
			<DndContext sensors={ sensors } collisionDetection={ closestCenter } onDragEnd={ handleDragEnd }>
				<SortableContext items={ sortableIds } strategy={ verticalListSortingStrategy }>
					<div className="sobiforms-builder-field-list">
						{ blocks.length > 0 && (
							<AddFieldSlot strings={ strings } onAdd={ () => openPicker( 0 ) } />
						) }
						{ blocks.map( ( block, index ) => (
							<Fragment key={ block.id }>
								<BlockRow
									block={ block }
									strings={ strings }
									serverMaxUploadMb={ serverMaxUploadMb }
									fileExtensionOptions={ fileExtensionOptions }
									onUpdateBlock={ updateBlock }
									onRemoveBlock={ removeBlock }
									onDuplicateBlock={ duplicateBlock }
								/>
								<AddFieldSlot strings={ strings } onAdd={ () => openPicker( index + 1 ) } />
							</Fragment>
						) ) }
					</div>
				</SortableContext>
			</DndContext>

			{ blocks.length > 0 && (
				<SubmitButtonRow
					strings={ strings }
					value={ submitButtonText }
					defaultText={ defaultSubmitButtonText }
					onChange={ onSubmitButtonTextChange }
				/>
			) }

			{ blocks.length === 0 && (
				<div className="sobiforms-builder-empty">
					<p className="sobiforms-builder-empty__title">{ strings.emptyStateTitle }</p>
					<p className="sobiforms-builder-empty__hint">{ strings.emptyStateHint }</p>
					<button
						type="button"
						className="button button-primary button-hero"
						onClick={ () => openPicker( 0 ) }
					>
						{ strings.addField }
					</button>
				</div>
			) }

			<FieldPickerOverlay
				open={ pickerIndex !== null }
				strings={ strings }
				onSelect={ handlePick }
				onClose={ closePicker }
			/>
		</div>
	);
} );
