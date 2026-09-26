/**
 * Submissions inbox — resize, search, filters, bulk selection.
 */
( function () {
	'use strict';

	var STORAGE_KEY = 'sobiforms_inbox_list_width';
	var MIN_WIDTH = 220;
	var MAX_RATIO = 0.55;
	var STEP = 16;
	var i18n = window.sobiformsInbox || {};

	function getInbox() {
		return document.querySelector( '[data-sobiforms-inbox]' );
	}

	function getMaxWidth( inbox ) {
		return Math.max( MIN_WIDTH, Math.floor( inbox.clientWidth * MAX_RATIO ) );
	}

	function clampWidth( inbox, width ) {
		return Math.min( Math.max( width, MIN_WIDTH ), getMaxWidth( inbox ) );
	}

	function applyListWidth( inbox, widthPx ) {
		inbox.style.setProperty( '--sobiforms-inbox-list-width', widthPx + 'px' );
	}

	function persistWidth( widthPx ) {
		try {
			localStorage.setItem( STORAGE_KEY, String( widthPx ) );
		} catch ( e ) {
			// ignore
		}
	}

	function restoreWidth( inbox ) {
		try {
			var stored = localStorage.getItem( STORAGE_KEY );
			if ( ! stored ) {
				return;
			}
			var width = parseInt( stored, 10 );
			if ( Number.isNaN( width ) ) {
				return;
			}
			applyListWidth( inbox, clampWidth( inbox, width ) );
		} catch ( e ) {
			// ignore
		}
	}

	function isStackedLayout() {
		return window.matchMedia( '(max-width: 782px)' ).matches;
	}

	function initSearch( inbox ) {
		var filterForm = inbox.querySelector( '#sobiforms-submissions-filter' );
		var searchInput = inbox.querySelector( '#sobiforms-submissions-search' );
		if ( ! filterForm || ! searchInput ) {
			return;
		}

		var activeSearch = searchInput.value.length > 0;

		function clearActiveSearch() {
			if ( ! activeSearch ) {
				return;
			}
			activeSearch = false;
			filterForm.submit();
		}

		searchInput.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'Enter' ) {
				event.preventDefault();
				filterForm.submit();
			}
		} );

		searchInput.addEventListener( 'search', function () {
			if ( searchInput.value === '' ) {
				clearActiveSearch();
			} else {
				activeSearch = true;
			}
		} );

		searchInput.addEventListener( 'input', function () {
			if ( searchInput.value === '' ) {
				clearActiveSearch();
			} else {
				activeSearch = true;
			}
		} );
	}

	function closeMenus( except ) {
		var menus = document.querySelectorAll(
			'.sobiforms-inbox-select__menu, .sobiforms-inbox-more__menu, .sobiforms-inbox-form-tab__menu'
		);
		menus.forEach( function ( menu ) {
			if ( except && menu === except ) {
				return;
			}
			menu.hidden = true;
			var toggle = menu.parentElement.querySelector( '[aria-expanded]');
			if ( toggle ) {
				toggle.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	}

	function initDropdowns( inbox ) {
		var selectWrap = inbox.querySelector( '[data-sobiforms-inbox-select]' );
		if ( selectWrap ) {
			var selectToggle = selectWrap.querySelector( '.sobiforms-inbox-select__toggle' );
			var selectMenu = selectWrap.querySelector( '.sobiforms-inbox-select__menu' );
			if ( selectToggle && selectMenu ) {
				selectToggle.addEventListener( 'click', function ( event ) {
					event.preventDefault();
					var open = ! selectMenu.hidden;
					closeMenus();
					selectMenu.hidden = open;
					selectToggle.setAttribute( 'aria-expanded', open ? 'false' : 'true' );
				} );
			}
		}

		var moreWrap = inbox.querySelector( '[data-sobiforms-inbox-more]' );
		if ( moreWrap ) {
			var moreToggle = moreWrap.querySelector( '[data-sobiforms-more-toggle]' );
			var moreMenu = moreWrap.querySelector( '.sobiforms-inbox-more__menu' );
			if ( moreToggle && moreMenu ) {
				moreToggle.addEventListener( 'click', function ( event ) {
					event.preventDefault();
					var open = ! moreMenu.hidden;
					closeMenus();
					moreMenu.hidden = open;
					moreToggle.setAttribute( 'aria-expanded', open ? 'false' : 'true' );
				} );
			}
		}

		var formFilter = inbox.querySelector( '[data-sobiforms-form-filter]' );
		if ( formFilter ) {
			var formToggle = formFilter.querySelector( '[data-sobiforms-form-filter-toggle]' );
			var formMenu = formFilter.querySelector( '.sobiforms-inbox-form-tab__menu' );
			if ( formToggle && formMenu ) {
				formToggle.addEventListener( 'click', function ( event ) {
					event.preventDefault();
					var open = ! formMenu.hidden;
					closeMenus();
					formMenu.hidden = open;
					formToggle.setAttribute( 'aria-expanded', open ? 'false' : 'true' );
				} );
			}
		}

		document.addEventListener( 'click', function ( event ) {
			if ( ! inbox.contains( event.target ) ) {
				closeMenus();
			}
		} );

		inbox.querySelectorAll( '[data-sobiforms-confirm]' ).forEach( function ( link ) {
			link.addEventListener( 'click', function ( event ) {
				var message = link.getAttribute( 'data-sobiforms-confirm' );
				if ( ! message ) {
					return;
				}
				if ( ! window.confirm( message ) ) {
					event.preventDefault();
				}
			} );
		} );
	}

	function getRowChecks( inbox ) {
		return inbox.querySelectorAll( '.sobiforms-inbox-row__check' );
	}

	function getCheckedRows( inbox ) {
		return inbox.querySelectorAll( '.sobiforms-inbox-row__check:checked' );
	}

	function getActiveRowCheck( inbox ) {
		var activeRow = inbox.querySelector( '.sobiforms-inbox-row.is-selected' );
		return activeRow ? activeRow.querySelector( '.sobiforms-inbox-row__check' ) : null;
	}

	function getBulkActionTargets( inbox ) {
		var checked = getCheckedRows( inbox );
		if ( checked.length ) {
			return checked;
		}

		var activeCheck = getActiveRowCheck( inbox );
		return activeCheck ? [ activeCheck ] : [];
	}

	function ensureBulkSubmitTargets( inbox ) {
		var checked = getCheckedRows( inbox );
		if ( checked.length ) {
			return checked;
		}

		var activeCheck = getActiveRowCheck( inbox );
		if ( activeCheck ) {
			activeCheck.checked = true;
			return [ activeCheck ];
		}

		return [];
	}

	function syncMasterCheckbox( inbox ) {
		var master = inbox.querySelector( '#sobiforms-inbox-select-all' );
		if ( ! master ) {
			return;
		}

		var checked = getCheckedRows( inbox );
		var total = getRowChecks( inbox ).length;

		master.checked = total > 0 && checked.length === total;
		master.indeterminate = checked.length > 0 && checked.length < total;
	}

	function rowMatchesSelectScope( input, scope ) {
		switch ( scope ) {
			case 'all':
				return true;
			case 'none':
				return false;
			case 'read':
				return input.dataset.isRead === '1';
			case 'unread':
				return input.dataset.isRead === '0';
			case 'starred':
				return input.dataset.isStarred === '1';
			case 'not_starred':
				return input.dataset.isStarred === '0';
			case 'spam':
				return input.dataset.isSpam === '1';
			case 'not_spam':
				return input.dataset.isSpam === '0';
			default:
				return false;
		}
	}

	function applyRowSelection( inbox, scope ) {
		getRowChecks( inbox ).forEach( function ( input ) {
			input.checked = rowMatchesSelectScope( input, scope );
		} );
		syncMasterCheckbox( inbox );
		updateBulkActions( inbox );
		closeMenus();
	}

	function updateBulkActions( inbox ) {
		var bar = inbox.querySelector( '[data-sobiforms-bulk-actions]' );
		if ( ! bar ) {
			return;
		}

		var targets = getBulkActionTargets( inbox );

		syncMasterCheckbox( inbox );

		if ( ! targets.length ) {
			bar.hidden = true;
			return;
		}

		bar.hidden = false;

		var hasUnread = false;
		var hasRead = false;
		var hasSpam = false;
		var hasHam = false;

		targets.forEach( function ( input ) {
			if ( input.dataset.isRead === '0' ) {
				hasUnread = true;
			}
			if ( input.dataset.isRead === '1' ) {
				hasRead = true;
			}
			if ( input.dataset.isSpam === '1' ) {
				hasSpam = true;
			}
			if ( input.dataset.isSpam === '0' ) {
				hasHam = true;
			}
		} );

		bar.querySelectorAll( '[data-bulk-action]' ).forEach( function ( btn ) {
			var action = btn.getAttribute( 'data-bulk-action' );
			var show = false;
			switch ( action ) {
				case 'mark_read':
					show = hasUnread;
					break;
				case 'mark_unread':
					show = hasRead;
					break;
				case 'mark_spam':
					show = hasHam;
					break;
				case 'mark_not_spam':
					show = hasSpam;
					break;
				case 'delete':
					show = true;
					break;
			}
			btn.hidden = ! show;
		} );
	}

	function initBulkSelection( inbox ) {
		var bulkForm = inbox.querySelector( '#sobiforms-bulk-form' );
		var master = inbox.querySelector( '#sobiforms-inbox-select-all' );
		var actionInput = inbox.querySelector( '#sobiforms-bulk-action' );

		if ( ! bulkForm || ! actionInput ) {
			return;
		}

		getRowChecks( inbox ).forEach( function ( input ) {
			input.addEventListener( 'change', function () {
				updateBulkActions( inbox );
			} );
			input.addEventListener( 'click', function ( event ) {
				event.stopPropagation();
			} );
		} );

		if ( master ) {
			master.addEventListener( 'change', function () {
				getRowChecks( inbox ).forEach( function ( input ) {
					input.checked = master.checked;
				} );
				updateBulkActions( inbox );
			} );
		}

		inbox.querySelectorAll( '[data-sobiforms-select-scope]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				applyRowSelection( inbox, btn.getAttribute( 'data-sobiforms-select-scope' ) );
			} );
		} );

		inbox.querySelectorAll( '[data-bulk-action]' ).forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var action = btn.getAttribute( 'data-bulk-action' );
				if ( ! ensureBulkSubmitTargets( inbox ).length ) {
					return;
				}
				if ( action === 'delete' ) {
					var message = i18n.confirmDeleteSelected || 'Delete selected submissions?';
					if ( ! window.confirm( message ) ) {
						return;
					}
				}
				actionInput.value = action;
				bulkForm.submit();
			} );
		} );

		updateBulkActions( inbox );
	}

	function initResize( inbox, handle ) {
		var dragging = false;
		var startX = 0;
		var startWidth = 0;

		function listWidthPx() {
			var list = inbox.querySelector( '.sobiforms-submissions-inbox__list' );
			return list ? list.offsetWidth : MIN_WIDTH;
		}

		function onPointerMove( clientX ) {
			var delta = clientX - startX;
			var next = clampWidth( inbox, startWidth + delta );
			applyListWidth( inbox, next );
		}

		function endDrag() {
			if ( ! dragging ) {
				return;
			}
			dragging = false;
			document.body.classList.remove( 'sobiforms-inbox-resizing' );
			persistWidth( listWidthPx() );
		}

		handle.addEventListener( 'mousedown', function ( event ) {
			if ( isStackedLayout() ) {
				return;
			}
			event.preventDefault();
			dragging = true;
			startX = event.clientX;
			startWidth = listWidthPx();
			document.body.classList.add( 'sobiforms-inbox-resizing' );
		} );

		document.addEventListener( 'mousemove', function ( event ) {
			if ( ! dragging ) {
				return;
			}
			onPointerMove( event.clientX );
		} );

		document.addEventListener( 'mouseup', endDrag );

		handle.addEventListener( 'keydown', function ( event ) {
			if ( isStackedLayout() ) {
				return;
			}
			var width = listWidthPx();
			if ( event.key === 'ArrowLeft' ) {
				event.preventDefault();
				var next = clampWidth( inbox, width - STEP );
				applyListWidth( inbox, next );
				persistWidth( next );
			} else if ( event.key === 'ArrowRight' ) {
				event.preventDefault();
				var nextRight = clampWidth( inbox, width + STEP );
				applyListWidth( inbox, nextRight );
				persistWidth( nextRight );
			}
		} );
	}

	function init() {
		var inbox = getInbox();
		if ( ! inbox ) {
			return;
		}

		initSearch( inbox );
		initDropdowns( inbox );
		initBulkSelection( inbox );

		var handle = inbox.querySelector( '.sobiforms-submissions-inbox__resize' );
		if ( handle ) {
			restoreWidth( inbox );
			initResize( inbox, handle );

			window.addEventListener( 'resize', function () {
				if ( isStackedLayout() ) {
					return;
				}
				var width = listWidthPxFromInbox( inbox );
				if ( width ) {
					applyListWidth( inbox, clampWidth( inbox, width ) );
				}
			} );
		}
	}

	function listWidthPxFromInbox( inbox ) {
		var list = inbox.querySelector( '.sobiforms-submissions-inbox__list' );
		return list ? list.offsetWidth : 0;
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
