import { useCallback, useEffect, useMemo, useRef, useState, type Dispatch } from 'react';
import { BuilderAction, BuilderState } from '../hooks/useBlocksReducer';
import type { Strings } from '../lib/i18n';
import { toFieldsSchema } from '../lib/schema';
import type { FileExtensionOption } from '../lib/fileExtensions';
import { createHiddenField, type HiddenField } from '../types/hidden-field';
import { useUnsavedChangesWarning } from '../hooks/useUnsavedChangesWarning';
import { EmbedOverlay } from './EmbedOverlay';
import { EditorHeader } from './EditorHeader';
import { FormCanvas } from './FormCanvas';
import { FormSettings, PageOption, SettingsPanel, type EmailFieldOption } from './SettingsPanel';

type Props = {
	state: BuilderState;
	settings: FormSettings;
	pages: PageOption[];
	strings: Strings;
	formId: number;
	slug: string;
	formTitle: string;
	submitButtonText: string;
	defaultSubmitButtonText: string;
	dispatch: Dispatch<BuilderAction>;
	form: HTMLFormElement;
	ajaxUrl: string;
	initialHiddenFields: HiddenField[];
	serverMaxUploadMb: number;
	fileExtensionOptions: FileExtensionOption[];
	defaultFileExtensions: string[];
};

function mapSaveError( code: string, strings: Strings ): string {
	switch ( code ) {
		case 'invalid_email':
			return strings.saveErrorInvalidEmail;
		case 'invalid_redirect':
			return strings.saveErrorInvalidRedirect;
		case 'invalid_close':
			return strings.saveErrorInvalidClose;
		case 'invalid_limit':
			return strings.saveErrorInvalidLimit;
		default:
			return strings.saveErrorGeneric;
	}
}

export function FormEditor( {
	state,
	settings,
	pages,
	strings,
	formId: initialFormId,
	slug: initialSlug,
	formTitle: initialFormTitle,
	submitButtonText: initialSubmitButtonText,
	defaultSubmitButtonText,
	dispatch,
	form,
	ajaxUrl,
	initialHiddenFields,
	serverMaxUploadMb,
	fileExtensionOptions,
	defaultFileExtensions,
}: Props ) {
	const [ submitButtonText, setSubmitButtonText ] = useState( initialSubmitButtonText );
	const [ formTitle, setFormTitle ] = useState( initialFormTitle );
	const [ embedOpen, setEmbedOpen ] = useState( false );
	const [ isDirty, setIsDirty ] = useState( false );
	const [ saveFlash, setSaveFlash ] = useState( false );
	const [ isSaving, setIsSaving ] = useState( false );
	const [ saveError, setSaveError ] = useState<string | null>( null );
	const [ currentFormId, setCurrentFormId ] = useState( initialFormId );
	const [ currentSlug, setCurrentSlug ] = useState( initialSlug );
	const [ paused, setPaused ] = useState( settings.paused );
	const [ closeEnabled, setCloseEnabled ] = useState( settings.close_enabled );
	const [ closeAt, setCloseAt ] = useState( settings.close_at );
	const [ closeLimitEnabled, setCloseLimitEnabled ] = useState( settings.close_limit_enabled );
	const [ closeLimit, setCloseLimit ] = useState( settings.close_limit );
	const [ submissionCount, setSubmissionCount ] = useState( settings.submission_count );
	const [ saveSubmissions, setSaveSubmissions ] = useState( settings.save_submissions );
	const [ recipientEmail, setRecipientEmail ] = useState( settings.recipient_email );
	const [ emailEnabled, setEmailEnabled ] = useState( settings.recipient_email.trim() !== '' );
	const [ confirmationEnabled, setConfirmationEnabled ] = useState(
		settings.send_confirmation_email
	);
	const [ confirmationEmailFieldId, setConfirmationEmailFieldId ] = useState(
		settings.confirmation_email_field_id || ''
	);
	const [ hiddenFields, setHiddenFields ] = useState<HiddenField[]>( () =>
		initialHiddenFields.length > 0 ? initialHiddenFields : [ createHiddenField() ]
	);
	const embedButtonRef = useRef<HTMLButtonElement>( null );
	const saveFlashTimerRef = useRef<number | null>( null );

	useUnsavedChangesWarning( isDirty, strings.unsavedChangesConfirm );

	const emailFields = useMemo( (): EmailFieldOption[] => {
		return state.blocks
			.filter( ( block ) => block.type === 'email' )
			.map( ( block ) => ( {
				id: block.id,
				label: block.label.trim() || strings.email,
				required: block.required,
			} ) );
	}, [ state.blocks, strings.email ] );

	const hasFileFields = useMemo(
		() => state.blocks.some( ( block ) => block.type === 'file' ),
		[ state.blocks ]
	);

	useEffect( () => {
		if ( hasFileFields && ! saveSubmissions ) {
			setSaveSubmissions( true );
		}
	}, [ hasFileFields, saveSubmissions ] );

	useEffect( () => {
		if ( emailFields.length === 0 ) {
			if ( confirmationEmailFieldId !== '' ) {
				setConfirmationEmailFieldId( '' );
			}
			return;
		}

		const hasSelected = emailFields.some(
			( field ) => field.id === confirmationEmailFieldId
		);
		if ( ! hasSelected || emailFields.length === 1 ) {
			const nextId = emailFields[0].id;
			if ( nextId !== confirmationEmailFieldId ) {
				setConfirmationEmailFieldId( nextId );
			}
		}
	}, [ emailFields, confirmationEmailFieldId ] );

	const markDirty = useCallback( () => {
		setSaveError( null );
		setIsDirty( true );
	}, [] );

	const wrappedDispatch: Dispatch<BuilderAction> = useCallback(
		( action ) => {
			markDirty();
			dispatch( action );
		},
		[ dispatch, markDirty ]
	);

	const triggerSaveFlash = useCallback( () => {
		setSaveFlash( true );
		if ( saveFlashTimerRef.current ) {
			window.clearTimeout( saveFlashTimerRef.current );
		}
		saveFlashTimerRef.current = window.setTimeout( () => setSaveFlash( false ), 2500 );
	}, [] );

	const syncFieldsJson = useCallback( () => {
		const jsonInput = document.getElementById( 'sobiforms-fields-json' ) as HTMLInputElement | null;
		if ( jsonInput ) {
			jsonInput.value = JSON.stringify( toFieldsSchema( state.blocks, hiddenFields ) );
		}
	}, [ state.blocks, hiddenFields ] );

	useEffect( () => {
		const timer = window.setTimeout( () => syncFieldsJson(), 300 );
		return () => window.clearTimeout( timer );
	}, [ syncFieldsJson ] );

	const performSave = useCallback( async () => {
		if ( ! isDirty || isSaving ) {
			return;
		}

		syncFieldsJson();
		setIsSaving( true );
		setSaveError( null );

		const formData = new FormData( form );
		formData.append( 'action', 'sobiforms_save_form' );
		formData.delete( 'sobiforms_save_form' );

		try {
			const response = await fetch( ajaxUrl, {
				method: 'POST',
				body: formData,
				credentials: 'same-origin',
			} );
			const json = await response.json().catch( () => null );

			if ( ! json || typeof json.success !== 'boolean' ) {
				setSaveError( strings.saveErrorGeneric );
				setIsSaving( false );
				return;
			}

			if ( ! json.success ) {
				const code = json.data?.code || 'save_failed';
				setSaveError( mapSaveError( code, strings ) );
				setIsSaving( false );
				return;
			}

			const newId = json.data?.formId;
			if ( newId && newId !== currentFormId ) {
				setCurrentFormId( newId );
				const idInput = form.querySelector(
					'[name="sobiforms_form_id"]'
				) as HTMLInputElement | null;
				if ( idInput ) {
					idInput.value = String( newId );
				}
				const url = new URL( window.location.href );
				url.searchParams.set( 'sobiforms_form_id', String( newId ) );
				window.history.replaceState( {}, '', url.toString() );
			}

			if ( json.data?.slug ) {
				setCurrentSlug( json.data.slug );
			}

			if ( typeof json.data?.submissionCount === 'number' ) {
				setSubmissionCount( json.data.submissionCount );
			}

			setIsDirty( false );
			setIsSaving( false );
			triggerSaveFlash();
		} catch {
			setSaveError( strings.saveErrorGeneric );
			setIsSaving( false );
		}
	}, [
		ajaxUrl,
		currentFormId,
		form,
		isDirty,
		isSaving,
		strings,
		syncFieldsJson,
		triggerSaveFlash,
	] );

	useEffect( () => {
		setSubmitButtonText( initialSubmitButtonText );
	}, [ initialSubmitButtonText ] );

	useEffect( () => {
		setFormTitle( initialFormTitle );
	}, [ initialFormTitle ] );

	useEffect( () => {
		const onSubmit = ( e: Event ) => {
			e.preventDefault();
			performSave();
		};
		form.addEventListener( 'submit', onSubmit );
		return () => form.removeEventListener( 'submit', onSubmit );
	}, [ form, performSave ] );

	useEffect( () => {
		const onFormChange = () => markDirty();
		form.addEventListener( 'input', onFormChange );
		form.addEventListener( 'change', onFormChange );
		return () => {
			form.removeEventListener( 'input', onFormChange );
			form.removeEventListener( 'change', onFormChange );
		};
	}, [ form, markDirty ] );

	useEffect( () => {
		const onKeyDown = ( e: KeyboardEvent ) => {
			if ( ( e.metaKey || e.ctrlKey ) && e.key === 's' ) {
				e.preventDefault();
				performSave();
			}
		};
		window.addEventListener( 'keydown', onKeyDown );
		return () => window.removeEventListener( 'keydown', onKeyDown );
	}, [ performSave ] );

	useEffect( () => {
		return () => {
			if ( saveFlashTimerRef.current ) {
				window.clearTimeout( saveFlashTimerRef.current );
			}
		};
	}, [] );

	const handleFormTitleChange = useCallback(
		( value: string ) => {
			markDirty();
			setFormTitle( value );
		},
		[ markDirty ]
	);

	const handleSubmitButtonTextChange = useCallback(
		( value: string ) => {
			markDirty();
			setSubmitButtonText( value );
		},
		[ markDirty ]
	);

	return (
		<div className="sobiforms-form-editor">
			<EditorHeader
				strings={ strings }
				formTitle={ formTitle }
				onFormTitleChange={ handleFormTitleChange }
				onOpenEmbed={ () => setEmbedOpen( true ) }
				embedButtonRef={ embedButtonRef }
				isDirty={ isDirty }
				saveFlash={ saveFlash }
				isSaving={ isSaving }
				saveError={ saveError }
			/>

			<div className="sobiforms-form-editor__body">
				<div className="sobiforms-form-editor__main">
					<FormCanvas
						blocks={ state.blocks }
						strings={ strings }
						dispatch={ wrappedDispatch }
						submitButtonText={ submitButtonText }
						defaultSubmitButtonText={ defaultSubmitButtonText }
						onSubmitButtonTextChange={ handleSubmitButtonTextChange }
						serverMaxUploadMb={ serverMaxUploadMb }
						fileExtensionOptions={ fileExtensionOptions }
						defaultFileExtensions={ defaultFileExtensions }
					/>
				</div>
				<SettingsPanel
					strings={ strings }
					settings={ settings }
					pages={ pages }
					paused={ paused }
					closeEnabled={ closeEnabled }
					closeAt={ closeAt }
					closeLimitEnabled={ closeLimitEnabled }
					closeLimit={ closeLimit }
					submissionCount={ submissionCount }
					saveSubmissions={ saveSubmissions }
					recipientEmail={ recipientEmail }
					emailEnabled={ emailEnabled }
					onPausedChange={ ( value ) => {
						markDirty();
						setPaused( value );
					} }
					onCloseEnabledChange={ ( value ) => {
						markDirty();
						setCloseEnabled( value );
					} }
					onCloseAtChange={ ( value ) => {
						markDirty();
						setCloseAt( value );
					} }
					onCloseLimitEnabledChange={ ( value ) => {
						markDirty();
						setCloseLimitEnabled( value );
						if ( value && closeLimit < 1 ) {
							setCloseLimit( 100 );
						}
					} }
					onCloseLimitChange={ ( value ) => {
						markDirty();
						setCloseLimit( value );
					} }
					onSaveSubmissionsChange={ ( value ) => {
						markDirty();
						setSaveSubmissions( value );
					} }
					onRecipientEmailChange={ ( value ) => {
						markDirty();
						setRecipientEmail( value );
					} }
					onEmailEnabledChange={ ( value ) => {
						markDirty();
						setEmailEnabled( value );
					} }
					emailFields={ emailFields }
					confirmationEnabled={ confirmationEnabled }
					confirmationEmailFieldId={ confirmationEmailFieldId }
					onConfirmationEnabledChange={ ( value ) => {
						markDirty();
						setConfirmationEnabled( value );
					} }
					onConfirmationEmailFieldIdChange={ ( value ) => {
						markDirty();
						setConfirmationEmailFieldId( value );
					} }
					hiddenFields={ hiddenFields }
					hasFileFields={ hasFileFields }
					onHiddenFieldsChange={ ( fields ) => {
						markDirty();
						setHiddenFields( fields );
					} }
				/>
			</div>

			<EmbedOverlay
				open={ embedOpen }
				onClose={ () => setEmbedOpen( false ) }
				strings={ strings }
				formId={ currentFormId }
				slug={ currentSlug }
				returnFocusRef={ embedButtonRef }
			/>
		</div>
	);
}
