# Laravel Livewire Submission Form Component

A comprehensive Laravel Livewire component for handling form submissions with real-time validation, user-friendly error messages, and success notifications.

## Features

- ✅ **Real-time validation** - Validates fields as users type
- ✅ **User-friendly error messages** - Clear, actionable error messages with icons
- ✅ **Success notifications** - Animated success messages with auto-hide functionality
- ✅ **Loading states** - Visual feedback during form submission
- ✅ **Responsive design** - Mobile-friendly with Tailwind CSS
- ✅ **Flexible data storage** - Stores form data as JSON in the database
- ✅ **Form reset** - Automatically clears form after successful submission
- ✅ **Additional metadata** - Captures IP address, user agent, and timestamp
- ✅ **CAPTCHA protection** - Google reCAPTCHA v2 integration for spam prevention
- ✅ **CAPTCHA auto-reset** - Automatically resets CAPTCHA after successful submission
- ✅ **Graceful fallback** - Shows message when JavaScript is disabled
- ✅ **Single submission per page** - Prevents multiple submissions until page refresh
- ✅ **Form state management** - Disables all fields after successful submission
- ✅ **Multi-language support** - Full translation support for English and Indonesian
- ✅ **Conditional CAPTCHA** - CAPTCHA only shows when environment keys are configured
- ✅ **Component-level messaging** - Success and error messages managed within component

## Database Structure

The component uses the `submissions` table with the following structure:

```php
Schema::create('submissions', function (Blueprint $table) {
    $table->id();
    $table->json('fields');  // Stores all form data as JSON
    $table->timestamps();
});
```

## Form Fields

The component includes the following fields:

- **Name** (required) - Full name of the submitter
- **Email** (required) - Email address with validation
- **Message** (required) - Main message content (10-1000 characters)
- **Subject** (optional) - Subject line for the message
- **Phone** (optional) - Phone number

## Installation & Setup

### 1. Install Livewire (if not already installed)

```bash
composer require livewire/livewire
```

### 2. Install reCAPTCHA Package

```bash
composer require anhskohbo/no-captcha
```

### 3. Publish reCAPTCHA Configuration

```bash
php artisan vendor:publish --provider="Anhskohbo\NoCaptcha\NoCaptchaServiceProvider"
```

### 4. Configure reCAPTCHA

1. Go to [Google reCAPTCHA](https://www.google.com/recaptcha/admin) and create a new site
2. Choose reCAPTCHA v2 "I'm not a robot" checkbox
3. Add your domain(s) to the list
4. Copy the Site Key and Secret Key
5. Add them to your `.env` file:

```env
NOCAPTCHA_SITEKEY=your_site_key_here
NOCAPTCHA_SECRET=your_secret_key_here
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Component Files

The component consists of two main files:

- **PHP Component**: `app/Livewire/SubmissionForm.php`
- **Blade View**: `resources/views/livewire/submission-form.blade.php`

## Usage

### Basic Usage

Include the component in any Blade view:

```blade
<livewire:submission-form />
```

### With Layout

```blade
@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Contact Us</h1>
        <livewire:submission-form />
    </div>
@endsection
```

### Required Dependencies

Make sure your layout includes:

```blade
@livewireStyles
<!-- Your content -->
@livewireScripts
```

For the animations and interactions to work properly, you'll also need:

- **Tailwind CSS** for styling
- **Alpine.js** for client-side interactions (optional, for enhanced UX)

## Testing

You can test the component by visiting:

```
/preview/submission-form
```

This route provides a complete test page with all features demonstrated.

## Validation Rules

The component includes comprehensive validation:

```php
#[Validate('required|string|min:2|max:255')]
public $name = '';

#[Validate('required|email|max:255')]
public $email = '';

#[Validate('required|string|min:10|max:1000')]
public $message = '';

#[Validate('nullable|string|max:255')]
public $subject = '';

#[Validate('nullable|string|max:50')]
public $phone = '';
```

## Data Storage

When a form is submitted, the data is stored in the `submissions` table as JSON:

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "message": "Hello, this is a test message.",
  "subject": "Test Subject",
  "phone": "+1234567890",
  "submitted_at": "2025-05-23T08:49:14.000000Z",
  "ip_address": "127.0.0.1",
  "user_agent": "Mozilla/5.0..."
}
```

## Customization

### Styling

The component uses Tailwind CSS classes. You can customize the appearance by modifying the classes in the Blade view file.

### Validation

To modify validation rules, update the `#[Validate]` attributes in the PHP component:

```php
#[Validate('required|string|min:5|max:100')]
public $name = '';
```

### Fields

To add new fields:

1. Add the property to the PHP component with validation
2. Add the field to the Blade view
3. Include it in the `submit()` method's data array

### Success Message

Customize the success message by modifying the success notification section in the Blade view.

## Error Handling

The component includes comprehensive error handling:

- **Validation errors** - Displayed in real-time with icons
- **Submission errors** - Caught and displayed as flash messages
- **Loading states** - Button disabled during submission
- **Multiple submission prevention** - Form disabled after successful submission

## Single Submission Protection

The form implements a single submission per page load mechanism:

- **Form state tracking** - Uses `$formSubmitted` property to track submission status
- **Field disabling** - All input fields become disabled and visually grayed out
- **Button state** - Submit button shows "Form Submitted ✓" and becomes disabled
- **CAPTCHA disabling** - CAPTCHA widget becomes non-interactive
- **User feedback** - Clear message instructs users to refresh for new submission
- **Page refresh required** - Only way to reset form for another submission

## Multi-Language Support

The form includes comprehensive translation support:

- **Translation files** - Located in `lang/en/submission-form.php` and `lang/id/submission-form.php`
- **All text elements** - Form labels, placeholders, messages, and buttons are translatable
- **Easy extension** - Add new languages by creating additional translation files
- **Laravel integration** - Uses Laravel's built-in `__()` helper function

### Available Languages

- **English** (`en`) - Complete translation
- **Indonesian** (`id`) - Complete translation

### Adding New Languages

1. Create a new translation file: `lang/{locale}/submission-form.php`
2. Copy the structure from `lang/en/submission-form.php`
3. Translate all values to your target language
4. Set your application locale in `.env`: `APP_LOCALE=your_locale`

## Conditional CAPTCHA

The CAPTCHA feature is intelligent and only appears when properly configured:

- **Environment detection** - Checks for `NOCAPTCHA_SITEKEY` and `NOCAPTCHA_SECRET`
- **Automatic hiding** - CAPTCHA section is completely hidden if keys are missing
- **Graceful degradation** - Form works perfectly without CAPTCHA when not configured
- **No validation errors** - CAPTCHA validation is skipped when disabled
- **Clean UI** - No empty spaces or broken widgets when CAPTCHA is disabled

### CAPTCHA States

1. **Enabled** - Both site key and secret key are configured in environment
2. **Disabled** - Either or both keys are missing/empty
3. **Development** - Use test keys for local development (see CAPTCHA setup guide)

## Browser Support

The component works in all modern browsers. For older browsers, you may need to include polyfills for:

- CSS Grid
- Flexbox
- ES6 features (if using Alpine.js)

## Security Features

- **CSRF Protection** - Automatic with Livewire
- **Input Sanitization** - Laravel's built-in validation
- **Rate Limiting** - Can be added via middleware
- **IP Tracking** - For monitoring and security
- **CAPTCHA Protection** - Google reCAPTCHA v2 prevents automated spam
- **Bot Detection** - Distinguishes between human users and bots

## Performance

- **Real-time validation** - Debounced to prevent excessive requests
- **Efficient updates** - Only validates changed fields
- **Minimal JavaScript** - Leverages Livewire's efficient DOM diffing

## Troubleshooting

### Common Issues

1. **Styles not loading**: Ensure Tailwind CSS is properly configured
2. **Validation not working**: Check that Livewire is properly installed
3. **Form not submitting**: Verify database connection and migrations
4. **CAPTCHA not loading**: Check that your site key is correct and domain is registered
5. **CAPTCHA validation failing**: Verify your secret key is correct in the .env file
6. **CAPTCHA not resetting**: Ensure Alpine.js is loaded for the reset functionality

### CAPTCHA Issues

- **Domain mismatch**: Make sure your domain is added to the reCAPTCHA site configuration
- **Localhost testing**: Add `localhost` and `127.0.0.1` to your reCAPTCHA domains for local development
- **HTTPS requirement**: reCAPTCHA may require HTTPS in production environments
- **Script loading**: If CAPTCHA doesn't appear, check browser console for JavaScript errors

### Debug Mode

Enable debug mode to see detailed error messages:

```php
// In your .env file
APP_DEBUG=true
```

## Contributing

To contribute to this component:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This component is open-sourced software licensed under the MIT license.