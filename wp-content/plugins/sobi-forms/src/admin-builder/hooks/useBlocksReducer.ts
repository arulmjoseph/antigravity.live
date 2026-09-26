import { useReducer } from 'react';
import { Block, FieldType, createBlock } from '../types/block';

export type BuilderState = {
	blocks: Block[];
};

export type BuilderAction =
	| { type: 'add_block'; blockType: FieldType; index?: number; defaultFileExtensions?: string[] }
	| { type: 'update_block'; id: string; patch: Partial<Block> }
	| { type: 'remove_block'; id: string }
	| { type: 'duplicate_block'; id: string }
	| { type: 'reorder'; from: number; to: number };

function reducer( state: BuilderState, action: BuilderAction ): BuilderState {
	switch ( action.type ) {
		case 'add_block': {
			const block = createBlock( action.blockType );
			if ( block.type === 'file' && action.defaultFileExtensions?.length ) {
				block.allowedExtensions = [ ...action.defaultFileExtensions ];
			}
			const index = action.index ?? state.blocks.length;
			const blocks = [ ...state.blocks ];
			blocks.splice( index, 0, block );
			return { ...state, blocks };
		}
		case 'update_block':
			return {
				...state,
				blocks: state.blocks.map( ( b ) =>
					b.id === action.id ? { ...b, ...action.patch } : b
				),
			};
		case 'remove_block':
			return { ...state, blocks: state.blocks.filter( ( b ) => b.id !== action.id ) };
		case 'duplicate_block': {
			const source = state.blocks.find( ( b ) => b.id === action.id );
			if ( ! source ) {
				return state;
			}
			const copy: Block = {
				...source,
				id: createBlock( source.type ).id,
				options: source.options ? [ ...source.options ] : undefined,
				allowedExtensions: source.allowedExtensions ? [ ...source.allowedExtensions ] : undefined,
			};
			const index = state.blocks.findIndex( ( b ) => b.id === action.id );
			const blocks = [ ...state.blocks ];
			blocks.splice( index + 1, 0, copy );
			return { ...state, blocks };
		}
		case 'reorder': {
			const blocks = [ ...state.blocks ];
			const [ moved ] = blocks.splice( action.from, 1 );
			blocks.splice( action.to, 0, moved );
			return { ...state, blocks };
		}
		default:
			return state;
	}
}

export function useBlocksReducer( initialBlocks: Block[] ) {
	return useReducer( reducer, { blocks: initialBlocks } );
}
