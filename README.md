<p align="center">
  <a href="" rel="noopener">
 <img width=200px height=200px src="https://i.imgur.com/6wj0hh6.jpg" alt="Project logo"></a>
</p>

<h3 align="center">Stream Plus</h3>

---

## 📝 Table of Contents

- [Local Deployment Instructions](#local_deployment_instructions)
- [Prerequisites](#getting_started)
- [Installation](#deployment)
- [Database Setup](#usage)
- [Running the Application](#built_using)
- [Additional Commands](#additional_commands)
- [Troubleshooting](../CONTRIBUTING.md)
- [Implemented Features](#authors)
- [Areas to Impove](#acknowledgement)

# Local Deployment Instructions

These instructions will help you set up and run the application locally.

## Prerequisites

- **PHP 8.1+** (or the required PHP version for your project)
- **Composer** – for PHP dependencies  
- **Node.js and npm/yarn** – if you are using frontend tooling  
- **Symfony CLI** (optional but recommended)  
- **MySQL/PostgreSQL/SQLite** – your chosen database (configured in your `.env` file)

## Installation

1. **Clone the repository:**

   ```bash
   git clone git@github.com:Nursultan551/StreamPlusSymfony.git
   cd streamplus-onboarding-symfony
   ```

2. **Install PHP dependencies:**

   ```bash
   composer install
   ```

3. **Install frontend dependencies** (if applicable):

   ```bash
   npm install
   ```
4. **Build artifacts** (if applicable):

   ```bash
   npm run dev
   ```

5. **Configure your environment variables:**

   - Copy the `.env` file to `.env.local`:
   
     ```bash
     cp .env .env.local
     ```
   
   - Update your database connection string and any other environment-specific variables in `.env.local`.

## Database Setup

1. **Create the database:**

   ```bash
   php bin/console doctrine:database:create
   ```

2. **Run Doctrine Migrations:**

   Generate a migration if needed:
   
   ```bash
   php bin/console doctrine:migrations:diff
   ```
   
   Then execute migrations:
   
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

## Running the Application

1. **Using Symfony CLI:**

   ```bash
   symfony serve
   ```

   The app will be available at [http://localhost:8000](http://localhost:8000).

2. **Using PHP's built-in web server:**

   ```bash
   php -S localhost:8000 -t public
   ```

   Then, open [http://localhost:8000](http://localhost:8000) in your browser.

## Additional Commands

- **Clear Cache:**

  ```bash
  php bin/console cache:clear
  ```

- **Run Tests (if configured):**

  ```bash
  php bin/phpunit
  ```

## Troubleshooting

- If you encounter database connection errors, double-check your `.env.local` settings.
- For asset issues, try running:

  ```bash
  yarn encore dev --watch
  ```
  or the appropriate build command for your frontend stack.

---

## Implemented Features

- **Multi-Step Onboarding Wizard:**  
  - A series of steps for user input including User Information, Address Information, Payment Information, and a Review/Confirmation step.
  
- **Client-Side Form Handling:**  
  - Stimulus-based controller to manage form navigation, data collection, and inline error display.
  - Automatic navigation logic to skip the Payment step for “free” subscription types.
  - “Previous” button logic that intelligently skips the hidden payment step when returning from the review.
  
- **Inline Error Messaging:**  
  - Each form field has a dedicated error container that displays server-returned validation errors immediately beneath the input.
  
- **Real-Time Field Formatting:**  
  - Auto-formatting for the expiration date field to insert a slash ("/") after two digits, ensuring it remains in MM/YY format.
  
- **Server-Side Validation:**  
  - Symfony Validator integration for validating User, Address, and Payment entities.
  - Custom callback in the Payment entity to validate the expiration date ensuring:
    - The month is between 01 and 12.
    - The year is a valid two-digit value (00 to 99) and the expiration date is in the future.
  
- **Field-Specific Validation Constraints:**  
  - **User Entity:**  
    - Ensures required fields (name, email, phone) are not blank.
    - Uses a UniqueEntity constraint to ensure the email is unique.
    - Validates the phone field for correct digit count (with exactly 10–15 characters allowed, if required).
  - **Address Entity:**  
    - Validates address fields (address line, city, postal code, state, country) using NotBlank and Length constraints.
    - Optionally applies Regex constraints (e.g., for numeric postal codes) based on business rules.
  - **Payment Entity:**  
    - Enforces that the credit card number is exactly 16 digits (using Length and Regex constraints).
    - Uses Regex and Callback constraints to ensure the expiration date is in MM/YY format and represents a future date.
    - Validates CVV to ensure it contains only digits and is exactly 3 or 4 digits long.
  
- **Server-Side Validation Endpoint:**  
  - A dedicated `/onboarding/validate-step` endpoint that validates partial wizard data and returns a structured JSON response with field-specific errors.
  
- **CSRF Protection (basic):**  
  - CSRF token validation is performed on form submission to ensure secure data handling.
  
- **Database Schema Management:**  
  - Doctrine migrations are used to automatically update the database schema based on entity changes.
  
- **Post-Submission Handling:**  
  - After successful submission, the application automatically redirects to the main page (`/`) and displays a success/thank-you message.
  
- **Deployment and Local Setup Documentation:**  
  - Detailed instructions provided in the README for local deployment, including prerequisites, environment configuration, and database setup.

  ---

## Potential areas to improve or pending features for future implementation

- **Enhanced Error Handling and UX:**  
  - Improve user feedback for validation errors with more dynamic inline displays (e.g., real-time validation).
  - Consider animations or tooltips for better error explanations.
  
- **Internationalization and Localization:**  
  - Expand validations for postal codes and phone numbers to support international formats.
  - Localize error messages and UI text.

- **Improved Payment Integration:**  
  - Integrate with an external payment gateway for secure payment processing.
  - Implement real-time card type detection and formatting for credit card numbers.

- **Accessibility Enhancements:**  
  - Ensure the wizard meets accessibility standards (ARIA attributes, keyboard navigation, etc.).

- **Robust Testing:**  
  - Add automated tests (unit, integration, and end-to-end) for both client-side and server-side logic.
  
- **Performance Optimizations:**  
  - Optimize form data handling and reduce potential network overhead (e.g., batch validations).

- **Enhanced Security:**  
  - Audit CSRF and data validation flows for edge cases.
  - Consider implementing rate limiting on validation endpoints.
  - Implement other security measures as per OWASP standards

- **User Experience Enhancements:**  
  - Provide a progress indicator or step tracker to guide the user through the onboarding process.
  - Implement a save-and-resume feature in case users need to continue later.