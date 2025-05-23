# Google reCAPTCHA Setup Guide

This guide will help you set up Google reCAPTCHA for the Laravel Livewire Submission Form component.

## Step 1: Create a reCAPTCHA Site

1. Go to [Google reCAPTCHA Admin Console](https://www.google.com/recaptcha/admin)
2. Click "Create" or the "+" button to add a new site
3. Fill in the form:
   - **Label**: Give your site a descriptive name (e.g., "My Website Contact Form")
   - **reCAPTCHA type**: Select "reCAPTCHA v2" → "I'm not a robot" Checkbox
   - **Domains**: Add your domains:
     - For production: `yourdomain.com`
     - For development: `localhost`, `127.0.0.1`, `your-local-domain.test`
   - **Owners**: Add additional email addresses if needed
4. Accept the reCAPTCHA Terms of Service
5. Click "Submit"

## Step 2: Get Your Keys

After creating the site, you'll see:
- **Site Key** (Public key) - Used in the frontend
- **Secret Key** (Private key) - Used in the backend

## Step 3: Configure Your Laravel Application

Add the keys to your `.env` file:

```env
NOCAPTCHA_SITEKEY=your_site_key_here
NOCAPTCHA_SECRET=your_secret_key_here
```

## Step 4: Test Keys for Development

For testing purposes, Google provides test keys that always pass validation:

```env
# Test keys - ONLY for development/testing
NOCAPTCHA_SITEKEY=6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI
NOCAPTCHA_SECRET=6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe
```

⚠️ **Warning**: These test keys should NEVER be used in production!

## Step 5: Domain Configuration

### For Local Development

Make sure to add these domains to your reCAPTCHA site:
- `localhost`
- `127.0.0.1`
- `your-app.test` (if using Laravel Valet or similar)

### For Production

Add your actual domain:
- `yourdomain.com`
- `www.yourdomain.com`

## Step 6: Verify Setup

1. Clear your application cache:
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

2. Visit your form page and check:
   - The reCAPTCHA widget loads
   - You can complete the challenge
   - Form submission works after completing CAPTCHA
   - Error message appears if CAPTCHA is not completed

## Troubleshooting

### CAPTCHA Not Loading
- Check browser console for JavaScript errors
- Verify your site key is correct
- Ensure your domain is registered in reCAPTCHA admin

### Validation Always Fails
- Check your secret key in `.env`
- Verify the domain matches your reCAPTCHA configuration
- Make sure you're not using test keys in production

### HTTPS Issues
- reCAPTCHA may require HTTPS in production
- For local development, HTTP is usually fine

## Security Best Practices

1. **Never expose your secret key** in frontend code
2. **Use environment variables** for keys
3. **Regularly rotate keys** if compromised
4. **Monitor reCAPTCHA analytics** for suspicious activity
5. **Keep domains list minimal** and up-to-date

## reCAPTCHA Analytics

You can monitor your reCAPTCHA usage in the admin console:
- View request volume
- Check solve rates
- Monitor for suspicious patterns
- Download detailed reports

## Additional Configuration

### Customizing Appearance

You can customize the reCAPTCHA widget appearance by modifying the render options in the Blade view:

```javascript
grecaptcha.render('captcha-widget', {
    'sitekey': 'your_site_key',
    'theme': 'light', // or 'dark'
    'size': 'normal', // or 'compact'
    'callback': (response) => {
        // Handle success
    },
    'expired-callback': () => {
        // Handle expiration
    },
    'error-callback': () => {
        // Handle errors
    }
});
```

### Language Support

reCAPTCHA automatically detects the user's language, but you can force a specific language:

```html
<script src="https://www.google.com/recaptcha/api.js?hl=en"></script>
```

Replace `en` with your desired language code.

## Support

If you encounter issues:
1. Check the [reCAPTCHA documentation](https://developers.google.com/recaptcha)
2. Review the troubleshooting section in the main README
3. Check browser developer tools for errors
4. Verify your configuration matches this guide