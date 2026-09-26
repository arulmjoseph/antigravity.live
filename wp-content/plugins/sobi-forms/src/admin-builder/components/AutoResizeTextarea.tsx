import { useCallback, useLayoutEffect, useRef, type TextareaHTMLAttributes } from 'react';

type Props = TextareaHTMLAttributes<HTMLTextAreaElement>;

export function AutoResizeTextarea( { value, onChange, rows = 1, ...rest }: Props ) {
	const ref = useRef<HTMLTextAreaElement>( null );

	const resize = useCallback( () => {
		const el = ref.current;
		if ( ! el ) {
			return;
		}
		// WP admin sets textarea min-height/height with !important — inline height must win.
		el.style.setProperty( 'height', '0', 'important' );
		el.style.setProperty( 'height', `${ el.scrollHeight }px`, 'important' );
	}, [] );

	useLayoutEffect( () => {
		resize();
		const el = ref.current;
		if ( ! el || typeof ResizeObserver === 'undefined' ) {
			return;
		}
		const observer = new ResizeObserver( () => resize() );
		observer.observe( el );
		return () => observer.disconnect();
	}, [ value, resize ] );

	return (
		<textarea
			{ ...rest }
			ref={ ref }
			rows={ rows }
			value={ value ?? '' }
			onChange={ ( e ) => {
				onChange?.( e );
				resize();
			} }
			onInput={ resize }
		/>
	);
}
