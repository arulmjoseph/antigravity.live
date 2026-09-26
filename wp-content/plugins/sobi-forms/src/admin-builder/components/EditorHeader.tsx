import type { RefObject } from 'react';
import type { Strings } from '../lib/i18n';

type Props = {
	strings: Strings;
	formTitle: string;
	onFormTitleChange: ( value: string ) => void;
	onOpenEmbed: () => void;
	embedButtonRef: RefObject<HTMLButtonElement | null>;
	isDirty: boolean;
	saveFlash: boolean;
	isSaving: boolean;
	saveError: string | null;
};

export function EditorHeader( {
	strings,
	formTitle,
	onFormTitleChange,
	onOpenEmbed,
	embedButtonRef,
	isDirty,
	saveFlash,
	isSaving,
	saveError,
}: Props ) {
	const saveLabel = isSaving ? strings.saving : saveFlash ? strings.formSaved : strings.saveForm;

	return (
		<header className="sobiforms-editor-header">
			<label className="sobiforms-editor-header__title-wrap">
				<span className="screen-reader-text">{ strings.formName }</span>
				<input
					type="text"
					name="sobiforms_form_title"
					id="sobiforms_form_title"
					className="sobiforms-editor-header__title"
					value={ formTitle }
					onChange={ ( e ) => onFormTitleChange( e.target.value ) }
					placeholder={ strings.formNamePlaceholder }
					required
				/>
			</label>

			<div className="sobiforms-editor-header__actions">
				{ saveError && (
					<p className="sobiforms-editor-header__save-error" role="alert">
						{ saveError }
					</p>
				) }
				<button
					ref={ embedButtonRef }
					type="button"
					className="button"
					onClick={ onOpenEmbed }
				>
					{ strings.embedForm }
				</button>
				<button
					type="submit"
					form="sobiforms-form-builder-form"
					className={
						'button button-primary sobiforms-editor-header__save' +
						( saveFlash ? ' is-saved' : '' )
					}
					disabled={ ( ! isDirty && ! saveFlash ) || isSaving }
				>
					{ saveLabel }
				</button>
			</div>
		</header>
	);
}
