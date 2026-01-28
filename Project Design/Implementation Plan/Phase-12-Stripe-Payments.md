# Phase 12: Stripe Payment Integration

## 12.1 Stripe Setup & Customer Management
**Effort: Large**

Core Stripe integration for payments.

**Dependencies:**
- Install Laravel Cashier: `composer require laravel/cashier`

**Database Changes:**
- [ ] Run Cashier migrations (creates customers, subscriptions, subscription_items tables)

**Files to create:**
- `app/Services/StripeService.php`
- `app/Http/Controllers/SubscriptionController.php`
- `app/Http/Controllers/StripeWebhookController.php`
- `config/stripe.php`

**Environment Variables:**
```
STRIPE_KEY=pk_...
STRIPE_SECRET=sk_...
STRIPE_WEBHOOK_SECRET=whsec_...
CASHIER_CURRENCY=usd
```

**Tasks:**
- [ ] Install and configure Laravel Cashier
- [ ] Create Stripe products and prices in Stripe Dashboard
- [ ] Create StripeService wrapper class
- [ ] Add Billable trait to Tenant model
- [ ] Create customer on tenant registration
- [ ] Create config file with plan/price mappings
- [ ] Write unit tests for Stripe service

---

## 12.2 Checkout & Subscription Flow
**Effort: Large**

Subscription purchase and management.

**Files to create:**
- `resources/views/subscription/index.blade.php`
- `resources/views/subscription/checkout.blade.php`
- `resources/views/subscription/success.blade.php`
- `resources/views/subscription/manage.blade.php`

**Tasks:**
- [ ] Create subscription overview page
- [ ] Implement Stripe Checkout for new subscriptions
- [ ] Create success/cancel return pages
- [ ] Implement plan change (upgrade/downgrade)
- [ ] Handle proration for mid-cycle changes
- [ ] Implement subscription cancellation
- [ ] Implement subscription resume (before end date)
- [ ] Add trial period support
- [ ] Write integration tests

---

## 12.3 Payment Method Management
**Effort: Medium**

Update and manage payment methods.

**Files to create:**
- `resources/views/subscription/payment-method.blade.php`

**Tasks:**
- [ ] Create payment method update form
- [ ] Implement Stripe Elements for card input
- [ ] Allow adding new payment method
- [ ] Set default payment method
- [ ] Remove old payment methods
- [ ] Show card last 4 digits and expiry
- [ ] Write tests

---

## 12.4 Invoice & Billing History
**Effort: Medium**

View and download invoices.

**Files to create:**
- `resources/views/subscription/invoices.blade.php`

**Tasks:**
- [ ] List invoice history
- [ ] Show invoice details (amount, date, status)
- [ ] Implement PDF invoice download
- [ ] Show upcoming invoice preview
- [ ] Display payment status indicators
- [ ] Write tests

---

## 12.5 Stripe Webhooks
**Effort: Medium**

Handle Stripe events for subscription lifecycle.

**Webhook Events to Handle:**
| Event | Action |
|-------|--------|
| customer.subscription.created | Activate subscription |
| customer.subscription.updated | Update subscription status |
| customer.subscription.deleted | Mark subscription cancelled |
| invoice.payment_succeeded | Log successful payment |
| invoice.payment_failed | Notify tenant, start grace period |
| customer.updated | Update customer info |

**Tasks:**
- [ ] Create webhook controller
- [ ] Implement signature verification
- [ ] Handle subscription.created event
- [ ] Handle subscription.updated event
- [ ] Handle subscription.deleted event
- [ ] Handle payment_succeeded event
- [ ] Handle payment_failed event
- [ ] Create PaymentFailedNotification
- [ ] Create GracePeriodNotification
- [ ] Sync tenant features based on subscription status
- [ ] Write webhook tests

---

## 12.6 Feature Add-on Purchases
**Effort: Medium**

Purchase and manage premium add-ons.

**Files to create:**
- `resources/views/subscription/addons.blade.php`

**Tasks:**
- [ ] Create add-on marketplace view
- [ ] Implement add-on purchase flow
- [ ] Add subscription item to existing subscription
- [ ] Remove add-on from subscription
- [ ] Show active add-ons on subscription page
- [ ] Update feature gates when add-ons change
- [ ] Write tests

---

## 12.7 Tenant Billing Details
**Effort: Medium**

Capture and manage tenant financial information for invoicing compliance.

**Database Changes:**
```php
Schema::create('tenant_billing_details', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('legal_name');
    $table->string('tax_id')->nullable(); // VAT/GST/TIN number
    $table->string('tax_id_type')->nullable(); // VAT, GST, TIN, etc.
    $table->string('company_registration_number')->nullable();
    $table->string('billing_email');
    $table->string('billing_phone')->nullable();
    $table->text('billing_address_line_1');
    $table->text('billing_address_line_2')->nullable();
    $table->string('billing_city');
    $table->string('billing_state')->nullable();
    $table->string('billing_postal_code');
    $table->string('billing_country', 2); // ISO country code
    $table->decimal('default_tax_rate', 5, 2)->default(0);
    $table->boolean('is_tax_exempt')->default(false);
    $table->string('tax_exempt_certificate')->nullable();
    $table->string('currency', 3)->default('USD');
    $table->string('payment_terms')->default('net_30');
    $table->text('invoice_notes')->nullable();
    $table->timestamps();
});
```

**Files to create:**
- `app/Models/TenantBillingDetail.php`
- `app/Http/Controllers/TenantBillingController.php`
- `app/Http/Requests/TenantBillingRequest.php`
- `resources/views/settings/billing-details.blade.php`

**Tasks:**
- [ ] Create migration for tenant_billing_details table
- [ ] Create TenantBillingDetail model with Tenant relationship
- [ ] Create TenantBillingRequest with validation rules
- [ ] Create TenantBillingController with CRUD operations
- [ ] Create billing details form view
- [ ] Add tax ID validation (format per country)
- [ ] Support multiple tax ID types (VAT, GST, TIN, etc.)
- [ ] Add billing details to tenant settings navigation
- [ ] Pre-populate from Stripe customer data if available
- [ ] Write feature tests

---

## 12.8 Ad-Hoc Invoice Management
**Effort: Large**

Create manual invoices with sequential numbering for regulatory compliance.

**Database Changes:**
```php
Schema::create('invoice_number_sequences', function (Blueprint $table) {
    $table->id();
    $table->integer('year');
    $table->string('type')->default('invoice'); // invoice, credit_note, etc.
    $table->string('prefix')->nullable();
    $table->integer('last_number')->default(0);
    $table->timestamps();
    $table->unique(['year', 'type', 'prefix']);
});

Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('invoice_number')->unique(); // 2026-001 format
    $table->string('type')->default('invoice'); // invoice, credit_note
    $table->string('status'); // draft, sent, paid, overdue, cancelled, void
    $table->foreignId('customer_tenant_id')->nullable()->constrained('tenants');
    $table->string('customer_name');
    $table->string('customer_email');
    $table->text('customer_address')->nullable();
    $table->string('customer_tax_id')->nullable();
    $table->date('issue_date');
    $table->date('due_date');
    $table->string('currency', 3)->default('USD');
    $table->decimal('subtotal', 12, 2);
    $table->decimal('tax_amount', 12, 2)->default(0);
    $table->decimal('discount_amount', 12, 2)->default(0);
    $table->decimal('total', 12, 2);
    $table->decimal('amount_paid', 12, 2)->default(0);
    $table->decimal('amount_due', 12, 2);
    $table->decimal('tax_rate', 5, 2)->default(0);
    $table->text('notes')->nullable();
    $table->text('terms')->nullable();
    $table->string('reference')->nullable(); // PO number, etc.
    $table->timestamp('sent_at')->nullable();
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
    $table->softDeletes();
});

Schema::create('invoice_items', function (Blueprint $table) {
    $table->id();
    $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
    $table->string('description');
    $table->decimal('quantity', 10, 2);
    $table->string('unit')->default('unit');
    $table->decimal('unit_price', 12, 2);
    $table->decimal('tax_rate', 5, 2)->default(0);
    $table->decimal('tax_amount', 12, 2)->default(0);
    $table->decimal('discount_percent', 5, 2)->default(0);
    $table->decimal('discount_amount', 12, 2)->default(0);
    $table->decimal('line_total', 12, 2);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});

Schema::create('invoice_payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
    $table->decimal('amount', 12, 2);
    $table->string('payment_method'); // cash, bank_transfer, check, stripe
    $table->string('reference')->nullable();
    $table->date('payment_date');
    $table->text('notes')->nullable();
    $table->foreignId('recorded_by')->constrained('users');
    $table->timestamps();
});
```

**Files to create:**
- `config/billing.php` - Platform company details and invoice settings
- `app/Models/Invoice.php`
- `app/Models/InvoiceItem.php`
- `app/Models/InvoicePayment.php`
- `app/Models/InvoiceNumberSequence.php`
- `app/Services/InvoiceService.php`
- `app/Services/InvoiceNumberGenerator.php`
- `app/Http/Controllers/SuperAdmin/InvoiceController.php`
- `app/Http/Requests/InvoiceRequest.php`
- `resources/views/super-admin/invoices/index.blade.php`
- `resources/views/super-admin/invoices/create.blade.php`
- `resources/views/super-admin/invoices/show.blade.php`
- `resources/views/super-admin/invoices/edit.blade.php`
- `resources/views/super-admin/invoices/pdf.blade.php`

**Platform Company Details (Invoice Issuer):**
```
Company: Checketts Propiedad SL
Tax ID: ESB42691550
Address: Calle Francisco Salzillo 9, Orihuela Costa, Alicante, 03189, Spain
```

**Environment Variables:**
```
BILLING_COMPANY_NAME="Checketts Propiedad SL"
BILLING_TAX_ID="ESB42691550"
BILLING_ADDRESS_LINE1="Calle Francisco Salzillo 9"
BILLING_CITY="Orihuela Costa"
BILLING_STATE="Alicante"
BILLING_POSTAL_CODE="03189"
BILLING_COUNTRY="Spain"
BILLING_COUNTRY_CODE="ES"
BILLING_DEFAULT_CURRENCY="EUR"
BILLING_DEFAULT_TAX_RATE="21.00"
```

**Invoice Number Generator:**
```php
class InvoiceNumberGenerator
{
    public function getNext(int $year = null, string $type = 'invoice'): string
    {
        $year = $year ?? now()->year;

        return DB::transaction(function () use ($year, $type) {
            $sequence = InvoiceNumberSequence::lockForUpdate()
                ->firstOrCreate(
                    ['year' => $year, 'type' => $type, 'prefix' => null],
                    ['last_number' => 0]
                );

            $sequence->increment('last_number');
            $number = str_pad($sequence->last_number, 3, '0', STR_PAD_LEFT);

            return "{$year}-{$number}";
        });
    }
}
```

**Tasks:**
- [ ] Create `config/billing.php` with platform company details
- [ ] Add billing environment variables to `.env.example`
- [ ] Create migrations for invoice tables
- [ ] Create Invoice model with relationships and scopes
- [ ] Create InvoiceItem model
- [ ] Create InvoicePayment model
- [ ] Create InvoiceNumberSequence model
- [ ] Create InvoiceNumberGenerator service with locking
- [ ] Create InvoiceService for invoice operations
- [ ] Create InvoiceController with CRUD
- [ ] Create InvoiceRequest with validation
- [ ] Create invoice list view with filtering
- [ ] Create invoice create/edit form with line items
- [ ] Implement dynamic line item addition (Alpine.js)
- [ ] Auto-calculate subtotal, tax, and total
- [ ] Create invoice detail view
- [ ] Create PDF invoice template with platform company details
- [ ] Include company name, tax ID, and address from config
- [ ] Implement invoice PDF generation (DomPDF)
- [ ] Implement send invoice via email
- [ ] Implement record payment functionality
- [ ] Create payment recording form
- [ ] Implement invoice status transitions
- [ ] Implement credit note creation (linked to original invoice)
- [ ] Implement invoice void (with reason)
- [ ] Create overdue invoice report
- [ ] Add invoice search and filtering
- [ ] Write comprehensive feature tests

---

## 12.9 EU Intra-Community VAT Compliance
**Effort: Large**

Implement EU VAT regulations for cross-border B2B transactions (required for Spanish company).

**VAT Treatment Rules:**
| Customer Type | Location | VAT Rate | Treatment |
|---------------|----------|----------|-----------|
| Business (B2B) | Spain | 21% | Domestic IVA |
| Business (B2B) | Other EU | 0% | Reverse Charge |
| Business (B2B) | Non-EU | 0% | Export (outside scope) |
| Consumer (B2C) | Spain | 21% | Domestic IVA |
| Consumer (B2C) | Other EU | 21% | Spanish IVA applies |
| Consumer (B2C) | Non-EU | 0% | Export (outside scope) |

**Files to create:**
- `app/Services/ViesValidationService.php` - VIES SOAP validation
- `app/Services/EuVatDeterminationService.php` - VAT rate logic
- `app/Http/Controllers/VatValidationController.php`
- `app/DTOs/ViesValidationResult.php`
- `app/DTOs/VatDetermination.php`
- `app/Enums/VatType.php`

**Database Changes:**
```php
// Add to tenant_billing_details
$table->string('billing_country_code', 2)->nullable();
$table->boolean('vat_validated')->default(false);
$table->timestamp('vat_validated_at')->nullable();
$table->string('vat_validation_request_id')->nullable();
$table->string('vat_company_name_from_vies')->nullable();

// Add to invoices
$table->string('vat_type')->nullable();
$table->boolean('reverse_charge')->default(false);
$table->string('vat_determination_reason')->nullable();
$table->string('customer_vat_number')->nullable();
$table->boolean('customer_vat_validated')->default(false);
$table->string('reverse_charge_text')->nullable();
```

**Tasks:**
- [ ] Create ViesValidationService using SOAP client
- [ ] Implement VIES WSDL connection with timeout handling
- [ ] Cache VIES validation results (24 hours)
- [ ] Create VatType enum (domestic, intra_eu_b2b, intra_eu_b2c, export)
- [ ] Create EuVatDeterminationService with tax logic
- [ ] Add EU country code list constant
- [ ] Create VatValidationController with validate/revalidate endpoints
- [ ] Add billing_country_code to tenant billing details form
- [ ] Implement real-time VAT number validation on input
- [ ] Display VIES company name/address after validation
- [ ] Store validation timestamp and request ID for audit
- [ ] Update invoice creation to use VAT determination service
- [ ] Auto-populate VAT fields when creating invoice
- [ ] Add reverse charge text to invoice PDF template
- [ ] Display both supplier and customer VAT numbers on invoice
- [ ] Add "VAT validated" badge on invoice
- [ ] Create scheduled command to re-validate VAT numbers quarterly
- [ ] Handle VIES service unavailability gracefully
- [ ] Create Modelo 349 export report (intra-community transactions)
- [ ] Log all VAT determinations for audit trail
- [ ] Write tests for all VAT scenarios
- [ ] Test with real EU VAT numbers in staging

**Reverse Charge Invoice Text:**
```
"Reverse charge - Article 196 Council Directive 2006/112/EC"
"The Client is responsible for tax payment to their local tax authority."
```
