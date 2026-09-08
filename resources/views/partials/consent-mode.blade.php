{{-- Google Consent Mode v2 defaults.
     MUST be included immediately BEFORE the Google Tag Manager snippet.
     Nothing that stores analytics data is allowed to fire until the visitor
     chooses, which is what makes the banner meaningful rather than decorative. --}}
<script>
(function () {
  window.dataLayer = window.dataLayer || [];
  function gtag(){ dataLayer.push(arguments); }
  window.gtag = window.gtag || gtag;

  var saved = null;
  try { saved = JSON.parse(localStorage.getItem('nyumba_cookie_consent')); } catch (e) {}

  var analytics = (saved && saved.analytics) ? 'granted' : 'denied';

  gtag('consent', 'default', {
    ad_storage: 'denied',
    ad_user_data: 'denied',
    ad_personalization: 'denied',
    analytics_storage: analytics,
    functionality_storage: 'granted',
    security_storage: 'granted',
    wait_for_update: 500
  });
})();
</script>