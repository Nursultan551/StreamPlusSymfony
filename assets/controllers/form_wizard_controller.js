import { Controller } from "@hotwired/stimulus";

// Stimulus controller for managing a multi-step onboarding form.
export default class extends Controller {
  // Define the DOM targets for form steps and summary review fields.
  static targets = [
    "step",
    "reviewName",
    "reviewEmail",
    "reviewPhone",
    "reviewSubscriptionType",
    "reviewAddressLine1",
    "reviewAddressLine2",
    "reviewCity",
    "reviewPostalCode",
    "reviewState",
    "reviewCountry",
    "reviewCreditCardNumber",
    "reviewExpirationDate",
    "paymentInfo",
  ];

  // Initialize controller state and form data when the component is connected.
  connect() {
    // Set the starting step index.
    this.currentStepIndex = 0;
    // Initialize form data with default empty values.
    this.formData = {
      name: "",
      email: "",
      phone: "",
      subscriptionType: "",
      addressLine1: "",
      addressLine2: "",
      city: "",
      postalCode: "",
      state: "",
      country: "",
      creditCardNumber: "",
      expirationDate: "",
      cvv: "",
    };
    // Display the first step in the form.
    this.showStep(this.currentStepIndex);
  }

  // Display a specific step by its index and trigger summary update when necessary.
  showStep(index) {
    // Loop through each step and toggle its visibility.
    this.stepTargets.forEach((step, i) => {
      step.classList.toggle("d-none", i !== index);
    });
    // When reaching the final review step (assumed index 3), fill in the summary and mask credit card details.
    if (index === 3) {
      this.fillSummary();
      this.maskCreditCard();
    }
  }

  // Mask the credit card number except for the last 4 digits.
  maskCreditCard() {
    // Get the credit card number from formData and trim extra spaces.
    let ccNumber = (this.formData.creditCardNumber || "").trim();
    if (!ccNumber) return; // Exit if no credit card number is provided.
    // Replace all digits except the last 4 with asterisks.
    if (ccNumber.length > 4) {
      const last4 = ccNumber.slice(-4);
      ccNumber = ccNumber.slice(0, -4).replace(/\d/g, "*") + last4;
    }
    // Update the review target element with the masked credit card number.
    this.reviewCreditCardNumberTarget.textContent = ccNumber;
  }

  // Process moving to the next step in the form.
  next() {
    // Retrieve subscription type to check if payment step should be skipped later.
    const subscriptionType = this.element.querySelector("#subscriptionType")?.value;
    // Save current step's data.
    this.saveStepData(this.currentStepIndex);
    // Clear any previous field errors.
    this.clearFieldErrors();

    // Build payload for server-side validation.
    const payload = {
      step: this.currentStepIndex,
      wizard: this.formData,
    };

    // Validate current step data via a fetch request.
    fetch("/onboarding/validate-step", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(payload),
    })
      .then((response) => {
        if (!response.ok) {
          // Handle potential validation errors (e.g., HTTP 422).
          if (response.status === 422 && response.errors) {
            this.showFieldErrors(response.errors);
            return;
          }
        }
        return response.json();
      })
      .then((data) => {
        // If server returns errors, display them without proceeding.
        if (data.errors) {
          this.showFieldErrors(data.errors);
          return;
        }
        // If validation passes and there are remaining steps, increment step index.
        if (this.currentStepIndex < this.stepTargets.length - 1) {
          this.currentStepIndex++;
          // Skip the payment step (index 2) if the subscription type is "free".
          if (subscriptionType === "free" && this.currentStepIndex === 2) {
            this.currentStepIndex++;
          }
          // Display the next form step.
          this.showStep(this.currentStepIndex);
        }
      })
      .catch((error) => {
        console.error("Validation request failed:", error);
        alert("Unable to validate step. Try again later.");
      });
  }

  // Move to the previous step in the form.
  previous() {
    if (this.currentStepIndex > 0) {
      // Save data from the current step before navigating backwards.
      this.saveStepData(this.currentStepIndex);
      this.currentStepIndex--;
      // Skip the payment step if subscription is free.
      if (this.formData.subscriptionType === "free" && this.currentStepIndex === 2) {
        this.currentStepIndex--;
      }
      // Display the previous step.
      this.showStep(this.currentStepIndex);
    }
  }

  // Clear any error messages and remove error highlighting from form fields.
  clearFieldErrors() {
    // Clear text content from all error containers.
    const errorContainers = this.element.querySelectorAll("[data-field-error-target]");
    errorContainers.forEach((el) => (el.textContent = ""));
    // Remove the "is-invalid" class from any input fields.
    const invalidFields = this.element.querySelectorAll(".is-invalid");
    invalidFields.forEach((field) => field.classList.remove("is-invalid"));
  }

  // Display error messages for specific fields based on the server's response.
  showFieldErrors(errors) {
    Object.keys(errors).forEach((fieldName) => {
      const message = errors[fieldName];
      // Locate the error container using the field's data attribute.
      const errorContainer = this.element.querySelector(`[data-field-error-target="${fieldName}"]`);
      if (errorContainer) {
        // If the error is an array, join messages with a separator.
        errorContainer.textContent = Array.isArray(message) ? message.join(" | ") : message;
      }
      // Add error styling to the corresponding input field.
      const input = this.element.querySelector(`#${fieldName}`);
      if (input) {
        input.classList.add("is-invalid");
      }
    });
  }

  // Placeholder for handling subscription changes if needed.
  subscriptionChanged(event) {
    // This function can be expanded to dynamically modify the form based on subscription changes.
  }

  // Save data entered in the current step to the formData object.
  saveStepData(stepIndex) {
    switch (stepIndex) {
      case 0:
        // Save basic user information.
        this.formData.name = this.element.querySelector("#name")?.value || "";
        this.formData.email = this.element.querySelector("#email")?.value || "";
        this.formData.phone = this.element.querySelector("#phone")?.value || "";
        this.formData.subscriptionType = this.element.querySelector("#subscriptionType")?.value || "";
        break;
      case 1:
        // Save address-related information.
        this.formData.addressLine1 = this.element.querySelector("#addressLine1")?.value || "";
        this.formData.addressLine2 = this.element.querySelector("#addressLine2")?.value || "";
        this.formData.city = this.element.querySelector("#city")?.value || "";
        this.formData.postalCode = this.element.querySelector("#postalCode")?.value || "";
        this.formData.state = this.element.querySelector("#state")?.value || "";
        this.formData.country = this.element.querySelector("#country")?.value || "";
        break;
      case 2:
        // Save payment information.
        this.formData.creditCardNumber = this.element.querySelector("#creditCardNumber")?.value || "";
        this.formData.expirationDate = this.element.querySelector("#expirationDate")?.value || "";
        this.formData.cvv = this.element.querySelector("#cvv")?.value || "";
        break;
    }
  }

  // Populate the summary page with the data stored in formData.
  fillSummary() {
    this.reviewNameTarget.textContent = this.formData.name;
    this.reviewEmailTarget.textContent = this.formData.email;
    this.reviewPhoneTarget.textContent = this.formData.phone;
    this.reviewSubscriptionTypeTarget.textContent = this.formData.subscriptionType;
    this.reviewAddressLine1Target.textContent = this.formData.addressLine1;
    this.reviewAddressLine2Target.textContent = this.formData.addressLine2;
    this.reviewCityTarget.textContent = this.formData.city;
    this.reviewPostalCodeTarget.textContent = this.formData.postalCode;
    this.reviewStateTarget.textContent = this.formData.state;
    this.reviewCountryTarget.textContent = this.formData.country;
    this.reviewExpirationDateTarget.textContent = this.formData.expirationDate;
    // Conditionally display the payment info section based on the subscription type.
    if (this.formData.subscriptionType === "premium") {
      this.paymentInfoTarget.classList.remove("d-none");
    } else {
      this.paymentInfoTarget.classList.add("d-none");
    }
  }

  // Handle final form submission.
  submitForm(event) {
    // Prevent the default form submission behavior.
    event.preventDefault();
    // Save the data from the current (final) step.
    this.saveStepData(this.currentStepIndex);
    // Package the form data for submission.
    const payload = { wizard: this.formData };

    // Submit the data to the server.
    fetch("/onboarding/create", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        // Include CSRF token for Rails apps.
        "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(payload),
    })
      .then((response) => {
        if (!response.ok) {
          throw new Error("Network response was not ok: " + response.statusText);
        }
        return response.json();
      })
      .then((data) => {
        console.log("Data successfully saved to DB:", data);
        // Redirect user upon successful submission.
        window.location.href = "/?success=1";
      })
      .catch((error) => {
        console.error("Error saving data:", error);
        alert("Oops. Something went wrong while saving your data.");
      });
  }

  // Format the expiration date input into MM/YY format.
  formatExpirationDate(event) {
    const input = event.target;
    // Remove all non-digit characters.
    let value = input.value.replace(/\D/g, "");
    // Insert a slash after the first two digits if applicable.
    if (value.length >= 3) {
      value = value.substring(0, 2) + "/" + value.substring(2, 4);
    }
    // Update the input field and formData with the formatted date.
    input.value = value;
    this.formData.expirationDate = value;
  }

  // Log changes when the country selection changes.
  countryChanged(event) {
    console.log("Country changed to:", event.target.value);
  }
}
