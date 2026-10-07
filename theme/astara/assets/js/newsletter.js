/**
 * Newsletter signup form (shortcode [astara_newsletter]).
 *
 * Posts the email to the theme's REST route and shows the result inline.
 * Config (endpoint, reCAPTCHA site key, messages) arrives in window.astaraNewsletter.
 * Google's reCAPTCHA script is loaded from here, only on pages with the form.
 */
( function () {
	'use strict';

	var cfg = window.astaraNewsletter || {};
	var form = document.querySelector( '[data-astara-newsletter]' );
	if ( ! form || ! cfg.endpoint ) {
		return;
	}

	var message = form.querySelector( '.astara-contact__newsletter-message' );
	var button = form.querySelector( 'button[type="submit"]' );
	var holder = form.querySelector( '[data-astara-recaptcha]' );
	var widgetId = null;

	function say( text, type ) {
		message.textContent = text;
		message.className = 'astara-contact__newsletter-message' + ( type ? ' is-' + type : '' );
	}

	if ( cfg.siteKey && holder ) {
		window.astaraRecaptchaReady = function () {
			widgetId = window.grecaptcha.render( holder, { sitekey: cfg.siteKey } );
		};

		var api = document.createElement( 'script' );
		api.src = 'https://www.google.com/recaptcha/api.js?onload=astaraRecaptchaReady&render=explicit';
		api.async = true;
		api.defer = true;
		document.head.appendChild( api );
	}

	form.addEventListener( 'submit', function ( event ) {
		event.preventDefault();

		var email = form.elements.email.value.trim();
		if ( ! email ) {
			say( cfg.i18n.enterEmail, 'error' );
			return;
		}

		var token = '';
		if ( cfg.siteKey ) {
			token = widgetId !== null ? window.grecaptcha.getResponse( widgetId ) : '';
			if ( ! token ) {
				say( cfg.i18n.confirmHuman, 'error' );
				return;
			}
		}

		button.disabled = true;
		say( cfg.i18n.sending, '' );

		fetch( cfg.endpoint, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
			body: JSON.stringify( {
				email: email,
				website: form.elements.website.value,
				'g-recaptcha-response': token,
			} ),
		} )
			.then( function ( res ) {
				return res
					.json()
					.catch( function () {
						return {};
					} )
					.then( function ( data ) {
						return { ok: res.ok, data: data };
					} );
			} )
			.then( function ( result ) {
				if ( result.ok && result.data.success ) {
					say( result.data.message || cfg.i18n.success, 'success' );
					form.reset();
				} else {
					say( result.data.message || cfg.i18n.error, 'error' );
				}
			} )
			.catch( function () {
				say( cfg.i18n.error, 'error' );
			} )
			.then( function () {
				button.disabled = false;
				if ( widgetId !== null ) {
					window.grecaptcha.reset( widgetId );
				}
			} );
	} );
} )();
