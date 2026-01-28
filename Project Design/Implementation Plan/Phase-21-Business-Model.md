# Phase 21: Business Model Features

## 21.1 Freemium Tier Implementation
**Effort: Medium**

Implement limited free tier.

**Freemium Limits:**
- 1 location
- 5 active employees
- Basic scheduling only
- No time & attendance
- No reports
- Plannrly branding

**Tasks:**
- [ ] Add 'free' plan to subscription system
- [ ] Implement location limit check
- [ ] Implement employee limit check
- [ ] Disable premium features for free tier
- [ ] Add upgrade prompts at limit points
- [ ] Add Plannrly branding to free tier emails
- [ ] Create upgrade path UI
- [ ] Write tests

---

## 21.2 Per-Employee Pricing
**Effort: Medium**

Usage-based pricing component.

**Tasks:**
- [ ] Track active employees per billing period
- [ ] "Active" = scheduled or clocked in during period
- [ ] Create monthly usage calculation
- [ ] Integrate with Stripe metered billing
- [ ] Show usage in billing dashboard
- [ ] Send usage reports to tenants
- [ ] Write tests

---

## 21.3 Partner/Reseller Program
**Effort: Large**

Enable partners to refer and resell.

**Files to create:**
- `app/Models/Partner.php`
- `app/Models/PartnerReferral.php`
- `app/Http/Controllers/PartnerController.php`
- `resources/views/partner/dashboard.blade.php`

**Tasks:**
- [ ] Create partner and referral migrations
- [ ] Create partner registration flow
- [ ] Generate unique referral codes
- [ ] Track referrals to tenant signups
- [ ] Calculate commission based on revenue
- [ ] Create partner dashboard
- [ ] Show referred tenants and earnings
- [ ] Create payout request system
- [ ] Write tests

---

## 21.4 Professional Services
**Effort: Small**

Document and enable professional service offerings.

**Tasks:**
- [ ] Create services page listing offerings
- [ ] Add "Request Migration Assistance" form
- [ ] Add "Request Training Session" form
- [ ] Create inquiry routing to sales team
- [ ] Track service requests in admin panel
- [ ] Write tests
