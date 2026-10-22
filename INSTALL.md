# Installing the new eyesonjobs.com website

This guide is written for the site owner. You do not need to know any code. Allow about an hour.

You have two files:

- `eyesonjobs-core.zip` is the **plugin**. It adds jobs, the forms, login and sign-up, and the Job Preparation Package payments.
- `eyesonjobs-theme.zip` is the **theme**. It is the new design.

Install the plugin first, then the theme.

---

## 1. Before you start: make a backup

1. In your WordPress dashboard go to **Plugins → Add New**, search for **UpdraftPlus**, then **Install Now** and **Activate**.
2. Go to **Settings → UpdraftPlus Backups** and click **Backup Now**. Tick both the files and the database.
3. Download the backup to your computer so you have a copy outside the website.

If you can, try everything on a **staging copy** of the site first. Most hosts (Hostinger, GoDaddy, SiteGround, Bluehost and others) have a "Create staging" button in their control panel. When the staging copy looks right, repeat the same steps on the live site.

If anything goes wrong, you can restore the backup from the same UpdraftPlus screen.

## 2. Install the plugin, then the theme

1. Go to **Plugins → Add New → Upload Plugin**, choose `eyesonjobs-core.zip`, click **Install Now**, then **Activate Plugin**.
2. Go to **Appearance → Themes → Add New → Upload Theme**, choose `eyesonjobs-theme.zip`, click **Install Now**, then **Activate**.

The plugin creates the job categories, locations and job types for you.

## 3. Create the pages and menus

1. Go to **Appearance → Eyes on Jobs Setup**.
2. Leave **Create and assign the main, footer and legal menus** ticked.
3. Only tick **Replace the content of pages that already exist** if you want the new text on pages that already exist, such as About Us or Contact Us. The old text is not lost. Open the page and use **Revisions** to get it back.
4. Click **Run setup**.

This builds Home, About, Services, Executive Search, Background Verification, Job Preparation Package (and its registration page), Submit Resume, Hire Talent, Contact, My Account and the policy pages. It also sets the home page and builds the menus.

## 4. Logo and contact details

- **Logo:** **Appearance → Customize → Site Identity → Select logo**. The official Eyes on Jobs logo is already built into the theme (header and footer), so you do not need to upload one. Upload here only if you want to replace it in the header.
- **Phones, emails, address, social links and the announcement bar:** **Appearance → Customize → Eyes on Jobs details**.

The numbers that are already filled in come from the live site: +91 99991 27911, +91 124 4079570, career@eyesonjobs.com, vikas@eyesonjobs.com and the Gurugram address. Check them once.

## 5. Where form messages go

Go to **Settings → Eyes on Jobs**. In **Notification emails**, career@eyesonjobs.com and vikas@eyesonjobs.com are already filled in. Every form is sent to both addresses.

Every submission is also saved under **Submissions** in the dashboard. You can filter by type (resume, job application, employer requirement, contact message, Job Prep Package). Resumes are kept in a private folder. Download them from inside the submission with **Download attached resume**. They are not public web links.

## 6. Spam protection: Google reCAPTCHA v3 (free)

1. Open https://www.google.com/recaptcha/admin/create (sign in with a Google account).
2. Give it any label, for example "eyesonjobs".
3. Choose **Score based (v3)**.
4. Under Domains, add `eyesonjobs.com`.
5. Submit. Google shows a **Site key** and a **Secret key**.
6. In the dashboard open **Settings → Eyes on Jobs** and paste them into the reCAPTCHA boxes, then click **Save Changes**.

If a real visitor is ever blocked, lower the **Minimum score** from 0.5 to 0.3. Until you add the keys, the forms still use a hidden trap field and rate limiting, so you are not unprotected.

## 7. Online payments for the Job Preparation Package: Razorpay

Customers can pay by UPI, cards, net banking and wallets.

1. Sign up at https://razorpay.com and finish **KYC** (business and PAN details). Razorpay asks for your **bank account** during KYC and sends the money there. **Enter bank details only on Razorpay's own site. Never send them to us by email or put them on the website.**
2. In the Razorpay Dashboard go to **Account & Settings → API Keys → Generate Key**. Start with **Test mode**. You get a Key ID (starts with `rzp_test_`) and a Key Secret.
3. In your site go to **Settings → Eyes on Jobs → Razorpay payments** and paste the Key ID and Key Secret. Save.
4. Do a test registration on the Job Preparation Package page. Razorpay's Test mode shows test payment options. No real money moves.
5. Add the **webhook**. In Razorpay go to **Account & Settings → Webhooks → Add New Webhook**.
   - **Webhook URL:** copy the one shown at the bottom of **Settings → Eyes on Jobs**. It ends in `/wp-json/eoj/v1/razorpay-webhook`.
   - **Secret:** make up a long random phrase. Paste the same phrase into **Webhook secret** on your site.
   - **Events:** tick `payment.captured` and `order.paid`.

   The webhook confirms a payment even if the customer closes the browser straight after paying.
6. When testing is fine, switch Razorpay to **Live mode**, generate **Live keys** (`rzp_live_…`) and replace the test keys on your site.

You can change the package name and prices in the same settings screen. The default is ₹7,080 struck through and ₹3,540 (50% off), inclusive of all taxes. The "no placement guarantee" note is always shown.

**Card details.** Customers type their credit or debit card details into Razorpay's own secure window, which opens on top of your page. Card numbers never reach your website or your server. That keeps you out of the legal and security burden of storing cards, so please do not add a card form of your own.

**Which bank account gets the money?** The one you add during Razorpay KYC. Razorpay settles every online payment to that account. You change it in the Razorpay Dashboard, not on your website.

### How customers pay

After registering, the customer is asked **“How would you like to pay?”** and picks one:

- **Credit / Debit card** (needs Razorpay): opens Razorpay’s secure window showing only the card form. The customer types the card details there.
- **UPI**: only when the customer picks it, a QR code is created. **It is valid for 5 minutes**, with a countdown. After that it shows “expired” and the customer taps “Generate new QR”. With Razorpay connected, the QR is a single-use Razorpay QR that Razorpay itself closes after 5 minutes, and the page notices the payment automatically. In the webhook (step 5 above) tick `qr_code.credited` as well. Without Razorpay, the QR uses your own UPI ID (see below). On phones the customer also sees buttons for PhonePe, Google Pay, Paytm and BHIM.
- **PayPal**: opens your PayPal.me link with the amount filled in. It mostly suits customers outside India.

Your bank account number is **never shown** to customers.

### Direct UPI and PayPal (optional)

Go to **Settings → Eyes on Jobs → Direct payments** and enter your **UPI ID** and/or **PayPal.me link**. This is used when Razorpay is not connected, and PayPal is always direct. For these payments the site cannot see the money arrive, so the customer enters their transaction or UTR number and clicks “I have paid, confirm”. You get an email and the entry under **Submissions** shows “Payment submitted: verify”. Check your UPI app or bank for that reference, then click **Mark as paid**. The customer then gets a receipt.

Cash App is not available in India, so it is not included.

**Until you add Razorpay keys, a UPI ID or a PayPal link**, registrations still work. They are saved and emailed to you, and the customer sees "Registration received. Our team will email you payment details within one working day."

If someone pays you by bank transfer or cash, open their entry under **Submissions** and click **Mark as paid (offline payment)**.

## 8. Make sure emails arrive (strongly recommended)

WordPress sends email in a way that often lands in spam.

1. Install the free **WP Mail SMTP** plugin (**Plugins → Add New**).
2. Connect it to your email provider (Google Workspace, Microsoft 365, Zoho, Brevo and others are all supported). Its setup wizard walks you through it.
3. Use its **Send a test email** button to confirm.

## 9. Adding jobs

1. Go to **Jobs → Add New**.
2. Type the job title and the description.
3. Fill in the **Job details** box: company, experience, salary, an extra email for applications (optional), and an "apply by" date (optional). After that date the job disappears from the listing.
4. On the right, choose a **Job Category**, **Job Location** and **Job Type**. The **Company logo** box is optional.
5. Click **Publish**.

The job appears on the Jobs page (10 per page, with search by keyword, city and industry). Each job page has an Apply form. The application is emailed to your team, to the extra email if you set one, and saved under **Submissions**. Google can also show your jobs in job search results.

> **Important:** the old job board's listings are **not** moved across automatically. Re-add the jobs you want to keep under **Jobs → Add New**. Visitors who open the old `/jobs-search/` or `/member/…` addresses are sent to the new pages automatically.

## 10. Candidate and employer accounts

Visitors can create a free **Candidate** or **Employer** account on the **My Account** page. They see their own Job Preparation Package registrations (with a "Complete payment" link if they have not paid yet) and can edit their profile. These accounts cannot enter the WordPress dashboard. They are sent to My Account instead. To switch sign-ups off, untick **Allow candidate and employer sign-up** in **Settings → Eyes on Jobs**.

## 11. Final checklist

- [ ] Backup done and downloaded
- [ ] Plugin installed and activated, then theme installed and activated
- [ ] Eyes on Jobs Setup run
- [ ] Logo uploaded and contact details checked
- [ ] reCAPTCHA keys added
- [ ] WP Mail SMTP set up and test email received
- [ ] Razorpay test payment worked, webhook added, then live keys pasted
- [ ] Jobs re-added
- [ ] Sent a test message from each form (Contact, Submit Resume, Hire Talent) and found it under Submissions

If something looks wrong, restore the UpdraftPlus backup and send us a screenshot of what you saw.

