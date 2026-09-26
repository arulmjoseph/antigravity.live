import { useEditor, EditorContent } from '@tiptap/react';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';
import { useCallback, useEffect, useMemo, useState } from 'react';
import { filterSlashItems, resolveSlashItems } from '../lib/filterSlashItems';
import type { Strings } from '../lib/i18n';
import { FieldType } from '../types/block';
import { SlashMenu } from './SlashMenu';

type Props = {
	strings: Strings;
	onInsert: ( type: FieldType ) => void;
	autoFocus?: boolean;
};

export function InsertionLine( { strings, onInsert, autoFocus }: Props ) {
	const [ slashOpen, setSlashOpen ] = useState( false );
	const [ query, setQuery ] = useState( '' );
	const [ selectedIndex, setSelectedIndex ] = useState( 0 );

	const slashItems = useMemo( () => resolveSlashItems( strings ), [ strings ] );

	const editor = useEditor( {
		extensions: [
			StarterKit.configure( { heading: false, bulletList: false, orderedList: false } ),
			Placeholder.configure( { placeholder: strings.insertPlaceholder } ),
		],
		onUpdate: ( { editor: ed } ) => {
			const text = ed.getText();
			if ( text.startsWith( '/' ) ) {
				setSlashOpen( true );
				setQuery( text.slice( 1 ) );
				setSelectedIndex( 0 );
			} else {
				setSlashOpen( false );
				setQuery( '' );
			}
		},
	} );

	const selectCurrent = useCallback( () => {
		const items = filterSlashItems( slashItems, query );
		const item = items[ Math.min( selectedIndex, Math.max( 0, items.length - 1 ) ) ];
		if ( item ) {
			onInsert( item.type );
			editor?.commands.clearContent();
			setSlashOpen( false );
			setQuery( '' );
			setSelectedIndex( 0 );
		}
	}, [ query, selectedIndex, onInsert, editor, slashItems ] );

	useEffect( () => {
		if ( ! editor ) {
			return;
		}

		const handleKeyDown = ( event: KeyboardEvent ) => {
			if ( ! slashOpen ) {
				return;
			}

			const items = filterSlashItems( slashItems, query );

			if ( event.key === 'ArrowDown' ) {
				event.preventDefault();
				setSelectedIndex( ( i ) => Math.min( i + 1, items.length - 1 ) );
			}
			if ( event.key === 'ArrowUp' ) {
				event.preventDefault();
				setSelectedIndex( ( i ) => Math.max( 0, i - 1 ) );
			}
			if ( event.key === 'Escape' ) {
				event.preventDefault();
				setSlashOpen( false );
				editor.commands.clearContent();
			}
			if ( event.key === 'Enter' ) {
				event.preventDefault();
				selectCurrent();
			}
		};

		editor.view.dom.addEventListener( 'keydown', handleKeyDown );
		return () => editor.view.dom.removeEventListener( 'keydown', handleKeyDown );
	}, [ editor, slashOpen, query, selectCurrent, slashItems ] );

	useEffect( () => {
		if ( autoFocus ) {
			editor?.commands.focus();
		}
	}, [ autoFocus, editor ] );

	return (
		<div className="relative pl-10">
			<EditorContent editor={ editor } className="min-h-[2rem] text-base text-neutral-700" />
			{ slashOpen && (
				<SlashMenu
					items={ slashItems }
					query={ query }
					selectedIndex={ selectedIndex }
					strings={ strings }
					onSelect={ ( item ) => {
						onInsert( item.type );
						editor?.commands.clearContent();
						setSlashOpen( false );
						setQuery( '' );
					} }
					onIndexChange={ setSelectedIndex }
				/>
			) }
		</div>
	);
}
