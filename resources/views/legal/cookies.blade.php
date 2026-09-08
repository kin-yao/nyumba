<x-layouts.legal :title="'Cookie Policy'" :lastUpdated="'September 2026'">

<div class="legal-note">
    This policy explains the cookies Nyumba sets, why each one exists, and how long it lasts.
    It sits alongside our <a href="{{ route('privacy') }}">Privacy Policy</a>, which covers
    personal information more generally.
</div>

<h2>1. What Cookies Are</h2>
<p>A cookie is a small text file stored on your device by your browser when you visit a website. Cookies let a site remember things between page loads, such as the fact that you are signed in. Some are essential for a site to work at all. Others are optional and are used to understand how a site is being used.</p>
<p>We also use closely related technologies, such as your browser's local storage, in the same way and for the same purposes. Where this policy says "cookies", it covers those too.</p>

<h2>2. Cookies We Set</h2>

<h3>2.1 Strictly necessary cookies</h3>
<p>These are required for the Service to function. Without them you cannot sign in, stay signed in, or submit a form safely. They cannot be switched off, and we do not ask for consent to set them because the Service cannot be delivered without them.</p>
<ul>
    <li><strong>nyumba-session</strong> — keeps you signed in as you move between pages, and holds temporary state such as form messages. Expires after 2 hours of inactivity. This cookie cannot be read by scripts in your browser.</li>
    <li><strong>XSRF-TOKEN</strong> — protects forms and requests against cross-site request forgery, so that another website cannot act on your behalf while you are signed in. Expires after 2 hours.</li>
</ul>

<h3>2.2 Functional cookies</h3>
<ul>
    <li><strong>nyumba_tenant_device</strong> — set only on the tenant portal, and only if a tenant chooses to have their device remembered when signing in. It lets that tenant return without receiving a one-time SMS code every time, which also keeps SMS costs down for their landlord. It lasts up to 180 days, and the period restarts each time it is used. It stores a random token, not the tenant's phone number or any personal information. A tenant can clear it by signing out, or by clearing cookies in their browser.</li>
</ul>

<h3>2.3 Analytics cookies</h3>
<p>We use Google Tag Manager on our public pages, including the homepage and sign-up pages, to understand how visitors find and use the site. Google Tag Manager does not itself store cookies, but the tools it loads may set their own, typically to recognise a returning visitor and to group page views into a single visit. Where analytics cookies are set, they can last up to two years.</p>
<p>These cookies are not essential. They are not used inside the landlord dashboard or the tenant portal, and they are not used to build a profile of you or to target advertising.</p>

<h2>3. Third Parties</h2>
<p>Some cookies are set by services we rely on rather than by us directly:</p>
<ul>
    <li><strong>Google</strong> — for the analytics described above. Google's own privacy notice is available at <a href="https://policies.google.com/privacy" rel="noopener" target="_blank">policies.google.com/privacy</a>.</li>
    <li><strong>Firebase</strong> (a Google service) — used for account sign-in and email verification. It may store authentication state in your browser so you are not asked to sign in repeatedly.</li>
</ul>
<p>We do not sell your data, and we do not use advertising or cross-site tracking cookies.</p>

<h2>4. Managing Cookies</h2>
<p>You can delete or block cookies through your browser settings. Every major browser lets you view what has been stored, remove individual cookies, and refuse new ones.</p>
<p>Please be aware that blocking strictly necessary cookies will stop you from signing in. Blocking the tenant device cookie is safe, but tenants will then receive an SMS code on every login.</p>

<h2>5. Your Rights</h2>
<p>Under the Kenya Data Protection Act, 2019, you have rights over your personal information, including the right to access it, correct it, and object to certain uses. Those rights and how to exercise them are set out in our <a href="{{ route('privacy') }}">Privacy Policy</a>.</p>
<p>If you are a tenant, note that your landlord or property manager is the data controller for your tenancy records. Requests about that information should go to them first.</p>

<h2>6. Changes to This Policy</h2>
<p>We may update this policy as the Service changes or as we add or remove tools. When we do, we will update the date shown at the top of this page. Significant changes will be communicated to account holders directly.</p>

<h2>7. Contact</h2>
<p>Questions about this policy can be sent to <a href="mailto:info@nyumbapc.co.ke">info@nyumbapc.co.ke</a>, or by WhatsApp on <a href="https://wa.me/254705056343" rel="noopener" target="_blank">0705 056 343</a>.</p>

</x-layouts.legal>