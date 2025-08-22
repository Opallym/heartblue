// --- Config ---
const nameCheck  = /^[-_a-zA-Z0-9]{4,22}$/;
const tokenCheck = /^[-_/+a-zA-Z0-9]{24,}$/;

// Matches Symfony Forms CSRF inputs:
//   registration_form[_token], activity[_token], plain _token, Security's _csrf_token
const SYMFONY_CSRF_SELECTOR =
  'input[name$="[_token]"], input[name="_token"], input[name="_csrf_token"]';

// Helpers
function isSymfonyForm(form) {
  return !!form.querySelector(SYMFONY_CSRF_SELECTOR);
}
function getCustomCsrfField(form) {
  // Opt-in: ONLY this hidden input enables the double-submit logic
  return form.querySelector('input[data-controller="csrf-protection"]');
}

// Submit: only custom (opt-in) forms, never Symfony forms
document.addEventListener('submit', (event) => {
  const form = event.target;
  if (!(form instanceof HTMLFormElement)) return;
  if (isSymfonyForm(form)) return;           // hands off Symfony forms
  if (!getCustomCsrfField(form)) return;     // not opted-in

  generateCsrfToken(form);
}, { capture: false });

// Turbo: add header only for custom forms
document.addEventListener('turbo:submit-start', (event) => {
  const form = event.detail?.formSubmission?.formElement;
  if (!form || isSymfonyForm(form) || !getCustomCsrfField(form)) return;

  const headers = generateCsrfHeaders(form);
  if (event.detail.formSubmission?.fetchRequest?.headers) {
    for (const k of Object.keys(headers)) {
      event.detail.formSubmission.fetchRequest.headers[k] = headers[k];
    }
  }
});

// Turbo: cleanup only for custom forms
document.addEventListener('turbo:submit-end', (event) => {
  const form = event.detail?.formSubmission?.formElement;
  if (!form || isSymfonyForm(form) || !getCustomCsrfField(form)) return;
  removeCsrfToken(form);
});

// --- API for custom forms only (opt-in) ---
export function generateCsrfToken(formElement) {
  const csrfField = getCustomCsrfField(formElement);
  if (!csrfField) return;

  let csrfCookie = csrfField.getAttribute('data-csrf-protection-cookie-value');
  let csrfToken  = csrfField.value;

  // Initialize cookie name from field value (short name), then generate token
  if (!csrfCookie && nameCheck.test(csrfToken)) {
    csrfField.setAttribute('data-csrf-protection-cookie-value', (csrfCookie = csrfToken));
    const bytes = new Uint8Array(18);
    (window.crypto || window.msCrypto).getRandomValues(bytes);
    csrfToken = btoa(String.fromCharCode.apply(null, bytes));
    csrfField.value = csrfToken;
    csrfField.dispatchEvent(new Event('change', { bubbles: true }));
  }

  if (csrfCookie && tokenCheck.test(csrfToken)) {
    const cookie = `${csrfCookie}_${csrfToken}=${csrfCookie}; path=/; samesite=strict`;
    document.cookie = location.protocol === 'https:' ? `__Host-${cookie}; secure` : cookie;
  }
}

export function generateCsrfHeaders(formElement) {
  const headers = {};
  const csrfField = getCustomCsrfField(formElement);
  if (!csrfField) return headers;

  const csrfCookie =
    csrfField.getAttribute('data-csrf-protection-cookie-value') ||
    csrfField.getAttribute('data-csrf-protection-cookie_value');

  if (tokenCheck.test(csrfField.value) && nameCheck.test(csrfCookie)) {
    // If you enabled framework.csrf_protection.check_header, align this name with your backend
    headers[`X-CSRF-${csrfCookie}`] = csrfField.value;
  }
  return headers;
}

export function removeCsrfToken(formElement) {
  const csrfField = getCustomCsrfField(formElement);
  if (!csrfField) return;

  const csrfCookie =
    csrfField.getAttribute('data-csrf-protection-cookie-value') ||
    csrfField.getAttribute('data-csrf-protection-cookie_value');

  if (tokenCheck.test(csrfField.value) && nameCheck.test(csrfCookie)) {
    const cookie = `${csrfCookie}_${csrfField.value}=0; path=/; samesite=strict; max-age=0`;
    document.cookie = location.protocol === 'https:' ? `__Host-${cookie}; secure` : cookie;
  }
}

export default 'csrf-protection-controller';
